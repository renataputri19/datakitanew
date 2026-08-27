<?php

namespace App\Http\Controllers\Develop;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Develop\Concerns\GateResponses;
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
    // Shared with ProxyController, the in-app gate that replaced this one on
    // this deployment. Both must describe the visitor identically.
    use GateResponses;

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
        $response = response('', 200);

        // Always every header, empty for an anonymous visitor on a public app,
        // so the app can't be tricked into trusting a client-supplied identity
        // header that Traefik would otherwise pass straight through.
        foreach ($this->identityHeaderValues($user) as $header => $value) {
            $response->headers->set($header, $value);
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
     * The gate's error page, shared with ProxyController.
     */
    private function deny(string $message, int $status): Response
    {
        return $this->gatePage('Akses ditolak', $message, $status);
    }
}
