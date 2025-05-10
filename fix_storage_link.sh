#!/bin/bash

# This script fixes the storage link issue on the production server
# It creates a copy of the storage/app/public directory in public/storage
# Use this when the symlink function is not available on your server

# Define paths
TARGET_PATH="storage/app/public"
LINK_PATH="public/storage"

# Check if the target directory exists
if [ ! -d "$TARGET_PATH" ]; then
    echo "Error: The target directory $TARGET_PATH does not exist."
    exit 1
fi

# Remove existing directory if it exists
if [ -d "$LINK_PATH" ]; then
    rm -rf "$LINK_PATH"
fi

# Create the directory
mkdir -p "$LINK_PATH"

# Copy all files from storage/app/public to public/storage
cp -R "$TARGET_PATH"/* "$LINK_PATH"/

echo "The [$LINK_PATH] directory has been created and populated with the contents of [$TARGET_PATH]."
echo "Note: This is not a symbolic link, but a copy. You will need to run this script again if you add new files to storage/app/public."

# Set proper permissions
chmod -R 755 "$LINK_PATH"
echo "Permissions set to 755 for $LINK_PATH"
