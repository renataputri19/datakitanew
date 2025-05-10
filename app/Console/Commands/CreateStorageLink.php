<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CreateStorageLink extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'storage:link:custom';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a symbolic link from "public/storage" to "storage/app/public" using copy instead of symlink';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $targetPath = storage_path('app/public');
        $linkPath = public_path('storage');

        // Remove existing directory if it exists
        if (File::exists($linkPath)) {
            File::deleteDirectory($linkPath);
        }

        // Create the directory
        File::makeDirectory($linkPath, 0755, true);

        // Copy all files from storage/app/public to public/storage
        $this->copyDirectory($targetPath, $linkPath);

        $this->info('The [' . $linkPath . '] directory has been created and populated with the contents of [' . $targetPath . '].');
        $this->info('Note: This is not a symbolic link, but a copy. You will need to run this command again if you add new files to storage/app/public.');
    }

    /**
     * Copy a directory from one location to another.
     *
     * @param string $source
     * @param string $destination
     * @return void
     */
    protected function copyDirectory($source, $destination)
    {
        if (!File::isDirectory($source)) {
            return;
        }

        // Create the destination directory if it doesn't exist
        if (!File::isDirectory($destination)) {
            File::makeDirectory($destination, 0755, true);
        }

        // Get all files and directories in the source directory
        $items = File::allFiles($source);

        foreach ($items as $item) {
            $sourcePath = $item->getPathname();
            $relativePath = str_replace($source . '/', '', $sourcePath);
            $destinationPath = $destination . '/' . $relativePath;

            // Create the directory structure
            $destinationDir = dirname($destinationPath);
            if (!File::isDirectory($destinationDir)) {
                File::makeDirectory($destinationDir, 0755, true);
            }

            // Copy the file
            File::copy($sourcePath, $destinationPath);
        }
    }
}
