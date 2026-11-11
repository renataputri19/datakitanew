<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * BPS Provinsi is a regular user plus the four BPS Statistik dashboards.
 *
 * It deliberately has no `is_bps`, so every `is_bps` gate (the rest of /bps,
 * MONALISA BPS, Buku Tamu settings, /develop) turns it away like any regular
 * user; only the `bps_statistik` routes let it in. Also covers who may reset
 * whose password from BPS > Manajemen Pengguna.
 */
class BpsProvinsiAccessTest extends TestCase
{
    use RefreshDatabase;

    private const STATISTIK_ROUTES = [
        'bps.statistik.index', 'bps.statistik.listrik', 'bps.statistik.ub', 'bps.statistik.konstruksi',
    ];

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->setRole($role); // does not persist
        $user->save();

        return $user->fresh();
    }

    // ─────────────────────────────────────────────────────────
    //  Role model
    // ─────────────────────────────────────────────────────────

    public function test_bps_provinsi_is_its_own_flag_and_never_is_bps(): void
    {
        $user = $this->user(User::ROLE_BPS_PROVINSI);

        $this->assertFalse($user->is_bps);
        $this->assertTrue($user->is_bps_provinsi);
        $this->assertSame(User::ROLE_BPS_PROVINSI, $user->role);
        $this->assertTrue($user->canViewBpsStatistik());
        $this->assertTrue($user->isStaff());

        $user->setRole(User::ROLE_ADMIN);
        $user->save();
        $user = $user->fresh();

        $this->assertTrue($user->is_bps);
        $this->assertFalse($user->is_bps_provinsi);
        $this->assertSame(User::ROLE_ADMIN, $user->role);
    }

    public function test_with_role_scope_agrees_with_role_counts(): void
    {
        $this->user(User::ROLE_SUPERADMIN);
        $this->user(User::ROLE_ADMIN);
        $this->user(User::ROLE_ADMIN);
        $this->user(User::ROLE_BPS_PROVINSI);
        $this->user(User::ROLE_KOMINFO);
        $this->user(User::ROLE_MITRA);
        $this->user(User::ROLE_USER);

        // Legacy overlap seen in prod: a superadmin that still carries is_bps
        // must count (and filter) as superadmin only, not also as BPS Batam.
        User::where('is_superadmin', true)->update(['is_bps' => true]);

        $counts = User::roleCounts();
        foreach (array_keys(User::roleDefinitions()) as $role) {
            $this->assertSame($counts[$role], User::withRole($role)->count(), "withRole('{$role}') disagrees with roleCounts()");
        }
        $this->assertSame(2, $counts[User::ROLE_ADMIN]);
        $this->assertSame(1, $counts[User::ROLE_BPS_PROVINSI]);
        $this->assertSame(1, $counts[User::ROLE_USER]);
    }

    public function test_bps_provinsi_entries_are_not_counted_as_respondents(): void
    {
        $prov = $this->user(User::ROLE_BPS_PROVINSI);
        $respondent = $this->user(User::ROLE_USER);

        $this->assertSame([$respondent->id], User::respondents()->pluck('id')->all());
        $this->assertNotContains($prov->id, User::respondents()->pluck('id')->all());
    }

    public function test_marking_migration_moves_bps_to_provinsi_and_never_grants_access(): void
    {
        $bps = $this->user(User::ROLE_ADMIN, ['email' => 'resa@gmail.com']);

        $migration = require database_path('migrations/2026_11_10_000002_mark_existing_bps_provinsi_accounts.php');
        $migration->up();

        $bps = $bps->fresh();
        $this->assertSame(User::ROLE_BPS_PROVINSI, $bps->role);
        $this->assertFalse($bps->is_bps);

        // A non-BPS account with that email is left exactly as it was.
        $bps->delete();
        $respondent = $this->user(User::ROLE_USER, ['email' => 'resa@gmail.com']);
        $migration->up();

        $this->assertSame(User::ROLE_USER, $respondent->fresh()->role);
    }

    // ─────────────────────────────────────────────────────────
    //  Route gates
    // ─────────────────────────────────────────────────────────

    public function test_only_the_statistik_routes_admit_bps_provinsi(): void
    {
        $admitsProvinsi = [];

        foreach (Route::getRoutes() as $route) {
            $name = (string) $route->getName();
            $middleware = $route->gatherMiddleware();

            if (in_array('bps_statistik', $middleware, true)) {
                $admitsProvinsi[] = $name;
                $this->assertNotContains('is_bps', $middleware, "{$name} has both gates; is_bps would block Provinsi");
                continue;
            }

            // Visitor-facing dev-app routes are public on purpose — the gate,
            // its guest landing step and the proxy decide access per app
            // (see routes/devportal.php). Every other develop.* is management.
            $devAppVisitorRoutes = ['develop.authz', 'develop.masuk', 'develop.proxy'];
            $isBpsArea = str_starts_with($name, 'bps.') || str_starts_with($name, 'monalisa.bps.')
                || (str_starts_with($name, 'develop.') && ! in_array($name, $devAppVisitorRoutes, true))
                || $name === 'antrian.setting';

            if ($isBpsArea) {
                $this->assertContains('is_bps', $middleware, "{$name} is a BPS page but is not behind is_bps");
            }
        }

        sort($admitsProvinsi);
        $expected = self::STATISTIK_ROUTES;
        sort($expected);
        $this->assertSame($expected, $admitsProvinsi, 'Only the four Statistik dashboards may admit BPS Provinsi');
    }

    public function test_bps_provinsi_opens_every_statistik_dashboard_with_a_statistik_only_menu(): void
    {
        $prov = $this->user(User::ROLE_BPS_PROVINSI);

        foreach (self::STATISTIK_ROUTES as $route) {
            $this->actingAs($prov)->get(route($route))->assertOk();
        }

        $page = $this->actingAs($prov)->get(route('bps.statistik.index'))->assertOk();

        foreach (self::STATISTIK_ROUTES as $route) {
            $page->assertSee(route($route) . '"', false);
        }
        foreach (['bps.dashboard', 'bps.news.index', 'bps.videos.index', 'bps.users.index', 'bps.user.profile.show',
                  'bps.sibstr.index', 'bps.pengingat.index'] as $hidden) {
            $page->assertDontSee(route($hidden) . '"', false);
        }
        $page->assertSee(route('dashboard') . '"', false)
             ->assertSee(route('user.profile.show') . '"', false);
    }

    public function test_bps_provinsi_is_a_regular_user_everywhere_else(): void
    {
        $prov = $this->user(User::ROLE_BPS_PROVINSI);

        $blocked = ['bps.dashboard', 'bps.news.index', 'bps.videos.index', 'bps.users.index', 'bps.user.profile.show',
                    'bps.sibstr.index', 'bps.sibstr.export', 'bps.ub.index', 'bps.listrik.index',
                    'bps.konstruksi.index', 'bps.pengingat.index', 'monalisa.bps.dashboard', 'antrian.setting'];
        if (Route::has('develop.index')) {
            $blocked[] = 'develop.index';
        }

        foreach ($blocked as $route) {
            $this->actingAs($prov)->get(route($route))->assertRedirect(route('dashboard'));
        }
    }

    public function test_user_dashboard_offers_provinsi_the_statistik_card_and_nothing_bps_only(): void
    {
        $prov = $this->user(User::ROLE_BPS_PROVINSI);

        foreach (['dashboard', 'dashboard.apps'] as $page) {
            $this->actingAs($prov)->get(route($page))
                ->assertOk()
                ->assertSee(route('bps.statistik.index'), false)
                ->assertSee('Statistik BPS')
                ->assertDontSee(route('bps.dashboard') . '"', false)
                ->assertDontSee(route('monalisa.bps.dashboard'), false);
        }

        // A plain user gets no Statistik card.
        $this->actingAs($this->user(User::ROLE_USER))->get(route('dashboard'))
            ->assertOk()
            ->assertDontSee('Statistik BPS');
    }

    public function test_bps_batam_keeps_the_full_bps_area(): void
    {
        $batam = $this->user(User::ROLE_ADMIN);

        foreach (['bps.dashboard', 'bps.sibstr.index', 'bps.news.index', 'bps.pengingat.index', 'bps.users.index',
                  'bps.statistik.index', 'monalisa.bps.dashboard'] as $route) {
            $this->actingAs($batam)->get(route($route))->assertOk();
        }

        $this->actingAs($batam)->get(route('bps.statistik.index'))
            ->assertSee(route('bps.dashboard') . '"', false)
            ->assertSee(route('bps.sibstr.index') . '"', false)
            ->assertSee(route('bps.news.index') . '"', false);
    }

    // ─────────────────────────────────────────────────────────
    //  Password reset from BPS > Manajemen Pengguna
    // ─────────────────────────────────────────────────────────

    private function reset(User $actor, User $target)
    {
        return $this->actingAs($actor)->postJson(route('bps.users.reset-password', $target->id), [
            'new_password'              => 'BaruSekali123',
            'new_password_confirmation' => 'BaruSekali123',
        ]);
    }

    public function test_bps_batam_can_reset_a_respondent_password(): void
    {
        $respondent = $this->user(User::ROLE_USER);

        $this->reset($this->user(User::ROLE_ADMIN), $respondent)->assertOk();

        $this->assertTrue(Hash::check('BaruSekali123', $respondent->fresh()->password));
    }

    public function test_no_bps_user_can_reset_a_staff_password(): void
    {
        $batam = $this->user(User::ROLE_ADMIN);

        foreach ([User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_BPS_PROVINSI] as $role) {
            $target = $this->user($role);

            $this->reset($batam, $target)->assertForbidden();
            $this->assertFalse(Hash::check('BaruSekali123', $target->fresh()->password), "{$role} password was changed");
        }
    }

    public function test_user_list_only_offers_reset_where_it_is_allowed(): void
    {
        $respondent = $this->user(User::ROLE_USER);
        $colleague  = $this->user(User::ROLE_ADMIN);
        $prov       = $this->user(User::ROLE_BPS_PROVINSI);

        $this->actingAs($this->user(User::ROLE_ADMIN))->get(route('bps.users.index'))
            ->assertOk()
            ->assertSee("openResetModal('{$respondent->id}'", false)
            ->assertDontSee("openResetModal('{$colleague->id}'", false)
            ->assertDontSee("openResetModal('{$prov->id}'", false)
            ->assertSee('Via Super Admin')
            ->assertSee('BPS Provinsi');
    }

    // ─────────────────────────────────────────────────────────
    //  Superadmin user management
    // ─────────────────────────────────────────────────────────

    public function test_superadmin_can_create_a_bps_provinsi_account(): void
    {
        $superadmin = $this->user(User::ROLE_SUPERADMIN);

        $this->actingAs($superadmin)->get(route('superadmin.users.create'))
            ->assertOk()
            ->assertSee('BPS Provinsi')
            ->assertSee('Admin BPS Batam')
            ->assertSee('value="bps_provinsi"', false);

        $this->actingAs($superadmin)->post(route('superadmin.users.store'), [
            'name'                  => 'Staf Provinsi',
            'email'                 => 'staf.prov@bps.go.id',
            'password'              => 'Rahasia12345',
            'password_confirmation' => 'Rahasia12345',
            'role'                  => User::ROLE_BPS_PROVINSI,
        ])->assertRedirect(route('superadmin.users.index'));

        $created = User::where('email', 'staf.prov@bps.go.id')->firstOrFail();
        $this->assertFalse($created->is_bps);
        $this->assertTrue($created->is_bps_provinsi);
        $this->assertSame(User::ROLE_BPS_PROVINSI, $created->role);
    }

    public function test_superadmin_role_filter_separates_batam_and_provinsi(): void
    {
        $superadmin = $this->user(User::ROLE_SUPERADMIN);
        $batam = $this->user(User::ROLE_ADMIN, ['name' => 'Batam Satu']);
        $prov  = $this->user(User::ROLE_BPS_PROVINSI, ['name' => 'Provinsi Satu']);

        $this->actingAs($superadmin)->get(route('superadmin.users.index', ['role' => User::ROLE_BPS_PROVINSI]))
            ->assertOk()->assertSee($prov->email)->assertDontSee($batam->email);

        $this->actingAs($superadmin)->get(route('superadmin.users.index', ['role' => User::ROLE_ADMIN]))
            ->assertOk()->assertSee($batam->email)->assertDontSee($prov->email);

        $this->actingAs($superadmin)->get(route('superadmin.users.index', ['role' => User::ROLE_USER]))
            ->assertOk()->assertDontSee($prov->email);
    }
}
