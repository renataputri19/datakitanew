<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use App\Services\DevApps\AppProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Where the access gate runs.
 *
 * 'traefik' is the original design: Traefik routes each app and calls
 * /develop/authz as a forwardAuth middleware. There is no Traefik on this
 * server, so 'proxy' is the default and the only mode that works here.
 *
 * The dangerous case is the routing verifier. It stops any container whose
 * Traefik gate it cannot positively confirm — correct behaviour under a
 * Traefik edge, and catastrophic under the proxy, where there is no Traefik
 * config to find and every healthy app would be stopped as "unprotected".
 */
class DevAppEdgeModeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('devapps.enabled', true);
        config()->set('devapps.public_host_url', 'https://datakita.test');
        config()->set('devapps.traefik.dynamic_path', null);
    }

    private function bpsUser(): User
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_ADMIN);
        $user->save();

        return $user;
    }

    private function makeApp(User $owner): DevApp
    {
        $app = DevApp::create([
            'slug'           => 'uji-pengembang',
            'name'           => 'Aplikasi Uji Pengembang',
            'owner_user_id'  => $owner->id,
            'git_repo'       => 'https://github.com/example/app.git',
            'git_branch'     => 'main',
            'auth_mode'      => DevApp::AUTH_LOGIN_REQUIRED,
            'container_port' => 3000,
        ]);

        $app->forceFill([
            'dokploy_application_id' => 'app-123',
            'dokploy_app_name'       => 'uji-pengembang-ab12cd',
        ])->save();

        return $app->fresh();
    }

    public function test_proxy_mode_is_the_default(): void
    {
        $this->assertSame('proxy', config('devapps.edge_mode'));
    }

    public function test_proxy_mode_never_stops_a_container_for_a_missing_traefik_gate(): void
    {
        config()->set('devapps.edge_mode', 'proxy');

        $app = $this->makeApp($this->bpsUser());
        $app->forceFill(['status' => DevApp::STATUS_RUNNING])->save();

        $status = app(AppProvisioner::class)->verifyRouting($app);

        // The proxy gate cannot go missing: it IS the route to the app.
        $this->assertSame(DevApp::ROUTING_PROTECTED, $status);
        $this->assertSame(DevApp::STATUS_RUNNING, $app->fresh()->status);
    }

    public function test_proxy_mode_hides_the_traefik_card_and_routing_badge(): void
    {
        config()->set('devapps.edge_mode', 'proxy');

        $owner = $this->bpsUser();
        $app   = $this->makeApp($owner);

        $response = $this->actingAs($owner)->get("/develop/{$app->id}");

        $response->assertOk();
        // Both would describe a mechanism that is not running here, and the
        // badge would read "Belum dipasang" on a perfectly protected app.
        $response->assertDontSee('Konfigurasi Rute (Traefik)');
        $response->assertDontSee($app->routingLabel());
    }

    public function test_traefik_mode_still_shows_the_routing_card(): void
    {
        config()->set('devapps.edge_mode', 'traefik');

        $owner = $this->bpsUser();
        $app   = $this->makeApp($owner);

        $this->actingAs($owner)
            ->get("/develop/{$app->id}")
            ->assertOk()
            ->assertSee('Konfigurasi Rute (Traefik)');
    }

    public function test_the_proxy_route_is_absent_in_traefik_mode(): void
    {
        // Traefik would route the app directly; a catch-all here would only
        // shadow DataKita's own fallback for no benefit.
        config()->set('devapps.edge_mode', 'traefik');

        $this->refreshApplicationWithEdgeMode('traefik');

        $this->assertNull(
            $this->app['router']->getRoutes()->getByName('develop.proxy'),
        );
    }

    /**
     * The proxy route is registered at boot, so the mode has to be in the
     * environment before the application starts.
     */
    private function refreshApplicationWithEdgeMode(string $mode): void
    {
        putenv("DEVAPPS_ENABLED=true");
        putenv("DEVAPPS_EDGE_MODE={$mode}");
        $_ENV['DEVAPPS_ENABLED'] = $_SERVER['DEVAPPS_ENABLED'] = 'true';
        $_ENV['DEVAPPS_EDGE_MODE'] = $_SERVER['DEVAPPS_EDGE_MODE'] = $mode;

        $this->refreshApplication();

        putenv('DEVAPPS_ENABLED');
        putenv('DEVAPPS_EDGE_MODE');
        unset(
            $_ENV['DEVAPPS_ENABLED'], $_SERVER['DEVAPPS_ENABLED'],
            $_ENV['DEVAPPS_EDGE_MODE'], $_SERVER['DEVAPPS_EDGE_MODE'],
        );
    }
}
