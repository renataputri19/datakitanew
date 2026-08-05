<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The edge authorisation endpoint is the only thing standing between an
 * anonymous visitor and somebody else's application, so every branch of it
 * gets a test — including the ones that must say no.
 *
 * Traefik's contract:
 *   2xx → forward the request (and copy the identity headers onto it)
 *   3xx → return the redirect to the browser
 *   4xx → return the error to the browser
 */
class DevAppAuthzTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('devapps.enabled', true);
        config()->set('devapps.public_host_url', 'https://datakita.test');
    }

    // ── Helpers ─────────────────────────────────────────────────────────

    private function makeApp(array $attributes = []): DevApp
    {
        return DevApp::create(array_merge([
            'slug'          => 'survei-listrik',
            'name'          => 'Survei Listrik',
            'owner_user_id' => User::factory()->create()->id,
            'git_repo'      => 'https://github.com/example/app.git',
            'git_branch'    => 'main',
            'auth_mode'     => DevApp::AUTH_LOGIN_REQUIRED,
        ], $attributes));
    }

    private function authz(DevApp $app)
    {
        return $this->get("/develop/authz/{$app->slug}");
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->setRole($role);
        $user->save();

        return $user;
    }

    // ── Public apps ─────────────────────────────────────────────────────

    public function test_public_app_allows_anonymous_visitors(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);

        $this->authz($app)->assertOk();
    }

    public function test_public_app_sends_empty_identity_headers_for_anonymous_visitors(): void
    {
        // Empty rather than absent: an absent header would be passed straight
        // through from the client, letting a visitor claim to be anyone.
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);

        $this->authz($app)
            ->assertOk()
            ->assertHeader('X-Datakita-User-Id', '')
            ->assertHeader('X-Datakita-User-Role', '');
    }

    // ── Login-gated apps ────────────────────────────────────────────────

    public function test_anonymous_visitor_is_redirected_towards_login(): void
    {
        // Via /develop/masuk rather than straight to /login: the gate runs
        // inside Traefik's forwardAuth subrequest and cannot reliably set a
        // session cookie on the browser, so the intended URL is carried in the
        // URL and recorded by that landing step instead.
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);

        $this->assertSame(
            route('develop.masuk', ['next' => $app->publicUrl()], false),
            (string) $this->authz($app)->headers->get('Location'),
        );
    }

    public function test_authenticated_user_is_allowed_and_gets_identity_headers(): void
    {
        $app  = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);
        $user = $this->userWithRole(User::ROLE_MITRA);

        $this->actingAs($user)
            ->authz($app)
            ->assertOk()
            ->assertHeader('X-Datakita-User-Id', (string) $user->id)
            ->assertHeader('X-Datakita-User-Email', $user->email)
            ->assertHeader('X-Datakita-User-Role', User::ROLE_MITRA);
    }

    // ── Role-gated apps ─────────────────────────────────────────────────

    public function test_role_mode_allows_a_listed_role(): void
    {
        $app = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_ROLE,
            'allowed_roles' => [User::ROLE_ADMIN],
        ]);

        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))
            ->authz($app)
            ->assertOk();
    }

    public function test_role_mode_rejects_an_unlisted_role(): void
    {
        $app = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_ROLE,
            'allowed_roles' => [User::ROLE_ADMIN],
        ]);

        $this->actingAs($this->userWithRole(User::ROLE_MITRA))
            ->authz($app)
            ->assertForbidden();
    }

    public function test_role_mode_with_an_empty_list_rejects_everyone(): void
    {
        // Fail closed: a half-configured app must not be wide open.
        $app = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_ROLE,
            'allowed_roles' => [],
        ]);

        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))
            ->authz($app)
            ->assertForbidden();
    }

    // ── Allowlist apps ──────────────────────────────────────────────────

    public function test_allowlist_mode_allows_the_owner_without_an_explicit_grant(): void
    {
        $owner = $this->userWithRole(User::ROLE_ADMIN);
        $app   = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_ALLOWLIST,
            'owner_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)->authz($app)->assertOk();
    }

    public function test_allowlist_mode_allows_a_granted_user_and_rejects_others(): void
    {
        $app     = $this->makeApp(['auth_mode' => DevApp::AUTH_ALLOWLIST]);
        $granted = $this->userWithRole(User::ROLE_MITRA);
        $other   = $this->userWithRole(User::ROLE_MITRA);

        $app->allowedUsers()->attach($granted->id);

        $this->actingAs($granted)->authz($app)->assertOk();
        $this->actingAs($other)->authz($app)->assertForbidden();
    }

    // ── Owner-only apps ─────────────────────────────────────────────────

    public function test_owner_only_mode_rejects_everyone_but_the_owner(): void
    {
        $owner = $this->userWithRole(User::ROLE_ADMIN);
        $app   = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_OWNER_ONLY,
            'owner_user_id' => $owner->id,
        ]);

        $this->actingAs($owner)->authz($app)->assertOk();
        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))->authz($app)->assertForbidden();
    }

    // ── Kill switches ───────────────────────────────────────────────────

    public function test_disabled_app_rejects_even_its_owner(): void
    {
        $owner = $this->userWithRole(User::ROLE_ADMIN);
        $app   = $this->makeApp([
            'auth_mode'     => DevApp::AUTH_PUBLIC,
            'owner_user_id' => $owner->id,
            'enabled'       => false,
        ]);

        $this->actingAs($owner)->authz($app)->assertStatus(503);
    }

    public function test_master_switch_off_rejects_everything(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_PUBLIC]);

        config()->set('devapps.enabled', false);

        $this->authz($app)->assertStatus(503);
    }

    public function test_unknown_slug_is_not_found(): void
    {
        $this->get('/develop/authz/tidak-ada')->assertNotFound();
    }

    public function test_unknown_auth_mode_fails_closed(): void
    {
        $app = $this->makeApp();
        // Simulate a row written by a future version with a mode this build
        // doesn't know about.
        $app->forceFill(['auth_mode' => 'something_new'])->save();

        $this->actingAs($this->userWithRole(User::ROLE_ADMIN))
            ->authz($app)
            ->assertForbidden();
    }

    // ── Redirect safety ─────────────────────────────────────────────────

    public function test_forwarded_host_from_another_domain_does_not_become_an_open_redirect(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);

        $response = $this->get("/develop/authz/{$app->slug}", [
            'X-Forwarded-Host'  => 'attacker.example.com',
            'X-Forwarded-Uri'   => '/uji',
            'X-Forwarded-Proto' => 'https',
        ]);

        // Neither the return URL nor the redirect's own base may carry the
        // spoofed host. The Location is checked raw — going through the URL
        // generator would re-introduce the request host on both sides and
        // hide exactly the bug this guards against.
        $location = (string) $response->headers->get('Location');

        $this->assertStringNotContainsString('attacker.example.com', $location);
        $this->assertSame(
            route('develop.masuk', ['next' => $app->publicUrl()], false),
            $location,
        );
    }

    public function test_forwarded_host_cannot_control_the_redirect_base(): void
    {
        // route() builds absolute URLs from the request host and this app
        // trusts X-Forwarded-Host, so the gate must emit a relative Location.
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);

        $location = (string) $this->get("/develop/authz/{$app->slug}", [
            'X-Forwarded-Host' => 'attacker.example.com',
        ])->headers->get('Location');

        $this->assertStringStartsWith('/develop/masuk', $location);
    }

    public function test_forwarded_uri_on_our_own_host_is_carried_through_to_login(): void
    {
        $app = $this->makeApp(['auth_mode' => DevApp::AUTH_LOGIN_REQUIRED]);

        $target = 'https://datakita.test/uji-pengembang/laporan?bulan=3';

        $this->get("/develop/authz/{$app->slug}", [
            'X-Forwarded-Host'  => 'datakita.test',
            'X-Forwarded-Uri'   => '/uji-pengembang/laporan?bulan=3',
            'X-Forwarded-Proto' => 'https',
        ])->assertRedirect(route('develop.masuk', ['next' => $target]));
    }

    // ── The landing step that records the return URL ────────────────────

    public function test_landing_step_remembers_the_target_and_sends_you_to_login(): void
    {
        $target = 'https://datakita.test/uji-pengembang/laporan?bulan=3';

        $this->get(route('develop.masuk', ['next' => $target]))
            ->assertRedirect(route('login'));

        // This is the write that has to happen in a real browser request —
        // Fortify's LoginResponse reads it via redirect()->intended().
        $this->assertSame($target, session('url.intended'));
    }

    public function test_landing_step_refuses_a_target_on_another_host(): void
    {
        $this->get(route('develop.masuk', ['next' => 'https://attacker.example.com/steal']))
            ->assertRedirect(route('login'));

        $this->assertNull(session('url.intended'));
    }

    public function test_landing_step_without_a_target_still_reaches_login(): void
    {
        $this->get(route('develop.masuk'))->assertRedirect(route('login'));

        $this->assertNull(session('url.intended'));
    }
}
