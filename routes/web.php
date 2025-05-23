<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\BPS\NewsController as BPSNewsController;
use App\Http\Controllers\BPS\VideoController as BPSVideoController;
use App\Http\Controllers\BPS\DashboardController as BPSDashboardController;
use App\Http\Controllers\BPS\UserController as BPSUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Main Pages
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/faq', [HomeController::class, 'faq'])->name('faq');
Route::get('/test', function () {
    return view('test');
});

// Data Statistics
// Route::get('/data', [DataController::class, 'index'])->name('data');
// Route::get('/data/{category}', [DataController::class, 'category'])->name('data.category');
// Route::get('/data/{category}/{id}', [DataController::class, 'show'])->name('data.show');

// News & Updates
Route::get('/news', [NewsController::class, 'index'])->name('news');
// Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show'); // Commented out to avoid overlap with real BPS website

// Integrated Systems
Route::get('/systems', [SystemController::class, 'index'])->name('systems');
// Route::get('/systems/{system}', [SystemController::class, 'show'])->name('systems.show');

// Antrian Routes
Route::prefix('antriantamu')->name('antrian.')->group(function () {
    Route::get('/', [AntrianController::class, 'index'])->name('index');
    Route::get('/nomor', [AntrianController::class, 'nomor'])->name('nomor');
    Route::get('/panggilan', [AntrianController::class, 'panggilan'])->name('panggilan');
    Route::get('/monitor', [AntrianController::class, 'monitor'])->name('monitor');

    // Setting route protected by auth and is_bps middleware
    Route::middleware(['auth', 'is_bps'])->get('/setting', [AntrianController::class, 'setting'])->name('setting');

    // API Routes
    Route::prefix('api')->name('api.')->group(function () {
        Route::get('/loket', [AntrianController::class, 'getLoket'])->name('loket');
        Route::post('/generate', [AntrianController::class, 'generateAntrian'])->name('generate');
        Route::post('/next', [AntrianController::class, 'getNextAntrian'])->name('next');
        Route::get('/status', [AntrianController::class, 'getAntrianStatus'])->name('status');
        Route::post('/setting', [AntrianController::class, 'saveSetting'])->name('setting');
        Route::post('/add-loket', [AntrianController::class, 'addLoket'])->name('add-loket');
        Route::post('/delete-loket', [AntrianController::class, 'deleteLoket'])->name('delete-loket');
    });
});

// Authentication Routes are handled by Fortify

// Dashboard (Protected Routes)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Routes
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile.show');
    Route::get('/user/password', [UserController::class, 'password'])->name('user.password.edit');

    // BPS Routes (Protected by BPS middleware)
    Route::middleware(['is_bps'])->prefix('bps')->name('bps.')->group(function () {
        // BPS Dashboard
        Route::get('/', [BPSDashboardController::class, 'index'])->name('dashboard');

        // News Management
        Route::resource('news', BPSNewsController::class);

        // Video Management
        Route::resource('videos', BPSVideoController::class);

        // BPS User Profile Route
        Route::get('/user/profile', [BPSUserController::class, 'profile'])->name('user.profile.show');
    });
});



// Fallback Route
Route::fallback(function () {
    return redirect()->route('home');
});



