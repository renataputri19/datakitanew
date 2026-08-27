<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use GuzzleHttp\Client;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The in-app gate. Every request to a dev app passes through here, so this is
 * now the only thing between an anonymous visitor and somebody else's
 * application — the same job the Traefik forwardAuth endpoint had.
 *
 * The two properties worth more than the rest, and the reason most of these
 * tests exist:
 *   - DataKita's session cookie must never reach the dev app;
 *   - the identity headers must be set by us, never passed through.
 */
class DevAppProxyTest extends TestCase
{
    use RefreshDatabase;

    /** Requests the proxy actually sent upstream. */
    private array $history = [];

    /**
     * The proxy route is registered at boot, conditional on the master switch,
     * so config()->set() in setUp() would come far too late — the route table
     * is already built. The switch has to be in the environment before the
     * application boots.
     */
    public function createApplication(): Application
    {
        putenv('DEVAPPS_ENABLED=true');
        $_ENV['DEVAPPS_ENABLED'] = $_SERVER['DEVAPPS_ENABLED'] = 'true';

        $app = require __DIR__ . '/../../bootstrap/app.php';
        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function tearDown(): void
    {
        // Do not leak the master switch into the rest of the suite.
        putenv('DEVAPPS_ENABLED');
        unset($_ENV['DEVAPPS_ENABLED'], $_SERVER['DEVAPPS_ENABLED']);

        parent::tearDown();
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    /**
     * Put a canned upstream response (or exception) in front of the proxy and
     * record what it sent.
     */
    private function upstreamReturns(PsrResponse|ConnectException ...$responses): void
    {
        $mock  = new MockHandler($responses);
        $stack = HandlerStack::create($mock);

        $stack->push(Middleware::history($this->history));

        $this->app->instance(ClientInterface::class, new Client(['handler' => $stack]));
    }

    private function sentRequest(int $index = 0): PsrRequest
    {
        $this->assertArrayHasKey($index, $this->history, 'The proxy sent no request upstream.');

        return $this->history[$index]['request'];
    }

    private function makeApp(array $attributes = []): DevApp
    {
        $app = DevApp::create(array_merge([
            'slug'           => 'survei-listrik',
            'name'           => 'Survei Listrik',
            'owner_user_id'  => User::factory()->create()->id,
            'git_repo'       => 'https://github.com/example/app.git',
            'git_branch'     => 'main',
            'auth_mode'      => DevApp::AUTH_PUBLIC,
            'container_port' => 3000,
            'strip_prefix'   => true,
        ], $attributes));

        // Assigned by Dokploy at provision time; it is the container hostname.
        $app->forceFill(['dokploy_app_name' => 'survei-listrik-ab12cd-xy'])->save();

        return $app->fresh();
    }

    // ── Access decisions ────────────────────────────────────────────────

    public function test_public_app_is_reachable_anonymously(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);
        $this->upstreamReturns(new PsrResponse(200, [], 'halo dari aplikasi'));

        $response = $this->get('/' . $app->slug);

        $response->assertOk();
        $this->assertSame('halo dari aplikasi', $response->streamedContent());
    }

    public function test_anonymous_visitor_to_a_gated_app_is_bounced_to_login(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);
        $this->upstreamReturns(new PsrResponse(200));

        $response = $this->get('/' . $app->slug . '/laporan?bulan=3');

        $response->assertRedirect(route('login'));
        // And it remembers where they were headed, as a relative URI — an
        // absolute one rebuilt from the request host would be spoofable.
        $this->assertSame('/survei-listrik/laporan?bulan=3', session('url.intended'));
        $this->assertSame([], $this->history, 'A denied request must never reach the app.');
    }

