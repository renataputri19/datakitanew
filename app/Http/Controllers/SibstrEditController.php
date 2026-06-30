<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SibstrEditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Read the active survey period from the current request or session.
     * Priority: (1) route params {year}/{period}, (2) query/body params, (3) session.
     * Returns ['tahun' => int, 'triwulan' => int, 'period' => string].
     */
    private function getPeriod(): array
    {
        $req = request();

        // Priority 1: hierarchical route parameters ({year}/{period} in URL)
        $routeYear   = $req->route('year');
        $routePeriod = $req->route('period');

        if ($routeYear !== null && $routePeriod !== null) {
            $tahun    = (int) $routeYear;
            $triwulan = $routePeriod === 'tahunan' ? 0 : (int) $routePeriod;
            $period   = $routePeriod;
            session(['sibstr.tahun' => $tahun, 'sibstr.triwulan' => $triwulan]);
            return compact('tahun', 'triwulan', 'period');
        }

        // Priority 2: query-string / request body (legacy fallback)
        if ($req->has('tahun') || $req->has('triwulan')) {
            $tahun    = (int) $req->input('tahun', 2025);
            $triwulan = (int) $req->input('triwulan', 0);
            $period   = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
            session(['sibstr.tahun' => $tahun, 'sibstr.triwulan' => $triwulan]);
            return compact('tahun', 'triwulan', 'period');
        }

        // Priority 3: session
        $tahun    = (int) session('sibstr.tahun', 2025);
        $triwulan = (int) session('sibstr.triwulan', 0);
        $period   = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        return compact('tahun', 'triwulan', 'period');
    }

    /**
     * Get the existing survey response for the authenticated user.
     * Only allow edit when the survey is completed (is_completed = true).
     * Period is resolved from route params {year}/{period} first, then session.
     */
    private function getExistingSurveyResponse()
    {
        $user = Auth::user();
        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $response = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->where('is_completed', true)
            ->first();

        if ($response) {
            return $response;
        }

        // Redirect to the matching non-edit route (same block, same period) so the
        // user can fill in the survey that hasn't been completed yet.
        $year        = request()->route('year');
        $routePeriod = request()->route('period');
        $currentName = optional(request()->route())->getName();

        if ($currentName && str_contains($currentName, '.edit.') && $year && $routePeriod) {
            $fallbackRoute  = str_replace('.edit.', '.', $currentName);
            $fallbackParams = ['year' => $year, 'period' => $routePeriod];
        } else {
            $fallbackRoute  = 'survey.sibstr.entry';
            $fallbackParams = [];
        }

        return redirect()
            ->route($fallbackRoute, $fallbackParams)
            ->with('warning', 'Hanya survei yang sudah selesai dapat diedit. Anda dialihkan ke halaman survei.');
    }

    /**
     * Fetch the immediately preceding period's survey response for reference comparison.
     */
    private function getPreviousPeriodResponse(int|string $userId, int $tahun, int $triwulan): ?SurveyResponse
    {
        if ($triwulan === 0) {
            return SurveyResponse::where('user_id', $userId)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun - 1)
                ->where('triwulan', 0)
                ->first();
        }

        if ($triwulan > 1) {
            return SurveyResponse::where('user_id', $userId)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan - 1)
                ->first();
        }

        $prevTw4 = SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun - 1)
            ->where('triwulan', 4)
            ->first();

        return $prevTw4 ?? SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun - 1)
            ->where('triwulan', 0)
            ->first();
    }

    /**
     * Fetch all prior-period survey responses for the given user (newest first).
     */
    private function getHistoricalResponses(int|string $userId, int $tahun, int $triwulan)
    {
        return SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where(function ($q) use ($tahun, $triwulan) {
                $q->where('tahun', '<', $tahun)
                  ->orWhere(function ($q2) use ($tahun, $triwulan) {
                      $q2->where('tahun', $tahun)->where('triwulan', '<', $triwulan);
                  });
            })
            ->orderBy('tahun', 'desc')
            ->orderBy('triwulan', 'desc')
            ->get();
    }

    /**
     * Common BPS RI static data.
     */
    private function bpsRiData(): array
    {
        return [
            'penghubung' => 'Tim Statistik Industri',
            'telepon'    => '021-3810291 ext. 5310–5313',
            'fax'        => '021-3863816, 021-3857046',
            'email'      => 'ibs@bps.go.id',
            'alamat'     => 'Jl. Dr. Sutomo No. 8, Jakarta 10710',
        ];
    }

    /**
     * Guard for Triwulan 2026 edit pages: denies access unless the 2025 Tahunan survey
     * has reached FINISH_SURVEY status (is_completed = true).
     * Returns a redirect when access is denied, or null to allow.
     */
    private function checkTriwulanAccess(): ?\Illuminate\Http\RedirectResponse
    {
        ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

        if ($tahun !== 2026 || $triwulan < 1) {
            return null;
        }

        $user = Auth::user();
        if (!SurveyResponse::isTahunanFullyCompletedForUser($user->id)) {
            return redirect()
                ->route('survey.sibstr.entry')
                ->with('error', 'Survei Triwulanan 2026 hanya dapat diakses setelah Survei Tahunan 2025 diselesaikan sepenuhnya melalui Blok VI.');
        }

        return null;
    }

    // ──────────────────────────────────────────────
    //  PER-TEMPLATE EDIT ROUTES
    //  Each returns the exact same keys the original
    //  window.surveyRoutes object uses for that block.
    //  All routes carry {year}/{period} params.
    // ──────────────────────────────────────────────

    private function editRoutesBlok1(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave' => route('survey.sibstr.edit.autosave', $p),
            'saveAll'  => route('survey.sibstr.edit.save', $p),
            'status'   => route('survey.sibstr.edit.status', $p),
            'nextBlok' => route('survey.sibstr.edit.blok2', $p),
        ];
    }

    private function editRoutesBlok2(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'            => route('survey.sibstr.edit.blok2.autosave', $p),
            'saveAll'             => route('survey.sibstr.edit.blok2.save', $p),
            'status'              => route('survey.sibstr.edit.blok2.status', $p),
            'backToBlok1'         => route('survey.sibstr.edit.blok1', $p),
            'nextBlok'            => route('survey.sibstr.edit.blok3a', $p),
            'blok3a'              => route('survey.sibstr.edit.blok3a', $p),
            'blok6'               => route('survey.sibstr.edit.blok6', $p),
            'blok3b_industri'     => route('survey.sibstr.edit.blok3b.industri', $p),
            'blok3b_nonindustri'  => route('survey.sibstr.edit.blok3b.nonindustri', $p),
        ];
    }

    private function editRoutesBlok3a(int $tahun, int $triwulan, $kbliPrefix): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];

        $nextBlok = ($kbliPrefix !== null && $kbliPrefix >= 10 && $kbliPrefix <= 33)
            ? route('survey.sibstr.edit.blok3b.industri', $p)
            : route('survey.sibstr.edit.blok3b.nonindustri', $p);

        return [
            'autoSave'            => route('survey.sibstr.edit.blok3a.autosave', $p),
            'saveAll'             => route('survey.sibstr.edit.blok3a.save', $p),
            'status'              => route('survey.sibstr.edit.blok3a.status', $p),
            'backToBlok2'         => route('survey.sibstr.edit.blok2', $p),
            'nextBlok'            => $nextBlok,
            'blok6'               => route('survey.sibstr.edit.blok6', $p),
            'blok3b_industri'     => route('survey.sibstr.edit.blok3b.industri', $p),
            'blok3b_nonindustri'  => route('survey.sibstr.edit.blok3b.nonindustri', $p),
        ];
    }

    private function editRoutesBlok3a2(int $tahun, int $triwulan, $kbliPrefix): array
    {
        // Legacy alias → same as blok3c-industri
        return $this->editRoutesBlok3cIndustri($tahun, $triwulan, $kbliPrefix);
    }

    private function editRoutesBlok3cIndustri(int $tahun, int $triwulan, $kbliPrefix): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];

        return [
            'autoSave'           => route('survey.sibstr.edit.blok3c.industri.autosave', $p),
            'saveAll'            => route('survey.sibstr.edit.blok3c.industri.save', $p),
            'status'             => route('survey.sibstr.edit.blok3c.industri.status', $p),
            'backToBlok3b'       => route('survey.sibstr.edit.blok3b.industri', $p),
            'nextBlok'           => route('survey.sibstr.edit.blok4', $p),
            'blok3b_industri'    => route('survey.sibstr.edit.blok3b.industri', $p),
        ];
    }

    private function editRoutesBlok3bIndustri(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'       => route('survey.sibstr.edit.blok3b.industri.autosave', $p),
            'saveAll'        => route('survey.sibstr.edit.blok3b.industri.save', $p),
            'status'         => route('survey.sibstr.edit.blok3b.industri.status', $p),
            'backToBlok3a'   => route('survey.sibstr.edit.blok3a', $p),
            'nextBlok'       => $triwulan > 0
                ? route('survey.sibstr.edit.blok5', $p)
                : route('survey.sibstr.edit.blok3c.industri', $p),
            'blok5'          => route('survey.sibstr.edit.blok5', $p),
            'blok3b_industri'=> route('survey.sibstr.edit.blok3b.industri', $p),
        ];
    }

    private function editRoutesBlok3bNonIndustri(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'           => route('survey.sibstr.edit.blok3b.nonindustri.autosave', $p),
            'saveAll'            => route('survey.sibstr.edit.blok3b.nonindustri.save', $p),
            'status'             => route('survey.sibstr.edit.blok3b.nonindustri.status', $p),
            'backToBlok2'        => route('survey.sibstr.edit.blok2', $p),
            'nextBlok'           => $triwulan > 0
                ? route('survey.sibstr.edit.blok5', $p)
                : route('survey.sibstr.edit.blok4', $p),
            'blok5'              => route('survey.sibstr.edit.blok5', $p),
            'blok3b_nonindustri' => route('survey.sibstr.edit.blok3b.nonindustri', $p),
        ];
    }

    private function editRoutesBlok4(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'                => route('survey.sibstr.edit.blok4.autosave', $p),
            'saveAll'                 => route('survey.sibstr.edit.blok4.save', $p),
            'status'                  => route('survey.sibstr.edit.blok4.status', $p),
            'backToBlok3cIndustri'    => route('survey.sibstr.edit.blok3c.industri', $p),
            'backToBlok3bNonIndustri' => route('survey.sibstr.edit.blok3b.nonindustri', $p),
            'blok5'                   => route('survey.sibstr.edit.blok5', $p),
            'nextBlok'                => route('survey.sibstr.edit.blok5', $p),
        ];
    }

    private function editRoutesBlok5(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'    => route('survey.sibstr.edit.blok5.autosave', $p),
            'saveAll'     => route('survey.sibstr.edit.blok5.save', $p),
            'status'      => route('survey.sibstr.edit.blok5.status', $p),
            'backToBlok4' => route('survey.sibstr.edit.blok4', $p),
            'blok6'       => route('survey.sibstr.edit.blok6', $p),
            'nextBlok'    => route('survey.sibstr.edit.blok6', $p),
        ];
    }

    private function editRoutesBlok6(int $tahun, int $triwulan): array
    {
        $period = $triwulan === 0 ? 'tahunan' : (string) $triwulan;
        $p = ['year' => $tahun, 'period' => $period];
        return [
            'autoSave'     => route('survey.sibstr.edit.blok6.autosave', $p),
            'saveAll'      => route('survey.sibstr.edit.blok6.save', $p),
            'status'       => route('survey.sibstr.edit.blok6.status', $p),
            'backToBlok5'  => route('survey.sibstr.edit.blok5', $p),
            'backToBlok2'  => route('survey.sibstr.edit.blok2', $p),
            'finishSurvey' => route('survey.sibstr.edit.blok6.finish', $p),
        ];
    }

    // ──────────────────────────────────────────────
    //  PAGE METHODS
    // ──────────────────────────────────────────────

    public function blok1()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $jenisKawasanOptions = SurveyResponse::getJenisKawasanOptions();
        $bpsRiData         = $this->bpsRiData();
        $isEditMode        = true;
        $editRoutes        = $this->editRoutesBlok1($tahun, $triwulan);
        $referenceResponse = $this->getPreviousPeriodResponse($surveyResponse->user_id, $tahun, $triwulan);

        // Cross-fill: overlapping Blok I answers from this user's UB survey
        $ub = \App\Models\UbSurveyResponse::where('user_id', $surveyResponse->user_id)
            ->where('tahun', 2026)
            ->first();
        $crossFill = null;
        if ($ub) {
            $items = \App\Support\SurveyCrossFill::ubToSibstr($ub);
            if (\App\Support\SurveyCrossFill::hasCopyable($items)) {
                $crossFill = [
                    'items'       => $items,
                    'sourceBadge' => 'Survei UB',
                    'sourceLabel' => 'Data dari Survei UB SE2026 yang sudah Anda isi',
                ];
            }
        }

        return view('survey.sibstr.blok1', compact(
            'surveyResponse', 'jenisKawasanOptions', 'bpsRiData',
            'isEditMode', 'editRoutes', 'tahun', 'triwulan', 'period',
            'referenceResponse', 'crossFill'
        ));
    }

    public function blok2()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode        = true;
        $editRoutes        = $this->editRoutesBlok2($tahun, $triwulan);
        $referenceResponse = $this->getPreviousPeriodResponse($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok2', compact(
            'surveyResponse', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'referenceResponse'
        ));
    }

    public function blok3a()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode          = true;
        $editRoutes          = $this->editRoutesBlok3a($tahun, $triwulan, $kbliPrefix);
        $historicalResponses = $this->getHistoricalResponses($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok3a', compact(
            'surveyResponse', 'kbliPrefix', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function blok3a2()
    {
        // Legacy alias → redirect to blok3c-industri
        return $this->blok3cIndustri();
    }

    public function blok3cIndustri()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode          = true;
        $editRoutes          = $this->editRoutesBlok3cIndustri($tahun, $triwulan, $kbliPrefix);
        $historicalResponses = $this->getHistoricalResponses($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok3c-industri', compact(
            'surveyResponse', 'kbliPrefix', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function blok3bIndustri()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode          = true;
        $editRoutes          = $this->editRoutesBlok3bIndustri($tahun, $triwulan);
        $historicalResponses = $this->getHistoricalResponses($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok3b-industri', compact(
            'surveyResponse', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function blok3bNonIndustri()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode          = true;
        $editRoutes          = $this->editRoutesBlok3bNonIndustri($tahun, $triwulan);
        $historicalResponses = $this->getHistoricalResponses($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok3b-nonindustri', compact(
            'surveyResponse', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function blok4()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode        = true;
        $editRoutes        = $this->editRoutesBlok4($tahun, $triwulan);
        $referenceResponse = $this->getPreviousPeriodResponse($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok4', compact(
            'surveyResponse', 'kbliPrefix', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'referenceResponse'
        ));
    }

    public function blok5()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode        = true;
        $editRoutes        = $this->editRoutesBlok5($tahun, $triwulan);
        $referenceResponse = $this->getPreviousPeriodResponse($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok5', compact(
            'surveyResponse', 'isEditMode', 'editRoutes',
            'tahun', 'triwulan', 'period', 'referenceResponse'
        ));
    }

    public function blok6()
    {
        if ($redirect = $this->checkTriwulanAccess()) {
            return $redirect;
        }

        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kondisiPerusahaan    = $surveyResponse->kondisi_perusahaan;
        $jaringanUnitKegiatan = $surveyResponse->jaringan_unit_kegiatan;

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $isEditMode        = true;
        $editRoutes        = $this->editRoutesBlok6($tahun, $triwulan);
        $referenceResponse = $this->getPreviousPeriodResponse($surveyResponse->user_id, $tahun, $triwulan);

        return view('survey.sibstr.blok6', compact(
            'surveyResponse', 'kondisiPerusahaan', 'jaringanUnitKegiatan',
            'isEditMode', 'editRoutes', 'tahun', 'triwulan', 'period', 'referenceResponse'
        ));
    }

    // ──────────────────────────────────────────────
    //  SAVE-ALL WRAPPERS (preserve is_completed = true)
    // ──────────────────────────────────────────────

    /**
     * Delegate to an existing SurveyController save method,
     * but force is_completed to remain true so editing never
     * reverts the completion status.
     */
    private function editSaveAll(Request $request, string $method)
    {
        $request->merge(['is_completed' => true]);
        return app(SurveyController::class)->{$method}($request);
    }

    public function saveAllBlok1(Request $request)
    {
        return $this->editSaveAll($request, 'saveAll');
    }

    public function saveAllBlok2(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok2');
    }

    public function saveAllBlok3a(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok3a');
    }

    public function saveAllBlok3a2(Request $request)
    {
        // Legacy alias → blok3c-industri
        return $this->saveAllBlok3cIndustri($request);
    }

    public function saveAllBlok3cIndustri(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok3cIndustri');
    }

    public function saveAllBlok3bIndustri(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok3bIndustri');
    }

    public function saveAllBlok3bNonIndustri(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok3bNonIndustri');
    }

    public function saveAllBlok4(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok4');
    }

    public function saveAllBlok5(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok5');
    }

    public function saveAllBlok6(Request $request)
    {
        return $this->editSaveAll($request, 'saveAllBlok6');
    }

    /**
     * Finish editing – save Blok 6 data and redirect.
     * The survey is already completed, so we just ensure data is saved.
     */
    public function finishSurvey(Request $request)
    {
        $request->merge(['is_completed' => true, '_edit_mode' => true]);
        return app(SurveyController::class)->finishSurvey($request);
    }
}

