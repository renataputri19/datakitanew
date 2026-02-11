<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\News;
use App\Models\Video;
use App\Models\SurveyResponse;

class DashboardController extends Controller
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
     * Display the BPS dashboard.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $user = Auth::user();
        
        // Get counts for dashboard
        $newsCount = News::count();
        $videoCount = Video::count();
        $sibstrCount = SurveyResponse::where('survey_type', 'sibstr')->count();
        $sibstrCompletedCount = SurveyResponse::where('survey_type', 'sibstr')->where('is_completed', true)->count();
        
        // Get recent items
        $recentNews = News::orderBy('created_at', 'desc')->take(5)->get();
        $recentVideos = Video::orderBy('created_at', 'desc')->take(5)->get();
        
        return view('bps.dashboard', compact('user', 'newsCount', 'videoCount', 'sibstrCount', 'sibstrCompletedCount', 'recentNews', 'recentVideos'));
    }
}
