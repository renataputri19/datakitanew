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
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\TemporarySurveyController;
use App\Http\Controllers\CompanyController;

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
// Temporary SIBSTR Survey Routes (Public Access)
Route::get('/survei/sibstr', [TemporarySurveyController::class, 'showSurvey'])->name('temporary.survey.sibstr');
Route::post('/survei/sibstr', [TemporarySurveyController::class, 'submitSurvey'])->name('temporary.survey.sibstr.submit');
Route::get('/survei/sibstr/companies/search', [TemporarySurveyController::class, 'searchCompanies'])->name('temporary.survey.sibstr.companies.search');

// Dashboard (Protected Routes)
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // User Profile Routes
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile.show');
    Route::get('/user/password', [UserController::class, 'password'])->name('user.password.edit');

    // Survey Routes (SIBSTR) - TEMPORARILY DISABLED
    // Route::prefix('survei')->name('survey.')->group(function () {
    //     Route::prefix('sibstr')->name('sibstr.')->group(function () {
    //         Route::get('/', [SurveyController::class, 'sibstrBlok1'])->name('blok1');
    //         Route::get('/blok1', [SurveyController::class, 'sibstrBlok1'])->name('blok1.show');
    //         Route::get('/blok2', [SurveyController::class, 'sibstrBlok2'])->name('blok2');
    //         Route::get('/blok3a', [SurveyController::class, 'sibstrBlok3a'])->name('blok3a');
    //         Route::get('/blok6', [SurveyController::class, 'sibstrBlok6'])->name('blok6');

    //         // AJAX Routes for auto-save
    //         Route::post('/auto-save', [SurveyController::class, 'autoSave'])->name('autosave');
    //         Route::post('/save-all', [SurveyController::class, 'saveAll'])->name('save');
    //         Route::get('/status', [SurveyController::class, 'getStatus'])->name('status');

    //         // Blok 2 specific routes
    //         Route::post('/blok2/auto-save', [SurveyController::class, 'autoSaveBlok2'])->name('blok2.autosave');
    //         Route::post('/blok2/save-all', [SurveyController::class, 'saveAllBlok2'])->name('blok2.save');
    //         Route::get('/blok2/status', [SurveyController::class, 'getStatusBlok2'])->name('blok2.status');

    //         // Blok IIIA specific routes
    //         Route::post('/blok3a/auto-save', [SurveyController::class, 'autoSaveBlok3a'])->name('blok3a.autosave');
    //         Route::post('/blok3a/save-all', [SurveyController::class, 'saveAllBlok3a'])->name('blok3a.save');
    //         Route::get('/blok3a/status', [SurveyController::class, 'getStatusBlok3a'])->name('blok3a.status');

    //         // Blok 6 specific routes
    //         Route::post('/blok6/auto-save', [SurveyController::class, 'autoSaveBlok6'])->name('blok6.autosave');
    //         Route::post('/blok6/save-all', [SurveyController::class, 'saveAllBlok6'])->name('blok6.save');
    //         Route::get('/blok6/status', [SurveyController::class, 'getStatusBlok6'])->name('blok6.status');
    //         Route::post('/blok6/finish', [SurveyController::class, 'finishSurvey'])->name('blok6.finish');
    //     });
    // });


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

// Superadmin Routes (Protected by Superadmin middleware)
Route::middleware(['auth', 'is_superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [TemporarySurveyController::class, 'superadminDashboard'])->name('dashboard');
    Route::get('/download/{filename}', [TemporarySurveyController::class, 'downloadFile'])->name('download.file');

    // Submissions Management Routes
    Route::get('/submissions/create', [TemporarySurveyController::class, 'createSubmission'])->name('submissions.create');
    Route::post('/submissions', [TemporarySurveyController::class, 'storeSubmission'])->name('submissions.store');
    Route::get('/submissions/{submission}/edit', [TemporarySurveyController::class, 'editSubmission'])->name('submissions.edit');
    Route::put('/submissions/{submission}', [TemporarySurveyController::class, 'updateSubmission'])->name('submissions.update');
    Route::delete('/submissions/{submission}', [TemporarySurveyController::class, 'destroySubmission'])->name('submissions.destroy');

    // Company Management Routes
    Route::resource('companies', CompanyController::class);
    Route::get('/companies-template/download', [CompanyController::class, 'downloadTemplate'])->name('companies.download-template');
    Route::post('/companies/import', [CompanyController::class, 'import'])->name('companies.import');
    Route::get('/companies/search', [CompanyController::class, 'search'])->name('companies.search');
});

// Fallback Route
Route::fallback(function () {
    return redirect()->route('home');
});
