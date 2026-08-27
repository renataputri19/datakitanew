<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use App\Services\DevApps\AppProvisioner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Per-app environment variables, and the boundary around them.
 *
 * saveEnvironment() replaces an app's whole env block on every deploy, so
 * before this existed anything a developer set by hand in Dokploy was wiped
 * on the next one — which meant an app could not hold its own database
 * credentials, which is most of what an app needs an environment for.
 *
 * The boundary: DATAKITA_HEADER_* tells the app which header carries the
 * visitor's identity. An app that could set those could point them at a
 * header the client controls and believe whatever it said.
 */
class DevAppEnvironmentTest extends TestCase
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

    private function makeApp(array $attributes = []): DevApp
    {
        return DevApp::create(array_merge([
            'slug'           => 'uji-pengembang',
            'name'           => 'Aplikasi Uji Pengembang',
            'owner_user_id'  => User::factory()->create()->id,
            'git_repo'       => 'https://github.com/example/app.git',
            'git_branch'     => 'main',
            'auth_mode'      => DevApp::AUTH_LOGIN_REQUIRED,
            'container_port' => 3000,
        ], $attributes));
    }

    private function payload(array $overrides = []): array
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

    // ── Parsing ─────────────────────────────────────────────────────────

    public function test_env_lines_are_parsed_into_pairs(): void
    {
        $vars = DevApp::parseEnvVars("DB_HOST=db-aplikasi\nDB_PORT=3306");

        $this->assertSame(['DB_HOST' => 'db-aplikasi', 'DB_PORT' => '3306'], $vars);
    }

    public function test_comments_and_blank_lines_are_ignored_so_a_dotenv_can_be_pasted(): void
    {
        $raw = "# kredensial database\n\nDB_HOST=db-aplikasi\n\n  # catatan\nDB_PASSWORD=rahasia\n";

        $this->assertSame(
            ['DB_HOST' => 'db-aplikasi', 'DB_PASSWORD' => 'rahasia'],
            DevApp::parseEnvVars($raw),
        );
    }

    public function test_a_value_containing_an_equals_sign_is_kept_whole(): void
    {
        // Base64 keys and DSNs routinely contain '='.
        $vars = DevApp::parseEnvVars('APP_KEY=base64:aGFsbG8=');

        $this->assertSame('base64:aGFsbG8=', $vars['APP_KEY']);
    }

    // ── The merge ───────────────────────────────────────────────────────

    public function test_owner_variables_reach_the_container(): void
    {
        $app = $this->makeApp(['env_vars' => "DB_HOST=db-aplikasi\nDB_DATABASE=aplikasi_saya"]);

        $block = app(AppProvisioner::class)->environmentBlock($app);

        $this->assertStringContainsString('DB_HOST=db-aplikasi', $block);
        $this->assertStringContainsString('DB_DATABASE=aplikasi_saya', $block);
    }

    public function test_datakita_variables_win_over_an_owner_who_tries_to_set_them(): void
    {
        // Validation blocks this at the form, so this is the backstop for a
        // row written before that rule existed — or edited around it.
        $app = $this->makeApp([
            'env_vars' => "DATAKITA_HEADER_USER_ID=X-Dikendalikan-Klien\nPORT=9999",
        ]);

        $block = app(AppProvisioner::class)->environmentBlock($app);

        $this->assertStringContainsString('DATAKITA_HEADER_USER_ID=X-Datakita-User-Id', $block);
        $this->assertStringNotContainsString('X-Dikendalikan-Klien', $block);
        $this->assertStringContainsString('PORT=3000', $block);
        $this->assertStringNotContainsString('PORT=9999', $block);
    }

    public function test_the_environment_still_carries_no_datakita_secrets(): void
    {
        $app = $this->makeApp(['env_vars' => 'DB_HOST=db-aplikasi']);

        $block = app(AppProvisioner::class)->environmentBlock($app);

        // The app is third-party code. It gets its own database, never ours.
        // Asserting the exact key set rather than hunting for known secrets:
        // an empty DB password in the test env would make a "does not contain"
        // check pass vacuously, and this catches anything added later too.
        $this->assertSame([
            'DB_HOST',
            'PORT',
            'DATAKITA_BASE_PATH',
            'DATAKITA_PUBLIC_URL',
            'DATAKITA_AUTH_MODE',
            'DATAKITA_HEADER_USER_ID',
            'DATAKITA_HEADER_USER_NAME',
            'DATAKITA_HEADER_USER_MAIL',
            'DATAKITA_HEADER_USER_ROLE',
        ], array_keys(DevApp::parseEnvVars($block)));

        $this->assertStringNotContainsString(config('app.key'), $block);
    }

    // ── Validation ──────────────────────────────────────────────────────

    public function test_the_portal_accepts_environment_variables(): void
    {
        $response = $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload(['env_vars' => "DB_HOST=db-aplikasi\nDB_PORT=3306"]));

        $response->assertSessionHasNoErrors();
        $this->assertSame(
            "DB_HOST=db-aplikasi\nDB_PORT=3306",
            DevApp::where('slug', 'uji-pengembang')->value('env_vars'),
        );
    }

    public function test_an_owner_cannot_claim_a_datakita_variable(): void
    {
        $response = $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload([
                'env_vars' => 'DATAKITA_HEADER_USER_ID=X-Saya-Kendalikan',
            ]));

        $response->assertSessionHasErrors('env_vars');
        $this->assertDatabaseCount('dev_apps', 0);
    }

    public function test_an_owner_cannot_claim_port(): void
    {
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload(['env_vars' => 'PORT=9999']))
            ->assertSessionHasErrors('env_vars');
    }

    public function test_a_name_merely_starting_with_port_is_still_allowed(): void
    {
        // PORT is reserved; PORTAL_URL is the owner's own business.
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload(['env_vars' => 'PORTAL_URL=https://contoh.id']))
            ->assertSessionHasNoErrors();
    }

    public function test_a_malformed_variable_name_is_rejected(): void
    {
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload(['env_vars' => 'NAMA SALAH=nilai']))
            ->assertSessionHasErrors('env_vars');
    }

    public function test_environment_variables_are_optional(): void
    {
        $this->actingAs($this->bpsUser())
            ->post('/develop', $this->payload())
            ->assertSessionHasNoErrors();
    }
}
