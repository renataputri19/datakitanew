<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the user profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profile(Request $request)
    {
        // Determine if we're in the BPS section
        $isBpsSection = $request->is('bps*');
        
        // Choose the appropriate layout
        $layout = $isBpsSection ? 'layouts.bps' : 'layouts.app';
        
        return view('dashboard.profile', [
            'layout' => $layout,
            'backRoute' => $isBpsSection ? route('bps.dashboard') : route('dashboard')
        ]);
    }

    /**
     * Show the password change page.
     *
     * @return \Illuminate\View\View
     */
    public function password(Request $request)
    {
        // Determine if we're in the BPS section
        $isBpsSection = $request->is('bps*');
        
        // Choose the appropriate layout
        $layout = $isBpsSection ? 'layouts.bps' : 'layouts.app';
        
        return view('dashboard.password', [
            'layout' => $layout,
            'backRoute' => $isBpsSection ? route('bps.dashboard') : route('dashboard')
        ]);
    }
}
