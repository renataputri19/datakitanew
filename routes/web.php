<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\DataController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\SystemController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserDashboardController;
use App\Http\Controllers\AntrianController;
use App\Http\Controllers\BPS\NewsController as BPSNewsController;
use App\Http\Controllers\BPS\VideoController as BPSVideoController;
use App\Http\Controllers\BPS\DashboardController as BPSDashboardController;
use App\Http\Controllers\BPS\UserController as BPSUserController;
use App\Http\Controllers\SurveyController;
use App\Http\Controllers\SibstrEditController;
use App\Http\Controllers\TemporarySurveyController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MonalisaController;
use App\Http\Controllers\Monalisa\KominfoController;
use App\Http\Controllers\Monalisa\BpsController as MonalisaBpsController;
use App\Http\Controllers\Monalisa\NotificationController as MonalisaNotificationController;

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

// MONALISA Routes
Route::prefix('monalisa')->name('monalisa.')->group(function () {
    // Public homepage (no authentication required)
    Route::get('/', [MonalisaController::class, 'index'])->name('index');
    Route::get('/home', [MonalisaController::class, 'index'])->name('home');

    // Dashboard route (requires authentication - redirects to appropriate dashboard)
    Route::middleware(['auth'])->group(function () {
        Route::get('/dashboard', [MonalisaController::class, 'dashboard'])->name('dashboard');
    });

    // Kominfo User Routes (Self-Assessment)
    Route::middleware(['auth', 'is_kominfo'])->prefix('kominfo')->name('kominfo.')->group(function () {
        Route::get('/dashboard', [KominfoController::class, 'dashboard'])->name('dashboard');
        // Route::get('/charts', [KominfoController::class, 'showCharts'])->name('charts'); // Commented out - Visualisasi page displays too much data
        Route::get('/indicator-analysis', [KominfoController::class, 'showIndicatorAnalysis'])->name('indicator-analysis');
        Route::get('/domain/{domainId}', [KominfoController::class, 'showDomain'])->name('domain');
        Route::get('/assessment/{indikatorId}', [KominfoController::class, 'showAssessment'])->name('assessment.show');
        Route::post('/assessment/{indikatorId}', [KominfoController::class, 'saveAssessment'])->name('assessment.save');
        Route::post('/assessment/{assessmentId}/submit', [KominfoController::class, 'submitAssessment'])->name('assessment.submit');
        Route::post('/assessment/{assessmentId}/upload', [KominfoController::class, 'uploadDocument'])->name('document.upload');
        Route::post('/document/{documentId}/replace', [KominfoController::class, 'replaceDocument'])->name('document.replace');
        Route::delete('/document/{documentId}', [KominfoController::class, 'deleteDocument'])->name('document.delete');
        Route::get('/document/{documentId}/download', [KominfoController::class, 'downloadDocument'])->name('document.download');

        // Notifications
        Route::get('/notifications', [MonalisaNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [MonalisaNotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/recent', [MonalisaNotificationController::class, 'getRecent'])->name('notifications.recent');
        Route::post('/notifications/{notificationId}/read', [MonalisaNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [MonalisaNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications/{notificationId}', [MonalisaNotificationController::class, 'destroy'])->name('notifications.delete');
    });

    // BPS User Routes (Verification/Audit)
    Route::middleware(['auth', 'is_bps'])->prefix('bps')->name('bps.')->group(function () {
        Route::get('/dashboard', [MonalisaBpsController::class, 'dashboard'])->name('dashboard');
        // Route::get('/charts', [MonalisaBpsController::class, 'showCharts'])->name('charts'); // Commented out - Visualisasi page displays too much data
        Route::get('/indicator-analysis', [MonalisaBpsController::class, 'showIndicatorAnalysis'])->name('indicator-analysis');
        Route::get('/domain/{domainId}', [MonalisaBpsController::class, 'showDomain'])->name('domain');
        Route::get('/assessments', [MonalisaBpsController::class, 'assessmentList'])->name('assessments');
        Route::get('/assessment/{assessmentId}', [MonalisaBpsController::class, 'showAssessment'])->name('assessment.show');
        Route::post('/assessment/{assessmentId}/verify', [MonalisaBpsController::class, 'verifyAssessment'])->name('assessment.verify');
        Route::post('/assessment/{assessmentId}/reject', [MonalisaBpsController::class, 'rejectAssessment'])->name('assessment.reject');
        Route::post('/document/{documentId}/comment', [MonalisaBpsController::class, 'addDocumentComment'])->name('document.comment');
        Route::get('/document/{documentId}/download', [MonalisaBpsController::class, 'downloadDocument'])->name('document.download');

        // Notifications
        Route::get('/notifications', [MonalisaNotificationController::class, 'index'])->name('notifications.index');
        Route::get('/notifications/unread-count', [MonalisaNotificationController::class, 'getUnreadCount'])->name('notifications.unread-count');
        Route::get('/notifications/recent', [MonalisaNotificationController::class, 'getRecent'])->name('notifications.recent');
        Route::post('/notifications/{notificationId}/read', [MonalisaNotificationController::class, 'markAsRead'])->name('notifications.read');
        Route::post('/notifications/read-all', [MonalisaNotificationController::class, 'markAllAsRead'])->name('notifications.read-all');
        Route::delete('/notifications/{notificationId}', [MonalisaNotificationController::class, 'destroy'])->name('notifications.delete');
    });
});

// Authentication Routes are handled by Fortify
// Email check route for registration validation
Route::post('/check-email', [App\Http\Controllers\AuthController::class, 'checkEmail'])->name('check.email');

// Test route for registration validation
Route::get('/test-registration', function () {
    return view('test-registration');
});

// Temporary SIBSTR Survey Routes (Public Access) - disabled to restore protected version
// Route::get('/survei/sibstr', [TemporarySurveyController::class, 'showSurvey'])->name('temporary.survey.sibstr');
// Route::post('/survei/sibstr', [TemporarySurveyController::class, 'submitSurvey'])->name('temporary.survey.sibstr.submit');
// Route::get('/survei/sibstr/companies/search', [TemporarySurveyController::class, 'searchCompanies'])->name('temporary.survey.sibstr.companies.search');

// Dashboard (Protected Routes)
Route::middleware(['auth'])->group(function () {
    // Unified user dashboard accessible to all authenticated users
    Route::get('/dashboard', [UserDashboardController::class, 'index'])->name('dashboard');

    // Dashboard subpages
    Route::get('/dashboard/apps', [UserDashboardController::class, 'apps'])->name('dashboard.apps');
    Route::get('/dashboard/profile', [UserDashboardController::class, 'profile'])->name('dashboard.profile');
    Route::get('/dashboard/news', [UserDashboardController::class, 'news'])->name('dashboard.news');
    Route::get('/dashboard/videos', [UserDashboardController::class, 'videos'])->name('dashboard.videos');
    Route::get('/dashboard/settings', [UserDashboardController::class, 'settings'])->name('dashboard.settings');

    // Survey Results (SIBSTR) — year picker + year detail
    Route::get('/dashboard/surveys/sibstr/results', [UserDashboardController::class, 'sibstrResults'])->name('dashboard.surveys.sibstr.results');
    Route::get('/dashboard/surveys/sibstr/results/{tahun}', [UserDashboardController::class, 'sibstrResultsYear'])->name('dashboard.surveys.sibstr.results.year')->where('tahun', '[0-9]{4}');
    Route::get('/dashboard/surveys/sibstr/download-certificate', [UserDashboardController::class, 'downloadSibstrCertificate'])->name('dashboard.surveys.sibstr.download-certificate');

    // Legacy user dashboard routes (still available for backward compatibility)
    Route::get('/userdashboard', [UserDashboardController::class, 'index'])->name('userdashboard');
    Route::get('/userdashboard/profile', [UserDashboardController::class, 'profile'])->name('userdashboard.profile');
    Route::get('/userdashboard/news', [UserDashboardController::class, 'news'])->name('userdashboard.news');
    Route::get('/userdashboard/videos', [UserDashboardController::class, 'videos'])->name('userdashboard.videos');
    Route::get('/userdashboard/settings', [UserDashboardController::class, 'settings'])->name('userdashboard.settings');
    Route::put('/user/profile/update', [UserDashboardController::class, 'updateProfile'])->name('user.profile.update');
    Route::put('/user/password/update', [UserDashboardController::class, 'updatePassword'])->name('user.password.update');

    // User Profile Routes
    Route::get('/user/profile', [UserController::class, 'profile'])->name('user.profile.show');
    Route::get('/user/password', [UserController::class, 'password'])->name('user.password.edit');

    // Survey Routes (SIBSTR) - Protected (requires authentication)
    Route::prefix('survei')->name('survey.')->group(function () {

        // ── SIBSTR LANDING PAGE (no period params) ──
        Route::get('/sibstr', [SurveyController::class, 'sibstrEntry'])->name('sibstr.entry');

        // ── SIBSTR SURVEY FORMS — Annual: /sibstr/{year}/tahunan/blok{n}
        //                        — Quarterly: /sibstr/{year}/{1-4}/blok{n}  ──
        Route::prefix('sibstr/{year}/{period}')
            ->name('sibstr.')
            ->where(['year' => '[0-9]{4}', 'period' => 'tahunan|[1-4]'])
            ->group(function () {
                // Page routes
                Route::get('/blok1',              [SurveyController::class, 'sibstrBlok1'])->name('blok1');
                Route::get('/blok2',              [SurveyController::class, 'sibstrBlok2'])->name('blok2');
                Route::get('/blok3a',             [SurveyController::class, 'sibstrBlok3a'])->name('blok3a');
                Route::get('/blok3b-industri',    [SurveyController::class, 'sibstrBlok3bIndustri'])->name('blok3b.industri');
                Route::get('/blok3b-nonindustri', [SurveyController::class, 'sibstrBlok3bNonIndustri'])->name('blok3b.nonindustri');
                Route::get('/blok4',              [SurveyController::class, 'sibstrBlok4'])->name('blok4');
                Route::get('/blok5',              [SurveyController::class, 'sibstrBlok5'])->name('blok5');
                Route::get('/blok6',              [SurveyController::class, 'sibstrBlok6'])->name('blok6');

                // Blok 1 AJAX
                Route::post('/blok1/auto-save', [SurveyController::class, 'autoSave'])->name('autosave');
                Route::post('/blok1/save-all',  [SurveyController::class, 'saveAll'])->name('save');
                Route::get('/blok1/status',     [SurveyController::class, 'getStatus'])->name('status');

                // Blok 2 AJAX
                Route::post('/blok2/auto-save', [SurveyController::class, 'autoSaveBlok2'])->name('blok2.autosave');
                Route::post('/blok2/save-all',  [SurveyController::class, 'saveAllBlok2'])->name('blok2.save');
                Route::get('/blok2/status',     [SurveyController::class, 'getStatusBlok2'])->name('blok2.status');

                // Blok 3A AJAX
                Route::post('/blok3a/auto-save', [SurveyController::class, 'autoSaveBlok3a'])->name('blok3a.autosave');
                Route::post('/blok3a/save-all',  [SurveyController::class, 'saveAllBlok3a'])->name('blok3a.save');
                Route::get('/blok3a/status',     [SurveyController::class, 'getStatusBlok3a'])->name('blok3a.status');

                // Blok 3A-2 (legacy alias – kept for backward compat, redirects to blok3c-industri)
                Route::get('/blok3a-2',              [SurveyController::class, 'sibstrBlok3cIndustri'])->name('blok3a2');
                Route::post('/blok3a-2/auto-save',   [SurveyController::class, 'autoSaveBlok3cIndustri'])->name('blok3a2.autosave');
                Route::post('/blok3a-2/save-all',    [SurveyController::class, 'saveAllBlok3cIndustri'])->name('blok3a2.save');
                Route::get('/blok3a-2/status',       [SurveyController::class, 'getStatusBlok3cIndustri'])->name('blok3a2.status');

                // Blok 3C Industri Page + AJAX
                Route::get('/blok3c-industri',              [SurveyController::class, 'sibstrBlok3cIndustri'])->name('blok3c.industri');
                Route::post('/blok3c-industri/auto-save',   [SurveyController::class, 'autoSaveBlok3cIndustri'])->name('blok3c.industri.autosave');
                Route::post('/blok3c-industri/save-all',    [SurveyController::class, 'saveAllBlok3cIndustri'])->name('blok3c.industri.save');
                Route::get('/blok3c-industri/status',       [SurveyController::class, 'getStatusBlok3cIndustri'])->name('blok3c.industri.status');

                // Blok 3B Industri AJAX
                Route::post('/blok3b-industri/auto-save', [SurveyController::class, 'autoSaveBlok3bIndustri'])->name('blok3b.industri.autosave');
                Route::post('/blok3b-industri/save-all',  [SurveyController::class, 'saveAllBlok3bIndustri'])->name('blok3b.industri.save');
                Route::get('/blok3b-industri/status',     [SurveyController::class, 'getStatusBlok3bIndustri'])->name('blok3b.industri.status');

                // Blok 3B Non-Industri AJAX
                Route::post('/blok3b-nonindustri/auto-save', [SurveyController::class, 'autoSaveBlok3bNonIndustri'])->name('blok3b.nonindustri.autosave');
                Route::post('/blok3b-nonindustri/save-all',  [SurveyController::class, 'saveAllBlok3bNonIndustri'])->name('blok3b.nonindustri.save');
                Route::get('/blok3b-nonindustri/status',     [SurveyController::class, 'getStatusBlok3bNonIndustri'])->name('blok3b.nonindustri.status');

                // Blok 4 AJAX
                Route::post('/blok4/auto-save', [SurveyController::class, 'autoSaveBlok4'])->name('blok4.autosave');
                Route::post('/blok4/save-all',  [SurveyController::class, 'saveAllBlok4'])->name('blok4.save');
                Route::get('/blok4/status',     [SurveyController::class, 'getStatusBlok4'])->name('blok4.status');

                // Blok 5 AJAX
                Route::post('/blok5/auto-save', [SurveyController::class, 'autoSaveBlok5'])->name('blok5.autosave');
                Route::post('/blok5/save-all',  [SurveyController::class, 'saveAllBlok5'])->name('blok5.save');
                Route::get('/blok5/status',     [SurveyController::class, 'getStatusBlok5'])->name('blok5.status');

                // Blok 6 AJAX
                Route::post('/blok6/auto-save', [SurveyController::class, 'autoSaveBlok6'])->name('blok6.autosave');
                Route::post('/blok6/save-all',  [SurveyController::class, 'saveAllBlok6'])->name('blok6.save');
                Route::get('/blok6/status',     [SurveyController::class, 'getStatusBlok6'])->name('blok6.status');
                Route::post('/blok6/finish',    [SurveyController::class, 'finishSurvey'])->name('blok6.finish');
            });

        // ── SIBSTR EDIT FLOW — /sibstr/{year}/{period}/edit/blok{n} ──
        Route::prefix('sibstr/{year}/{period}/edit')
            ->name('sibstr.edit.')
            ->where(['year' => '[0-9]{4}', 'period' => 'tahunan|[1-4]'])
            ->group(function () {
                // Page routes
                Route::get('/blok1',              [SibstrEditController::class, 'blok1'])->name('blok1');
                Route::get('/blok2',              [SibstrEditController::class, 'blok2'])->name('blok2');
                Route::get('/blok3a',             [SibstrEditController::class, 'blok3a'])->name('blok3a');
                Route::get('/blok3b-industri',    [SibstrEditController::class, 'blok3bIndustri'])->name('blok3b.industri');
                Route::get('/blok3b-nonindustri', [SibstrEditController::class, 'blok3bNonIndustri'])->name('blok3b.nonindustri');
                Route::get('/blok4',              [SibstrEditController::class, 'blok4'])->name('blok4');
                Route::get('/blok5',              [SibstrEditController::class, 'blok5'])->name('blok5');
                Route::get('/blok6',              [SibstrEditController::class, 'blok6'])->name('blok6');

                // Blok 1 AJAX
                Route::post('/blok1/auto-save', [SurveyController::class, 'autoSave'])->name('autosave');
                Route::post('/blok1/save-all',  [SibstrEditController::class, 'saveAllBlok1'])->name('save');
                Route::get('/blok1/status',     [SurveyController::class, 'getStatus'])->name('status');

                // Blok 2 AJAX
                Route::post('/blok2/auto-save', [SurveyController::class, 'autoSaveBlok2'])->name('blok2.autosave');
                Route::post('/blok2/save-all',  [SibstrEditController::class, 'saveAllBlok2'])->name('blok2.save');
                Route::get('/blok2/status',     [SurveyController::class, 'getStatusBlok2'])->name('blok2.status');

                // Blok 3A AJAX
                Route::post('/blok3a/auto-save', [SurveyController::class, 'autoSaveBlok3a'])->name('blok3a.autosave');
                Route::post('/blok3a/save-all',  [SibstrEditController::class, 'saveAllBlok3a'])->name('blok3a.save');
                Route::get('/blok3a/status',     [SurveyController::class, 'getStatusBlok3a'])->name('blok3a.status');

                // Blok 3A-2 (legacy alias → blok3c-industri)
                Route::get('/blok3a-2',              [SibstrEditController::class, 'blok3cIndustri'])->name('blok3a2');
                Route::post('/blok3a-2/auto-save',   [SurveyController::class, 'autoSaveBlok3cIndustri'])->name('blok3a2.autosave');
                Route::post('/blok3a-2/save-all',    [SibstrEditController::class, 'saveAllBlok3cIndustri'])->name('blok3a2.save');
                Route::get('/blok3a-2/status',       [SurveyController::class, 'getStatusBlok3cIndustri'])->name('blok3a2.status');

                // Blok 3C Industri Page + AJAX (edit flow)
                Route::get('/blok3c-industri',              [SibstrEditController::class, 'blok3cIndustri'])->name('blok3c.industri');
                Route::post('/blok3c-industri/auto-save',   [SurveyController::class, 'autoSaveBlok3cIndustri'])->name('blok3c.industri.autosave');
                Route::post('/blok3c-industri/save-all',    [SibstrEditController::class, 'saveAllBlok3cIndustri'])->name('blok3c.industri.save');
                Route::get('/blok3c-industri/status',       [SurveyController::class, 'getStatusBlok3cIndustri'])->name('blok3c.industri.status');

                // Blok 3B Industri AJAX
                Route::post('/blok3b-industri/auto-save', [SurveyController::class, 'autoSaveBlok3bIndustri'])->name('blok3b.industri.autosave');
                Route::post('/blok3b-industri/save-all',  [SibstrEditController::class, 'saveAllBlok3bIndustri'])->name('blok3b.industri.save');
                Route::get('/blok3b-industri/status',     [SurveyController::class, 'getStatusBlok3bIndustri'])->name('blok3b.industri.status');

                // Blok 3B Non-Industri AJAX
                Route::post('/blok3b-nonindustri/auto-save', [SurveyController::class, 'autoSaveBlok3bNonIndustri'])->name('blok3b.nonindustri.autosave');
                Route::post('/blok3b-nonindustri/save-all',  [SibstrEditController::class, 'saveAllBlok3bNonIndustri'])->name('blok3b.nonindustri.save');
                Route::get('/blok3b-nonindustri/status',     [SurveyController::class, 'getStatusBlok3bNonIndustri'])->name('blok3b.nonindustri.status');

                // Blok 4 AJAX
                Route::post('/blok4/auto-save', [SurveyController::class, 'autoSaveBlok4'])->name('blok4.autosave');
                Route::post('/blok4/save-all',  [SibstrEditController::class, 'saveAllBlok4'])->name('blok4.save');
                Route::get('/blok4/status',     [SurveyController::class, 'getStatusBlok4'])->name('blok4.status');

                // Blok 5 AJAX
                Route::post('/blok5/auto-save', [SurveyController::class, 'autoSaveBlok5'])->name('blok5.autosave');
                Route::post('/blok5/save-all',  [SibstrEditController::class, 'saveAllBlok5'])->name('blok5.save');
                Route::get('/blok5/status',     [SurveyController::class, 'getStatusBlok5'])->name('blok5.status');

                // Blok 6 AJAX
                Route::post('/blok6/auto-save', [SurveyController::class, 'autoSaveBlok6'])->name('blok6.autosave');
                Route::post('/blok6/save-all',  [SibstrEditController::class, 'saveAllBlok6'])->name('blok6.save');
                Route::get('/blok6/status',     [SurveyController::class, 'getStatusBlok6'])->name('blok6.status');
                Route::post('/blok6/finish',    [SibstrEditController::class, 'finishSurvey'])->name('blok6.finish');
            });
    });


    // BPS Routes (Protected by BPS middleware)
    Route::middleware(['is_bps'])->prefix('bps')->name('bps.')->group(function () {
        // BPS Dashboard
        Route::get('/', [BPSDashboardController::class, 'index'])->name('dashboard');

        // News Management
        Route::resource('news', BPSNewsController::class);

        // Video Management
        Route::resource('videos', BPSVideoController::class);

        // SIBSTR Survey Data Management (View Only)
        Route::get('/sibstr', [App\Http\Controllers\BPS\SibstrController::class, 'index'])->name('sibstr.index');
        Route::get('/sibstr/{id}', [App\Http\Controllers\BPS\SibstrController::class, 'show'])->name('sibstr.show');
        Route::get('/sibstr/{id}/download', [App\Http\Controllers\BPS\SibstrController::class, 'download'])->name('sibstr.download');

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

// Form Routes
Route::get('/form/institution', [FormController::class, 'showInstitutionForm'])->name('form.institution');
Route::post('/form/institution', [FormController::class, 'submitInstitutionForm'])->name('form.institution.submit');

// Fallback Route
Route::fallback(function () {
    return redirect()->route('home');
});
