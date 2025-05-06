<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
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
     * Display the dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Sample dashboard data
        $stats = [
            'data_views' => 1250,
            'downloads' => 350,
            'favorites' => 45
        ];
        
        return view('dashboard.index', compact('user', 'stats'));
    }

    /**
     * Display the user profile.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        $user = Auth::user();
        
        return view('dashboard.profile', compact('user'));
    }

    /**
     * Display the settings page.
     *
     * @return \Illuminate\View\View
     */
    public function settings()
    {
        $user = Auth::user();
        
        return view('dashboard.settings', compact('user'));
    }
}
