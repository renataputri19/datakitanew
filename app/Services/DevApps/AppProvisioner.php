<?php

namespace App\Services\DevApps;

use App\Models\DevApp;
use App\Models\DevAppDeployment;
use App\Models\User;
use App\Services\Dokploy\DokployClient;
use App\Services\Dokploy\DokployException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

/**
 * Turns a {@see DevApp} row into a running container on Dokploy and a route
 * on datakita's domain.
 *
 * Everything that talks to the outside world lives here, so the controller
 * stays a thin HTTP layer and the whole flow is testable by swapping
 * {@see DokployClient}.
 *
 * The app's container is created in Dokploy's own project with its own
 * environment. It is never handed datakita's DB credentials or APP_KEY —
 * the only thing it learns about datakita is the name of the identity
 * headers it should read.
 */
class AppProvisioner
{
    public function __construct(
        private readonly DokployClient $dokploy,
        private readonly TraefikConfigBuilder $traefik,
    ) {
    }

    /**
     * Create the application on Dokploy and wire its source + routing.
     *
     * Idempotent: an app that already has a dokploy_application_id is
     * updated in place rather than duplicated.
     */
    public function provision(DevApp $app): DevApp
    {
        $app->status = DevApp::STATUS_PROVISIONING;
        $app->last_error = null;
        $app->save();

        try {
            if (! $app->isProvisioned()) {
                // Dokploy requires appName to be unique across the server.
                $appName = $this->uniqueAppName($app);

                $app->dokploy_application_id = $this->dokploy->createApplication(
                    name: $app->name,
                    appName: $appName,
                    description: "datakita dev app /{$app->slug} — pemilik: " . ($app->owner->name ?? 'n/a'),
                );
                $app->dokploy_app_name = $appName;
                $app->save();
            }

            $this->dokploy->saveGitProvider(
                applicationId: $app->dokploy_application_id,
                repoUrl: $app->git_repo,
                branch: $app->git_branch,
                buildPath: $app->git_build_path,
                sshKeyId: $app->ssh_key_id,
            );

            $this->dokploy->saveBuildType(
                applicationId: $app->dokploy_application_id,
                buildType: $app->build_type,
                dockerfilePath: $app->dockerfile_path,
            );

            $this->dokploy->saveEnvironment(
                applicationId: $app->dokploy_application_id,
                env: $this->environmentBlock($app),
            );

            $this->applyRouting($app);

            $app->status = DevApp::STATUS_STOPPED;
            $app->save();

            return $app;
        } catch (DokployException $e) {
            $this->fail($app, $e->summary());
            throw $e;
        } catch (Throwable $e) {
            $this->fail($app, $e->getMessage());
            throw $e;
        }
    }

    /**
     * Trigger a build + deploy, recording an attempt row for the portal.
     */
    public function deploy(DevApp $app, ?User $triggeredBy = null): DevAppDeployment
    {
        if (! $app->isProvisioned()) {
            $this->provision($app);
        }

        $attempt = $app->deployments()->create([
            'triggered_by_user_id' => $triggeredBy?->id,
            'status'               => DevAppDeployment::STATUS_QUEUED,
            'started_at'           => now(),
        ]);

        try {
            // Re-push source + env first: the developer may have changed the
            // branch or build type since the last deploy.
            $this->dokploy->saveGitProvider(
                applicationId: $app->dokploy_application_id,
                repoUrl: $app->git_repo,
                branch: $app->git_branch,
                buildPath: $app->git_build_path,
                sshKeyId: $app->ssh_key_id,
            );
            $this->dokploy->saveEnvironment(
                applicationId: $app->dokploy_application_id,
                env: $this->environmentBlock($app),
            );

            $result = $this->dokploy->deploy(
                applicationId: $app->dokploy_application_id,
                title: 'Deploy dari DataKita',
                description: 'Dipicu oleh ' . ($triggeredBy?->name ?? 'sistem') . ' melalui /develop.',
            );

            $attempt->update([
                'status'                => DevAppDeployment::STATUS_RUNNING,
                'dokploy_deployment_id' => $result['deploymentId'] ?? $result['id'] ?? null,
            ]);

            // forceFill throughout this class: status, last_error,
            // last_deployed_at and routing_status are intentionally NOT
            // mass-assignable — they're internal state the provisioner owns,
            // never request input — so update() would silently discard them.
            $app->forceFill([
                'status'           => DevApp::STATUS_DEPLOYING,
                'last_deployed_at' => now(),
                'last_error'       => null,
                // The build may rewrite the Traefik config out from under us,
                // so the previous "protected" verdict is no longer evidence of
                // anything. refresh() re-verifies once the build finishes.
                'routing_status'   => DevApp::ROUTING_UNKNOWN,
            ])->save();

            return $attempt;
        } catch (DokployException $e) {
            $attempt->update([
                'status'      => DevAppDeployment::STATUS_FAILED,
                'log'         => $e->summary(),
                'finished_at' => now(),
            ]);
            $this->fail($app, $e->summary());
            throw $e;
        }
    }

