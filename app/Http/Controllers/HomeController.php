<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Video;

class HomeController extends Controller
{
    // Maximum number of items to display on homepage
    const MAX_NEWS = 4;
    const MAX_VIDEOS = 2;

    // Official BPS websites (same as in NewsController)
    const BPS_NEWS_URL = 'https://batamkota.bps.go.id/id/pressrelease';
    const BPS_YOUTUBE_URL = 'https://www.youtube.com/@bpskotabatam1884';

    public function index()
    {
        // Get data for homepage
        $data = $this->prepareHomeData();

        return view('home', $data);
    }

    /**
     * Prepare all data needed for the homepage
     *
     * @return array
     */
    private function prepareHomeData()
    {
        // Get featured news (BRS category, limited to MAX_NEWS)
        $featuredNews = News::where('category', 'BRS')
            ->latest('date')
            ->limit(self::MAX_NEWS)
            ->get();

        // Format dates for news
        $featuredNews = $this->formatDates($featuredNews);

        // Get featured videos (limited to MAX_VIDEOS)
        $featuredVideos = Video::latest('date')
            ->limit(self::MAX_VIDEOS)
            ->get();

        // Format dates for videos
        $featuredVideos = $this->formatDates($featuredVideos);

        // Get latest video month and year for dynamic title
        $latestVideo = Video::latest('date')->first();
        $latestMonth = null;
        $latestYear = null;

        if ($latestVideo) {
            $latestMonth = $latestVideo->date->translatedFormat('F');
            $latestYear = $latestVideo->date->format('Y');
        }

        // Add official URLs for "View More" links
        $bpsNewsUrl = self::BPS_NEWS_URL;
        $bpsYoutubeUrl = self::BPS_YOUTUBE_URL;

        return compact(
            'featuredNews',
            'featuredVideos',
            'bpsNewsUrl',
            'bpsYoutubeUrl',
            'latestMonth',
            'latestYear'
        );
    }

    /**
     * Format dates for a collection of items
     *
     * @param \Illuminate\Database\Eloquent\Collection $items
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function formatDates($items)
    {
        return $items->transform(function ($item) {
            $item->formatted_date = $item->date->translatedFormat('j F Y');
            return $item;
        });
    }

    public function about()
    {
        return view('about');
    }

    public function faq()
    {
        return view('faq');
    }
}
