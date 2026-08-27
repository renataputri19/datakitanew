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
use App\Http\Controllers\SurveyUbController;
use App\Http\Controllers\SurveyUbEditController;
use App\Http\Controllers\TemporarySurveyController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\MonalisaController;
use App\Http\Controllers\Monalisa\KominfoController;
use App\Http\Controllers\Monalisa\BpsController as MonalisaBpsController;
use App\Http\Controllers\Monalisa\NotificationController as MonalisaNotificationController;
use App\Http\Controllers\DevLoginController;
use App\Http\Controllers\PetaController;
use App\Http\Controllers\Superadmin\UserController as SuperadminUserController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| DataKita proper. The developer portal lives in routes/devportal.php and is
| loaded separately by APP_ROLE — see App\Support\AppRole.
|
*/

// ── Dev gate routes (always registered, controller aborts 404 in production) ──
Route::get('/dev-login',  [DevLoginController::class, 'showForm'])->name('dev.login');
Route::post('/dev-login', [DevLoginController::class, 'login'])->name('dev.login.submit');
Route::post('/dev-logout',[DevLoginController::class, 'logout'])->name('dev.logout');
// ─────────────────────────────────────────────────────────────────────────────

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
        Route::get('/document/{documentId}/view', [KominfoController::class, 'viewDocument'])->name('document.view');

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
        Route::post('/assessment/{assessmentId}/cancel-verify', [MonalisaBpsController::class, 'cancelVerification'])->name('assessment.cancel-verify');
        Route::post('/document/{documentId}/comment', [MonalisaBpsController::class, 'addDocumentComment'])->name('document.comment');
        Route::get('/document/{documentId}/download', [MonalisaBpsController::class, 'downloadDocument'])->name('document.download');
        Route::get('/document/{documentId}/view', [MonalisaBpsController::class, 'viewDocument'])->name('document.view');

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
// Email check route for registration validation. Throttled because it is a
// public, unauthenticated endpoint that queries the users table and would
// otherwise allow unlimited email-enumeration / account-discovery probing.
Route::post('/check-email', [App\Http\Controllers\AuthController::class, 'checkEmail'])
    ->middleware('throttle:20,1')
    ->name('check.email');

// Test route for registration validation
Route::get('/test-registration', function () {
    return view('test-registration');
});

// Temporary SIBSTR Survey Routes (Public Access) - disabled to restore protected version
// Route::get('/survei/sibstr', [TemporarySurveyController::class, 'showSurvey'])->name('temporary.survey.sibstr');
// Route::post('/survei/sibstr', [TemporarySurveyController::class, 'submitSurvey'])->name('temporary.survey.sibstr.submit');
// Route::get('/survei/sibstr/companies/search', [TemporarySurveyController::class, 'searchCompanies'])->name('temporary.survey.sibstr.companies.search');

