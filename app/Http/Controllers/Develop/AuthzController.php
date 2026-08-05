<?php

namespace App\Http\Controllers\Develop;

use App\Http\Controllers\Controller;
use App\Models\DevApp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
// The base Symfony type, not Illuminate\Http\Response: this controller returns
// both plain responses and redirects, and only the base class covers each.
use Symfony\Component\HttpFoundation\Response;

/**
 * The edge authorisation endpoint.
 *
 * Traefik's forwardAuth middleware calls this before it forwards ANY request
 * to a dev app — pages, XHR, images, favicons, everything. Its answer is the
 * only thing standing between an anonymous visitor and somebody else's
 * application, so it fails closed on every path that isn't an explicit yes.
 *
 * Contract with Traefik:
 *   2xx  → forward the request, copying the listed authResponseHeaders onto it
 *   3xx  → return the redirect to the browser (used to bounce to /login)
 *   4xx  → return the error to the browser
 *
 * Performance note: this runs once per asset request, so it does exactly one
 * indexed lookup and no caching. Caching was considered and rejected — a
 * stale cache here means a revoked user keeps their access, and this codebase
 * has already been bitten once by a file-cache race.
 */
class AuthzController extends Controller
{
    public function __invoke(Request $request, string $slug): Response
    {
        // Master switch off → nothing is mounted, deny everything.
        if (! config('devapps.enabled')) {
            return $this->deny('Portal pengembang tidak aktif.', 503);
        }

        $app = DevApp::where('slug', $slug)->first();

        if (! $app) {
            return $this->deny('Aplikasi tidak ditemukan.', 404);
        }

        $user = Auth::user();

        if ($app->allows($user)) {
            return $this->allow($user);
        }

        // Anonymous visitor to an app that needs a login → send them to sign
        // in and come back, rather than showing a bare 403.
        if (! $user && $app->requiresLogin()) {
            return $this->redirectToLogin($request, $app);
        }

        if (! $app->enabled) {
            return $this->deny('Aplikasi ini sedang dinonaktifkan oleh pengelola.', 503);
        }

        return $this->deny('Akun Anda tidak memiliki akses ke aplikasi ini.', 403);
    }

    /**
     * 200 plus the identity headers Traefik copies onto the forwarded
     * request. These are the dev app's only source of identity.
     */
    private function allow(?User $user): Response
    {
        $headers = config('devapps.identity_headers', []);

        $response = response('', 200);

        if ($user) {
            $response->headers->set($headers['id'] ?? 'X-Datakita-User-Id', (string) $user->id);
            // Names can carry non-ASCII; header values must not.
            $response->headers->set($headers['name'] ?? 'X-Datakita-User-Name', $this->headerSafe($user->name));
            $response->headers->set($headers['email'] ?? 'X-Datakita-User-Email', (string) $user->email);
            $response->headers->set($headers['role'] ?? 'X-Datakita-User-Role', (string) $user->role);
        } else {
            // Public app, anonymous visitor. Send the headers as empty rather
            // than omitting them, so the app can't be tricked into trusting a
            // client-supplied identity header that Traefik would otherwise
            // pass straight through.
            foreach ($headers as $header) {
                $response->headers->set($header, '');
            }
        }

        return $response;
    }

    /**
     * Bounce an anonymous visitor to the datakita login, remembering where
     * they were headed.
     *
     * The intended URL is deliberately NOT written to the session here. This
     * method runs inside Traefik's forwardAuth subrequest, so any session
     * cookie we set would have to be relayed back to the browser through a
     * non-2xx auth response — behaviour that varies between Traefik versions.
     * When it isn't relayed, the browser reaches /login on a different session
     * with no url.intended and Fortify drops it on /dashboard instead.
     *
     * Instead we send the browser to a normal datakita URL carrying the target,
     * and {@see self::rememberAndLogin()} does the session write in a request
     * the browser actually made.
     */
    private function redirectToLogin(Request $request, DevApp $app): Response
    {
        $target = $this->originalUrl($request) ?: $app->publicUrl();

        // Relative, deliberately. route() builds absolute URLs from the
        // current request's host, and this application trusts X-Forwarded-Host
        // — so an absolute redirect here could be pointed at an attacker's
        // domain by spoofing that header on a direct call to this endpoint.
        // A relative Location is resolved by the browser against the real
        // origin, which no header can influence.
        //
        // away() rather than to(): to() puts the path back through the URL
        // generator, which re-absolutises it against that same spoofable host.
        // away() emits the string as given.
        return redirect()->away(route('develop.masuk', ['next' => $target], false));
    }

