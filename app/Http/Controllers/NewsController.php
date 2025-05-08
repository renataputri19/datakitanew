<?php

namespace App\Http\Controllers;

use App\Models\News;
use App\Models\Video;
use Illuminate\Http\Request;

class NewsController extends Controller
{
    // Maximum number of items to display
    const MAX_ITEMS = 9;

    // Official BPS websites
    const BPS_NEWS_URL = 'https://batamkota.bps.go.id/id/pressrelease';
    const BPS_YOUTUBE_URL = 'https://www.youtube.com/@bpskotabatam1884';

    public function index()
    {
        // Get all data with proper formatting
        $data = $this->prepareNewsData();

        // Return view with data
        return view('news.index', $data);
    }

    /**
     * Prepare all data needed for the news index page
     *
     * @return array
     */
    private function prepareNewsData()
    {
        // Get limited news items from database
        $newsItems = News::latest('date')->limit(self::MAX_ITEMS)->get();
        $newsItems = $this->formatDates($newsItems);

        // Get limited videos from database
        $videos = Video::latest('date')->limit(self::MAX_ITEMS)->get();
        $videos = $this->formatDates($videos);

        // Prepare category-specific news
        $brsNews = $this->getBRSNews();
        $eventNews = $this->getEventNews();
        $publicationNews = $this->getPublicationNews();

        // Add official URLs for "View More" links
        $bpsNewsUrl = self::BPS_NEWS_URL;
        $bpsYoutubeUrl = self::BPS_YOUTUBE_URL;

        return compact(
            'newsItems',
            'videos',
            'brsNews',
            'eventNews',
            'publicationNews',
            'bpsNewsUrl',
            'bpsYoutubeUrl'
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
            $item->formatted_date = $item->date->format('j F Y');
            return $item;
        });
    }

    /**
     * Get BRS news items
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getBRSNews()
    {
        $news = News::where('category', 'BRS')
            ->latest('date')
            ->limit(self::MAX_ITEMS)
            ->get();

        return $this->formatDates($news);
    }

    /**
     * Get Event news items
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getEventNews()
    {
        $news = News::where('category', 'Event')
            ->latest('date')
            ->limit(self::MAX_ITEMS)
            ->get();

        return $this->formatDates($news);
    }

    /**
     * Get Publication news items
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getPublicationNews()
    {
        $news = News::where('category', 'Publikasi')
            ->latest('date')
            ->limit(self::MAX_ITEMS)
            ->get();

        return $this->formatDates($news);
    }

    /**
     * Extract YouTube video ID from URL
     *
     * @param string $url
     * @return string|null
     */
    public static function extractYoutubeId($url)
    {
        $videoId = null;
        if (preg_match('/(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches)) {
            $videoId = $matches[1];
        }
        return $videoId;
    }

    public function show($id)
    {
        // Find news item by ID
        $newsItem = News::find($id);

        // If news item not found, redirect to news index with error message
        if (!$newsItem) {
            return redirect()->route('news')->with('error', 'Berita tidak ditemukan.');
        }

        // Format date for display
        $newsItem->formatted_date = $newsItem->date->format('j F Y');

        // Get related news
        $relatedNews = $newsItem->getRelatedNews();

        // Format date for related news
        $relatedNews = $this->formatDates($relatedNews);

        // Add related news to news item
        $newsItem->related_news = $relatedNews;

        return view('news.show', compact('newsItem'));
    }
}
