<?php

namespace Tests\Feature;

use App\Models\DevApp;
use App\Models\User;
use App\Services\Dokploy\DokployClient;
use App\Services\Dokploy\DokployException;
use App\Services\DevApps\AppProvisioner;
use App\Services\DevApps\TraefikConfigBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The auth gate lives in Traefik, not in datakita — so if Dokploy overwrites
 * the app's Traefik config, requests reach the app without datakita ever being
 * consulted. No check inside this codebase can refuse them.
 *
 * These tests cover the only defence available: read the config back, and if
 * the gate is positively gone, stop the container.
 */
class DevAppRoutingVerificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('devapps.enabled', true);
        config()->set('devapps.public_host_url', 'https://datakita.test');
        config()->set('devapps.forward_auth_base', 'http://datakita-app');
        config()->set('devapps.traefik.dynamic_path', null);
        config()->set('dokploy.base_url', 'http://dokploy:3000');
        config()->set('dokploy.api_key', 'test-key');
    }

    /**
     * Note the forceFill: dokploy_application_id, dokploy_app_name and status
     * are deliberately not mass-assignable — they're set by the provisioner,
     * never by a request — so create() would silently drop them and leave the
     * app looking un-provisioned.
     */
    private function makeApp(array $attributes = []): DevApp
    {
        $guarded = array_intersect_key($attributes, array_flip([
            'dokploy_application_id', 'dokploy_app_name', 'status', 'routing_status',
        ]));

        $app = DevApp::create(array_merge([
            'slug'          => 'uji-pengembang',
            'name'          => 'Aplikasi Uji Pengembang',
            'owner_user_id' => User::factory()->create()->id,
            'git_repo'      => 'https://github.com/example/app.git',
            'git_branch'    => 'main',
            'auth_mode'     => DevApp::AUTH_LOGIN_REQUIRED,
        ], array_diff_key($attributes, $guarded)));

        $app->forceFill(array_merge([
            'dokploy_application_id' => 'app-123',
            'dokploy_app_name'       => 'uji-pengembang-abc123',
        ], $guarded))->save();

        return $app;
    }

    // ── The verifier itself ─────────────────────────────────────────────

    public function test_the_config_we_generate_passes_verification(): void
    {
        $app     = $this->makeApp();
        $builder = app(TraefikConfigBuilder::class);

        $this->assertTrue($builder->verifyProtection($app, $builder->build($app)));
    }

    public function test_empty_config_fails_verification(): void
    {
        $app = $this->makeApp();

        $this->assertFalse(app(TraefikConfigBuilder::class)->verifyProtection($app, ''));
    }

    public function test_config_without_forward_auth_fails_verification(): void
    {
        // The dangerous shape: a valid router pointing at the app, but no gate.
        // The app would be live and completely open.
        $app = $this->makeApp();

        $config = <<<YAML
        http:
          routers:
            devapp-uji-pengembang:
              rule: "Host(`datakita.test`) && PathPrefix(`/uji-pengembang`)"
              service: devapp-uji-pengembang
          services:
            devapp-uji-pengembang:
              loadBalancer:
                servers:
                  - url: "http://uji-pengembang-abc123:3000"
        YAML;

        $this->assertFalse(app(TraefikConfigBuilder::class)->verifyProtection($app, $config));
    }

    public function test_config_without_the_cookie_scrub_fails_verification(): void
    {
        $app     = $this->makeApp();
        $builder = app(TraefikConfigBuilder::class);

        // Gate present, cookie stripping removed — the app would receive
        // datakita's session cookie.
        $config = str_replace('Cookie: ""', 'X-Nothing: "1"', $builder->build($app));

        $this->assertFalse($builder->verifyProtection($app, $config));
    }

    public function test_a_config_belonging_to_another_app_fails_verification(): void
    {
        // Guards against a copied config passing: the forwardAuth address must
        // name THIS app's authz endpoint.
        $app   = $this->makeApp();
        $other = $this->makeApp(['slug' => 'aplikasi-lain', 'name' => 'Lain']);

        $builder = app(TraefikConfigBuilder::class);

        $this->assertFalse($builder->verifyProtection($app, $builder->build($other)));
    }

    // ── Enforcement ─────────────────────────────────────────────────────

    public function test_a_confirmed_unprotected_app_is_stopped(): void
    {
        $app = $this->makeApp(['status' => DevApp::STATUS_RUNNING]);

        $stopped = false;

        $dokploy = new class($stopped) extends DokployClient {
            public function __construct(public &$stopped) {}
            public function isConfigured(): bool { return true; }
            public function readTraefikConfig(string $applicationId): string
            {
                return "http:\n  routers: {}\n";   // gate missing
            }
            public function stop(string $applicationId): void { $this->stopped = true; }
        };

        $provisioner = new AppProvisioner($dokploy, app(TraefikConfigBuilder::class));

        $this->assertSame(DevApp::ROUTING_UNPROTECTED, $provisioner->verifyRouting($app));
        $this->assertTrue($stopped, 'An unprotected app must be stopped — it cannot be refused any other way.');

        $app->refresh();
        $this->assertTrue($app->isConfirmedUnprotected());
        $this->assertSame(DevApp::STATUS_STOPPED, $app->status);
        $this->assertNotNull($app->routing_error);
    }

    public function test_an_unprotected_app_that_cannot_be_stopped_is_disabled_instead(): void
    {
        $app = $this->makeApp(['status' => DevApp::STATUS_RUNNING]);

        $dokploy = new class extends DokployClient {
            public function __construct() {}
            public function isConfigured(): bool { return true; }
            public function readTraefikConfig(string $applicationId): string { return "http: {}\n"; }
            public function stop(string $applicationId): void
            {
                throw new DokployException('Dokploy unreachable');
            }
        };

        (new AppProvisioner($dokploy, app(TraefikConfigBuilder::class)))->verifyRouting($app);

        $app->refresh();
        $this->assertFalse($app->enabled, 'Falling back to the portal-side gate is the last resort.');
        $this->assertStringContainsString('tidak dapat dihentikan', (string) $app->last_error);
    }

    public function test_a_read_failure_does_not_stop_a_working_app(): void
    {
        // "We could not check" must not be treated as "it is broken" — a
        // transient API error should never take a healthy app offline.
        $app = $this->makeApp(['status' => DevApp::STATUS_RUNNING]);

        $stopped = false;

        $dokploy = new class($stopped) extends DokployClient {
            public function __construct(public &$stopped) {}
            public function isConfigured(): bool { return true; }
            public function readTraefikConfig(string $applicationId): string
            {
                throw new DokployException('timeout');
            }
            public function stop(string $applicationId): void { $this->stopped = true; }
        };

        $result = (new AppProvisioner($dokploy, app(TraefikConfigBuilder::class)))->verifyRouting($app);

        $this->assertSame(DevApp::ROUTING_UNVERIFIABLE, $result);
        $this->assertFalse($stopped);

        $app->refresh();
        $this->assertSame(DevApp::STATUS_RUNNING, $app->status);
        $this->assertTrue($app->enabled);
        // ...but it is NOT reported as protected either.
        $this->assertFalse($app->isProtected());
    }

    public function test_a_good_config_marks_the_app_protected(): void
    {
        $app     = $this->makeApp();
        $builder = app(TraefikConfigBuilder::class);
        $good    = $builder->build($app);

        $dokploy = new class($good) extends DokployClient {
            public function __construct(private string $good) {}
            public function isConfigured(): bool { return true; }
            public function readTraefikConfig(string $applicationId): string { return $this->good; }
        };

        $result = (new AppProvisioner($dokploy, $builder))->verifyRouting($app);

        $this->assertSame(DevApp::ROUTING_PROTECTED, $result);

        $app->refresh();
        $this->assertTrue($app->isProtected());
        $this->assertNull($app->routing_error);
        $this->assertNotNull($app->routing_checked_at);
    }

    // ── Applying ────────────────────────────────────────────────────────

    public function test_apply_routing_pushes_the_config_through_the_api_and_verifies_it(): void
    {
        $app     = $this->makeApp();
        $builder = app(TraefikConfigBuilder::class);

        $pushed = null;

        $dokploy = new class($pushed) extends DokployClient {
            public function __construct(public &$pushed) {}
            public function isConfigured(): bool { return true; }
            public function updateTraefikConfig(string $applicationId, string $traefikConfig): void
            {
                $this->pushed = $traefikConfig;
            }
            public function readTraefikConfig(string $applicationId): string
            {
                return (string) $this->pushed;   // Dokploy stored it faithfully
            }
        };

        $result = (new AppProvisioner($dokploy, $builder))->applyRouting($app);

        $this->assertSame('dokploy-api', $result);
        $this->assertStringContainsString('forwardAuth', (string) $pushed);
        $this->assertTrue($app->fresh()->isProtected());
    }

    public function test_apply_routing_detects_dokploy_discarding_the_config(): void
    {
        // The scenario this whole mechanism exists for: the push succeeds, but
        // Dokploy regenerates the file from its own domain settings, so the
        // gate never actually lands.
        $app = $this->makeApp(['status' => DevApp::STATUS_RUNNING]);

        $stopped = false;

        $dokploy = new class($stopped) extends DokployClient {
            public function __construct(public &$stopped) {}
            public function isConfigured(): bool { return true; }
            public function updateTraefikConfig(string $applicationId, string $traefikConfig): void {}
            public function readTraefikConfig(string $applicationId): string
            {
                return "http:\n  routers:\n    generated-by-dokploy: {}\n";
            }
            public function stop(string $applicationId): void { $this->stopped = true; }
        };

        (new AppProvisioner($dokploy, app(TraefikConfigBuilder::class)))->applyRouting($app);

        $this->assertTrue($stopped);
        $this->assertTrue($app->fresh()->isConfirmedUnprotected());
    }
}
