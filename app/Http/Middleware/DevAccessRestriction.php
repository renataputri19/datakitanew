<?php

namespace App\Http\Middleware;

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
     */
    public function handle(Request $request, Closure $next): Response
    {
        $allowedEmail = config('app.dev_allowed_email');

        if ($allowedEmail && Auth::check()) {
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
