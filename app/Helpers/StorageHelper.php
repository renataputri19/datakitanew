<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StorageHelper
{
    /**
     * Ensure the directory exists in the public storage.
     *
     * @param string $directory
     * @return void
     */
    public static function ensureDirectoryExists(string $directory): void
    {
        if (!Storage::disk('public')->exists($directory)) {
            Storage::disk('public')->makeDirectory($directory);
            Log::info("Directory '{$directory}' created in public storage.");
        }
    }

    /**
     * Get the URL for a file in the public storage.
     *
     * @param string|null $path
     * @return string|null
     */
    public static function url(?string $path): ?string
    {
        if (empty($path)) {
            return null;
        }

        return Storage::disk('public')->url($path);
    }
}
