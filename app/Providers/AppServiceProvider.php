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
        if (env('FORCE_HTTPS', false) || $this->app->environment('production')) {
            URL::forceScheme('https');
        }
    }
}
