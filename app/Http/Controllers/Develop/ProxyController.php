<?php

namespace App\Http\Controllers\Develop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Develop\Concerns\GateResponses;
use App\Models\DevApp;
use App\Support\AppRole;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\TransferException;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Psr\Http\Message\ResponseInterface;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

/**
 * The in-app gate: DataKita authorises the request, then forwards it.
 *
 * This replaces the Traefik forwardAuth design. There is no Traefik on this
 * server — SafeLine proxies straight to each published port — so there is no
 * edge at which to hang a gate, and it has to live here instead. The access
 * decision is still {@see DevApp::allows()}, the same method the edge gate
 * called, so the two can never disagree about who may enter.
 *
 * Two properties matter more than anything else here:
 *
 *   1. The dev app never receives DataKita's session cookie. It is third-party
 *      code running under DataKita's origin; handing it the session token
 *      would let it act as the visitor. The Cookie header is dropped.
 *   2. The identity headers are always set, never merely forwarded. A client
 *      that sends its own X-Datakita-User-Id must not have it believed.
 *
 * Known limits, accepted deliberately (see docs/DEVPORTAL_PLAN.md):
 * no WebSocket upgrade, request bodies are buffered in PHP, and every proxied
 * request occupies a PHP-FPM worker for its whole lifetime.
 */
class ProxyController extends Controller
{
    use GateResponses;

    /**
     * Headers that describe a single connection rather than the message, and
     * must never be copied across a proxy hop (RFC 9110 §7.6.1).
     */
    private const HOP_BY_HOP = [
        'connection', 'keep-alive', 'proxy-authenticate', 'proxy-authorization',
        'te', 'trailer', 'transfer-encoding', 'upgrade',
    ];

    public function __construct(private readonly ClientInterface $http)
    {
    }

    public function __invoke(Request $request, string $slug, string $path = ''): SymfonyResponse
    {
        // The master switch is checked here as well as at route registration:
        // a cached route table built while the portal was on must not outlive
        // the decision to turn it off.
        $app = config('devapps.enabled')
            ? DevApp::where('slug', $slug)->first()
            : null;

        if (! $app) {
            return $this->nothingMountedHere();
        }

        $user = Auth::user();

        if (! $app->allows($user)) {
            // Anonymous visitor to an app that needs a login → send them to
            // sign in and come back, rather than showing a bare 403.
            if (! $user && $app->requiresLogin()) {
                return $this->bounceToLogin($request);
            }

            if (! $app->enabled) {
                return $this->gatePage(
                    'Aplikasi dinonaktifkan',
                    'Aplikasi ini sedang dinonaktifkan oleh pengelola.',
                    SymfonyResponse::HTTP_SERVICE_UNAVAILABLE,
                );
            }

            return $this->gatePage(
                'Akses ditolak',
                'Akun Anda tidak memiliki akses ke aplikasi ini.',
                SymfonyResponse::HTTP_FORBIDDEN,
            );
        }

        return $this->forward($request, $app, $path);
    }

    /**
     * No app claims this slug.
     *
     * Where DataKita itself is served, this must look exactly like any other
     * unknown path did before the proxy existed — the catch-all route is
     * matched ahead of Route::fallback(), so without this the proxy would
     * quietly take over the 404 behaviour of the whole site.
     */
    private function nothingMountedHere(): SymfonyResponse
    {
        if (AppRole::servesDatakita()) {
            return redirect()->route('home');
        }

        return $this->gatePage(
            'Tidak ditemukan',
            'Tidak ada aplikasi yang terpasang di alamat ini.',
            SymfonyResponse::HTTP_NOT_FOUND,
        );
    }

    /**
     * Unlike the edge gate, this runs in a request the browser actually made,
     * so the session written here is the session that reaches /login — no
     * /develop/masuk hand-off is needed.
     */
    private function bounceToLogin(Request $request): SymfonyResponse
    {
        // getRequestUri() is path+query, relative. fullUrl() would rebuild an
        // absolute URL from the request host, which this application takes
        // from X-Forwarded-Host — spoofable, and an open redirect if stored.
        $request->session()->put('url.intended', $request->getRequestUri());

        return redirect()->route('login');
    }

    // ── Forwarding ──────────────────────────────────────────────────────

