<?php

namespace App\Http\Controllers;

class MonalisaController extends Controller
{
    /**
     * Display the Monalisa homepage.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('monalisa.home');
    }

    /**
     * Display the Monalisa dashboard (for authenticated BPS or Kominfo users).
     * Redirects to appropriate dashboard based on user role.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        $user = auth()->user();

        // Redirect to appropriate dashboard based on user role
        if ($user->is_bps) {
            return redirect()->route('monalisa.bps.dashboard');
        } elseif ($user->is_kominfo_user) {
            return redirect()->route('monalisa.kominfo.dashboard');
        }

        // If user is neither BPS nor Kominfo, show error
        return redirect()->route('monalisa.index')
            ->with('error', 'Anda tidak memiliki akses ke dashboard MONALISA.');
    }
}
