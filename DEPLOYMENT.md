# Production Deployment Guide

## Vite Asset Build and Deployment

### Problem Solved
This guide addresses the "Vite manifest not found" error that occurs when deploying Laravel applications with Vite to production servers.

### Root Cause
The error occurs because:
1. Vite assets are not built for production
2. The `public/build/manifest.json` file is missing on the production server
3. Laravel's `@vite()` directive cannot locate the compiled assets

### Solution Steps

#### 1. Local Development Build
Before deploying to production, ensure you build the assets locally:

```bash
# Install dependencies (if not already installed)
npm install

# Build assets for production
npm run build
```

This command will:
- Compile and minify CSS/JS files
- Generate hashed filenames for cache busting
- Create the `manifest.json` file in `public/build/`
- Place all compiled assets in `public/build/assets/`

#### 2. Verify Build Output
After running `npm run build`, verify these files exist:
- `public/build/manifest.json`
- `public/build/assets/app-[hash].css`
- `public/build/assets/app-[hash].js`

#### 3. Deploy to Production Server
Upload the following directories/files to your production server:

**Required directories:**
```
public/build/
├── manifest.json
└── assets/
    ├── app-[hash].css
    └── app-[hash].js
```

**Production server path:**
```
/home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/
```

#### 4. Deployment Methods

**Option A: Manual Upload**
1. Build assets locally: `npm run build`
2. Upload `public/build/` directory to production server
3. Ensure file permissions are correct (644 for files, 755 for directories)

**Option B: Automated Deployment Script**
Create a deployment script that:
1. Runs `npm run build` on your local/CI environment
2. Uploads the built assets to the production server
3. Clears any application caches

**Option C: CI/CD Pipeline**
Set up a CI/CD pipeline that:
1. Installs Node.js dependencies
2. Runs `npm run build`
3. Deploys the built assets to production

### Important Notes

1. **Never run `npm run dev` on production** - This is for local development only
2. **Always run `npm run build`** - This creates optimized production assets
3. **Include the entire `public/build/` directory** in your deployment
4. **The `manifest.json` file is critical** - Laravel uses it to locate hashed asset files

### Troubleshooting

If you still get the manifest error after deployment:

1. **Check file existence:**
   ```bash
   ls -la /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/
   ```

2. **Verify manifest.json content:**
   ```bash
   cat /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/manifest.json
   ```

3. **Check file permissions:**
   ```bash
   chmod 644 /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/manifest.json
   chmod 644 /home/u762815253/domains/angkabatam.id/public_html/ipds/datakita/public/build/assets/*
   ```

4. **Clear Laravel caches:**
   ```bash
   php artisan config:clear
   php artisan view:clear
   php artisan cache:clear
   ```

### Configuration Details

The Vite configuration in `vite.config.js` has been updated to ensure the manifest.json is placed in the correct location:

```javascript
build: {
    manifest: 'manifest.json', // Places manifest.json directly in build directory
    outDir: 'public/build',
}
```

This ensures Laravel can find the manifest at `public/build/manifest.json` as expected.