    private function forward(Request $request, DevApp $app, string $path): SymfonyResponse
    {
        $base = $this->upstreamBase($app);

        if ($base === null) {
            return $this->gatePage(
                'Aplikasi belum siap',
                'Aplikasi ini belum selesai dipasang. Coba lagi setelah deploy pertama berhasil.',
                SymfonyResponse::HTTP_SERVICE_UNAVAILABLE,
            );
        }

        $uri = $base . $this->upstreamPath($app, $path);

        if ($query = $request->getQueryString()) {
            $uri .= '?' . $query;
        }

        $body    = $this->bodyOptions($request);
        $headers = $this->requestHeaders($request, $app);

        // Guzzle generates the Content-Type when it builds the body itself,
        // and for multipart that carries the boundary. Forwarding the inbound
        // one alongside would announce a boundary that isn't in the body.
        if ($body !== [] && ! isset($body['body'])) {
            unset($headers['Content-Type'], $headers['content-type']);
        }

        $options = $body + [
            'headers'         => $headers,
            'http_errors'     => false,   // the app's own 4xx/5xx are its to send
            'allow_redirects' => false,   // we rewrite Location ourselves
            'decode_content'  => false,   // pass the body through byte-for-byte
            'stream'          => true,
            'connect_timeout' => (float) config('devapps.proxy.connect_timeout', 5),
            'timeout'         => (float) config('devapps.proxy.timeout', 60),
            'version'         => 1.1,
        ];

        try {
            /** @var ResponseInterface $upstream */
            $upstream = $this->http->request($request->getMethod(), $uri, $options);
        } catch (ConnectException $e) {
            return $this->unreachable($app, $e->getMessage());
        } catch (TransferException $e) {
            // Timeouts and protocol errors land here. A dead container must
            // produce a readable page, not a stack trace on DataKita's domain.
            return $this->unreachable($app, $e->getMessage());
        }

        return $this->relay($upstream, $app);
    }

    /**
     * The app's container on the Docker network. Never a public hostname —
     * that would send the request back out through SafeLine.
     */
    private function upstreamBase(DevApp $app): ?string
    {
        $host = trim((string) $app->dokploy_app_name);

        if ($host === '') {
            return null;
        }

        return 'http://' . $host . ':' . (int) $app->container_port;
    }

    /**
     * Where the request lands inside the app.
     *
     * With strip_prefix on, the app believes it is mounted at "/" and never
     * sees its slug. With it off, the app is written to expect its own mount
     * path and gets the whole thing.
     */
    private function upstreamPath(DevApp $app, string $path): string
    {
        $path = '/' . ltrim($path, '/');

        if ($app->strip_prefix) {
            return $path;
        }

        return rtrim($app->mountPath(), '/') . $path;
    }

    /**
     * The Guzzle options that carry the request body, if there is one.
     *
     * php://input is not always available: PHP consumes it for multipart form
     * data, and Symfony's test client never fills it. When it is empty but the
     * request clearly had content, rebuild the body from the parsed request —
     * otherwise every file upload through the proxy arrives empty.
     *
     * @return array<string, mixed>
     */
    private function bodyOptions(Request $request): array
    {
        if (in_array($request->getMethod(), ['GET', 'HEAD'], true)) {
            return [];
        }

        $raw = $request->getContent();

        if ($raw !== '') {
            return ['body' => $raw];
        }

        $files  = $request->allFiles();
        $params = $request->request->all();

        if ($files !== []) {
            return ['multipart' => $this->multipartParts($params, $files)];
        }

        if ($params !== []) {
            return ['form_params' => $params];
        }

        return [];
    }

    /**
     * @param  array<string, mixed>  $params
     * @param  array<string, mixed>  $files
     * @return list<array<string, mixed>>
     */
    private function multipartParts(array $params, array $files): array
    {
        $parts = [];

        foreach (Arr::dot($params) as $name => $value) {
            $parts[] = [
                'name'     => $this->bracketName((string) $name),
                'contents' => (string) $value,
            ];
        }

        foreach (Arr::dot($files) as $name => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $parts[] = [
                'name'     => $this->bracketName((string) $name),
                'contents' => fopen($file->getRealPath(), 'r'),
                'filename' => $file->getClientOriginalName(),
                'headers'  => ['Content-Type' => $file->getClientMimeType()],
            ];
        }

        return $parts;
    }

    /**
     * "alamat.kota" → "alamat[kota]", the form-field name the app expects.
     */
    private function bracketName(string $dotted): string
    {
        $segments = explode('.', $dotted);
        $first    = array_shift($segments);

        return $first . implode('', array_map(fn ($s) => "[{$s}]", $segments));
    }

    /**
     * @return array<string, string|list<string>>
     */
    private function requestHeaders(Request $request, DevApp $app): array
    {
        $identity = $this->identityHeaderValues(Auth::user());
        $reserved = array_map('strtolower', array_keys($identity));

        $headers = [];

        foreach ($request->headers->all() as $name => $values) {
            $lower = strtolower($name);

            if (in_array($lower, self::HOP_BY_HOP, true)) {
                continue;
            }

            // Rebuilt below from the decrypted cookie bag, without ours.
            if ($lower === 'cookie') {
                continue;
            }

            // Guzzle derives Host from the URI, and Content-Length from the
            // body we actually send.
            if ($lower === 'host' || $lower === 'content-length') {
                continue;
            }

            // A client-supplied identity header must never survive to the app.
            if (in_array($lower, $reserved, true)) {
                continue;
            }

            $headers[$name] = $values;
        }

        if ($cookie = $this->forwardableCookies($request)) {
            $headers['Cookie'] = $cookie;
        }

        foreach ($identity as $name => $value) {
            $headers[$name] = $value;
        }

        $headers['X-Forwarded-For']    = $request->ip();
        $headers['X-Forwarded-Host']   = $request->getHost();
        $headers['X-Forwarded-Proto']  = $request->getScheme();
        // Frameworks that honour it will generate correct URLs under the slug.
        $headers['X-Forwarded-Prefix'] = $app->strip_prefix ? rtrim($app->mountPath(), '/') : '';

        return $headers;
    }

