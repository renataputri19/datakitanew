<?php

namespace App\Services\Dokploy;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Thin HTTP wrapper over the Dokploy panel API.
 *
 * Deliberately dumb: it knows how to authenticate, how to call an endpoint by
 * its config key, and how to turn a failure into a {@see DokployException}.
 * All the "what does deploying an app mean" logic lives in
 * {@see \App\Services\DevApps\AppProvisioner}.
 *
 * Endpoint names come from config/dokploy.php because Dokploy renames them
 * between releases — see the note there.
 */
class DokployClient
{
    public function __construct(
        private readonly ?string $baseUrl = null,
        private readonly ?string $apiKey = null,
    ) {
    }

    /**
     * Whether the integration is configured well enough to attempt a call.
     */
    public function isConfigured(): bool
    {
        return $this->baseUrl() !== '' && $this->apiKey() !== '';
    }

    // ── Projects ────────────────────────────────────────────────────────

    /**
     * List projects. Used by `dokploy:ping` to prove the token works and to
     * help an admin find the right DOKPLOY_PROJECT_ID.
     *
     * @return array<int, array<string, mixed>>
     */
    public function projects(): array
    {
        $data = $this->get('project_all');

        return is_array($data) ? $data : [];
    }

    // ── Applications ────────────────────────────────────────────────────

    /**
     * Create an empty application inside the configured project.
     *
     * @return string the new applicationId
     */
    public function createApplication(string $name, string $appName, ?string $description = null): string
    {
        // Dokploy 0.29+ nests applications under an environment; older
        // versions attach them straight to the project. Send whichever id is
        // configured — environment wins when both are set.
        $environmentId = config('dokploy.environment_id');

        $payload = array_filter([
            'name'          => $name,
            'appName'       => $appName,
            'description'   => $description,
            'environmentId' => $environmentId,
            'projectId'     => $environmentId ? null : config('dokploy.project_id'),
            'serverId'      => config('dokploy.server_id'),
        ], fn ($v) => $v !== null && $v !== '');

        $data = $this->post('application_create', $payload);

        $id = $data['applicationId'] ?? $data['id'] ?? null;

        if (! is_string($id) || $id === '') {
            throw new DokployException(
                'Dokploy did not return an applicationId for the new application.',
                $this->endpoint('application_create'),
                200,
                json_encode($data),
            );
        }

        return $id;
    }

    public function application(string $applicationId): array
    {
        $data = $this->get('application_one', ['applicationId' => $applicationId]);

        return is_array($data) ? $data : [];
    }