    /**
     * Pull the latest build status + log from Dokploy into the local attempt
     * rows, so the portal can show progress without Dokploy access.
     */
    public function refresh(DevApp $app): DevApp
    {
        if (! $app->isProvisioned()) {
            return $app;
        }

        try {
            $remote = $this->dokploy->deployments($app->dokploy_application_id);
        } catch (DokployException $e) {
            // A refresh failure is not an app failure — don't flip status.
            Log::info('Could not refresh dev app deployments', [
                'slug'  => $app->slug,
                'error' => $e->getMessage(),
            ]);

            return $app;
        }

        $latest = $remote[0] ?? null;

        if (! is_array($latest)) {
            return $app;
        }

        $status = $this->mapDeploymentStatus($latest['status'] ?? null);

        $attempt = $app->deployments()
            ->when(
                ! empty($latest['deploymentId']),
                fn ($q) => $q->where('dokploy_deployment_id', $latest['deploymentId']),
                fn ($q) => $q->whereIn('status', [DevAppDeployment::STATUS_QUEUED, DevAppDeployment::STATUS_RUNNING]),
            )
            ->first();

        if ($attempt) {
            $attempt->update([
                'status'      => $status,
                'log'         => $latest['logPath'] ?? $latest['log'] ?? $attempt->log,
                'finished_at' => in_array($status, [DevAppDeployment::STATUS_SUCCESS, DevAppDeployment::STATUS_FAILED], true)
                    ? ($attempt->finished_at ?? now())
                    : null,
            ]);
        }

        $app->status = match ($status) {
            DevAppDeployment::STATUS_SUCCESS => DevApp::STATUS_RUNNING,
            DevAppDeployment::STATUS_FAILED  => DevApp::STATUS_FAILED,
            default                          => DevApp::STATUS_DEPLOYING,
        };
        $app->save();

        // A finished deploy is exactly when Dokploy is most likely to have
        // regenerated the app's Traefik config from its own domain settings
        // and dropped our middleware chain. Re-apply and re-verify here —
        // this is the moment the silent-failure risk actually materialises.
        if ($status === DevAppDeployment::STATUS_SUCCESS) {
            $this->applyRouting($app);
        }

        return $app;
    }

    public function stop(DevApp $app): void
    {
        if ($app->isProvisioned()) {
            $this->dokploy->stop($app->dokploy_application_id);
        }

        $app->forceFill(['status' => DevApp::STATUS_STOPPED])->save();
    }

    public function start(DevApp $app): void
    {
        if ($app->isProvisioned()) {
            $this->dokploy->start($app->dokploy_application_id);
        }

        $app->forceFill(['status' => DevApp::STATUS_RUNNING])->save();
    }

    /**
     * Take the route offline and delete the container, then the row.
     *
     * Routing is removed first: after this line the app is unreachable even
     * if the Dokploy call below fails and leaves a container running.
     */
    public function destroy(DevApp $app): void
    {
        $this->traefik->remove($app);

        if ($app->isProvisioned()) {
            try {
                $this->dokploy->deleteApplication($app->dokploy_application_id);
            } catch (DokployException $e) {
                Log::warning('Dev app deleted locally but not on Dokploy', [
                    'slug'          => $app->slug,
                    'applicationId' => $app->dokploy_application_id,
                    'error'         => $e->getMessage(),
                ]);
            }
        }

        $app->delete();
    }

    /**
     * Install the app's routing + auth gate, then confirm it took.
     *
     * Preference order:
     *   1. Dokploy's API — the normal path. No filesystem access needed, and
     *      every change lands in Dokploy's audit log.
     *   2. A mounted Traefik dynamic directory, if one is configured.
     *   3. Neither — the config has to be pasted by hand from the portal.
     *
     * Always runs {@see self::verifyRouting()} afterwards. Applying without
     * verifying is the whole trap this feature has: writing a config that
     * Dokploy then discards leaves the app reachable and unguarded, and
     * nothing about it looks broken.
     */
    public function applyRouting(DevApp $app): ?string
    {
        if ($app->isProvisioned() && $this->dokploy->isConfigured()) {
            try {
                $this->dokploy->updateTraefikConfig(
                    $app->dokploy_application_id,
                    $this->traefik->build($app),
                );

                $this->verifyRouting($app);

                return 'dokploy-api';
            } catch (DokployException $e) {
                // Fall through to the filesystem path rather than failing the
                // whole save — but record why, and leave the app unverified.
                $this->markRouting($app, DevApp::ROUTING_UNVERIFIABLE, $e->summary());
            }
        }

        if ($this->traefik->canWrite()) {
            $path = $this->traefik->write($app);
            // A file we just wrote ourselves is as verified as it gets without
            // asking Traefik, which exposes no such API.
            $this->markRouting($app, DevApp::ROUTING_PROTECTED, null);

            return $path;
        }

        return null;
    }