    /**
     * The Cookie header the dev app is allowed to see.
     *
     * DataKita's own cookies are removed by name. That is the security
     * requirement: the app is third-party code sharing this origin, and with
     * the session token it could act as the visitor against DataKita itself.
     *
     * Its own cookies are forwarded, because an app that cannot keep a session
     * is an app nobody can build. They are read from the decrypted cookie bag
     * rather than the raw header on purpose: DataKita's EncryptCookies
     * middleware encrypted them on the way out, so the raw header holds
     * ciphertext the app would not recognise. Reading them here — and letting
     * EncryptCookies re-encrypt on the way back — makes the round trip
     * transparent to the app, and keeps its cookies encrypted in the browser.
     */
    private function forwardableCookies(Request $request): string
    {
        $pairs = [];

        foreach ($request->cookies->all() as $name => $value) {
            if (! is_string($value) || $this->isDatakitaCookie((string) $name)) {
                continue;
            }

            $pairs[] = $name . '=' . rawurlencode($value);
        }

        return implode('; ', $pairs);
    }

    /**
     * Cookies that belong to DataKita and must not cross the boundary in
     * either direction.
     */
    private function isDatakitaCookie(string $name): bool
    {
        if (str_starts_with(strtolower($name), 'remember_web')) {
            return true;
        }

        foreach (array_filter([config('session.cookie'), 'XSRF-TOKEN']) as $ours) {
            if (strcasecmp($name, (string) $ours) === 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Stream the app's response back to the browser.
     */
    private function relay(ResponseInterface $upstream, DevApp $app): SymfonyResponse
    {
        $headers = [];

        foreach ($upstream->getHeaders() as $name => $values) {
            $lower = strtolower($name);

            if (in_array($lower, self::HOP_BY_HOP, true)) {
                continue;
            }

            if ($lower === 'location') {
                $values = [$this->rewriteLocation((string) ($values[0] ?? ''), $app)];
            }

            if ($lower === 'set-cookie') {
                $values = $this->safeCookies($values);

                if ($values === []) {
                    continue;
                }
            }

            $headers[$name] = $values;
        }

        $body = $upstream->getBody();

        return response()->stream(function () use ($body) {
            while (! $body->eof()) {
                echo $body->read(8192);
            }

            $body->close();
        }, $upstream->getStatusCode(), $headers);
    }

    /**
     * Drop any cookie whose name would collide with one of DataKita's own.
     *
     * The app shares DataKita's origin, so a Set-Cookie it sends is a cookie
     * on DataKita's domain. Left alone, a dev app could overwrite the
     * visitor's session cookie or CSRF token — deliberately or by using the
     * same framework defaults.
     *
     * @param  list<string>  $cookies
     * @return list<string>
     */
    private function safeCookies(array $cookies): array
    {
        return array_values(array_filter(
            $cookies,
            fn (string $cookie) => ! $this->isDatakitaCookie(trim((string) strtok($cookie, '='))),
        ));
    }

    /**
     * Keep a redirect inside the app's own mount path.
     *
     * An app that thinks it lives at "/" will answer with Location: /login,
     * which on this domain is DataKita's login page, not the app's.
     */
    private function rewriteLocation(string $location, DevApp $app): string
    {
        if ($location === '' || ! $app->strip_prefix) {
            return $location;
        }

        $parts = parse_url($location);

        if ($parts === false) {
            return $location;
        }

        if (isset($parts['host'])) {
            // Only rewrite redirects that point back at the app's own
            // container. Anything else is a deliberate external redirect.
            if ($parts['host'] !== trim((string) $app->dokploy_app_name)) {
                return $location;
            }

            $location = ($parts['path'] ?? '/')
                . (isset($parts['query']) ? '?' . $parts['query'] : '')
                . (isset($parts['fragment']) ? '#' . $parts['fragment'] : '');
        }

        // Relative to the current document — the browser resolves it under
        // the slug already.
        if (! str_starts_with($location, '/')) {
            return $location;
        }

        $mount = rtrim($app->mountPath(), '/');

        if ($location === $mount || str_starts_with($location, $mount . '/')) {
            return $location;
        }

        return $mount . $location;
    }

    private function unreachable(DevApp $app, string $reason): SymfonyResponse
    {
        Log::warning('Dev app did not answer the proxy', [
            'slug'   => $app->slug,
            'reason' => $reason,
        ]);

        return $this->gatePage(
            'Aplikasi tidak merespons',
            'Aplikasi ini sedang tidak berjalan atau gagal merespons. Hubungi pemilik aplikasi.',
            SymfonyResponse::HTTP_BAD_GATEWAY,
        );
    }
}
