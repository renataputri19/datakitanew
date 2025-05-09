<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Domain;
use App\Models\TimelineEvent;

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
     * Redirect to the Monalisa dashboard.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function dashboard()
    {
        return redirect('/systems/monalisa/dashboard');
    }
}
