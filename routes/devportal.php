<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Develop\DevAppController;
use App\Http\Controllers\Develop\AuthzController as DevelopAuthzController;
use App\Http\Controllers\Develop\ProxyController;
use App\Http\Middleware\VerifyCsrfToken;

/*
|--------------------------------------------------------------------------
| Developer Portal Routes
|--------------------------------------------------------------------------
|
| Lets a BPS user mount an application built in their own Git repo under a
| path on this domain. The app runs in its own container on Dokploy; only
| the routing and the access decision live here.
|
| Loaded by RouteServiceProvider when APP_ROLE is `devportal` or `all`, in
| the `web` middleware group — the access decision reads the session cookie.
| With APP_ROLE unset (production) this file is never read and none of these
| paths exist. See App\Support\AppRole and docs/DEVELOP_PORTAL.md.
|
| Ordering matters inside this file: /develop/masuk must stay above the
| group, or /develop/{app} swallows it.
|
*/

// Edge authorisation endpoint, called by Traefik's forwardAuth before every
// request to a dev app. Deliberately outside the auth middleware: it must be
// reachable anonymously so it can answer "no" and bounce to /login. It runs
// in the `web` group so it can read the session cookie Traefik forwards.
Route::get('/develop/authz/{slug}', DevelopAuthzController::class)
    ->where('slug', '[a-z0-9-]+')
    ->name('develop.authz');

// Landing step between the gate and the login form. The gate cannot write the
// session itself — it runs inside Traefik's forwardAuth subrequest, whose
// Set-Cookie is not reliably relayed to the browser — so it sends the visitor
// here, and this records where they were headed. Guest-accessible by design.
Route::get('/develop/masuk', [DevelopAuthzController::class, 'rememberAndLogin'])
    ->name('develop.masuk');

Route::middleware(['auth', 'is_bps'])->prefix('develop')->name('develop.')->group(function () {
    Route::get('/',        [DevAppController::class, 'index'])->name('index');
    Route::get('/create',  [DevAppController::class, 'create'])->name('create');
    Route::post('/',       [DevAppController::class, 'store'])->name('store');

    Route::get('/{app}',            [DevAppController::class, 'show'])->name('show');
    Route::get('/{app}/edit',       [DevAppController::class, 'edit'])->name('edit');
    Route::put('/{app}',            [DevAppController::class, 'update'])->name('update');
    Route::delete('/{app}',         [DevAppController::class, 'destroy'])->name('destroy');

    Route::post('/{app}/deploy',    [DevAppController::class, 'deploy'])->name('deploy');
    Route::post('/{app}/refresh',   [DevAppController::class, 'refresh'])->name('refresh');
    Route::post('/{app}/stop',      [DevAppController::class, 'stop'])->name('stop');
    Route::post('/{app}/start',     [DevAppController::class, 'start'])->name('start');
    Route::post('/{app}/toggle',    [DevAppController::class, 'toggle'])->name('toggle');

    Route::get('/{app}/traefik.yml', [DevAppController::class, 'traefikConfig'])->name('traefik');
});

/*
|--------------------------------------------------------------------------
| The proxy
|--------------------------------------------------------------------------
|
| Registered LAST, and deliberately so: it is a catch-all, and every named
| route above must be matched before it. In `all` mode routes/web.php is
| loaded first, so DataKita's own paths win too.
|
| Only registered when the portal is switched on and the gate runs in-app.
| That keeps the route table identical to DataKita's when the portal is off,
| rather than having a catch-all quietly displace Route::fallback().
|
*/
if (config('devapps.enabled') && config('devapps.edge_mode') === 'proxy') {
    $mountPrefix = trim((string) config('devapps.mount_prefix', ''), '/');

    Route::any(($mountPrefix === '' ? '' : $mountPrefix . '/') . '{slug}/{path?}', ProxyController::class)
        ->where('slug', '[a-z0-9-]+')
        ->where('path', '.*')
        // The dev app runs its own framework with its own CSRF tokens, and
        // DataKita's token is meaningless to it. Safe to drop here because
        // the proxy changes no DataKita state — it reads one row and
        // forwards — and the session cookie never reaches the app anyway.
        ->withoutMiddleware(VerifyCsrfToken::class)
        ->name('develop.proxy');
}
