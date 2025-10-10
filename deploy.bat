@echo off
REM Production Deployment Script for Laravel + Vite (Windows)
REM This script builds assets and prepares them for production deployment

echo 🚀 Starting production deployment process...

REM Check if npm is available
where npm >nul 2>nul
if %errorlevel% neq 0 (
    echo ❌ Error: npm is not installed. Please install Node.js and npm first.
    exit /b 1
)

REM Check if package.json exists
if not exist "package.json" (
    echo ❌ Error: package.json not found. Make sure you're in the project root directory.
    exit /b 1
)

REM Install dependencies
echo 📦 Installing Node.js dependencies...
call npm install

if %errorlevel% neq 0 (
    echo ❌ Error: Failed to install dependencies.
    exit /b 1
)

REM Build assets for production
echo 🔨 Building assets for production...
call npm run build

if %errorlevel% neq 0 (
    echo ❌ Error: Failed to build assets.
    exit /b 1
)

REM Verify build output
echo ✅ Verifying build output...

if not exist "public\build\manifest.json" (
    echo ❌ Error: manifest.json not found in public\build\
    exit /b 1
)

if not exist "public\build\assets" (
    echo ❌ Error: assets directory not found in public\build\
    exit /b 1
)

echo ✅ Build verification successful!

REM Display build summary
echo.
echo 📊 Build Summary:
echo ==================
dir "public\build\manifest.json"
echo Asset files:
dir "public\build\assets\"

echo.
echo 🎉 Production build completed successfully!
echo.
echo 📋 Next Steps:
echo 1. Upload the entire 'public\build\' directory to your production server
echo 2. Ensure the files are placed at: /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/
echo 3. Set proper file permissions (644 for files, 755 for directories)
echo 4. Clear Laravel caches on production server if needed
echo.
echo 🔗 For detailed deployment instructions, see DEPLOYMENT.md

pause