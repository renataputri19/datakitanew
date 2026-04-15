<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * DevAuthGate
 *
 * Intercepts every web request in the development environment and redirects
 * unauthenticated visitors to the manual dev-login page.
 *
 * ACTIVATION:  Set  DEV_AUTH_ENABLED=true  in your .env file.
 * PRODUCTION:  Leave DEV_AUTH_ENABLED unset (or false) — this middleware
 *              becomes a no-op and adds zero overhead to the production stack.
 */
class DevAuthGate
{
    /**
     * Routes that are always passable without authentication in dev mode.
     * These prevent redirect loops and allow assets/CSRF to function.
     */
    private const ALWAYS_ALLOW = [
        'dev.login',        // the dev login page itself
        'dev.login.submit', // the dev login POST handler
    ];

    public function handle(Request $request, Closure $next): Response
    {
        // ── 1. Feature flag: completely inactive in production ────────────────
        if (!config('app.dev_auth_enabled')) {
            return $next($request);
        }

        // ── 2. Already authenticated → pass through ───────────────────────────
        if (Auth::check()) {
            return $next($request);
        }

        // ── 3. Never intercept the dev-login routes (avoid redirect loop) ─────
        if ($request->routeIs(...self::ALWAYS_ALLOW)) {
            return $next($request);
        }

        // ── 4. Let JSON/API callers get a clean 401 instead of a redirect ──────
        if ($request->expectsJson()) {
            return response()->json(['message' => 'Unauthenticated (dev gate).'], 401);
        }

        // ── 5. Store intended URL so we can redirect after login ──────────────
        session(['dev_intended_url' => $request->url()]);

        return redirect()->route('dev.login');
    }
}
