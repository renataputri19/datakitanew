<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
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
        $this->middleware(['auth', 'is_bps']);
    }

    /**
     * Show the user profile page.
     *
     * @return \Illuminate\View\View
     */
    public function profile()
    {
        return view('bps.profile');
    }
}
