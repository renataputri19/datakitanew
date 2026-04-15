<?php

namespace App\Http\Middleware;

use App\Http\Middleware\DevAuthGate;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class DevAccessRestriction
{
    /**
     * Restrict access to a single allowed email in dev environments.
     *
     * Set DEV_ALLOWED_EMAIL in .env to enable restriction.
     * Leave unset (or empty) in production to disable entirely.
     *
     * Exception: if the DevAuthGate unlock cookie is present, the environment
     * has already been opened by the authorised account, so any user may log in
     * and use the full app (same behaviour as production).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedEmail = config('app.dev_allowed_email');

        // No restriction configured → pass through
        if (!$allowedEmail) {
            return $next($request);
        }

        // Environment already unlocked by the authorised first login → pass through
        // This lets any account use the app after putri.henessa opened the gate.
        if ($request->cookie(DevAuthGate::DEV_COOKIE) === '1') {
            return $next($request);
        }

        // Still enforce the single-email lock for authenticated users
        if (Auth::check()) {
            if (strtolower(Auth::user()->email) !== strtolower($allowedEmail)) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Akses ke lingkungan ini dibatasi. Anda tidak memiliki izin untuk masuk.']);
            }
        }

        return $next($request);
    }
}