// TUNJUKIN SE — Penuntun dan Penunjuk Arah SE: field guide map for petugas (login required)
Route::middleware(['auth'])->prefix('tunjukin-se')->name('peta.')->group(function () {
    Route::get('/', [PetaController::class, 'index'])->name('index');
    Route::get('/data/index', [PetaController::class, 'indexData'])->name('data.index');
    Route::get('/data/kelurahan/{key}', [PetaController::class, 'kelurahan'])->name('data.kelurahan');
});

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

    // Survey Results (SIBSTR) — consolidated into the single /survei/sibstr page.
    // The old year-picker and year-detail URLs now redirect there; the route
    // names are kept so existing links and bookmarks keep resolving.
    Route::redirect('/dashboard/surveys/sibstr/results', '/survei/sibstr')
        ->name('dashboard.surveys.sibstr.results');
    Route::redirect('/dashboard/surveys/sibstr/results/{tahun}', '/survei/sibstr')
        ->name('dashboard.surveys.sibstr.results.year')
        ->where('tahun', '[0-9]{4}');
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

        // ── UB SURVEY (SE2026-L.UB) ──
        Route::get('/ub',                      [SurveyUbController::class, 'entry'])->name('ub.entry');
        Route::post('/ub/start-edit',          [SurveyUbController::class, 'startEdit'])->name('ub.start-edit');
        Route::get('/ub/blok1a',               [SurveyUbController::class, 'blok1a'])->name('ub.blok1a');
        Route::post('/ub/blok1a/autosave',     [SurveyUbController::class, 'autoSaveBlok1a'])->name('ub.blok1a.autosave');
        Route::get('/ub/blok1a/status',        [SurveyUbController::class, 'getStatusBlok1a'])->name('ub.blok1a.status');
        Route::post('/ub/blok1a/save',         [SurveyUbController::class, 'saveBlok1a'])->name('ub.blok1a.save');
        Route::get('/ub/blok1b',               [SurveyUbController::class, 'blok1b'])->name('ub.blok1b');
        Route::post('/ub/blok1b/autosave',     [SurveyUbController::class, 'autoSaveBlok1b'])->name('ub.blok1b.autosave');
        Route::get('/ub/blok1b/status',        [SurveyUbController::class, 'getStatusBlok1b'])->name('ub.blok1b.status');
        Route::post('/ub/blok1b/save',         [SurveyUbController::class, 'saveBlok1b'])->name('ub.blok1b.save');
        Route::get('/ub/blok1c',               [SurveyUbController::class, 'blok1c'])->name('ub.blok1c');
        Route::post('/ub/blok1c/autosave',     [SurveyUbController::class, 'autoSaveBlok1c'])->name('ub.blok1c.autosave');
        Route::get('/ub/blok1c/status',        [SurveyUbController::class, 'getStatusBlok1c'])->name('ub.blok1c.status');
        Route::post('/ub/blok1c/save',         [SurveyUbController::class, 'saveBlok1c'])->name('ub.blok1c.save');
        Route::get('/ub/blok1d',               [SurveyUbController::class, 'blok1d'])->name('ub.blok1d');
        Route::post('/ub/blok1d/autosave',     [SurveyUbController::class, 'autoSaveBlok1d'])->name('ub.blok1d.autosave');
        Route::get('/ub/blok1d/status',        [SurveyUbController::class, 'getStatusBlok1d'])->name('ub.blok1d.status');
        Route::post('/ub/blok1d/save',         [SurveyUbController::class, 'saveBlok1d'])->name('ub.blok1d.save');
        Route::get('/ub/blok2',                [SurveyUbController::class, 'blok2'])->name('ub.blok2');
        Route::post('/ub/blok2/autosave',      [SurveyUbController::class, 'autoSaveBlok2'])->name('ub.blok2.autosave');
        Route::get('/ub/blok2/status',         [SurveyUbController::class, 'getStatusBlok2'])->name('ub.blok2.status');
        Route::post('/ub/blok2/save',          [SurveyUbController::class, 'saveBlok2'])->name('ub.blok2.save');
        Route::get('/ub/blok3',                [SurveyUbController::class, 'blok3'])->name('ub.blok3');
        Route::post('/ub/blok3/autosave',      [SurveyUbController::class, 'autoSaveBlok3'])->name('ub.blok3.autosave');
        Route::get('/ub/blok3/status',         [SurveyUbController::class, 'getStatusBlok3'])->name('ub.blok3.status');
        Route::post('/ub/blok3/finish',        [SurveyUbController::class, 'finish'])->name('ub.blok3.finish');
        Route::get('/ub/pdf',                  [SurveyUbController::class, 'downloadPdf'])->name('ub.pdf.download');

        // ── UB EDIT (completed surveys) ──
        Route::get('/ub/edit/blok1a',          [SurveyUbEditController::class, 'blok1a'])->name('ub.edit.blok1a');
        Route::post('/ub/edit/blok1a/save',    [SurveyUbEditController::class, 'saveBlok1a'])->name('ub.edit.blok1a.save');
        Route::get('/ub/edit/blok1b',          [SurveyUbEditController::class, 'blok1b'])->name('ub.edit.blok1b');
        Route::post('/ub/edit/blok1b/save',    [SurveyUbEditController::class, 'saveBlok1b'])->name('ub.edit.blok1b.save');
        Route::get('/ub/edit/blok1c',          [SurveyUbEditController::class, 'blok1c'])->name('ub.edit.blok1c');
        Route::post('/ub/edit/blok1c/save',    [SurveyUbEditController::class, 'saveBlok1c'])->name('ub.edit.blok1c.save');
        Route::get('/ub/edit/blok1d',          [SurveyUbEditController::class, 'blok1d'])->name('ub.edit.blok1d');
        Route::post('/ub/edit/blok1d/save',    [SurveyUbEditController::class, 'saveBlok1d'])->name('ub.edit.blok1d.save');
        Route::get('/ub/edit/blok2',           [SurveyUbEditController::class, 'blok2'])->name('ub.edit.blok2');
        Route::post('/ub/edit/blok2/save',     [SurveyUbEditController::class, 'saveBlok2'])->name('ub.edit.blok2.save');
        Route::get('/ub/edit/blok3',           [SurveyUbEditController::class, 'blok3'])->name('ub.edit.blok3');
        Route::post('/ub/edit/blok3/finish',   [SurveyUbEditController::class, 'finish'])->name('ub.edit.blok3.finish');

        // ── SURVEI LISTRIK (produksi & nilai produksi listrik bulanan) ──
        Route::get('/listrik',                 [App\Http\Controllers\SurveyListrikController::class, 'entry'])->name('listrik.entry');
        Route::post('/listrik/start-edit',     [App\Http\Controllers\SurveyListrikController::class, 'startEdit'])->name('listrik.start-edit');
        Route::get('/listrik/blok1',           [App\Http\Controllers\SurveyListrikController::class, 'blok1'])->name('listrik.blok1');
        Route::post('/listrik/blok1/autosave', [App\Http\Controllers\SurveyListrikController::class, 'autoSaveBlok1'])->name('listrik.blok1.autosave');
        Route::get('/listrik/blok1/status',    [App\Http\Controllers\SurveyListrikController::class, 'getStatusBlok1'])->name('listrik.blok1.status');
        Route::post('/listrik/blok1/save',     [App\Http\Controllers\SurveyListrikController::class, 'saveBlok1'])->name('listrik.blok1.save');
        Route::get('/listrik/blok2',           [App\Http\Controllers\SurveyListrikController::class, 'blok2'])->name('listrik.blok2');
        Route::post('/listrik/blok2/autosave', [App\Http\Controllers\SurveyListrikController::class, 'autoSaveBlok2'])->name('listrik.blok2.autosave');
        Route::get('/listrik/blok2/status',    [App\Http\Controllers\SurveyListrikController::class, 'getStatusBlok2'])->name('listrik.blok2.status');
        Route::post('/listrik/blok2/save',     [App\Http\Controllers\SurveyListrikController::class, 'saveBlok2'])->name('listrik.blok2.save');
        Route::get('/listrik/blok3',           [App\Http\Controllers\SurveyListrikController::class, 'blok3'])->name('listrik.blok3');
        Route::post('/listrik/blok3/autosave', [App\Http\Controllers\SurveyListrikController::class, 'autoSaveBlok3'])->name('listrik.blok3.autosave');
        Route::get('/listrik/blok3/status',    [App\Http\Controllers\SurveyListrikController::class, 'getStatusBlok3'])->name('listrik.blok3.status');
        Route::post('/listrik/blok3/finish',   [App\Http\Controllers\SurveyListrikController::class, 'finish'])->name('listrik.blok3.finish');
        Route::get('/listrik/pdf',             [App\Http\Controllers\SurveyListrikController::class, 'downloadPdf'])->name('listrik.pdf.download');

        // ── SIBSTR LANDING PAGE (no period params) ──
        Route::get('/sibstr', [SurveyController::class, 'sibstrEntry'])->name('sibstr.entry');

        // ── MITRA: read-only detail & PDF download (no bps access required) ──
        Route::middleware(['is_mitra'])->group(function () {
            Route::get('/mitra/sibstr/{id}',          [SurveyController::class, 'mitraSibstrShow'])->name('mitra.sibstr.show');
            Route::get('/mitra/sibstr/{id}/download', [SurveyController::class, 'mitraSibstrDownload'])->name('mitra.sibstr.download');

            Route::get('/mitra/ub/{id}',          [SurveyUbController::class, 'mitraUbShow'])->name('mitra.ub.show');
            Route::get('/mitra/ub/{id}/download', [SurveyUbController::class, 'mitraUbDownload'])->name('mitra.ub.download');
        });

        // ── SIBSTR SURVEY FORMS — Annual: /sibstr/{year}/tahunan/blok{n}
        //                        — Quarterly: /sibstr/{year}/{1-4}/blok{n}  ──
        Route::prefix('sibstr/{year}/{period}')
            ->name('sibstr.')
            ->where(['year' => '[0-9]{4}', 'period' => 'tahunan|[1-4]'])
            ->group(function () {
                // Blok navigation rail — re-rendered live after each autosave
                Route::get('/nav',                [SurveyController::class, 'sibstrNav'])->name('nav');

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

        // SIBSTR Statistics Dashboard (quarterly data viz)
        Route::get('/statistik', [App\Http\Controllers\BPS\StatistikController::class, 'index'])->name('statistik.index');

        // Listrik Statistics Dashboard (monthly electricity production viz)
        Route::get('/statistik-listrik', [App\Http\Controllers\BPS\StatistikListrikController::class, 'index'])->name('statistik.listrik');

        // UB Statistics Dashboard (annual business census viz)
        Route::get('/statistik-ub', [App\Http\Controllers\BPS\StatistikUbController::class, 'index'])->name('statistik.ub');

        // News Management
        Route::resource('news', BPSNewsController::class);

        // Video Management
        Route::resource('videos', BPSVideoController::class);

        // SIBSTR Survey Data Management (view + PDF + delete — BPS only)
        // NOTE: /export must stay above /{id} or the show route swallows it.
        Route::get('/sibstr', [App\Http\Controllers\BPS\SibstrController::class, 'index'])->name('sibstr.index');
        Route::get('/sibstr/export', [App\Http\Controllers\BPS\SibstrController::class, 'export'])->name('sibstr.export');
        Route::get('/sibstr/{id}', [App\Http\Controllers\BPS\SibstrController::class, 'show'])->name('sibstr.show');
        Route::get('/sibstr/{id}/download', [App\Http\Controllers\BPS\SibstrController::class, 'download'])->name('sibstr.download');
        Route::delete('/sibstr/{id}', [App\Http\Controllers\BPS\SibstrController::class, 'destroy'])->name('sibstr.destroy');

        // UB Survey Data Management (view + PDF + delete — BPS only)
        Route::get('/ub', [App\Http\Controllers\BPS\UbController::class, 'index'])->name('ub.index');
        Route::get('/ub/export', [App\Http\Controllers\BPS\UbController::class, 'export'])->name('ub.export');
        Route::get('/ub/{id}/download', [App\Http\Controllers\BPS\UbController::class, 'download'])->name('ub.download');
        Route::get('/ub/{id}', [App\Http\Controllers\BPS\UbController::class, 'show'])->name('ub.show');
        Route::delete('/ub/{id}', [App\Http\Controllers\BPS\UbController::class, 'destroy'])->name('ub.destroy');

        // Listrik Survey Data Management (view + PDF + delete — BPS only)
        Route::get('/listrik', [App\Http\Controllers\BPS\ListrikController::class, 'index'])->name('listrik.index');
        Route::get('/listrik/export', [App\Http\Controllers\BPS\ListrikController::class, 'export'])->name('listrik.export');
        Route::get('/listrik/{id}/download', [App\Http\Controllers\BPS\ListrikController::class, 'download'])->name('listrik.download');
        Route::get('/listrik/{id}', [App\Http\Controllers\BPS\ListrikController::class, 'show'])->name('listrik.show');
        Route::delete('/listrik/{id}', [App\Http\Controllers\BPS\ListrikController::class, 'destroy'])->name('listrik.destroy');

        // BPS User Profile Route
        Route::get('/user/profile', [BPSUserController::class, 'profile'])->name('user.profile.show');

        // User Management Routes
        Route::get('/users', [BPSUserController::class, 'index'])->name('users.index');
        Route::post('/users/{id}/reset-password', [BPSUserController::class, 'resetPassword'])->name('users.reset-password');
    });
});

