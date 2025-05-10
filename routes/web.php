<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
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



