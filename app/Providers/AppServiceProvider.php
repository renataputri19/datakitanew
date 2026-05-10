<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force https:// for all generated URLs (asset(), route(), url())
        // when behind a TLS-terminating reverse proxy like Dokploy/Traefik.
        // Without this, Vite emits http:// URLs which the browser blocks
        // as mixed content.
        //
        // Trigger on ANY of:
        //  - APP_URL is https://...           (the deployment is served over TLS)
        //  - FORCE_HTTPS=true                  (explicit operator override)
        //  - APP_ENV=production                (legacy default)
        //  - the current request came in over https (X-Forwarded-Proto via TrustProxies)
        $appUrl = (string) config('app.url', '');
        $isHttpsAppUrl = str_starts_with($appUrl, 'https://');
        $requestIsSecure = $this->app->runningInConsole()
            ? false
            : (bool) request()->isSecure();

        if ($isHttpsAppUrl
            || env('FORCE_HTTPS', false)
            || $this->app->environment('production')
            || $requestIsSecure
        ) {
            URL::forceScheme('https');
        }
    }
}