    /**
     * Landing step between the auth gate and the login form.
     *
     * Runs as an ordinary browser request, so the session it writes is the
     * session the browser carries into /login.
     */
    public function rememberAndLogin(Request $request): Response
    {
        $next = (string) $request->query('next', '');

        // Only ever remember a URL on our own host — `next` is attacker-
        // supplied in the general case, and an unchecked value here would
        // turn the login page into an open redirect.
        if ($next !== '' && $this->isOwnHost($next)) {
            $request->session()->put('url.intended', $next);
        }

        return redirect()->to(route('login'));
    }

    /**
     * Rebuild the URL the browser actually asked for.
     *
     * On a forwardAuth call the request line points at this endpoint; the
     * original target only survives in the X-Forwarded-* headers, which
     * Traefik sets because the middleware has trustForwardHeader: true.
     */
    private function originalUrl(Request $request): ?string
    {
        $host = $request->header('X-Forwarded-Host');
        $uri  = $request->header('X-Forwarded-Uri');

        if (! $host || ! $uri) {
            return null;
        }

        $proto = $request->header('X-Forwarded-Proto', 'https');

        // Only ever hand back a URL on our own host — an attacker-supplied
        // X-Forwarded-Host must not turn the login page into an open redirect.
        $expected = parse_url((string) (config('devapps.public_host_url') ?: config('app.url')), PHP_URL_HOST);

        if ($expected && ! hash_equals((string) $expected, explode(':', $host)[0])) {
            return null;
        }

        return $proto . '://' . $host . $uri;
    }

    /**
     * Whether an absolute URL points at the host datakita serves.
     */
    private function isOwnHost(string $url): bool
    {
        $host     = parse_url($url, PHP_URL_HOST);
        $expected = parse_url((string) (config('devapps.public_host_url') ?: config('app.url')), PHP_URL_HOST);

        return $host && $expected && hash_equals((string) $expected, (string) $host);
    }

    /**
     * A small, self-contained error page. It renders inside whatever the
     * browser was loading, so it stays dependency-free.
     */
    private function deny(string $message, int $status): Response
    {
        $safe = e($message);
        $home = e(url('/'));

        $html = <<<HTML
        <!doctype html>
        <html lang="id"><head><meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Akses ditolak</title>
        <style>
          body{font-family:system-ui,-apple-system,"Segoe UI",sans-serif;background:#f8fafc;color:#0f172a;
               display:flex;align-items:center;justify-content:center;min-height:100vh;margin:0;padding:1.5rem}
          .card{background:#fff;border:1px solid #e2e8f0;border-radius:.75rem;padding:2rem;max-width:26rem;
                box-shadow:0 1px 3px rgba(0,0,0,.08);text-align:center}
          h1{font-size:1.125rem;margin:0 0 .5rem}
          p{color:#475569;font-size:.9375rem;line-height:1.5;margin:0 0 1.25rem}
          a{display:inline-block;background:#2563eb;color:#fff;text-decoration:none;padding:.5rem 1rem;
            border-radius:.5rem;font-size:.875rem;font-weight:500}
          @media (prefers-color-scheme:dark){
            body{background:#0f172a;color:#f1f5f9}
            .card{background:#1e293b;border-color:#334155}
            p{color:#94a3b8}
          }
        </style></head>
        <body><div class="card">
          <h1>Akses ditolak</h1>
          <p>{$safe}</p>
          <a href="{$home}">Kembali ke DataKita</a>
        </div></body></html>
        HTML;

        return response($html, $status)->header('Content-Type', 'text/html; charset=utf-8');
    }

    /**
     * HTTP header values must be ISO-8859-1; transliterate anything else so a
     * user with an accented name doesn't break the whole response.
     */
    private function headerSafe(?string $value): string
    {
        $value = (string) $value;

        $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $value);

        return preg_replace('/[^\x20-\x7E]/', '', $ascii === false ? $value : $ascii) ?? '';
    }
}