    public function test_logged_in_visitor_is_refused_an_app_they_do_not_own(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_OWNER_ONLY]);
        $this->upstreamReturns(new PsrResponse(200));

        $response = $this->actingAs(User::factory()->create())->get('/' . $app->slug);

        $response->assertForbidden();
        $this->assertSame([], $this->history, 'A denied request must never reach the app.');
    }

    public function test_disabled_app_is_closed_even_to_its_owner(): void
    {
        $owner = User::factory()->create();
        $app   = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_OWNER_ONLY,
            'owner_user_id' => $owner->id,
            'enabled'       => false,
        ]);
        $this->upstreamReturns(new PsrResponse(200));

        $this->actingAs($owner)->get('/' . $app->slug)->assertStatus(503);
        $this->assertSame([], $this->history);
    }

    // ── The cookie boundary, which is the whole point ───────────────────

    public function test_datakita_session_cookie_is_never_forwarded(): void
    {
        $app  = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);
        $user = User::factory()->create();
        $this->upstreamReturns(new PsrResponse(200));

        $this->actingAs($user)
            ->withUnencryptedCookie(config('session.cookie'), 'rahasia-sekali')
            ->withUnencryptedCookie('XSRF-TOKEN', 'token-datakita')
            ->withUnencryptedCookie('remember_web_abc123', 'ingat-saya')
            ->get('/' . $app->slug);

        $forwarded = $this->sentRequest()->getHeaderLine('Cookie');

        // The dev app is third-party code on our origin. With the session
        // token it could act as the visitor against DataKita itself.
        $this->assertStringNotContainsString('rahasia-sekali', $forwarded);
        $this->assertStringNotContainsString('token-datakita', $forwarded);
        $this->assertStringNotContainsString('ingat-saya', $forwarded);
        $this->assertStringNotContainsString(config('session.cookie'), $forwarded);
    }

    public function test_the_app_still_gets_its_own_cookies(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);
        $this->upstreamReturns(new PsrResponse(200));

        // withCookie(), not withUnencryptedCookie(): a cookie the browser holds
        // for this origin was encrypted by DataKita on its way out, so that is
        // the only shape one can arrive in.
        $this->withCookie('app_session', 'sesi-milik-aplikasi')
            ->get('/' . $app->slug);

        // An app that cannot keep a session is an app nobody can build. Only
        // DataKita's own cookies are held back.
        $this->assertStringContainsString(
            'app_session=sesi-milik-aplikasi',
            $this->sentRequest()->getHeaderLine('Cookie'),
        );
    }

    // ── Identity headers ────────────────────────────────────────────────

    public function test_identity_headers_describe_the_logged_in_user(): void
    {
        $app  = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);
        $user = User::factory()->create(['name' => 'Renata', 'email' => 'renata@bps.go.id']);
        $this->upstreamReturns(new PsrResponse(200));

        $this->actingAs($user)->get('/' . $app->slug);

        $sent = $this->sentRequest();
        $this->assertSame((string) $user->id, $sent->getHeaderLine('X-Datakita-User-Id'));
        $this->assertSame('Renata', $sent->getHeaderLine('X-Datakita-User-Name'));
        $this->assertSame('renata@bps.go.id', $sent->getHeaderLine('X-Datakita-User-Email'));
    }

    public function test_identity_headers_are_present_but_empty_for_an_anonymous_visitor(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);
        $this->upstreamReturns(new PsrResponse(200));

        $this->get('/' . $app->slug);

        $sent = $this->sentRequest();

        // Present-but-empty, not absent: an app that merely checks for the
        // header's existence must not be fooled by a client that supplies it.
        foreach (['Id', 'Name', 'Email', 'Role'] as $part) {
            $this->assertTrue($sent->hasHeader("X-Datakita-User-{$part}"));
            $this->assertSame('', $sent->getHeaderLine("X-Datakita-User-{$part}"));
        }
    }

    public function test_client_supplied_identity_headers_are_discarded(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);
        $this->upstreamReturns(new PsrResponse(200));

        $this->get('/' . $app->slug, [
            'X-Datakita-User-Id'    => 'admin-uuid-yang-dipalsukan',
            'X-Datakita-User-Email' => 'superadmin@bps.go.id',
        ]);

        $sent = $this->sentRequest();
        $this->assertSame('', $sent->getHeaderLine('X-Datakita-User-Id'));
        $this->assertSame('', $sent->getHeaderLine('X-Datakita-User-Email'));
    }

    // ── Forwarding mechanics ────────────────────────────────────────────

    public function test_request_is_forwarded_to_the_container_with_the_slug_stripped(): void
    {
        $app = $this->makeApp(['strip_prefix' => true]);
        $this->upstreamReturns(new PsrResponse(200));

        $this->get('/' . $app->slug . '/laporan/2026?format=pdf');

        $this->assertSame(
            'http://survei-listrik-ab12cd-xy:3000/laporan/2026?format=pdf',
            (string) $this->sentRequest()->getUri(),
        );
    }

    public function test_app_that_knows_its_own_base_path_keeps_the_prefix(): void
    {
        $app = $this->makeApp(['strip_prefix' => false]);
        $this->upstreamReturns(new PsrResponse(200));

        $this->get('/' . $app->slug . '/laporan');

        $this->assertSame(
            'http://survei-listrik-ab12cd-xy:3000/survei-listrik/laporan',
            (string) $this->sentRequest()->getUri(),
        );
    }

    public function test_post_body_and_method_survive_the_hop(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(201));

        $this->post('/' . $app->slug . '/simpan', ['nama' => 'uji']);

        $sent = $this->sentRequest();
        $this->assertSame('POST', $sent->getMethod());
        $this->assertStringContainsString('nama=uji', (string) $sent->getBody());
    }

    public function test_hop_by_hop_headers_are_dropped_from_the_response(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(200, [
            'Connection'        => 'keep-alive',
            'Transfer-Encoding' => 'chunked',
            'X-App-Header'      => 'dipertahankan',
        ]));

        $response = $this->get('/' . $app->slug);

        $response->assertHeaderMissing('Connection');
        $response->assertHeaderMissing('Transfer-Encoding');
        $response->assertHeader('X-App-Header', 'dipertahankan');
    }

    public function test_redirects_are_rewritten_to_stay_under_the_slug(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(302, ['Location' => '/login']));

        $this->get('/' . $app->slug . '/rahasia')
            ->assertHeader('Location', '/survei-listrik/login');
    }

    public function test_a_redirect_already_under_the_slug_is_left_alone(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(302, ['Location' => '/survei-listrik/beranda']));

        $this->get('/' . $app->slug)
            ->assertHeader('Location', '/survei-listrik/beranda');
    }

    public function test_an_external_redirect_is_not_rewritten(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(302, ['Location' => 'https://bps.go.id/']));

        $this->get('/' . $app->slug)
            ->assertHeader('Location', 'https://bps.go.id/');
    }

    public function test_dev_app_cannot_overwrite_datakita_session_cookies(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(200, [
            'Set-Cookie' => [
                config('session.cookie') . '=sesi-yang-dibajak; Path=/',
                'XSRF-TOKEN=token-palsu; Path=/',
                'app_preference=gelap; Path=/',
            ],
        ]));

        $response = $this->get('/' . $app->slug);

        // The app shares our origin, so its Set-Cookie lands on our domain.
        // Its own cookies are fine; ours are not its to write — a dev app
        // that could set `datakita_session` could fixate a visitor's session.
        $joined = implode(' | ', $response->headers->all('set-cookie'));

        $this->assertStringNotContainsString('sesi-yang-dibajak', $joined);
        $this->assertStringNotContainsString('token-palsu', $joined);

        // Its own cookie survives. The value is ciphertext here because
        // EncryptCookies runs on the way out — and decrypts on the way back
        // in, so the app sees its own plaintext again. See
        // ProxyController::forwardableCookies().
        $this->assertStringContainsString('app_preference=', $joined);
    }

    public function test_an_apps_cookie_round_trips_back_to_it_in_plaintext(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(
            new PsrResponse(200, ['Set-Cookie' => 'app_preference=gelap; Path=/']),
            new PsrResponse(200),
        );

        $first = $this->get('/' . $app->slug);

        // Replay the cookie the browser would now hold, exactly as stored.
        $stored = $first->headers->getCookies()[0];
        $this->withUnencryptedCookie($stored->getName(), $stored->getValue())
            ->get('/' . $app->slug);

        $this->assertStringContainsString(
            'app_preference=gelap',
            $this->sentRequest(1)->getHeaderLine('Cookie'),
        );
    }

    // ── Failure modes ───────────────────────────────────────────────────

    public function test_a_dead_container_gives_a_readable_page_not_a_stack_trace(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new ConnectException(
            'Connection refused',
            new PsrRequest('GET', 'http://survei-listrik-ab12cd-xy:3000/'),
        ));

        $response = $this->get('/' . $app->slug);

        $response->assertStatus(502);
        $response->assertSee('Aplikasi tidak merespons');
        $response->assertDontSee('ConnectException');
        $response->assertDontSee('vendor/guzzlehttp');
    }

    public function test_an_app_that_was_never_provisioned_is_not_proxied_to(): void
    {
        $app = $this->makeApp();
        $app->forceFill(['dokploy_app_name' => null])->save();
        $this->upstreamReturns(new PsrResponse(200));

        $this->get('/' . $app->slug)->assertStatus(503);
        $this->assertSame([], $this->history);
    }

    public function test_the_apps_own_error_status_is_passed_through(): void
    {
        $app = $this->makeApp();
        $this->upstreamReturns(new PsrResponse(422, [], 'validasi gagal'));

        $this->get('/' . $app->slug)->assertStatus(422);
    }

    // ── Not shadowing DataKita ──────────────────────────────────────────

    public function test_an_unclaimed_slug_still_falls_back_to_datakita_home(): void
    {
        $this->upstreamReturns(new PsrResponse(200));

        // The catch-all is matched ahead of Route::fallback(), so without the
        // explicit hand-back this would have taken over the whole site's 404.
        $this->get('/halaman-yang-tidak-ada')->assertRedirect(route('home'));
        $this->assertSame([], $this->history);
    }

    public function test_the_proxy_never_shadows_a_real_datakita_route(): void
    {
        // A dev app cannot claim these — DevApp::slugIsReserved() derives the
        // blocklist from the route table — but the route ordering must hold
        // regardless, because the row could predate a new DataKita route.
        $this->makeApp(['slug' => 'news']);
        $this->upstreamReturns(new PsrResponse(200, [], 'aplikasi pihak ketiga'));

        $response = $this->get('/news');

        $this->assertSame([], $this->history, 'DataKita\'s own route must win.');
        $response->assertDontSee('aplikasi pihak ketiga');
    }

    public function test_proxy_route_is_exempt_from_datakita_csrf(): void
    {
        // The dev app runs its own framework with its own tokens. If this
        // regressed, every POST into a dev app would 419.
        $route = $this->app['router']->getRoutes()->getByName('develop.proxy');

        $this->assertNotNull($route, 'The proxy route should be registered when the portal is on.');
        $this->assertContains(
            \App\Http\Middleware\VerifyCsrfToken::class,
            $route->excludedMiddleware(),
        );
    }
}
