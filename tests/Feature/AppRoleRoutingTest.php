<?php

namespace Tests\Feature;

use App\Support\AppRole;
use Illuminate\Support\Facades\Route;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * APP_ROLE decides which route files this container publishes.
 *
 * The point of the split is negative: a portal-only container must NOT serve
 * /bps or /superadmin, and a plain DataKita container (production, APP_ROLE
 * unset) must NOT serve the portal. Those absences are the security property,
 * so they are asserted directly rather than inferred from the happy path.
 *
 * The role is read once, while the app boots, so each case re-bootstraps the
 * application with a different APP_ROLE in the environment. Dotenv is
 * immutable — it will not overwrite a value already present in $_ENV — so the
 * value set here wins over whatever .env says, and the assertions hold on any
 * machine.
 */
class AppRoleRoutingTest extends TestCase
{
    /** @var array<string, string|false> */
    private array $originalEnv = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->originalEnv = [
            'env'    => $_ENV['APP_ROLE'] ?? null,
            'server' => $_SERVER['APP_ROLE'] ?? null,
            'putenv' => getenv('APP_ROLE'),
        ];
    }

    protected function tearDown(): void
    {
        // Leaking APP_ROLE would silently change the route table for every
        // test that runs after this one in the same process.
        $this->applyRoleToEnvironment($this->originalEnv['env']);

        parent::tearDown();
    }

    /**
     * Re-boot the application with APP_ROLE set to $role (null = unset).
     */
    private function bootWithRole(?string $role): void
    {
        $this->applyRoleToEnvironment($role);

        $this->refreshApplication();
    }

    private function applyRoleToEnvironment(?string $role): void
    {
        if ($role === null) {
            unset($_ENV['APP_ROLE'], $_SERVER['APP_ROLE']);
            putenv('APP_ROLE');

            return;
        }

        $_ENV['APP_ROLE'] = $_SERVER['APP_ROLE'] = $role;
        putenv("APP_ROLE={$role}");
    }

    /** @test */
    public function unset_app_role_serves_datakita_without_the_portal(): void
    {
        // '' is how an empty Docker variable arrives; null is a truly absent
        // one. Both must mean "production DataKita".
        $this->bootWithRole('');

        $this->assertSame(AppRole::DATAKITA, AppRole::current());

        $this->assertTrue(Route::has('bps.users.index'));
        $this->assertTrue(Route::has('superadmin.dashboard'));

        $this->assertFalse(Route::has('develop.index'));
        $this->assertFalse(Route::has('develop.authz'));
        $this->assertFalse(Route::has('develop.masuk'));
    }

    /** @test */
    public function devportal_role_serves_the_portal_and_404s_datakita(): void
    {
        $this->bootWithRole(AppRole::DEVPORTAL);

        $this->assertTrue(Route::has('develop.index'));
        $this->assertTrue(Route::has('develop.authz'));
        $this->assertTrue(Route::has('develop.masuk'));

        $this->assertFalse(Route::has('bps.users.index'));
        $this->assertFalse(Route::has('superadmin.dashboard'));

        // No route and no fallback — DataKita's paths are simply not here.
        $this->get('/bps/users')->assertNotFound();
        $this->get('/superadmin/dashboard')->assertNotFound();
        $this->get('/')->assertNotFound();
    }

    /** @test */
    public function devportal_role_keeps_fortify_auth_so_the_gate_can_bounce_to_login(): void
    {
        $this->bootWithRole(AppRole::DEVPORTAL);

        // The portal's whole value is "gated by DataKita login". Fortify
        // registers its own routes, independent of RouteServiceProvider —
        // this asserts that stays true when web.php is not loaded.
        $this->assertTrue(Route::has('login'));

        $this->get('/develop')->assertRedirect(route('login'));
    }

    /** @test */
    public function all_role_serves_both_surfaces(): void
    {
        $this->bootWithRole(AppRole::ALL);

        $this->assertTrue(Route::has('bps.users.index'));
        $this->assertTrue(Route::has('superadmin.dashboard'));
        $this->assertTrue(Route::has('develop.index'));
        $this->assertTrue(Route::has('develop.masuk'));
    }

    /** @test */
    public function all_role_does_not_let_the_portal_shadow_datakita_routes(): void
    {
        $this->bootWithRole(AppRole::ALL);

        // devportal.php loads after web.php, and /develop/{app} is greedy
        // enough to eat /develop/masuk if the order inside that file slips.
        $this->assertSame(
            'develop.masuk',
            Route::getRoutes()->match(
                \Illuminate\Http\Request::create('/develop/masuk', 'GET')
            )->getName(),
        );

        // Route::fallback() lives in web.php and must still win last place,
        // even though devportal.php registered routes after it.
        $this->get('/tidak-ada-halaman-ini')->assertRedirect(route('home'));
    }

    /** @test */
    public function an_unrecognised_app_role_is_rejected_rather_than_guessed(): void
    {
        // Falling back to DATAKITA on a typo would publish /bps and
        // /superadmin on a host meant to serve the portal alone.
        config(['app.role' => 'devporta1']);

        $this->expectException(InvalidArgumentException::class);

        AppRole::current();
    }
}
