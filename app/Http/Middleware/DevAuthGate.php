<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * DevAuthGate
 *
 * Two-stage dev barrier:
 *
 * Stage 1 – Environment lock
 *   The first visit has no unlock cookie → redirected to /dev-login.
 *   Only DEV_ALLOWED_EMAIL can log in there.
 *   A successful login sets a persistent "dev_unlocked" cookie (8 hours).
 *
 * Stage 2 – Full production behaviour
 *   Every subsequent request (even after Fortify logout) carries that cookie.
 *   The gate sees the cookie and lets EVERYTHING through, so normal Fortify
 *   login/register/profile-update/etc. all work exactly like production.
 *
 * ACTIVATION:  DEV_AUTH_ENABLED=true in .env
 * PRODUCTION:  Leave the flag unset — this middleware is a complete no-op.
 */
class DevAuthGate
{
    /**
     * Name of the cookie that marks "this browser has been unlocked".
     * Must match DevLoginController::DEV_COOKIE.
     */
    public const DEV_COOKIE = 'dev_env_unlocked';

    /**
     * Routes that always pass through regardless of cookie / auth state.
     * Only the dev-login page itself needs to be here to avoid a redirect loop.
     */
    private const ALWAYS_ALLOW = [
        'dev.login',
        'dev.login.submit',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // ── 1. Feature flag off → completely inactive (production path) ──────
        if (!config('app.dev_auth_enabled')) {
            return $next($request);
        }

        // ── 2. Already authenticated → full production behaviour ─────────────
        if (Auth::check()) {
            return $next($request);
        }

        // ── 3. Browser already unlocked by a previous putri.henessa login ────
        //       The cookie survives Fortify logout, so any user can log in now.
        if ($request->cookie(self::DEV_COOKIE) === '1') {
            return $next($request);
        }

        // ── 4. The dev-login page itself must always be reachable ────────────
        if ($request->routeIs(...self::ALWAYS_ALLOW)) {
            return $next($request);
        }

        // ── 5. JSON callers get a clean 401 ─────────────────────────────────
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Dev environment locked.'], 401);
        }

        // ── 6. First visit — send to the dev gate ────────────────────────────
        session(['dev_intended_url' => $request->url()]);

        $loginUrl = app('router')->has('dev.login')
            ? route('dev.login')
            : url('/dev-login');

        return redirect($loginUrl);
    }
}
