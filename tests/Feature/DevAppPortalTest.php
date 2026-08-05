<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use App\Services\DevApps\TraefikConfigBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Portal access, slug guards, and the invariants that keep one BPS user's
 * app from reaching into another's — or into datakita's own routes.
 */
class DevAppPortalTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('devapps.enabled', true);
        config()->set('devapps.public_host_url', 'https://datakita.test');
        // Keep the tests off the real Traefik directory.
        config()->set('devapps.traefik.dynamic_path', null);
    }

    private function bpsUser(): User
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_ADMIN);
        $user->save();

        return $user;
    }

    /**
     * The fixture name is deliberately unlike anything in the BPS sidebar —
     * "Survei Listrik" is a real nav item, and a leak assertion against it
     * would always fail on the layout rather than on the listing.
     */
    private function validPayload(array $overrides = []): array
    {
        return array_merge([
            'name'           => 'Aplikasi Uji Pengembang',
            'slug'           => 'uji-pengembang',
            'git_repo'       => 'https://github.com/example/app.git',
            'git_branch'     => 'main',
            'git_build_path' => '/',
            'build_type'     => 'nixpacks',
            'container_port' => 3000,
            'auth_mode'      => DevApp::AUTH_LOGIN_REQUIRED,
        ], $overrides);
    }

    // ── Access to the portal itself ─────────────────────────────────────

    public function test_portal_is_hidden_when_the_feature_is_off(): void
    {
        config()->set('devapps.enabled', false);

        $this->actingAs($this->bpsUser())->get('/develop')->assertNotFound();
    }

    public function test_non_bps_user_cannot_open_the_portal(): void
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_MITRA);
        $user->save();

        $this->actingAs($user)->get('/develop')->assertRedirect();
    }

    public function test_bps_user_can_open_the_portal(): void
    {
        $this->actingAs($this->bpsUser())->get('/develop')->assertOk();
    }

    public function test_every_portal_page_renders(): void
    {
        // Blade errors in these templates would otherwise only surface in
        // production, so each one gets rendered at least once.
        $user = $this->bpsUser();

        $this->actingAs($user)->get('/develop/create')->assertOk();

        $this->actingAs($user)->post('/develop', $this->validPayload());
        $app = DevApp::firstOrFail();

        $this->actingAs($user)->get("/develop/{$app->id}")->assertOk()->assertSee($app->name);
        $this->actingAs($user)->get("/develop/{$app->id}/edit")->assertOk();

        $this->actingAs($user)
            ->get("/develop/{$app->id}/traefik.yml")
            ->assertOk()
            ->assertHeader('Content-Type', 'text/yaml; charset=utf-8');
    }

    public function test_show_page_renders_for_every_access_mode(): void
    {
        // The role and allowlist branches render extra rows; make sure none of
        // them blow up on a null list.
        $user = $this->bpsUser();

        foreach (array_keys(DevApp::authModeDefinitions()) as $index => $mode) {
            $this->actingAs($user)->post('/develop', $this->validPayload([
                'slug' => "uji-mode-{$index}",
                'name' => "Uji Mode {$index}",
                'auth_mode' => $mode,
            ]));

            $app = DevApp::where('slug', "uji-mode-{$index}")->firstOrFail();

            $this->actingAs($user)->get("/develop/{$app->id}")->assertOk();
        }
    }

    // ── Slug guards ─────────────────────────────────────────────────────

    public function test_slug_that_collides_with_a_datakita_route_is_rejected(): void
    {
        // Traefik matches the more specific PathPrefix router first, so "bps"
        // here would hand datakita's own admin area to somebody else's app.
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->validPayload(['slug' => 'bps']))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('dev_apps', 0);
    }

    public function test_reserved_non_laravel_slug_is_rejected(): void
    {
        // Served by nginx, so it never appears in the route table.
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->validPayload(['slug' => 'storage']))
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_with_invalid_characters_is_rejected(): void
    {
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->validPayload(['slug' => 'Survei_Listrik']))
            ->assertSessionHasErrors('slug');
    }

    public function test_slug_must_be_unique(): void
    {
        $this->actingAs($this->bpsUser())->post('/develop', $this->validPayload())->assertRedirect();

        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->validPayload(['name' => 'Lain']))
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseCount('dev_apps', 1);
    }

    public function test_valid_app_is_created_and_owned_by_the_creator(): void
    {
        $user = $this->bpsUser();

        $this->actingAs($user)->post('/develop', $this->validPayload())->assertRedirect();

        $this->assertDatabaseHas('dev_apps', [
            'slug'          => 'uji-pengembang',
            'owner_user_id' => $user->id,
            'status'        => DevApp::STATUS_DRAFT,
        ]);
    }

    // ── Ownership ───────────────────────────────────────────────────────

    public function test_bps_user_cannot_manage_another_users_app(): void
    {
        $owner    = $this->bpsUser();
        $intruder = $this->bpsUser();

        $this->actingAs($owner)->post('/develop', $this->validPayload());
        $app = DevApp::firstOrFail();

        $this->actingAs($intruder)->get("/develop/{$app->id}")->assertForbidden();
        $this->actingAs($intruder)->post("/develop/{$app->id}/toggle")->assertForbidden();
        $this->actingAs($intruder)->delete("/develop/{$app->id}")->assertForbidden();
    }

    public function test_index_only_lists_the_current_users_apps(): void
    {
        $owner = $this->bpsUser();
        $this->actingAs($owner)->post('/develop', $this->validPayload());

        // The create redirect leaves a flash message naming the app in the
        // session; clear it so the assertion below tests the listing itself.
        $this->flushSession();

        $this->actingAs($this->bpsUser())
            ->get('/develop')
            ->assertOk()
            ->assertDontSee('Aplikasi Uji Pengembang')
            ->assertDontSee('/uji-pengembang');
    }

    // ── Access-mode handling ────────────────────────────────────────────

    public function test_switching_away_from_role_mode_clears_the_stale_role_list(): void
    {
        // A leftover list would silently widen access if the mode were ever
        // switched back.
        $user = $this->bpsUser();
        $this->actingAs($user)->post('/develop', $this->validPayload([
            'auth_mode'     => DevApp::AUTH_ROLE,
            'allowed_roles' => [User::ROLE_ADMIN],
        ]));

        $app = DevApp::firstOrFail();
        $this->assertSame([User::ROLE_ADMIN], $app->allowed_roles);

        $this->actingAs($user)->put("/develop/{$app->id}", $this->validPayload([
            'auth_mode' => DevApp::AUTH_OWNER_ONLY,
        ]));

        $this->assertNull($app->fresh()->allowed_roles);
    }

    public function test_allowlist_grants_only_the_selected_user(): void
    {
        // Regression: ids were cast to int, and UUID-to-int gives the leading
        // digits, so MySQL type-juggling attached whichever other users
        // coerced to the same number. Only reproduced when the random UUIDs
        // happened to collide, which made it look like flaky test.
        $user = $this->bpsUser();

        $granted = User::factory()->create();
        User::factory()->count(5)->create();   // decoys

        $this->actingAs($user)->post('/develop', $this->validPayload([
            'auth_mode'     => DevApp::AUTH_ALLOWLIST,
            'allowed_users' => [$granted->id],
        ]));

        $allowed = DevApp::firstOrFail()->allowedUsers;

        $this->assertCount(1, $allowed);
        $this->assertSame($granted->id, $allowed->first()->id);
    }

    public function test_switching_away_from_allowlist_mode_clears_the_explicit_grants(): void
    {
        $user    = $this->bpsUser();
        $granted = User::factory()->create();

        $this->actingAs($user)->post('/develop', $this->validPayload([
            'auth_mode'     => DevApp::AUTH_ALLOWLIST,
            'allowed_users' => [$granted->id],
        ]));

        $app = DevApp::firstOrFail();
        $this->assertCount(1, $app->allowedUsers);

        $this->actingAs($user)->put("/develop/{$app->id}", $this->validPayload([
            'auth_mode' => DevApp::AUTH_PUBLIC,
        ]));

        $this->assertCount(0, $app->fresh()->allowedUsers);
    }

    public function test_toggle_closes_access_immediately(): void
    {
        $user = $this->bpsUser();
        $this->actingAs($user)->post('/develop', $this->validPayload(['auth_mode' => DevApp::AUTH_PUBLIC]));
        $app = DevApp::firstOrFail();

        $this->get("/develop/authz/{$app->slug}")->assertOk();

        $this->actingAs($user)->post("/develop/{$app->id}/toggle")->assertRedirect();

        // No redeploy, no proxy reload — the very next request is refused.
        $this->get("/develop/authz/{$app->slug}")->assertStatus(503);
    }

    public function test_owner_quota_is_enforced(): void
    {
        config()->set('devapps.max_apps_per_owner', 1);

        $user = $this->bpsUser();
        $this->actingAs($user)->post('/develop', $this->validPayload())->assertRedirect();

        $this->actingAs($user)->get('/develop/create')->assertForbidden();
    }

    // ── Generated routing config ────────────────────────────────────────

    public function test_generated_traefik_config_scrubs_the_session_cookie_after_the_auth_check(): void
    {
        $user = $this->bpsUser();
        $this->actingAs($user)->post('/develop', $this->validPayload());
        $app = DevApp::firstOrFail();

        $yaml = app(TraefikConfigBuilder::class)->build($app);

        $this->assertStringContainsString('Cookie: ""', $yaml);
        $this->assertStringContainsString('forwardAuth', $yaml);

        // Order matters: the auth middleware needs the cookie to identify the
        // user, so the scrub must come after it in the chain.
        $authPosition  = strpos($yaml, '- devapp-uji-pengembang-auth@file');
        $scrubPosition = strpos($yaml, '- devapp-scrub-cookie@file');

        $this->assertNotFalse($authPosition);
        $this->assertNotFalse($scrubPosition);
        $this->assertLessThan($scrubPosition, $authPosition);
    }

    public function test_generated_traefik_config_routes_the_expected_host_and_path(): void
    {
        $user = $this->bpsUser();
        $this->actingAs($user)->post('/develop', $this->validPayload());

        $yaml = app(TraefikConfigBuilder::class)->build(DevApp::firstOrFail());

        $this->assertStringContainsString(
            'rule: "Host(`datakita.test`) && PathPrefix(`/uji-pengembang`)"',
            $yaml,
        );
    }
}
