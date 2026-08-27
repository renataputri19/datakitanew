<?php

namespace App\Providers;

use App\Support\AppRole;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/dashboard';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Which route files load is decided by APP_ROLE, so a portal-only
        // container never publishes /bps or /superadmin. With APP_ROLE unset
        // this is exactly the pre-split route table plus nothing.
        //
        // Note for deployment: docker/entrypoint.sh runs `route:cache` at
        // container start, after the environment is in place, so the cache is
        // always built for the role that container actually runs as.
        $this->routes(function () {
            if (AppRole::servesDatakita()) {
                Route::middleware('api')
                    ->prefix('api')
                    ->group(base_path('routes/api.php'));

                Route::middleware('web')
                    ->group(base_path('routes/web.php'));
            }

            // Registered after web.php. Safe in `all` mode: no DataKita route
            // matches /develop/*, and Route::fallback() is sorted last by the
            // router regardless of where it was declared.
            if (AppRole::servesDevPortal()) {
                Route::middleware('web')
                    ->group(base_path('routes/devportal.php'));
            }
        });
    }
}
