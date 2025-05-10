<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class StorageLinkServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Check if the storage link exists and create it if it doesn't
        $this->ensureStorageLinkExists();
    }

    /**
     * Ensure the storage link exists.
     */
    protected function ensureStorageLinkExists(): void
    {
        $publicPath = public_path('storage');
        $storagePath = storage_path('app/public');

        // If the storage link doesn't exist, create it
        if (!file_exists($publicPath) || !is_link($publicPath)) {
            try {
                // On Windows, we need to use a different approach
                if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
                    // Remove the directory if it exists but is not a symlink
                    if (file_exists($publicPath) && !is_link($publicPath)) {
                        // Use rmdir for empty directories or unlink for files
                        if (is_dir($publicPath)) {
                            rmdir($publicPath);
                        } else {
                            unlink($publicPath);
                        }
                    }

                    // Create the symlink using the Windows-specific command
                    exec('mklink /D "' . $publicPath . '" "' . $storagePath . '"');
                } else {
                    // For non-Windows systems, use the standard symlink function
                    symlink($storagePath, $publicPath);
                }

                Log::info('Storage link created successfully.');
            } catch (\Exception $e) {
                Log::error('Failed to create storage link: ' . $e->getMessage());
            }
        }
    }
}
