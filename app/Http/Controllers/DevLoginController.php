<?php

namespace App\Http\Controllers;

use App\Http\Middleware\DevAuthGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DevLoginController
 *
 * Handles the one-time "unlock" gate for the development environment.
 *
 * Only the account matching DEV_ALLOWED_EMAIL (e.g. putri.henessa@bps.go.id)
 * can log in here.  A successful login sets a persistent "dev_env_unlocked"
 * cookie that lasts 8 hours.  That cookie survives Fortify logout, so after
 * this first unlock ANY account in the database can use the full app — login,
 * register, update profile — exactly like production.
 *
 * In production this controller is unreachable: abort_unless() exits with 404.
 */
class DevLoginController extends Controller
{
    /**
     * Cookie name — must match DevAuthGate::DEV_COOKIE.
     * Lifetime in minutes (8 hours).
     */
    private const DEV_COOKIE   = DevAuthGate::DEV_COOKIE;
    private const COOKIE_TTL   = 60 * 8; // 480 minutes

    // ──────────────────────────────────────────────────────────────────────────

    public function showForm(Request $request)
    {
        abort_unless(config('app.dev_auth_enabled'), 404);

        // Already authenticated AND cookie already set → go straight to app
        if (Auth::check() && $request->cookie(self::DEV_COOKIE) === '1') {
            return redirect($this->intendedUrl());
        }

        return view('dev.login', [
            'allowedEmail' => config('app.dev_allowed_email'),
        ]);
    }

    /**
     * Process the unlock form.
     *
     * 1. Validates credentials against the real users table (Auth::attempt)
     * 2. Rejects anyone whose email ≠ DEV_ALLOWED_EMAIL
     * 3. On success: sets the unlock cookie, then redirects to the intended page.
     *    From this point on, ANY account can use the full production auth flow.
     */
    public function login(Request $request)
    {
        abort_unless(config('app.dev_auth_enabled'), 404);

        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        // ── Step 1: check DEV_ALLOWED_EMAIL before even hitting the database ─
        $allowedEmail = config('app.dev_allowed_email');

        if ($allowedEmail && strtolower($credentials['email']) !== strtolower($allowedEmail)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Hanya akun yang berwenang yang dapat membuka akses dev environment ini.',
                ]);
        }

        // ── Step 2: authenticate against the real users table ────────────────
        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors([
                    'email' => 'Email atau kata sandi salah.',
                ]);
        }

        $request->session()->regenerate();

        // ── Step 3: stamp the browser as "unlocked" ───────────────────────────
        // This cookie survives Auth::logout() / session()->invalidate().
        // Once set, DevAuthGate will let ANY account pass through,
        // so the rest of the app behaves exactly like production.
        $unlockCookie = cookie(
            name:     self::DEV_COOKIE,
            value:    '1',
            minutes:  self::COOKIE_TTL,
            path:     '/',
            secure:   $request->isSecure(),
            httpOnly: true,
            sameSite: 'Lax',
        );

        $intended = session()->pull('dev_intended_url', route('home'));

        return redirect($intended)->cookie($unlockCookie);
    }

    /**
     * Log out and clear the unlock cookie, effectively re-locking the gate.
     * Normal Fortify logout does NOT call this — it only clears the session.
     * Use this only when you want to fully re-lock the dev environment.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Expire the unlock cookie to re-lock the environment
        $expireCookie = cookie(self::DEV_COOKIE, '', -1, '/');

        return redirect()->route('dev.login')->cookie($expireCookie);
    }

    private function intendedUrl(): string
    {
        return session()->pull('dev_intended_url', route('home'));
    }
}