    /**
     * Point the application at the developer's own Git repository.
     *
     * sshKeyId is only needed for private repos; public HTTPS clones work
     * without one.
     */
    public function saveGitProvider(
        string $applicationId,
        string $repoUrl,
        string $branch,
        string $buildPath = '/',
        ?string $sshKeyId = null,
    ): void {
        $this->post('save_git_provider', array_filter([
            'applicationId'      => $applicationId,
            'customGitUrl'       => $repoUrl,
            'customGitBranch'    => $branch,
            'customGitBuildPath' => $buildPath ?: '/',
            'customGitSSHKeyId'  => $sshKeyId,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * @param  string  $buildType  nixpacks | dockerfile | heroku_buildpacks | paketo
     */
    public function saveBuildType(string $applicationId, string $buildType, ?string $dockerfilePath = null): void
    {
        $this->post('save_build_type', array_filter([
            'applicationId' => $applicationId,
            'buildType'     => $buildType,
            'dockerfile'    => $buildType === 'dockerfile' ? ($dockerfilePath ?: 'Dockerfile') : null,
        ], fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Replace the application's environment block.
     *
     * @param  string  $env  raw KEY=value lines, exactly as Dokploy stores them
     */
    public function saveEnvironment(string $applicationId, string $env): void
    {
        $this->post('save_environment', [
            'applicationId' => $applicationId,
            'env'           => $env,
        ]);
    }

    public function deploy(string $applicationId): array
    {
        return (array) $this->post('application_deploy', ['applicationId' => $applicationId]);
    }

    public function stop(string $applicationId): void
    {
        $this->post('application_stop', ['applicationId' => $applicationId]);
    }

    public function start(string $applicationId): void
    {
        $this->post('application_start', ['applicationId' => $applicationId]);
    }

    public function deleteApplication(string $applicationId): void
    {
        $this->post('application_delete', ['applicationId' => $applicationId]);
    }

    // ── Domains ─────────────────────────────────────────────────────────

    /**
     * Bind host + path to the application so Traefik routes it.
     *
     * The path is what puts the app under datakita's own domain rather than
     * a subdomain of its own.
     *
     * @return string the new domainId
     */
    public function createDomain(string $applicationId, string $host, string $path, int $port, bool $https = true): string
    {
        $data = $this->post('domain_create', array_filter([
            'applicationId'   => $applicationId,
            'host'            => $host,
            'path'            => $path,
            'port'            => $port,
            'https'           => $https,
            'certificateType' => $https ? 'letsencrypt' : 'none',
        ], fn ($v) => $v !== null && $v !== ''));

        return (string) ($data['domainId'] ?? $data['id'] ?? '');
    }

    public function deleteDomain(string $domainId): void
    {
        $this->post('domain_delete', ['domainId' => $domainId]);
    }

    // ── Deployments ─────────────────────────────────────────────────────

    /**
     * @return array<int, array<string, mixed>>
     */
    public function deployments(string $applicationId): array
    {
        $data = $this->get('deployment_all', ['applicationId' => $applicationId]);

        return is_array($data) ? $data : [];
    }

    // ── Plumbing ────────────────────────────────────────────────────────

    private function baseUrl(): string
    {
        return rtrim($this->baseUrl ?? (string) config('dokploy.base_url'), '/');
    }

    private function apiKey(): string
    {
        return (string) ($this->apiKey ?? config('dokploy.api_key'));
    }

    private function endpoint(string $key): string
    {
        $path = config("dokploy.endpoints.{$key}");

        if (! $path) {
            throw new DokployException("No Dokploy endpoint configured for \"{$key}\".");
        }

        return $this->baseUrl() . '/api/' . ltrim($path, '/');
    }

    /**
     * @param  array<string, mixed>  $query
     */
    private function get(string $key, array $query = []): mixed
    {
        return $this->send('get', $key, $query);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function post(string $key, array $payload = []): mixed
    {
        return $this->send('post', $key, $payload);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function send(string $method, string $key, array $data): mixed
    {
        if (! $this->isConfigured()) {
            throw new DokployException(
                'Dokploy is not configured. Set DOKPLOY_URL and DOKPLOY_API_KEY.',
                $key,
            );
        }

        $url = $this->endpoint($key);

        try {
            /** @var Response $response */
            $response = Http::withHeaders([
                    'x-api-key' => $this->apiKey(),
                    'Accept'    => 'application/json',
                ])
                ->timeout((int) config('dokploy.timeout', 30))
                ->withOptions(['verify' => (bool) config('dokploy.verify_tls', true)])
                ->{$method}($url, $data);
        } catch (ConnectionException $e) {
            // Surface the underlying cURL message. "Could not connect" alone
            // is useless — it covers DNS failure, a firewall, a timeout and
            // (most often on Windows PHP) a missing CA bundle, and the fix
            // is different for each.
            throw new DokployException(
                'Tidak dapat menghubungi Dokploy: ' . $this->connectionHint($e),
                $url,
                null,
                null,
                $e,
            );
        }

        if ($response->failed()) {
            // The body can echo back the payload, which for saveEnvironment
            // contains the app's env block — keep it out of the log.
            Log::warning('Dokploy API call failed', [
                'endpoint' => $key,
                'status'   => $response->status(),
            ]);

            throw new DokployException(
                $this->errorMessage($response),
                $url,
                $response->status(),
                $response->body(),
            );
        }

        $json = $response->json();

        // Dokploy wraps some responses in {result: {data: {json: ...}}}
        // (tRPC) and returns others bare. Unwrap when present.
        if (is_array($json) && isset($json['result']['data']['json'])) {
            return $json['result']['data']['json'];
        }

        return $json;
    }

    /**
     * Translate a cURL/Guzzle connection failure into something actionable.
     *
     * Always keeps the raw message on the end — the guesses below cover the
     * common cases, not every case.
     */
    private function connectionHint(ConnectionException $e): string
    {
        $raw = $e->getMessage();

        $hint = match (true) {
            str_contains($raw, 'certificate') || str_contains($raw, 'SSL') || str_contains($raw, 'CA') =>
                'sertifikat TLS tidak dapat diverifikasi. PHP di Windows sering belum punya CA bundle — '
                . 'unduh cacert.pem dari https://curl.se/ca/cacert.pem lalu set curl.cainfo dan openssl.cafile di php.ini.',

            str_contains($raw, 'Could not resolve host') || str_contains($raw, 'getaddrinfo') =>
                'nama host tidak dapat di-resolve. Periksa ejaan DOKPLOY_URL dan DNS mesin ini.',

            str_contains($raw, 'Connection refused') =>
                'koneksi ditolak. Dokploy mungkin tidak melayani port tersebut.',

            str_contains($raw, 'timed out') || str_contains($raw, 'Timeout') =>
                'waktu tunggu habis. Kemungkinan diblokir firewall.',

            default => 'periksa DOKPLOY_URL dan jaringan.',
        };

        return $hint . ' [' . $raw . ']';
    }

    private function errorMessage(Response $response): string
    {
        $json = $response->json();

        foreach ([['error', 'json', 'message'], ['error', 'message'], ['message']] as $path) {
            $value = data_get($json, implode('.', $path));

            if (is_string($value) && $value !== '') {
                return $value;
            }
        }

        return match ($response->status()) {
            401, 403 => 'Dokploy menolak API key. Periksa DOKPLOY_API_KEY.',
            404      => 'Endpoint Dokploy tidak ditemukan. Periksa nama endpoint di config/dokploy.php terhadap <DOKPLOY_URL>/swagger.',
            default  => 'Dokploy mengembalikan HTTP ' . $response->status() . '.',
        };
    }
}
