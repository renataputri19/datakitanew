<?php

/**
 * This script creates a copy of the storage/app/public directory in public/storage
 * Use this when the symlink function is not available on your server
 */

// Define paths
$targetPath = __DIR__ . '/storage/app/public';
$linkPath = __DIR__ . '/public/storage';

// Check if the target directory exists
if (!file_exists($targetPath)) {
    echo "Error: The target directory {$targetPath} does not exist.\n";
    exit(1);
}

// Remove existing directory if it exists
if (file_exists($linkPath)) {
    removeDirectory($linkPath);
}

// Create the directory
mkdir($linkPath, 0755, true);

// Copy all files from storage/app/public to public/storage
copyDirectory($targetPath, $linkPath);

echo "The [{$linkPath}] directory has been created and populated with the contents of [{$targetPath}].\n";
echo "Note: This is not a symbolic link, but a copy. You will need to run this script again if you add new files to storage/app/public.\n";

/**
 * Copy a directory from one location to another.
 *
 * @param string $source
 * @param string $destination
 * @return void
 */
function copyDirectory($source, $destination)
{
    if (!is_dir($source)) {
        return;
    }

    // Create the destination directory if it doesn't exist
    if (!is_dir($destination)) {
        mkdir($destination, 0755, true);
    }

    // Get all files and directories in the source directory
    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($items as $item) {
        $sourcePath = $item->getPathname();
        $relativePath = substr($sourcePath, strlen($source) + 1);
        $destinationPath = $destination . '/' . $relativePath;

        if ($item->isDir()) {
            // Create the directory
            if (!is_dir($destinationPath)) {
                mkdir($destinationPath, 0755, true);
            }
        } else {
            // Copy the file
            copy($sourcePath, $destinationPath);
        }
    }
}

/**
 * Remove a directory and all its contents.
 *
 * @param string $directory
 * @return void
 */
function removeDirectory($directory)
{
    if (!is_dir($directory)) {
        return;
    }

    $items = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::CHILD_FIRST
    );

    foreach ($items as $item) {
        if ($item->isDir()) {
            rmdir($item->getPathname());
        } else {
            unlink($item->getPathname());
        }
    }

    rmdir($directory);
}
