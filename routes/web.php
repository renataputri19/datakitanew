<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DashboardController;

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
Route::get('/data', [DataController::class, 'index'])->name('data');
Route::get('/data/{category}', [DataController::class, 'category'])->name('data.category');
Route::get('/data/{category}/{id}', [DataController::class, 'show'])->name('data.show');

// News & Updates
Route::get('/news', [NewsController::class, 'index'])->name('news');
Route::get('/news/{id}', [NewsController::class, 'show'])->name('news.show');

// Integrated Systems
Route::get('/systems', [SystemController::class, 'index'])->name('systems');
Route::get('/systems/{system}', [SystemController::class, 'show'])->name('systems.show');

// Authentication Routes are handled by Fortify

// Dashboard (Protected Routes)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Routes
    Route::get('/user/profile', function () {
        return view('dashboard.profile');
    })->name('user.profile.show');

    Route::get('/user/password', function () {
        return view('dashboard.password');
    })->name('user.password.edit');
});