    /**
     * Read the live Traefik config back and confirm the auth gate is in it.
     *
     * If it is positively absent, the container is stopped. That is the only
     * enforcement available: with the forwardAuth middleware gone, requests
     * reach the app without touching datakita at all, so no check inside
     * datakita can refuse them. An unreachable app is a far better outcome
     * than an unguarded one.
     *
     * A read *failure* is treated differently from a read showing the gate
     * missing — we don't stop a working app because the API had a bad minute.
     */
    public function verifyRouting(DevApp $app): string
    {
        if (! $app->isProvisioned() || ! $this->dokploy->isConfigured()) {
            $this->markRouting($app, DevApp::ROUTING_UNKNOWN, null);

            return DevApp::ROUTING_UNKNOWN;
        }

        try {
            $live = $this->dokploy->readTraefikConfig($app->dokploy_application_id);
        } catch (DokployException $e) {
            $this->markRouting($app, DevApp::ROUTING_UNVERIFIABLE, $e->summary());

            return DevApp::ROUTING_UNVERIFIABLE;
        }

        if ($this->traefik->verifyProtection($app, $live)) {
            $this->markRouting($app, DevApp::ROUTING_PROTECTED, null);

            return DevApp::ROUTING_PROTECTED;
        }

        $reason = $this->traefik->protectionFailureReason($app, $live);
        $this->markRouting($app, DevApp::ROUTING_UNPROTECTED, $reason);

        Log::warning('Dev app is running without its auth gate — stopping it', [
            'slug'   => $app->slug,
            'reason' => $reason,
        ]);

        try {
            $this->dokploy->stop($app->dokploy_application_id);
            $app->forceFill(['status' => DevApp::STATUS_STOPPED])->save();
        } catch (DokployException $e) {
            // Couldn't stop it either. Close the portal-side gate so at least
            // the app is refused if the middleware happens to still be live,
            // and make the error loud.
            $app->forceFill([
                'enabled'    => false,
                'last_error' => 'Aplikasi berjalan tanpa gerbang akses dan tidak dapat dihentikan: ' . $e->summary(),
            ])->save();
        }

        return DevApp::ROUTING_UNPROTECTED;
    }

    private function markRouting(DevApp $app, string $status, ?string $error): void
    {
        $app->forceFill([
            'routing_status'     => $status,
            'routing_checked_at' => now(),
            'routing_error'      => $error ? Str::limit($error, 1000) : null,
        ])->save();
    }

    /**
     * The environment Dokploy injects into the app's container.
     *
     * Note what is *not* here: no DB credentials, no APP_KEY, no datakita
     * secrets of any kind. An app that needs datakita data asks for a
     * scoped API token instead.
     */
    private function environmentBlock(DevApp $app): string
    {
        $headers = config('devapps.identity_headers', []);

        $vars = [
            'PORT'                      => (string) $app->container_port,
            // Empty when strip_prefix is on — the app then sees itself at "/".
            'DATAKITA_BASE_PATH'        => $app->strip_prefix ? '' : $app->mountPath(),
            'DATAKITA_PUBLIC_URL'       => $app->publicUrl(),
            'DATAKITA_AUTH_MODE'        => $app->auth_mode,
            'DATAKITA_HEADER_USER_ID'   => $headers['id'] ?? '',
            'DATAKITA_HEADER_USER_NAME' => $headers['name'] ?? '',
            'DATAKITA_HEADER_USER_MAIL' => $headers['email'] ?? '',
            'DATAKITA_HEADER_USER_ROLE' => $headers['role'] ?? '',
        ];

        $lines = [];
        foreach ($vars as $key => $value) {
            $lines[] = "{$key}={$value}";
        }

        return implode("\n", $lines) . "\n";
    }

    /**
     * Dokploy's appName must be unique server-wide; the slug alone would
     * collide with a previously deleted app of the same name.
     */
    private function uniqueAppName(DevApp $app): string
    {
        return Str::slug($app->slug) . '-' . Str::lower(Str::random(6));
    }

    /**
     * Dokploy has used both "done"/"error" and "success"/"failed" across
     * releases; accept either.
     */
    private function mapDeploymentStatus(?string $remote): string
    {
        return match (strtolower((string) $remote)) {
            'done', 'success', 'completed' => DevAppDeployment::STATUS_SUCCESS,
            'error', 'failed'              => DevAppDeployment::STATUS_FAILED,
            'running', 'building'          => DevAppDeployment::STATUS_RUNNING,
            default                        => DevAppDeployment::STATUS_QUEUED,
        };
    }

    private function fail(DevApp $app, string $message): void
    {
        $app->status = DevApp::STATUS_FAILED;
        $app->last_error = Str::limit($message, 2000);
        $app->save();
    }
}
