<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * DevLoginController
 *
 * Handles the manual login page shown exclusively in the development
 * environment (DEV_AUTH_ENABLED=true).  Uses the same users table and
 * Auth::attempt() as the production flow so credentials are identical.
 *
 * In production this controller is never reachable: the routes are only
 * registered when config('app.dev_auth_enabled') is truthy.
 */
class DevLoginController extends Controller
{
    /**
     * Show the dev login form.
     * Hard-aborts with 404 as a second safety net if somehow reached in prod.
     */
    public function showForm(Request $request)
    {
        abort_unless(config('app.dev_auth_enabled'), 404);

        // Already authenticated → send straight to the intended page or home
        if (Auth::check()) {
            return redirect($this->intendedUrl());
        }

        return view('dev.login');
    }

    /**
     * Process the dev login form submission.
     * Uses the exact same Auth::attempt() call as production; credentials are
     * validated against the live users table, maintaining data consistency.
     */
    public function login(Request $request)
    {
        abort_unless(config('app.dev_auth_enabled'), 404);

        $credentials = $request->validate([
            'email'    => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:1'],
        ], [
            'email.required'    => 'Alamat email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Kata sandi wajib diisi.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $intended = session()->pull('dev_intended_url', route('home'));

            return redirect($intended);
        }

        return back()
            ->withInput($request->only('email'))
            ->withErrors([
                'email' => 'Email atau kata sandi tidak cocok. Pastikan akun ini ada di database dev.',
            ]);
    }

    /**
     * Log the user out and return to the dev login page.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dev.login');
    }

    private function intendedUrl(): string
    {
        return session()->pull('dev_intended_url', route('home'));
    }
}