// Superadmin Routes (Protected by Superadmin middleware)
Route::middleware(['auth', 'is_superadmin'])->prefix('superadmin')->name('superadmin.')->group(function () {
    Route::get('/dashboard', [TemporarySurveyController::class, 'superadminDashboard'])->name('dashboard');
    Route::get('/download/{filename}', [TemporarySurveyController::class, 'downloadFile'])->name('download.file');

    // Recycle bin for survey submissions soft-deleted by BPS
    Route::get('/data-terhapus', [App\Http\Controllers\Superadmin\TrashController::class, 'index'])->name('trash.index');
    Route::post('/data-terhapus/{type}/restore-all', [App\Http\Controllers\Superadmin\TrashController::class, 'restoreAll'])->name('trash.restore-all');
    Route::post('/data-terhapus/{type}/{id}/restore', [App\Http\Controllers\Superadmin\TrashController::class, 'restore'])->name('trash.restore');

    // User Management (create users & assign a single, non-overlapping role)
    Route::get('/users', [SuperadminUserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [SuperadminUserController::class, 'create'])->name('users.create');
    Route::post('/users', [SuperadminUserController::class, 'store'])->name('users.store');
    Route::get('/users/{user}/edit', [SuperadminUserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{user}', [SuperadminUserController::class, 'update'])->name('users.update');
    Route::delete('/users/{user}', [SuperadminUserController::class, 'destroy'])->name('users.destroy');

    // Submissions Management Routes
    Route::get('/submissions', [TemporarySurveyController::class, 'submissionsIndex'])->name('submissions.index');
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
