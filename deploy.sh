#!/bin/bash

# Production Deployment Script for Laravel + Vite
# This script builds assets and prepares them for production deployment

echo "🚀 Starting production deployment process..."

# Check if Node.js is available
if ! command -v npm &> /dev/null; then
    echo "❌ Error: npm is not installed. Please install Node.js and npm first."
    exit 1
fi

# Check if package.json exists
if [ ! -f "package.json" ]; then
    echo "❌ Error: package.json not found. Make sure you're in the project root directory."
    exit 1
fi

# Install dependencies
echo "📦 Installing Node.js dependencies..."
npm install

if [ $? -ne 0 ]; then
    echo "❌ Error: Failed to install dependencies."
    exit 1
fi

# Build assets for production
echo "🔨 Building assets for production..."
npm run build

if [ $? -ne 0 ]; then
    echo "❌ Error: Failed to build assets."
    exit 1
fi

# Verify build output
echo "✅ Verifying build output..."

if [ ! -f "public/build/manifest.json" ]; then
    echo "❌ Error: manifest.json not found in public/build/"
    exit 1
fi

if [ ! -d "public/build/assets" ]; then
    echo "❌ Error: assets directory not found in public/build/"
    exit 1
fi

echo "✅ Build verification successful!"

# Display build summary
echo ""
echo "📊 Build Summary:"
echo "=================="
echo "Manifest file: $(ls -la public/build/manifest.json)"
echo "Asset files:"
ls -la public/build/assets/

echo ""
echo "🎉 Production build completed successfully!"
echo ""
echo "📋 Next Steps:"
echo "1. Upload the entire 'public/build/' directory to your production server"
echo "2. Ensure the files are placed at: /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/"
echo "3. Set proper file permissions (644 for files, 755 for directories)"
echo "4. Clear Laravel caches on production server if needed"
echo ""
echo "🔗 For detailed deployment instructions, see DEPLOYMENT.md"