<?php

namespace App\Helpers;

class YoutubeHelper
{
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
    
    /**
     * Get YouTube thumbnail URL
     * 
     * @param string $videoId
     * @param string $quality (maxresdefault, hqdefault, mqdefault, sddefault)
     * @return string
     */
    public static function getThumbnailUrl($videoId, $quality = 'maxresdefault')
    {
        return "https://img.youtube.com/vi/{$videoId}/{$quality}.jpg";
    }
}
