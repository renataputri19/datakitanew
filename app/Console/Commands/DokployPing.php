<?php

namespace App\Console\Commands;

use App\Models\DevApp;
use App\Services\Dokploy\DokployClient;
use App\Services\Dokploy\DokployException;
use App\Services\DevApps\TraefikConfigBuilder;
use Illuminate\Console\Command;

/**
 * Pre-flight check for the developer portal.
 *
 * Endpoint names differ between Dokploy releases, so the first thing that
 * goes wrong is usually a 404 from an endpoint that was renamed. Running this
 * turns that into one clear line instead of a failed deploy.
 */
class DokployPing extends Command
{
    protected $signature = 'dokploy:ping';

    protected $description = 'Check the developer portal configuration: Dokploy connectivity, project, and Traefik write access';

    public function handle(DokployClient $dokploy, TraefikConfigBuilder $traefik): int
    {
        $failures = 0;

        $this->newLine();
        $this->line('<options=bold>Portal pengembang — pemeriksaan konfigurasi</>');
        $this->newLine();

        // ── Feature flag ────────────────────────────────────────────────
        if (config('devapps.enabled')) {
            $this->ok('DEVAPPS_ENABLED', 'aktif');
        } else {
            $this->warn2('DEVAPPS_ENABLED', 'nonaktif — /develop akan 404 sampai diaktifkan');
        }

        // ── Public host ─────────────────────────────────────────────────
        $host = parse_url((string) (config('devapps.public_host_url') ?: config('app.url')), PHP_URL_HOST);

        if ($host) {
            $prefix = trim((string) config('devapps.mount_prefix', ''), '/');
            $this->ok('Host publik', $host . ($prefix ? "/{$prefix}/<slug>" : '/<slug>'));
        } else {
            $this->fail('Host publik', 'APP_URL / DEVAPPS_PUBLIC_HOST_URL tidak valid');
            $failures++;
        }

        // ── Traefik dynamic dir ─────────────────────────────────────────
        $dynamicPath = config('devapps.traefik.dynamic_path');

        if (! $dynamicPath) {
            $this->warn2('Traefik dynamic path', 'belum diatur — konfigurasi rute harus disalin manual');
        } elseif ($traefik->canWrite()) {
            $this->ok('Traefik dynamic path', $dynamicPath . ' (dapat ditulis)');
        } else {
            $this->fail('Traefik dynamic path', $dynamicPath . ' tidak ada atau tidak dapat ditulis');
            $failures++;
        }

        // ── ForwardAuth reachability, as Traefik will see it ─────────────
        $this->ok('ForwardAuth', rtrim((string) config('devapps.forward_auth_base'), '/') . '/develop/authz/<slug>');

        // ── Dokploy ─────────────────────────────────────────────────────
        if (! $dokploy->isConfigured()) {
            $this->fail('Dokploy', 'DOKPLOY_URL / DOKPLOY_API_KEY belum diisi');

            return $this->summary($failures + 1);
        }

        try {
            $projects = $dokploy->projects();
            $this->ok('Dokploy API', count($projects) . ' proyek terbaca');
        } catch (DokployException $e) {
            $this->fail('Dokploy API', $e->summary());
            $this->line('    Periksa nama endpoint di config/dokploy.php terhadap ' . config('dokploy.base_url') . '/swagger');

            return $this->summary($failures + 1);
        }

        $projectId     = config('dokploy.project_id');
        $environmentId = config('dokploy.environment_id');

        if (! $projectId && ! $environmentId) {
            $this->fail('Target', 'DOKPLOY_ENVIRONMENT_ID / DOKPLOY_PROJECT_ID belum diisi');
            $failures++;
            $this->listProjects($projects);
        } elseif ($environmentId) {
            // 0.29+ path: applications live inside an environment.
            $match = $this->findEnvironment($projects, $environmentId);

            if ($match) {
                $this->ok('DOKPLOY_ENVIRONMENT_ID', $match);
            } else {
                $this->fail('DOKPLOY_ENVIRONMENT_ID', "\"{$environmentId}\" tidak ditemukan di Dokploy");
                $failures++;
                $this->listProjects($projects);
            }
        } else {
            $match = collect($projects)->first(
                fn ($p) => ($p['projectId'] ?? $p['id'] ?? null) === $projectId
            );

            if ($match) {
                $this->ok('DOKPLOY_PROJECT_ID', ($match['name'] ?? $projectId));

                // Warn rather than fail: a project that has environments on a
                // 0.29+ install will reject application.create without one.
                if (! empty($match['environments'])) {
                    $this->warn2(
                        'Environments terdeteksi',
                        'proyek ini punya environment — isi DOKPLOY_ENVIRONMENT_ID, bukan PROJECT_ID',
                    );
                    $this->listProjects($projects);
                }
            } else {
                $this->fail('DOKPLOY_PROJECT_ID', "\"{$projectId}\" tidak ditemukan di Dokploy");
                $failures++;
                $this->listProjects($projects);
            }
        }

        // ── Registered apps ─────────────────────────────────────────────
        $count = DevApp::count();
        $this->ok('Aplikasi terdaftar', (string) $count);

        return $this->summary($failures);
    }

    /**
     * Print every project and, on 0.29+, the environments inside it — these
     * ids are what you copy into DOKPLOY_ENVIRONMENT_ID.
     *
     * @param  array<int, array<string, mixed>>  $projects
     */
    private function listProjects(array $projects): void
    {
        if (! $projects) {
            return;
        }

        $this->newLine();
        $this->line('    Salin id yang sesuai ke .env:');

        foreach ($projects as $project) {
            $id   = $project['projectId'] ?? $project['id'] ?? '?';
            $name = $project['name'] ?? '(tanpa nama)';

            $this->line("      <options=bold>{$name}</>");
            $this->line("        DOKPLOY_PROJECT_ID={$id}");

            foreach ($project['environments'] ?? [] as $environment) {
                $envId   = $environment['environmentId'] ?? $environment['id'] ?? '?';
                $envName = $environment['name'] ?? '(tanpa nama)';
                $this->line("        DOKPLOY_ENVIRONMENT_ID={$envId}   <fg=gray># {$envName}</>");
            }
        }
    }

    /**
     * Resolve an environment id to a readable "project / environment" label.
     *
     * @param  array<int, array<string, mixed>>  $projects
     */
    private function findEnvironment(array $projects, string $environmentId): ?string
    {
        foreach ($projects as $project) {
            foreach ($project['environments'] ?? [] as $environment) {
                if (($environment['environmentId'] ?? $environment['id'] ?? null) === $environmentId) {
                    return ($project['name'] ?? '?') . ' / ' . ($environment['name'] ?? '?');
                }
            }
        }

        return null;
    }

    private function ok(string $label, string $detail): void
    {
        $this->line("  <fg=green>✓</> <options=bold>{$label}</>  <fg=gray>{$detail}</>");
    }

    private function warn2(string $label, string $detail): void
    {
        $this->line("  <fg=yellow>!</> <options=bold>{$label}</>  <fg=gray>{$detail}</>");
    }

    private function fail(string $label, string $detail): void
    {
        $this->line("  <fg=red>✗</> <options=bold>{$label}</>  <fg=gray>{$detail}</>");
    }

    private function summary(int $failures): int
    {
        $this->newLine();

        if ($failures === 0) {
            $this->info('Semua pemeriksaan lolos.');

            return self::SUCCESS;
        }

        $this->error("{$failures} pemeriksaan gagal. Deploy belum bisa dijalankan.");

        return self::FAILURE;
    }
}
