<?php

namespace App\Http\Controllers;

use App\Models\SurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Read the active survey period from the current request or session.
     * Priority: (1) route params {year}/{period}, (2) query/body params, (3) session.
     * Returns ['tahun' => int, 'triwulan' => int, 'period' => string].
     * 'period' is 'tahunan' for annual (triwulan=0) or '1'–'4' for quarterly.
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

        // Priority 2: query-string / request body (legacy fallback for AJAX)
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
     * Fetch the survey response for the immediately preceding period (for read-only comparison).
     * For a quarterly row (triwulan 1–4), looks at the previous triwulan of the same year,
     * or TW4 of the previous year when triwulan = 1.
     * For an annual row (triwulan = 0), looks at the previous year's annual row.
     */
    private function getPreviousPeriodResponse(int|string $userId, int $tahun, int $triwulan): ?SurveyResponse
    {
        if ($triwulan === 0) {
            // Annual — compare with previous year's annual
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

        // triwulan === 1: look at TW4 of the previous year, or the annual row
        $prevTw4 = SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun - 1)
            ->where('triwulan', 4)
            ->first();

        if ($prevTw4) {
            return $prevTw4;
        }

        // Fall back to previous year's annual row
        return SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun - 1)
            ->where('triwulan', 0)
            ->first();
    }

    /**
     * Fetch all prior-period survey responses for the given user (newest first).
     * Used to populate the historical-data reference drawer on Blok 3A / 3B pages.
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
     * Guard for Triwulan 2026: blocks access unless the 2025 Tahunan survey has
     * reached FINISH_SURVEY status (is_completed = true, set only by Block 6 finish).
     * Returns a redirect to the landing page when access is denied, or null to allow.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function checkTriwulanAccess($user): ?\Illuminate\Http\RedirectResponse
    {
        ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

        if ($tahun !== 2026 || $triwulan < 1) {
            return null;
        }

        if (!SurveyResponse::isTahunanFullyCompletedForUser($user->id)) {
            return redirect()
                ->route('survey.sibstr.entry')
                ->with('error', 'Survei Triwulanan 2026 hanya dapat diakses setelah Survei Tahunan 2025 diselesaikan sepenuhnya melalui Blok VI.');
        }

        // Block access to a quarter that has not opened yet (e.g. TW II before its
        // launch date), even via direct URL or an existing draft.
        if (!in_array($triwulan, SurveyResponse::availableTriwulan($tahun), true)) {
            return redirect()
                ->route('survey.sibstr.entry')
                ->with('error', 'Survei triwulan ini belum dibuka.');
        }

        return null;
    }

    /**
     * Check if the SIBSTR survey for the current period is completed.
     * Period-aware: only redirects if THIS period's row is completed.
     *
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse|null
     */
    private function checkSibstrCompletion($user)
    {
        ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

        $isCompleted = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->where('is_completed', true)
            ->exists();

        if ($isCompleted) {
            return redirect()->route('dashboard.surveys.sibstr.results');
        }

        return null;
    }

    /**
     * Sequential block access guard.
     *
     * Prevents navigating to a survey block unless all prerequisite blocks for
     * that user's path (determined by kondisi_perusahaan and KBLI) are complete.
     * Returns a redirect to the first incomplete prerequisite, or null when the
     * requested block is accessible.
     *
     * @param  string  $requestedBlock  Route-name suffix, e.g. 'blok6', 'blok3b.industri'
     */
    private function checkSequentialBlockAccess(string $requestedBlock): ?\Illuminate\Http\RedirectResponse
    {
        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();
        $user = Auth::user();

        $response = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->first();

        $firstIncomplete = $this->resolveFirstIncompleteBlock($response, $requestedBlock, $triwulan);

        if ($firstIncomplete !== null) {
            return redirect()
                ->route("survey.sibstr.{$firstIncomplete}", ['year' => $tahun, 'period' => $period])
                ->with('warning', 'Silakan lengkapi blok sebelumnya terlebih dahulu sebelum melanjutkan.');
        }

        return null;
    }

    /**
     * Determine the first prerequisite block that is not yet complete for the
     * requested block, or null when the requested block is accessible.
     *
     * Block sequences by company type and period:
     *
     *  Non-active company (any period):
     *    blok1 → blok2 → blok6
     *
     *  Active + Industri (KBLI 10–33), tahunan:
     *    blok1 → blok2 → blok3a → blok3b.industri → blok3c.industri → blok4 → blok5 → blok6
     *
     *  Active + Industri (KBLI 10–33), triwulanan:
     *    blok1 → blok2 → blok3a → blok3b.industri → blok5 → blok6
     *
     *  Active + Non-Industri, tahunan:
     *    blok1 → blok2 → blok3b.nonindustri → blok4 → blok5 → blok6
     *
     *  Active + Non-Industri, triwulanan:
     *    blok1 → blok2 → blok3b.nonindustri → blok5 → blok6
     *
     * @param  \App\Models\SurveyResponse|null  $r
     * @param  string  $requestedBlock
     * @param  int     $triwulan
     * @return string|null  Route-name suffix of the first incomplete prerequisite, or null.
     */
    private function resolveFirstIncompleteBlock(
        ?SurveyResponse $r,
        string $requestedBlock,
        int $triwulan
    ): ?string {
        // blok1 is always the universal entry point
        if ($requestedBlock === 'blok1') {
            return null;
        }

        // Everything beyond blok1 requires it to be complete
        if (!$r || !$r->isBlok1Complete()) {
            return 'blok1';
        }

        if ($requestedBlock === 'blok2') {
            return null;
        }

        // Everything beyond blok2 requires it to be submitted with key fields
        if (!$r->isBlok2Complete()) {
            return 'blok2';
        }

        // Non-active company: only blok6 follows blok2
        if ($r->kondisi_perusahaan !== 'masih_aktif') {
            return ($requestedBlock === 'blok6') ? null : 'blok6';
        }

        // Active company – build the ordered path for this user + period
        $kbliIndustri = $r->isKbliIndustri();

        $path = $kbliIndustri
            ? ($triwulan === 0
                ? ['blok3a', 'blok3b.industri', 'blok3c.industri', 'blok4', 'blok5', 'blok6']
                : ['blok3a', 'blok3b.industri', 'blok5', 'blok6'])
            : ($triwulan === 0
                ? ['blok3b.nonindustri', 'blok4', 'blok5', 'blok6']
                : ['blok3b.nonindustri', 'blok5', 'blok6']);

        // Completion checkers keyed by block route-name suffix
        $completionOf = [
            'blok3a'             => fn () => $r->isBlok3aComplete(),
            'blok3b.industri'    => fn () => (bool) $r->blok3b_industri_completed,
            'blok3c.industri'    => fn () => (bool) $r->blok3a2_completed,
            'blok3b.nonindustri' => fn () => (bool) $r->blok3b_nonindustri_completed,
            'blok4'              => fn () => (bool) $r->blok4_completed,
            'blok5'              => fn () => (bool) $r->blok5_completed,
            'blok6'              => fn () => true,
        ];

        $posInPath = array_search($requestedBlock, $path, true);

        if ($posInPath === false) {
            // Block is not part of this user's valid path.
            // Redirect to the first incomplete block in the valid path.
            foreach ($path as $block) {
                if ($block === 'blok6') {
                    break;
                }
                $checker = $completionOf[$block] ?? fn () => true;
                if (!$checker()) {
                    return $block;
                }
            }
            return 'blok6';
        }

        // Check every prerequisite block in order
        for ($i = 0; $i < $posInPath; $i++) {
            $block   = $path[$i];
            $checker = $completionOf[$block] ?? fn () => true;
            if (!$checker()) {
                return $block;
            }
        }

        return null; // All prerequisites met; access is granted
    }

    /**
     * Comprehensive sequential validation for the final survey submission.
     *
     * Aggregates the "Data belum lengkap" criteria from every individual block
     * page and evaluates them in order. Returns a ['block' => ..., 'label' => ...]
     * array for the FIRST failing block, or null when all blocks are complete.
     *
     * Sequences mirror resolveFirstIncompleteBlock:
     *
     *   Non-active company (any period):
     *     blok1 → blok2
     *
     *   Active + Industri (KBLI 10–33), tahunan:
     *     blok1 → blok2 → blok3a → blok3b.industri → blok3c.industri → blok4 → blok5
     *
     *   Active + Industri (KBLI 10–33), triwulanan:
     *     blok1 → blok2 → blok3a → blok3b.industri → blok5
     *
     *   Active + Non-Industri, tahunan:
     *     blok1 → blok2 → blok3b.nonindustri → blok4 → blok5
     *
     *   Active + Non-Industri, triwulanan:
     *     blok1 → blok2 → blok3b.nonindustri → blok5
     *
     * @param  SurveyResponse|null $r
     * @param  int                 $triwulan
     * @return array{block: string, label: string}|null
     */
    private function runFinishSurveyValidation(?SurveyResponse $r, int $triwulan): ?array
    {
        $labels = [
            'blok1'              => 'Blok I (Identitas Perusahaan)',
            'blok2'              => 'Blok II (Keterangan Umum Perusahaan)',
            'blok3a'             => 'Blok IIIA (Daftar Barang yang Diproduksi)',
            'blok3b.industri'    => 'Blok IIIB Industri (Produksi dan Pendapatan)',
            'blok3c.industri'    => 'Blok IIIC Industri (Pengeluaran)',
            'blok3b.nonindustri' => 'Blok IIIB Non-Industri (Pendapatan)',
            'blok4'              => 'Blok IV (Fenomena dan Indikator)',
            'blok5'              => 'Blok V (Tenaga Kerja)',
        ];

        $fail = fn (string $block): array => [
            'block' => $block,
            'label' => $labels[$block] ?? "Blok {$block}",
        ];

        $isTahunan = $triwulan === 0;

        // ── Blok 1 ──────────────────────────────────────────────
        if (!$r || !$r->isBlok1Complete()) {
            return $fail('blok1');
        }

        // ── Blok 2 ──────────────────────────────────────────────
        if (!$r->isBlok2Complete()) {
            return $fail('blok2');
        }

        // Non-active company: blok1 & blok2 are the only requirements
        if ($r->kondisi_perusahaan !== 'masih_aktif') {
            return null;
        }

        // ── Blok 3 — KBLI-conditional ───────────────────────────
        if ($r->isKbliIndustri()) {
            // Industri path: 3A → 3B Industri → 3C Industri (tahunan only)
            if (!$r->isBlok3aComplete()) {
                return $fail('blok3a');
            }
            if (!$r->isBlok3bIndustriComplete()) {
                return $fail('blok3b.industri');
            }
            if ($isTahunan && !$r->blok3a2_completed) {
                return $fail('blok3c.industri');
            }
        } else {
            // Non-Industri path: 3B Non-Industri only (3A and 3C are skipped entirely)
            if (!$r->blok3b_nonindustri_completed) {
                return $fail('blok3b.nonindustri');
            }
        }

        // ── Blok 4 (tahunan only) ────────────────────────────────
        if ($isTahunan && !$r->isBlok4Complete()) {
            return $fail('blok4');
        }

        // ── Blok 5 ──────────────────────────────────────────────
        if (!$r->blok5_completed) {
            return $fail('blok5');
        }

        return null; // All blocks complete — proceed to finish
    }

    /**
     * SIBSTR survey landing/overview page.
     * Shows the sequential steps: Annual 2025 → Quarterly 2026.
     * Quarterly is gated behind annual completion + Q207 fields.
     */
    public function sibstrEntry()
    {
        $user         = Auth::user();

        // Mitra users see the full SIBSTR results index at this URL
        if ($user->is_mitra) {
            return $this->mitraSibstrIndex(request());
        }

        $annualYear   = 2025;
        $triwulanYear = 2026;

        // Fetch tahunan 2025 row
        $annualResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $annualYear)
            ->where('triwulan', 0)
            ->first();

        $annualDone       = $annualResponse && $annualResponse->is_completed;
        $annualInProgress = $annualResponse && !$annualResponse->is_completed;

        // Quarterly unlocked ONLY when annual_survey_status = 'FINISH_SURVEY'.
        // This is set exclusively by finishSurvey() when the user submits Block 6.
        // Legacy rows with is_completed = true but null annual_survey_status remain locked
        // until the user re-submits Block 6 through the finish flow.
        $quarterlyUnlocked = SurveyResponse::isTahunanFullyCompletedForUser($user->id);

        // Build triwulan cards for 2026
        $availableTriwulan = SurveyResponse::availableTriwulan($triwulanYear);

        $twRows = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $triwulanYear)
            ->where('triwulan', '>', 0)
            ->get()
            ->keyBy('triwulan');

        $triwulanCards = [];
        for ($tw = 1; $tw <= 4; $tw++) {
            $resp         = $twRows->get($tw);
            $isAvailable  = in_array($tw, $availableTriwulan, true);
            $isCompleted  = $resp ? (bool) $resp->is_completed : false;
            $isInProgress = $resp && !$isCompleted;

            // A quarter that hasn't opened yet stays locked even if a draft row
            // already exists (e.g. created before its launch date) — nothing is
            // actionable until it opens.
            if (!$isAvailable) {
                $isCompleted  = false;
                $isInProgress = false;
            }

            $triwulanCards[$tw] = [
                'triwulan'       => $tw,
                'label'          => SurveyResponse::triwulanLabel($tw),
                'response'       => $resp,
                'is_available'   => $isAvailable,
                'is_completed'   => $isCompleted,
                'is_in_progress' => $isInProgress,
                'is_locked'      => !$isAvailable,
            ];
        }

        return view('survey.sibstr.landing', [
            'annualResponse'    => $annualResponse,
            'annualDone'        => $annualDone,
            'annualInProgress'  => $annualInProgress,
            'quarterlyUnlocked' => $quarterlyUnlocked,
            'annualYear'        => $annualYear,
            'triwulanYear'      => $triwulanYear,
            'availableTriwulan' => $availableTriwulan,
            'triwulanCards'     => $triwulanCards,
        ]);
    }

    /**
     * Display the SIBSTR survey form (Block 1).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok1()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }
        
        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for this user+period
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok1', $tahun, $triwulan);

        // Fetch reference response from the immediately preceding period for comparison
        $referenceResponse = $this->getPreviousPeriodResponse($user->id, $tahun, $triwulan);

        // Cross-fill: offer to copy overlapping Blok I answers from the user's UB survey.
        // Only relevant when identity is first established (Tahunan or TW I). For
        // TW II–IV the previous-quarter reference drawer is the correct source, so
        // the UB cross-fill is suppressed there.
        $crossFill = $triwulan <= 1 ? $this->ubCrossFillForSibstr($user->id) : null;

        // Get jenis kawasan options
        $jenisKawasanOptions = SurveyResponse::getJenisKawasanOptions();

        // BPS RI static data
        $bpsRiData = [
            'penghubung' => 'Tim Statistik Industri',
            'telepon' => '021-3810291 ext. 5310–5313',
            'fax' => '021-3863816, 021-3857046',
            'email' => 'ibs@bps.go.id',
            'alamat' => 'Jl. Dr. Sutomo No. 8, Jakarta 10710',
        ];

        return view('survey.sibstr.blok1', compact(
            'surveyResponse', 'jenisKawasanOptions', 'bpsRiData',
            'referenceResponse', 'tahun', 'triwulan', 'period', 'crossFill'
        ));
    }

    /**
     * Build the cross-fill payload for a SIBSTR Blok 1 form: the overlapping
     * answers from this user's UB survey, ready for the cross-fill drawer.
     *
     * @return array{items: array, sourceBadge: string, sourceLabel: string}|null
     */
    private function ubCrossFillForSibstr(int|string $userId): ?array
    {
        $ub = \App\Models\UbSurveyResponse::where('user_id', $userId)
            ->where('tahun', 2026)
            ->first();

        if (!$ub) {
            return null;
        }

        $items = \App\Support\SurveyCrossFill::ubToSibstr($ub);
        if (!\App\Support\SurveyCrossFill::hasCopyable($items)) {
            return null;
        }

        return [
            'items'       => $items,
            'sourceBadge' => 'Survei UB',
            'sourceLabel' => 'Data dari Survei UB SE2026 yang sudah Anda isi',
        ];
    }

    /**
     * Display the SIBSTR survey form (Block 2).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok2()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok2')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for this user+period
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok2', $tahun, $triwulan);

        // Reference response for blok2 read-only comparison panel
        $referenceResponse = $this->getPreviousPeriodResponse($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok2', compact('surveyResponse', 'referenceResponse', 'tahun', 'triwulan', 'period'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIA).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3a()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok3a')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for this user+period
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a', $tahun, $triwulan);

        // Check if user should access this block (kondisi_perusahaan must be 'masih_aktif')
        // Use the latest survey response regardless of section, since getOrCreateForUser
        // updates the single row's survey_section as the user navigates.
        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse || $latestResponse->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()
                ->route('survey.sibstr.blok6', ['year' => $tahun, 'period' => $period])
                ->with('warning', 'Blok IIIA hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".');
        }

        // Determine KBLI prefix to help the view set a sensible fallback next block
        $kbliPrefix = null;
        if ($latestResponse?->kbli_utama && preg_match('/^(\d{2})/', $latestResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $historicalResponses = $this->getHistoricalResponses($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok3a', compact('surveyResponse', 'kbliPrefix', 'tahun', 'triwulan', 'period', 'historicalResponses'));
    }

    /**
     * Display the SIBSTR survey form (Block 6).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok6()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok6')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for this user+period
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok6', $tahun, $triwulan);

        // Fetch latest response values to control conditional navigation and hints
        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        $kondisiPerusahaan = $latestResponse?->kondisi_perusahaan;
        // Also fetch R202 (jaringan_unit_kegiatan) to control back navigation when option 'e' is selected
        $jaringanUnitKegiatan = $latestResponse?->jaringan_unit_kegiatan;

        $referenceResponse = $this->getPreviousPeriodResponse($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok6', compact('surveyResponse', 'kondisiPerusahaan', 'jaringanUnitKegiatan', 'tahun', 'triwulan', 'period', 'referenceResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block IV - Fenomena dan Catatan).
     */
    public function sibstrBlok4()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok4')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for Blok 4
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4', $tahun, $triwulan);

        // Fetch KBLI from latest response to help decide back navigation to 3B variant
        $latestResponseBlok = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        $kbli = $latestResponseBlok?->kbli_utama;
        $kbliPrefix = null;
        if ($kbli && preg_match('/^(\d{2})/', $kbli, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $referenceResponse = $this->getPreviousPeriodResponse($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok4', compact('surveyResponse', 'kbliPrefix', 'tahun', 'triwulan', 'period', 'referenceResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block V - Kondisi dan Prospek Usaha).
     */
    public function sibstrBlok5()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok5')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Get or create survey response for Blok 5
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5', $tahun, $triwulan);

        $referenceResponse = $this->getPreviousPeriodResponse($user->id, $tahun, $triwulan);

        // Determine KBLI prefix so triwulanan back-navigation can pick the correct blok3b
        $kbliPrefix = null;
        $latestBlok5 = \App\Models\SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->whereNotNull('kbli_utama')
            ->latest()
            ->first();
        if ($latestBlok5?->kbli_utama && preg_match('/^(\d{2})/', $latestBlok5->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        return view('survey.sibstr.blok5', compact('surveyResponse', 'tahun', 'triwulan', 'period', 'referenceResponse', 'kbliPrefix'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIB Industri).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3bIndustri()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok3b.industri')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Ensure perusahaan masih aktif menggunakan latest response
        $latestResponse3b = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse3b || $latestResponse3b->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()
                ->route('survey.sibstr.blok6', ['year' => $tahun, 'period' => $period])
                ->with('warning', 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.');
        }

        // Create or get Blok 3B Industri response
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri', $tahun, $triwulan);

        $historicalResponses = $this->getHistoricalResponses($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok3b-industri', compact('surveyResponse', 'tahun', 'triwulan', 'period', 'historicalResponses'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIB Non-Industri) - placeholder.
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3bNonIndustri()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok3b.nonindustri')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        // Pastikan perusahaan masih aktif menggunakan latest response
        $latestResponse3bNon = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse3bNon || $latestResponse3bNon->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()
                ->route('survey.sibstr.blok6', ['year' => $tahun, 'period' => $period])
                ->with('warning', 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.');
        }

        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri', $tahun, $triwulan);
        $historicalResponses = $this->getHistoricalResponses($user->id, $tahun, $triwulan);
        return view('survey.sibstr.blok3b-nonindustri', compact('surveyResponse', 'tahun', 'triwulan', 'period', 'historicalResponses'));
    }

    /**
     * Auto-save survey data via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSave(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the request - simplified validation for auto-save
            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable|string|max:1000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // Default to SIBSTR Blok1 for now
            $surveyType = 'sibstr';
            $surveySection = 'blok1';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Update the specific field
            $updateData = [
                $fieldName => $fieldValue,
            ];

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAll(Request $request)
    {
        try {
            $user = Auth::user();
            
            // Validate the request - updated with required fields and NIB validation
            $validator = Validator::make($request->all(), [
                'kip' => 'nullable|string|max:255',
                'idsbr' => 'nullable|string|max:255',
                // Required fields in I. KETERANGAN UMUM section
                'nama_perusahaan' => 'required|string|max:1000',
                'alamat_pabrik' => 'required|string|max:1000',
                'kabupaten_kota' => 'required|string|max:255',
                'telepon_fax' => 'required|string|max:255',
                'penghubung' => 'required|string|max:255',
                'email' => 'required|email|max:255',
                'nib' => 'required|string|regex:/^[0-9]{13}$/|size:13',
                'jenis_kawasan' => 'required|string|in:ekonomi_khusus,industri,luar_kawasan',
                'nama_kawasan' => 'required|string|max:255',
                'nama_pengelola_kawasan' => 'required|string|max:255',
                // Required fields in LEGALISASI PERUSAHAAN section
                'legalisasi_nama' => 'required|string|max:255',
                'legalisasi_jabatan' => 'required|string|max:255',
                // Optional NIK: allow any input without restrictions
                'legalisasi_nik' => 'nullable|string',
                // BPS Provinsi fields commented out - no longer validated
                // 'bps_provinsi_penghubung' => 'nullable|string|max:255',
                // 'bps_provinsi_telepon' => 'nullable|string|max:255',
                // 'bps_provinsi_fax' => 'nullable|string|max:255',
                // 'bps_provinsi_email' => 'nullable|email|max:255',
                // 'bps_provinsi_alamat' => 'nullable|string|max:1000',
            ], [
                // Custom error messages
                'nama_perusahaan.required' => 'Nama perusahaan wajib diisi.',
                'alamat_pabrik.required' => 'Alamat pabrik/tempat usaha wajib diisi.',
                'kabupaten_kota.required' => 'Kabupaten/kota wajib diisi.',
                'telepon_fax.required' => 'Telepon/fax wajib diisi.',
                'penghubung.required' => 'Nama penghubung wajib diisi.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'nib.required' => 'NIB (Nomor Induk Berusaha) wajib diisi.',
                'nib.regex' => 'NIB harus berupa 13 digit angka.',
                'nib.size' => 'NIB harus berupa 13 digit angka.',
                'jenis_kawasan.required' => 'Jenis kawasan wajib dipilih.',
                'nama_kawasan.required' => 'Nama kawasan wajib diisi.',
                'nama_pengelola_kawasan.required' => 'Nama perusahaan pengelola kawasan wajib diisi.',
                'legalisasi_nama.required' => 'Nama penanggung jawab wajib diisi.',
                'legalisasi_jabatan.required' => 'Jabatan penanggung jawab wajib diisi.',
                // No validation messages for NIK since it accepts any input
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Default to SIBSTR Blok1
            $surveyType = 'sibstr';
            $surveySection = 'blok1';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

            // Mark as completed if requested
            // Mark as completed if requested
            if ($request->has('is_completed')) {
                // For Blok 1, we restrict completion unless it was ALREADY completed (Edit Mode)
                $updateData['is_completed'] = $surveyResponse->is_completed ? true : false;
            }

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Survey data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'is_completed' => $surveyResponse->is_completed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save survey data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey progress/status.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatus(Request $request)
    {
        try {
            $user = Auth::user();
            $surveyType = $request->input('survey_type', 'sibstr');
            $surveySection = $request->input('survey_section', 'blok1');

            $surveyResponse = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', $surveyType)
                ->where('survey_section', $surveySection)
                ->first();

            if (!$surveyResponse) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'is_completed' => false,
                    'last_saved_at' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => true,
                'is_completed' => $surveyResponse->is_completed,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('d/m/Y H:i:s') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok 2 via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSaveBlok2(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the request - simplified validation for auto-save
            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // Handle specific field type conversions for Blok 2
            $numericFields = [
                'rata_rata_tenaga_kerja',
                'jumlah_cabang_dan_unit_usaha',
                'jumlah_bulan_aktif_2025',
                'rata_hari_kerja_bulanan_2025',
                'rata_jam_kerja_per_hari_2025',
                'rata_shift_per_hari_2025',
                'tenaga_kerja_laki_laki',
                'tenaga_kerja_perempuan',
                'tenaga_kerja_produksi',
                'tenaga_kerja_lainnya',
                'tenaga_kerja_asing',
                'tenaga_kerja_outsourcing',
                // Q207 Tahunan 2025 new fields
                'jumlah_seluruh_pekerja',
                'pekerja_bukan_outsourcing_produksi',
                'pekerja_bukan_outsourcing_lainnya',
                'pekerja_outsourcing_produksi',
                'pekerja_outsourcing_lainnya',
            ];

            if (in_array($fieldName, $numericFields, true)) {
                // Convert to integer for numeric fields, null if empty
                $fieldValue = ($fieldValue === '' || $fieldValue === null) ? null : (int) $fieldValue;
            } elseif (in_array($fieldName, ['kegiatan_utama_perusahaan', 'produk_utama_perusahaan'], true)) {
                // Long text fields
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 1000);
            } elseif ($fieldName === 'kbli_utama') {
                // KBLI should be 5 digits when provided; store as trimmed string
                $fieldValue = $fieldValue === null ? null : substr(preg_replace('/\D/', '', (string) $fieldValue), 0, 5);
            } elseif (in_array($fieldName, ['model_industri_oem', 'model_industri_odm', 'model_industri_obm', 'model_industri_tidak_ada'], true)) {
                // Boolean checkbox/flag fields
                $fieldValue = $fieldValue ? 1 : 0;
            } elseif (in_array($fieldName, ['sertifikasi_keamanan_produk', 'sertifikasi_kesehatan_keberlanjutan', 'sertifikasi_kualitas_manajemen', 'sertifikasi_tidak_ada', 'sertifikasi_lainnya', 'model_industri_lainnya'], true)) {
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 500);
            } else {
                // Other fields (radio buttons, text inputs) - ensure they're strings and limit length
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 255);
            }

            // Skip virtual/non-DB fields
            if (in_array($fieldName, ['model_industri_lainnya_check'], true)) {
                return response()->json(['success' => true, 'message' => 'Skipped virtual field']);
            }

            // Blok 2 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok2';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Update the specific field
            $updateData = [
                $fieldName => $fieldValue,
            ];

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok 2 at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAllBlok2(Request $request)
    {
        try {
            $user = Auth::user();

            // Conditional validation based on kondisi_perusahaan
            $kondisiPerusahaan = $request->input('kondisi_perusahaan');
            $isMasihAktif = $kondisiPerusahaan === 'masih_aktif';

            // Base validation rules
            $rules = [
                'kondisi_perusahaan' => 'required|string|in:masih_aktif,belum_beroperasi,tutup,pindah,tidak_ditemukan,double_ganda_duplikat',
            ];

            // Additional validation messages
            $messages = [
                'kondisi_perusahaan.required' => 'Kondisi perusahaan wajib dipilih',
                'kondisi_perusahaan.in' => 'Pilihan kondisi perusahaan tidak valid',
            ];

            // Only validate other fields if kondisi_perusahaan is 'masih_aktif'
            if ($isMasihAktif) {
                $requestTriwulan = (int) $request->input('triwulan', 0);
                $isTahunan = $requestTriwulan === 0;

                $rules = array_merge($rules, [
                    'jaringan_unit_kegiatan' => 'required|string|in:tunggal,pabrik_unit_produksi,pusat_ada_kegiatan_produksi,kantor_pusat_administrasi_perwakilan,unit_pembantu_penunjang',
                ]);

                // Q203-Q206: tahunan-only (hidden for triwulanan)
                if ($isTahunan) {
                    $rules = array_merge($rules, [
                        // Q203 required only when 202 = c or d
                        'jumlah_cabang_dan_unit_usaha' => 'nullable|integer|min:0|required_if:jaringan_unit_kegiatan,pusat_ada_kegiatan_produksi|required_if:jaringan_unit_kegiatan,kantor_pusat_administrasi_perwakilan',

                        // Q204 sub-fields required only when 202 = b
                        'info_kantor_pusat_nama' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                        'info_kantor_pusat_alamat' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                        'info_kantor_pusat_email' => 'nullable|email|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                        'info_kantor_pusat_negara' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                        'info_kantor_pusat_provinsi' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                        'info_kantor_pusat_kabkota' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',

                        // Q205/206 should NOT be required when 202 = e (unit_pembantu_penunjang)
                        'jumlah_bulan_aktif_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:12',
                        'rata_hari_kerja_bulanan_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:31',
                        'rata_jam_kerja_per_hari_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:24',
                        'rata_shift_per_hari_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:3',
                    ]);
                }

                // Q207: period-aware TK validation
                if ($isTahunan) {
                    // Tahunan: require detailed Q207 worker breakdown
                    $rules = array_merge($rules, [
                        'jumlah_seluruh_pekerja'             => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'tenaga_kerja_laki_laki'             => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'tenaga_kerja_perempuan'             => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'pekerja_bukan_outsourcing_produksi' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'pekerja_bukan_outsourcing_lainnya'  => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'pekerja_outsourcing_produksi'       => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'pekerja_outsourcing_lainnya'        => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                        'tenaga_kerja_asing'                 => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    ]);
                } else {
                    // Triwulanan: require only single average TK entry (Q207)
                    $rules['rata_rata_tenaga_kerja'] = 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0';
                }

                // Q208: kegiatan utama (both modes)
                $rules = array_merge($rules, [
                    'kegiatan_utama_perusahaan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|max:1000',
                    'produk_utama_perusahaan'   => 'nullable|string|max:1000',
                    'kbli_utama' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|regex:/^\d{5}$/',
                    // Q210 sertifikasi (nullable — both modes)
                    'sertifikasi_keamanan_produk'        => 'nullable|string|max:500',
                    'sertifikasi_kesehatan_keberlanjutan' => 'nullable|string|max:500',
                    'sertifikasi_kualitas_manajemen'      => 'nullable|string|max:500',
                    'sertifikasi_tidak_ada'               => 'nullable|string|max:500',
                    'sertifikasi_lainnya'                 => 'nullable|string|max:500',
                    // Q211 model industri (nullable — both modes)
                    'model_industri_oem'       => 'nullable|boolean',
                    'model_industri_odm'       => 'nullable|boolean',
                    'model_industri_obm'       => 'nullable|boolean',
                    'model_industri_tidak_ada' => 'nullable|boolean',
                    'model_industri_lainnya'   => 'nullable|string|max:500',
                ]);

                // Q209, Q212, Q213: tahunan-only (hidden for triwulanan)
                if ($isTahunan) {
                    $rules = array_merge($rules, [
                        // Q209
                        'memproduksi_barang_sendiri'      => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                        'menyediakan_layanan_makan_minum' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                        'penjualan_barang_pihak_lain'     => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                        'aktivitas_jasa'                  => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                        // Q212
                        'penggunaan_internet' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                        'internet_a1_menerima_pesanan' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'internet_a2_produksi'         => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'internet_a3_distribusi'       => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'internet_a4_beli_bahan_baku'  => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'internet_a5_promosi'          => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'internet_a6_lainnya'          => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        'pemanfaatan_teknologi_digital' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                        // Q213
                        'produksi_ramah_lingkungan'         => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya_seluruh,ya_sebagian,tidak',
                        'penggunaan_input_ramah_lingkungan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                    ]);
                }

                $messages = array_merge($messages, [
                    'jaringan_unit_kegiatan.required' => 'Jaringan atau unit kegiatan perusahaan wajib dipilih',
                    'jaringan_unit_kegiatan.in' => 'Pilihan jaringan atau unit kegiatan perusahaan tidak valid',
                    'jumlah_cabang_dan_unit_usaha.required_if' => 'Pertanyaan 203 wajib diisi saat R202 = c atau d',
                    'info_kantor_pusat_nama.required_if' => 'Nama Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_alamat.required_if' => 'Alamat Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_email.required_if' => 'Email Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_email.email' => 'Format email Kantor Pusat tidak valid',
                    'info_kantor_pusat_negara.required_if' => 'Negara Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_provinsi.required_if' => 'Provinsi Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_kabkota.required_if' => 'Kabupaten/Kota Kantor Pusat wajib diisi saat R202 = b',
                    'penggunaan_internet.required' => 'Pertanyaan penggunaan internet wajib dipilih',
                    'kbli_utama.regex' => 'KBLI harus berupa 5 digit angka (contoh: 12345)',
                    'internet_a1_menerima_pesanan.required_if' => 'Isian 210a (menerima pesanan) wajib diisi saat 210 = Ya',
                    'pemanfaatan_teknologi_digital.required_if' => 'Isian 210b (teknologi digital) wajib diisi saat 210 = Ya',
                ]);
            }

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Blok 2 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok2';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token', 'tahun', 'triwulan', 'model_industri_lainnya_check']);

            // Normalise model_industri checkboxes to 0/1 (unchecked = absent from POST = 0)
            foreach (['model_industri_oem', 'model_industri_odm', 'model_industri_obm', 'model_industri_tidak_ada'] as $sField) {
                $updateData[$sField] = $request->boolean($sField) ? 1 : 0;
            }

            // Mark as completed if requested
            // Mark as completed if requested
            if ($request->has('is_completed')) {
                // For Blok 2, we restrict completion unless it was ALREADY completed (Edit Mode)
                $updateData['is_completed'] = $surveyResponse->is_completed ? true : false;
            }

            $surveyResponse->updateWithAutoSave($updateData);

            // Determine next block by kondisi_perusahaan and KBLI
            // Masih aktif:
            // - KBLI prefix 10-33 (industri) → Blok 3A
            // - KBLI prefix outside 10-33 (non-industri) → Blok 3B Non-Industri
            // Tidak aktif → Blok 6
            if ($isMasihAktif) {
                $kbli = $request->input('kbli_utama');
                if (!$kbli) {
                    // fallback to saved value if not present in request
                    $kbli = $surveyResponse->kbli_utama ?? null;
                }

                $nextBlock = 'blok3b_nonindustri';
                if ($kbli && preg_match('/^(\d{2})/', $kbli, $m)) {
                    $prefix = (int) $m[1];
                    if ($prefix >= 10 && $prefix <= 33) {
                        $nextBlock = 'blok3a';
                    }
                }
            } else {
                $nextBlock = 'blok6';
            }

            return response()->json([
                'success' => true,
                'message' => 'Survey data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'is_completed' => $surveyResponse->is_completed,
                'next_block' => $nextBlock,
                'kondisi_perusahaan' => $kondisiPerusahaan
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save survey data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey progress/status for Blok 2.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusBlok2(Request $request)
    {
        try {
            $user = Auth::user();
            $surveyType = 'sibstr';
            $surveySection = 'blok2';

            $surveyResponse = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', $surveyType)
                ->where('survey_section', $surveySection)
                ->first();

            if (!$surveyResponse) {
                return response()->json([
                    'success' => true,
                    'exists' => false,
                    'is_completed' => false,
                    'last_saved_at' => null
                ]);
            }

            return response()->json([
                'success' => true,
                'exists' => true,
                'is_completed' => $surveyResponse->is_completed,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('d/m/Y H:i:s') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok 6 via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSaveBlok6(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the request - simplified validation for auto-save
            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // Handle specific field type conversions for Blok 6
            if ($fieldName === 'catatan') {
                // Text field - ensure it's a string
                $fieldValue = $fieldValue === null ? null : (string) $fieldValue;
            }

            // Blok 6 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok6';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Update the JSON field
            $current = $surveyResponse->blok6_data ?? [];
            $current[$fieldName] = $fieldValue;

            $surveyResponse->updateWithAutoSave(['blok6_data' => $current]);

            return response()->json([
                'success' => true,
                'message' => 'Data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok IV via AJAX.
     */
    public function autoSaveBlok4(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4', $tahun, $triwulan);

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // JSON container for Blok 4
            $current = $surveyResponse->blok4_data ?? [];

            // Support nested fields like blok4[triwulan1]
            if (preg_match('/^blok4\[(.+)\]$/', $fieldName, $matches)) {
                $key = $matches[1];
                $current[$key] = $fieldValue;
                $surveyResponse->updateWithAutoSave(['blok4_data' => $current]);
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok V via AJAX.
     */
    public function autoSaveBlok5(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5', $tahun, $triwulan);

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // JSON container for Blok 5
            $current = $surveyResponse->blok5_data ?? [];

            // Support nested fields like blok5[501][p1]
            if (preg_match('/^blok5\[(.+)\]$/', $fieldName)) {
                // Extract bracketed keys
                preg_match_all('/\[(.*?)\]/', $fieldName, $matches);
                $keys = $matches[1] ?? [];

                if (count($keys) === 2) {
                    [$rowKey, $periodKey] = $keys;
                    if (!isset($current[$rowKey]) || !is_array($current[$rowKey])) {
                        $current[$rowKey] = [];
                    }
                    $current[$rowKey][$periodKey] = $fieldValue;
                    $surveyResponse->updateWithAutoSave(['blok5_data' => $current]);
                } elseif (count($keys) === 1) {
                    $key = $keys[0];
                    $current[$key] = $fieldValue;
                    $surveyResponse->updateWithAutoSave(['blok5_data' => $current]);
                } else {
                    // Fallback to direct update
                    $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
                }
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok 6 at once.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAllBlok6(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the request for Blok 6 fields
            $validator = Validator::make($request->all(), [
                'catatan' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Blok 6 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok6';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token', 'tahun', 'triwulan']);

            // Mark as completed if requested
            if ($request->has('is_completed')) {
                $updateData['is_completed'] = $request->boolean('is_completed');
            }

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Survey data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save survey data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok IV at once.
     */
    public function saveAllBlok4(Request $request)
    {
        try {
            $user = Auth::user();

            $isCompleted = $request->boolean('is_completed', false);

            // When completing, all four textarea fields are strictly required
            $requiredOrNullable = $isCompleted ? 'required' : 'nullable';

            $validator = Validator::make($request->all(), [
                'blok4.triwulan1' => "{$requiredOrNullable}|string|max:3000",
                'blok4.triwulan2' => "{$requiredOrNullable}|string|max:3000",
                'blok4.triwulan3' => "{$requiredOrNullable}|string|max:3000",
                'blok4.triwulan4' => "{$requiredOrNullable}|string|max:3000",
            ], [
                'blok4.triwulan1.required' => 'Fenomena Triwulan I (Jan–Mar) wajib diisi',
                'blok4.triwulan2.required' => 'Fenomena Triwulan II (Apr–Jun) wajib diisi',
                'blok4.triwulan3.required' => 'Fenomena Triwulan III (Jul–Sep) wajib diisi',
                'blok4.triwulan4.required' => 'Fenomena Triwulan IV (Okt–Des) wajib diisi',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4', $tahun, $triwulan);

            $data = $request->input('blok4', []);

            $surveyResponse->updateWithAutoSave([
                'blok4_data' => $data,
                'blok4_completed' => $request->boolean('is_completed', false),
            ]);

            // Next block after Blok 4 is Blok 5
            $nextBlock = 'blok5';

            return response()->json([
                'success' => true,
                'message' => 'Blok IV data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block' => $nextBlock,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IV data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok V at once.
     */
    public function saveAllBlok5(Request $request)
    {
        try {
            $user = Auth::user();

            $isCompleted = $request->boolean('is_completed', false);

            // Blok 5 fields are non-mandatory — accept any partial submission.
            ['triwulan' => $twCheck] = $this->getPeriod();
            $rows = ['501','502','503','504','505','506','507'];
            $periods = $twCheck > 0 ? ['p1','p2'] : ['p1','p2','p3','p4','p5','p6'];

            $rules = [
                'blok5' => 'nullable|array',
            ];

            foreach ($rows as $row) {
                foreach ($periods as $period) {
                    if ($row === '506') {
                        $rules["blok5.$row.$period"] = 'nullable|in:lebih_cepat,tetap,lebih_lambat';
                    } else {
                        $rules["blok5.$row.$period"] = 'nullable|in:naik,tetap,turun';
                    }
                }
            }

            $messages = [
                'blok5.*.*.in' => 'Pilihan tidak valid.',
            ];

            $validator = Validator::make($request->all(), $rules, $messages);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5', $tahun, $triwulan);

            $data = $request->input('blok5', []);

            $surveyResponse->updateWithAutoSave([
                'blok5_data' => $data,
                'blok5_completed' => $isCompleted,
            ]);

            // Next block after Blok 5 is Blok 6
            $nextBlock = 'blok6';

            return response()->json([
                'success' => true,
                'message' => 'Blok V data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block' => $nextBlock,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok V data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok IV.
     */
    public function getStatusBlok4(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4', $tahun, $triwulan);
            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => (bool) ($surveyResponse->blok4_completed ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Blok IV status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok V.
     */
    public function getStatusBlok5(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5', $tahun, $triwulan);
            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => (bool) ($surveyResponse->blok5_completed ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Blok V status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok 6.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusBlok6(Request $request)
    {
        try {
            $user = Auth::user();

            // Blok 6 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok6';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => $surveyResponse->is_completed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok IIIA via AJAX.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function autoSaveBlok3a(Request $request)
    {
        try {
            $user = Auth::user();

            // Validate the request - simplified validation for auto-save
            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            // Get or create survey response
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a', $tahun, $triwulan);

            // Handle different field types
            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // Handle JSON fields for Blok IIIA
            if (str_starts_with($fieldName, 'blok3a_')) {

                // Handle blok3a_products fields
                if (str_starts_with($fieldName, 'blok3a_products')) {
                    $currentData = $surveyResponse->blok3a_products ?? [];

                    // Pattern: blok3a_products[0][jenis_barang]
                    if (preg_match('/^blok3a_products\[(\d+)\]\[jenis_barang\]$/', $fieldName, $matches)) {
                        $productIndex = (int) $matches[1];

                        if (!isset($currentData[$productIndex])) {
                            $currentData[$productIndex] = [
                                'jenis_barang' => '',
                                'uraian' => '',
                                'satuan' => '',
                                'kbli_5digit' => '',
                                'persen_ekspor' => '',
                                'negara_ekspor' => '',
                                'banyaknya' => [],
                                'nilai' => [],
                                'harga_satuan' => [],
                            ];
                        }

                        $currentData[$productIndex]['jenis_barang'] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_products' => $currentData]);
                    }
                    // Pattern: blok3a_products[0][uraian] or blok3a_products[0][satuan]
                    elseif (preg_match('/^blok3a_products\[(\d+)\]\[(uraian|satuan|kbli_5digit|persen_ekspor|negara_ekspor)\]$/', $fieldName, $matches)) {
                        $productIndex = (int) $matches[1];
                        $fieldType = $matches[2]; // uraian, satuan, kbli_5digit, persen_ekspor, or negara_ekspor

                        if (!isset($currentData[$productIndex])) {
                            $currentData[$productIndex] = [
                                'jenis_barang' => '',
                                'uraian' => '',
                                'satuan' => '',
                                'kbli_5digit' => '',
                                'persen_ekspor' => '',
                                'negara_ekspor' => '',
                                'banyaknya' => [],
                                'nilai' => [],
                                'harga_satuan' => [],
                            ];
                        }

                        $currentData[$productIndex][$fieldType] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_products' => $currentData]);
                    }
                    // Pattern: blok3a_products[0][banyaknya][2024_des]
                    elseif (preg_match('/^blok3a_products\[(\d+)\]\[(\w+)\]\[(\w+)\]$/', $fieldName, $matches)) {
                        $productIndex = (int) $matches[1];
                        $fieldType = $matches[2]; // banyaknya, nilai, harga_satuan
                        $month = $matches[3];

                        if (!isset($currentData[$productIndex])) {
                            $currentData[$productIndex] = [
                                'jenis_barang' => '',
                                'uraian' => '',
                                'satuan' => '',
                                'kbli_5digit' => '',
                                'persen_ekspor' => '',
                                'negara_ekspor' => '',
                                'banyaknya' => [],
                                'nilai' => [],
                                'harga_satuan' => [],
                            ];
                        }

                        $currentData[$productIndex][$fieldType][$month] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_products' => $currentData]);
                    }
                }
                // Handle blok3a_lainnya fields
                elseif (str_starts_with($fieldName, 'blok3a_lainnya')) {
                    $currentData = $surveyResponse->blok3a_lainnya ?? [];

                    // Pattern: blok3a_lainnya[nilai][2024_des]
                    if (preg_match('/^blok3a_lainnya\[nilai\]\[(\w+)\]$/', $fieldName, $matches)) {
                        $month = $matches[1];

                        if (!isset($currentData['nilai'])) {
                            $currentData['nilai'] = [];
                        }

                        $currentData['nilai'][$month] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_lainnya' => $currentData]);
                    }
                }
                // Handle blok3a_totals fields
                elseif (str_starts_with($fieldName, 'blok3a_totals')) {
                    $currentData = $surveyResponse->blok3a_totals ?? [];

                    // Pattern: blok3a_totals[2024_des]
                    if (preg_match('/^blok3a_totals\[(\w+)\]$/', $fieldName, $matches)) {
                        $month = $matches[1];
                        $currentData[$month] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_totals' => $currentData]);
                    }
                }
                // Handle new blok3a_pendapatan_lainnya fields (Q302a-f)
                elseif (str_starts_with($fieldName, 'blok3a_pendapatan_lainnya')) {
                    $currentData = $surveyResponse->blok3a_pendapatan_lainnya ?? [];
                    // Pattern: blok3a_pendapatan_lainnya[q302a]
                    if (preg_match('/^blok3a_pendapatan_lainnya\[(\w+)\]$/', $fieldName, $matches)) {
                        $subKey = $matches[1];
                        $currentData[$subKey] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_pendapatan_lainnya' => $currentData]);
                    }
                }
                else {
                    // Fallback for other blok3a fields (including blok3a_q305_online)
                    $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
                }
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok IIIA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function saveAllBlok3a(Request $request)
    {
        try {
            $user = Auth::user();

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Check conditional access
            $latestResponse3a = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$latestResponse3a || $latestResponse3a->kondisi_perusahaan !== 'masih_aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Blok IIIA hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".'
                ], 403);
            }

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a', $tahun, $triwulan);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

            // Mark as completed if requested
            if ($request->has('is_completed')) {
                // For Blok 3A, we restrict completion unless it was ALREADY completed (Edit Mode)
                $updateData['is_completed'] = $surveyResponse->is_completed ? true : false;
            }

            $surveyResponse->updateWithAutoSave($updateData);

            // Determine next block based on KBLI (first two digits)
            $nextBlock = 'blok3b_nonindustri';
            $kbli = $latestResponse3a?->kbli_utama;
            if ($kbli && preg_match('/^(\d{2})/', $kbli, $m)) {
                $prefix = (int) $m[1];
                if ($prefix >= 10 && $prefix <= 33) {
                    $nextBlock = 'blok3b_industri';
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Blok IIIA data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'is_completed' => $surveyResponse->is_completed,
                'next_block' => $nextBlock,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IIIA data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok IIIA.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStatusBlok3a(Request $request)
    {
        try {
            $user = Auth::user();

            // Default to SIBSTR Blok3a
            $surveyType = 'sibstr';
            $surveySection = 'blok3a';
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection, $tahun, $triwulan);

            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => $surveyResponse->is_completed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey status: ' . $e->getMessage()
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────────────
    //  BLOK IIIA-2 – Bahan baku dan bahan penolong
    // ─────────────────────────────────────────────────────────

    public function sibstrBlok3a2()
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse || $latestResponse->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()
                ->route('survey.sibstr.blok6', ['year' => $tahun, 'period' => $period])
                ->with('warning', 'Blok IIIA-2 hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".');
        }

        $kbliPrefix = null;
        if ($latestResponse?->kbli_utama && preg_match('/^(\d{2})/', $latestResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $historicalResponses = $this->getHistoricalResponses($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok3a-2', compact(
            'surveyResponse', 'kbliPrefix', 'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function autoSaveBlok3a2(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors'  => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

            $fieldName  = $request->input('field');
            $fieldValue = $request->input('value');

            if (preg_match('/^blok3a2_materials\[(\d+)\]\[(\w+)\]$/', $fieldName, $matches)) {
                $index   = (int) $matches[1];
                $subField = $matches[2];
                $current = $surveyResponse->blok3a2_materials ?? [];
                if (!isset($current[$index])) {
                    $current[$index] = [
                        'nama_bahan'    => '', 'satuan_standar' => '',
                        'dn_banyaknya'  => '', 'dn_nilai'       => '',
                        'ln_banyaknya'  => '', 'ln_nilai'       => '',
                        'negara_asal'   => '',
                    ];
                }
                $current[$index][$subField] = $fieldValue;
                $surveyResponse->updateWithAutoSave(['blok3a2_materials' => $current]);
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function saveAllBlok3a2(Request $request)
    {
        try {
            $user = Auth::user();

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            $latestResponse = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$latestResponse || $latestResponse->kondisi_perusahaan !== 'masih_aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Blok IIIA-2 hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".'
                ], 403);
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

            $materials = $request->input('blok3a2_materials', []);
            $materials = array_values(array_filter((array) $materials, fn($m) => !empty($m['nama_bahan'])));

            $isCompleted = $request->boolean('is_completed', false);

            $surveyResponse->updateWithAutoSave([
                'blok3a2_materials' => $materials,
                'blok3a2_completed' => $isCompleted,
            ]);

            // Determine next block based on KBLI (first two digits)
            $nextBlock = 'blok3b_nonindustri';
            $kbli = $latestResponse?->kbli_utama;
            if ($kbli && preg_match('/^(\d{2})/', $kbli, $m)) {
                $prefix = (int) $m[1];
                if ($prefix >= 10 && $prefix <= 33) {
                    $nextBlock = 'blok3b_industri';
                }
            }

            return response()->json([
                'success'       => true,
                'message'       => 'Blok IIIA-2 data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block'    => $nextBlock,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IIIA-2 data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatusBlok3a2(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

            return response()->json([
                'success'       => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed'  => $surveyResponse->is_completed
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get survey status: ' . $e->getMessage()
            ], 500);
        }
    }

    // ═══════════════════════════════════════════════════════════════════
    //  Blok IIIC Industri (Bahan Baku + Nilai Aset + Kepemilikan Modal)
    //  Data stored in: blok3a2_materials (materials) +
    //                  blok3b_industri_data (Q318/Q319)
    // ═══════════════════════════════════════════════════════════════════

    public function sibstrBlok3cIndustri(Request $request)
    {
        $user = Auth::user();

        if ($redirect = $this->checkTriwulanAccess($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSibstrCompletion($user)) {
            return $redirect;
        }

        if ($redirect = $this->checkSequentialBlockAccess('blok3c.industri')) {
            return $redirect;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->first();

        $kbliPrefix = null;
        if ($latestResponse?->kbli_utama && preg_match('/^(\d{2})/', $latestResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $historicalResponses = $this->getHistoricalResponses($user->id, $tahun, $triwulan);

        return view('survey.sibstr.blok3c-industri', compact(
            'surveyResponse', 'kbliPrefix', 'tahun', 'triwulan', 'period', 'historicalResponses'
        ));
    }

    public function autoSaveBlok3cIndustri(Request $request)
    {
        // Handle blok3b_industri[*] keys (Q318/Q319 now live on this page)
        $fieldName = $request->input('field', '');
        if (preg_match('/^blok3b_industri\[(.+)\]$/', $fieldName, $matches)) {
            try {
                $user = Auth::user();
                ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
                $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);
                $key = $matches[1];
                $current = $surveyResponse->blok3b_industri_data ?? [];
                $current[$key] = $request->input('value');
                $surveyResponse->updateWithAutoSave(['blok3b_industri_data' => $current]);
                return response()->json([
                    'success'       => true,
                    'message'       => 'Data auto-saved successfully',
                    'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                ]);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Auto-save failed: ' . $e->getMessage()], 500);
            }
        }
        // Fall back to blok3a2 auto-save logic for material fields
        return $this->autoSaveBlok3a2($request);
    }

    public function saveAllBlok3cIndustri(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a2', $tahun, $triwulan);

            // Save materials (same as blok3a2)
            $materials = $request->input('blok3a2_materials', []);
            $materials = array_values(array_filter((array) $materials, fn($m) => !empty($m['nama_bahan'])));

            // Save blok3b_industri_data fields for Q318/Q319
            $blok3bInput = $request->input('blok3b_industri', []);
            $existingBlok3b = $surveyResponse->blok3b_industri_data ?? [];
            foreach ($blok3bInput as $key => $value) {
                $existingBlok3b[$key] = $value;
            }

            $isCompleted = $request->boolean('is_completed', false);

            $surveyResponse->updateWithAutoSave([
                'blok3a2_materials'      => $materials,
                'blok3a2_completed'      => $isCompleted,
                'blok3b_industri_data'   => $existingBlok3b,
            ]);

            return response()->json([
                'success'       => true,
                'message'       => 'Blok IIIC data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block'    => 'blok4',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IIIC data: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getStatusBlok3cIndustri(Request $request)
    {
        return $this->getStatusBlok3a2($request);
    }

    /**
     * Auto-save survey data for Blok IIIB Industri.
     */
    public function autoSaveBlok3bIndustri(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri', $tahun, $triwulan);

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // JSON container for Blok 3B Industri
            $current = $surveyResponse->blok3b_industri_data ?? [];

            // Support nested fields like blok3b_industri[q306_awal]
            if (preg_match('/^blok3b_industri\[(.+)\]$/', $fieldName, $matches)) {
                $key = $matches[1];
                $current[$key] = $fieldValue;
                $surveyResponse->updateWithAutoSave(['blok3b_industri_data' => $current]);
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok IIIB Industri.
     */
    public function saveAllBlok3bIndustri(Request $request)
    {
        try {
            $user = Auth::user();
            $isCompleted = $request->boolean('is_completed', false);

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Guard: only allow if perusahaan masih aktif (use latest response)
            $latestResponse3bI = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$latestResponse3bI || $latestResponse3bI->kondisi_perusahaan !== 'masih_aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.'
                ], 403);
            }

            // Sanitize incoming nested data: treat empty strings as null
            $incoming = $request->input('blok3b_industri', []);
            $sanitized = [];
            foreach ($incoming as $key => $val) {
                if (is_string($val) && trim($val) === '') {
                    $sanitized[$key] = null;
                } else {
                    $sanitized[$key] = $val;
                }
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri', $tahun, $triwulan);
            // Merge with existing data so Q318/Q319 (now on blok3c page) are not wiped
            $existingBlok3b = $surveyResponse->blok3b_industri_data ?? [];
            $data = array_merge($existingBlok3b, $sanitized);

            // Compute totals for Q309 (awal and akhir)
            $q306_awal = (float) ($data['q306_awal'] ?? 0);
            $q307_awal = (float) ($data['q307_awal'] ?? 0);
            $q308_awal = (float) ($data['q308_awal'] ?? 0);
            $q306_akhir = (float) ($data['q306_akhir'] ?? 0);
            $q307_akhir = (float) ($data['q307_akhir'] ?? 0);
            $q308_akhir = (float) ($data['q308_akhir'] ?? 0);

            $data['q309_awal'] = $q306_awal + $q307_awal + $q308_awal;
            $data['q309_akhir'] = $q306_akhir + $q307_akhir + $q308_akhir;

            // Compute year-level inventory totals for Q310b (awal and akhir)
            $q306_year_awal = (float) ($data['q306_year_awal'] ?? 0);
            $q307_year_awal = (float) ($data['q307_year_awal'] ?? 0);
            $q308_year_awal = (float) ($data['q308_year_awal'] ?? 0);
            $q306_year_akhir = (float) ($data['q306_year_akhir'] ?? 0);
            $q307_year_akhir = (float) ($data['q307_year_akhir'] ?? 0);
            $q308_year_akhir = (float) ($data['q308_year_akhir'] ?? 0);

            $data['q310b_awal'] = $q306_year_awal + $q307_year_awal + $q308_year_awal;
            $data['q310b_akhir'] = $q306_year_akhir + $q307_year_akhir + $q308_year_akhir;

            // Merge sanitized payload back into the request for validation if completing
            $request->merge(['blok3b_industri' => $data]);

            if ($isCompleted) {
                // Validation rules for numeric currency-like fields (non-negative) and percentages (0..100)
                $rules = [
                    'blok3b_industri.q306_awal'       => 'nullable|numeric|min:0',
                    'blok3b_industri.q306_akhir'      => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_awal'       => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_akhir'      => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_awal'       => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_akhir'      => 'nullable|numeric|min:0',
                    'blok3b_industri.q309_awal'       => 'nullable|numeric|min:0',
                    'blok3b_industri.q309_akhir'      => 'nullable|numeric|min:0',
                    'blok3b_industri.q310'            => 'nullable|numeric|min:0',
                    // Q311a: biaya TK triwulan lalu — nullable because no input field exists on this page
                    'blok3b_industri.q311a'           => 'nullable|numeric|min:0',
                    'blok3b_industri.q313'            => 'nullable|numeric|min:0',
                    'blok3b_industri.q315a'           => 'nullable|numeric|min:0',
                    'blok3b_industri.q315b'           => 'nullable|numeric|min:0',
                ];

                // Tahunan-only fields: year inventory, TK year breakdown, year costs
                if ($triwulan === 0) {
                    $rules = array_merge($rules, [
                        'blok3b_industri.q306_year_awal'  => 'nullable|numeric|min:0',
                        'blok3b_industri.q306_year_akhir' => 'nullable|numeric|min:0',
                        'blok3b_industri.q307_year_awal'  => 'nullable|numeric|min:0',
                        'blok3b_industri.q307_year_akhir' => 'nullable|numeric|min:0',
                        'blok3b_industri.q308_year_awal'  => 'nullable|numeric|min:0',
                        'blok3b_industri.q308_year_akhir' => 'nullable|numeric|min:0',
                        'blok3b_industri.q310b_awal'      => 'nullable|numeric|min:0',
                        'blok3b_industri.q310b_akhir'     => 'nullable|numeric|min:0',
                        'blok3b_industri.q310_year'       => 'nullable|numeric|min:0',
                        // Q311b: TK selama tahun 2025 — nullable because no input fields exist on this page
                        'blok3b_industri.q311b'           => 'nullable|numeric|min:0',
                        'blok3b_industri.q311b1'          => 'nullable|numeric|min:0',
                        'blok3b_industri.q311b2'          => 'nullable|numeric|min:0',
                        'blok3b_industri.q313_year'       => 'nullable|numeric|min:0',
                    ]);
                }

                $messages = [
                    'blok3b_industri.q319g.max' => 'Total kepemilikan tidak boleh melebihi 100%.'
                ];
                $attributes = ['blok3b_industri.q319g' => 'Total kepemilikan (Q319g)'];

                $validator = Validator::make($request->all(), $rules, $messages, $attributes);

                // Tahunan-only: Q311b must be >= b.1 + b.2
                if ($triwulan === 0) {
                    $validator->after(function($v) use ($data) {
                        $b  = (float) ($data['q311b']  ?? 0);
                        $b1 = (float) ($data['q311b1'] ?? 0);
                        $b2 = (float) ($data['q311b2'] ?? 0);
                        if ($b < ($b1 + $b2)) {
                            $v->errors()->add('blok3b_industri[q311b]', 'Nilai b (tahun 2025) harus ≥ b.1 + b.2');
                        }
                    });
                }

                if ($validator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Validation failed',
                        'errors' => $validator->errors(),
                        'debug' => [
                            'is_completed' => $isCompleted,
                            'applied_validation' => true,
                            'received_fields' => array_keys($incoming ?? []),
                        ],
                    ], 422);
                }
            }

            $surveyResponse->updateWithAutoSave([
                'blok3b_industri_data' => $data,
                'blok3b_industri_completed' => $isCompleted,
            ]);

            // Next block: triwulanan skips blok3c entirely → blok5
            $nextBlock = $triwulan > 0 ? 'blok5' : 'blok3c_industri';

            return response()->json([
                'success' => true,
                'message' => 'Blok IIIB Industri data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block' => $nextBlock,
                'debug' => [
                    'is_completed' => $isCompleted,
                    'applied_validation' => (bool) $isCompleted,
                    'received_fields' => array_keys($incoming ?? []),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IIIB Industri data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok IIIB Industri.
     */
    public function getStatusBlok3bIndustri(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri', $tahun, $triwulan);
            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => (bool) ($surveyResponse->blok3b_industri_completed ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Blok IIIB Industri status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey status for Blok IIIB Non-Industri (placeholder).
     */
    public function getStatusBlok3bNonIndustri(Request $request)
    {
        try {
            $user = Auth::user();
            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri', $tahun, $triwulan);
            return response()->json([
                'success' => true,
                'last_saved_at' => $surveyResponse->last_saved_at ? $surveyResponse->last_saved_at->format('H:i:s') : null,
                'is_completed' => (bool) ($surveyResponse->blok3b_nonindustri_completed ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get Blok IIIB Non-Industri status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Auto-save survey data for Blok IIIB Non-Industri.
     */
    public function autoSaveBlok3bNonIndustri(Request $request)
    {
        try {
            $user = Auth::user();

            $validator = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri', $tahun, $triwulan);

            $fieldName = $request->input('field');
            $fieldValue = $request->input('value');

            // JSON container for Blok 3B Non-Industri
            $current = $surveyResponse->blok3b_nonindustri_data ?? [];

            // Support nested fields like blok3b_nonindustri[q306a]
            if (preg_match('/^blok3b_nonindustri\[(.+)\]$/', $fieldName, $matches)) {
                $key = $matches[1];
                $current[$key] = $fieldValue;
                $surveyResponse->updateWithAutoSave(['blok3b_nonindustri_data' => $current]);
            } else {
                $surveyResponse->updateWithAutoSave([$fieldName => $fieldValue]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Data auto-saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Auto-save failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save all survey data for Blok IIIB Non-Industri.
     */
    public function saveAllBlok3bNonIndustri(Request $request)
    {
        try {
            $user = Auth::user();

            ['tahun' => $tahun, 'triwulan' => $triwulan] = $this->getPeriod();

            // Guard: only allow if perusahaan masih aktif (use latest response)
            $latestResponse3bN = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$latestResponse3bN || $latestResponse3bN->kondisi_perusahaan !== 'masih_aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.'
                ], 403);
            }

            // Sanitize incoming nested data: treat empty strings as null
            $incoming = $request->input('blok3b_nonindustri', []);
            $sanitized = [];
            foreach ($incoming as $key => $val) {
                if (is_string($val) && trim($val) === '') {
                    $sanitized[$key] = null;
                } else {
                    $sanitized[$key] = $val;
                }
            }

            // Compute derived totals BEFORE validation so validation sees numeric values
            $q303 = (float) ($sanitized['q303'] ?? 0);
            $q304 = (float) ($sanitized['q304'] ?? 0);
            $sanitized['q305'] = $q303 + $q304;

            $q306a = (float) ($sanitized['q306a'] ?? 0);
            $q307a = (float) ($sanitized['q307a'] ?? 0);
            $q308a = (float) ($sanitized['q308a'] ?? 0);
            $q306b = (float) ($sanitized['q306b'] ?? 0);
            $q307b = (float) ($sanitized['q307b'] ?? 0);
            $q308b = (float) ($sanitized['q308b'] ?? 0);
            $sanitized['q309a'] = $q306a + $q307a + $q308a;
            $sanitized['q309b'] = $q306b + $q307b + $q308b;

            // Merge sanitized payload back into the request for validation
            $request->merge(['blok3b_nonindustri' => $sanitized]);

            // Validation rules for numeric fields (non-negative) and percentages
            $rules = [
                'blok3b_nonindustri.q303'     => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q304'     => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q305'     => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q312'     => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q317_a'   => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q315_a'   => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q315_c'   => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q315_e'   => 'nullable|numeric|min:0',
                // Q313: biaya TK — nullable for Triwulanan; required added below for Tahunan
                'blok3b_nonindustri.q313_a1'  => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q313_a2'  => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q313_b1'  => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q313_b2'  => 'nullable|numeric|min:0',
            ];

            // Tahunan-only fields
            if ($triwulan === 0) {
                $rules = array_merge($rules, [
                    // Q313: biaya TK required for Tahunan
                    'blok3b_nonindustri.q313_a1'           => 'required|numeric|min:0',
                    'blok3b_nonindustri.q313_a2'           => 'required|numeric|min:0',
                    'blok3b_nonindustri.q313_b1'           => 'required|numeric|min:0',
                    'blok3b_nonindustri.q313_b2'           => 'required|numeric|min:0',
                    'blok3b_nonindustri.q303_year'         => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q304_year'         => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q305_year'         => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q306_year_awal'    => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q306_year_akhir'   => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q307_year_awal'    => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q307_year_akhir'   => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q308_year_awal'    => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q308_year_akhir'   => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q312_year'         => 'nullable|numeric|min:0',
                    // Q317b-k (tahunan-only subs)
                    'blok3b_nonindustri.q317_b'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_c1'           => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_c2'           => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_d'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_e'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_f'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_g'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_h'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_i'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_j'            => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q317_k'            => 'nullable|numeric|min:0',
                    // Q321-Q322 asset and capital fields
                    'blok3b_nonindustri.q318a'             => 'nullable|numeric|min:0',
                    'blok3b_nonindustri.q318b'             => 'nullable|numeric|min:0',
                ]);
            }

            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri', $tahun, $triwulan);

            // Use sanitized and computed data for storage
            $data = $sanitized;

            $surveyResponse->updateWithAutoSave([
                'blok3b_nonindustri_data' => $data,
                'blok3b_nonindustri_completed' => $request->boolean('is_completed', false),
            ]);

            // Triwulanan skips blok4 (fenomena) and goes directly to blok5
            $nextBlock = $triwulan > 0 ? 'blok5' : 'blok4';

            return response()->json([
                'success' => true,
                'message' => 'Blok IIIB Non-Industri data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'next_block' => $nextBlock,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save Blok IIIB Non-Industri data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Finish the survey and mark as completed.
     *
     * Runs the comprehensive sequential validation (runFinishSurveyValidation)
     * before persisting any completion flags. On success for the 2025 Tahunan
     * survey, sets annual_survey_status = 'FINISH_SURVEY', which is the
     * authoritative gate that unlocks Triwulan 2026 access.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function finishSurvey(Request $request)
    {
        try {
            $user = Auth::user();

            ['tahun' => $tahun, 'triwulan' => $triwulan, 'period' => $period] = $this->getPeriod();

            // Save any Blok 6 payload (catatan, etc.) before running validation
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok6', $tahun, $triwulan);

            $incoming = $request->except(['_token', 'is_completed', '_edit_mode']);
            if (!empty($incoming)) {
                $current = $surveyResponse->blok6_data ?? [];
                foreach ($incoming as $key => $val) {
                    $current[$key] = $val;
                }
                $surveyResponse->updateWithAutoSave(['blok6_data' => $current]);
                $surveyResponse->refresh();
            }

            // ── Comprehensive sequential validation ─────────────────────────
            // Re-fetch the single consolidated row for this period; all blocks
            // share one row, so this is equivalent to $surveyResponse after refresh.
            $consolidated = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('tahun', $tahun)
                ->where('triwulan', $triwulan)
                ->first();

            $failed = $this->runFinishSurveyValidation($consolidated, $triwulan);

            if ($failed !== null) {
                $isEditMode  = $request->boolean('_edit_mode');
                $routePrefix = $isEditMode ? 'survey.sibstr.edit.' : 'survey.sibstr.';
                $redirectUrl = route($routePrefix . $failed['block'], ['year' => $tahun, 'period' => $period, 'show_validation' => 1]);

                return response()->json([
                    'success'          => false,
                    'redirect_to'      => $redirectUrl,
                    'incomplete_block' => $failed['block'],
                    'message'          => "{$failed['label']} belum lengkap. Silakan lengkapi terlebih dahulu.",
                ], 422);
            }
            // ── End validation ───────────────────────────────────────────────

            $isTahunan  = $triwulan === 0;
            $updateData = ['is_completed' => true, 'blok6_completed' => true];

            if ($isTahunan) {
                // Setting FINISH_SURVEY is the authoritative gate for Triwulan 2026 access.
                // isTahunanFullyCompletedForUser() checks exactly this field.
                $updateData['annual_survey_status'] = 'FINISH_SURVEY';
            }

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success'                 => true,
                'message'                 => $isTahunan
                    ? 'Survei Tahunan 2025 berhasil diselesaikan. Akses Survei Triwulanan 2026 telah dibuka.'
                    : 'Survei berhasil diselesaikan.',
                'completed_at'            => $surveyResponse->last_saved_at->format('Y-m-d H:i:s'),
                'triwulan_access_granted' => $isTahunan,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete survey: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Render the SIBSTR results index for Mitra users at /survei/sibstr.
     * Replicates BPS\SibstrController::index() without requiring is_bps.
     */
    private function mitraSibstrIndex(Request $request)
    {
        $user = Auth::user();

        $search         = $request->input('search');
        $status         = $request->input('status');
        $sortBy         = $request->input('sort_by', 'updated_at');
        $sortOrder      = $request->input('sort_order', 'desc');
        $perPage        = $request->input('per_page', 25);
        $year           = (int) $request->input('year', now()->year);
        $type           = $request->input('type', 'tahunan');
        $triwulanFilter = $request->input('triwulan');

        $isTahunan = ($type === 'tahunan');

        $baseQuery = SurveyResponse::with('user')
            ->where('survey_type', 'sibstr')
            ->where('tahun', $year);

        if ($isTahunan) {
            $baseQuery->where('triwulan', 0);
        } else {
            $baseQuery->where('triwulan', '>', 0);
            if ($triwulanFilter !== null && $triwulanFilter !== '') {
                $baseQuery->where('triwulan', (int) $triwulanFilter);
            }
        }

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('kip', 'like', "%{$search}%")
                  ->orWhere('idsbr', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%")
                                ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== null && $status !== '') {
            if ($isTahunan) {
                if ($status === 'completed') {
                    $baseQuery->where('annual_survey_status', 'FINISH_SURVEY');
                } elseif ($status === 'in_progress') {
                    $baseQuery->where(function ($q) {
                        $q->whereNull('annual_survey_status')
                          ->orWhere('annual_survey_status', '!=', 'FINISH_SURVEY');
                    });
                }
            } else {
                if ($status === 'completed') {
                    $baseQuery->where('is_completed', true);
                } elseif ($status === 'in_progress') {
                    $baseQuery->where('is_completed', false);
                }
            }
        }

        if ($isTahunan) {
            $latestIds = (clone $baseQuery)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id')
                ->pluck('id');
        } else {
            $latestIds = (clone $baseQuery)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id', 'triwulan')
                ->pluck('id');
        }

        $query = SurveyResponse::with('user')->whereIn('id', $latestIds);

        $allowedSortColumns = ['updated_at', 'created_at', 'nama_perusahaan', 'is_completed'];
        if (in_array($sortBy, $allowedSortColumns)) {
            $query->orderBy($sortBy, $sortOrder);
        } else {
            $query->orderBy('updated_at', 'desc');
        }

        $surveyResponses = $query->paginate($perPage)->withQueryString();

        $statsBase = SurveyResponse::where('survey_type', 'sibstr')->where('tahun', $year);
        if ($isTahunan) {
            $statsBase->where('triwulan', 0);
            $allLatestIds = (clone $statsBase)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id')
                ->pluck('id');
            $stats = [
                'total'       => $allLatestIds->count(),
                'completed'   => SurveyResponse::whereIn('id', $allLatestIds)
                                     ->where('annual_survey_status', 'FINISH_SURVEY')
                                     ->count(),
                'in_progress' => SurveyResponse::whereIn('id', $allLatestIds)
                                     ->where(function ($q) {
                                         $q->whereNull('annual_survey_status')
                                           ->orWhere('annual_survey_status', '!=', 'FINISH_SURVEY');
                                     })->count(),
            ];
        } else {
            $statsBase->where('triwulan', '>', 0);
            $allLatestIds = (clone $statsBase)
                ->selectRaw('MAX(id) as id')
                ->groupBy('user_id', 'triwulan')
                ->pluck('id');
            $stats = [
                'total'       => $allLatestIds->count(),
                'completed'   => SurveyResponse::whereIn('id', $allLatestIds)->where('is_completed', true)->count(),
                'in_progress' => SurveyResponse::whereIn('id', $allLatestIds)->where('is_completed', false)->count(),
            ];
        }

        return view('mitra.sibstr.index', compact(
            'surveyResponses', 'stats', 'user',
            'year', 'type', 'isTahunan', 'triwulanFilter'
        ));
    }

    /**
     * Show a single SIBSTR submission for Mitra users (read-only, user-dashboard layout).
     */
    public function mitraSibstrShow($id)
    {
        abort_if(!Auth::user()->is_mitra, 403);

        $requested = SurveyResponse::with('user')
            ->where('survey_type', 'sibstr')
            ->findOrFail($id);

        $surveyResponse = SurveyResponse::unifiedForUser(
            $requested->user_id,
            'sibstr',
            $requested->tahun ?? 2025,
            $requested->triwulan ?? 0
        ) ?? $requested;

        $user = Auth::user();

        $bpsRiData = [
            'penghubung' => 'Tim Statistik Industri',
            'telepon' => '021-3810291 ext. 5310–5313',
            'fax' => '021-3863816, 021-3857046',
            'email' => 'ibs@bps.go.id',
            'alamat' => 'Jl. Dr. Sutomo No. 8, Jakarta 10710',
        ];

        $jenisKawasanOptions = SurveyResponse::getJenisKawasanOptions();
        $kondisiPerusahaan   = $surveyResponse->kondisi_perusahaan;
        $jaringanUnitKegiatan = $surveyResponse->jaringan_unit_kegiatan;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $isMasihAktif          = $kondisiPerusahaan === 'masih_aktif';
        $isIndustri            = $kbliPrefix !== null && $kbliPrefix >= 10 && $kbliPrefix <= 33;
        $isUnitPembantuPenunjang = $jaringanUnitKegiatan === 'unit_pembantu_penunjang';
        $isTahunanRecord       = (((int)($surveyResponse->triwulan ?? 0)) === 0);

        $showBlocks = [
            'blok1'            => true,
            'blok2'            => true,
            'blok3a'           => $isMasihAktif && $isIndustri,
            'blok3bIndustri'   => $isMasihAktif && $isIndustri,
            'blok3bNonIndustri'=> $isMasihAktif && !$isIndustri,
            'blok3c'           => $isMasihAktif && $isIndustri && $isTahunanRecord,
            'blok4'            => $isMasihAktif && $isTahunanRecord,
            'blok5'            => $isMasihAktif,
            'blok6'            => true,
        ];

        $showAfterQ201  = $isMasihAktif;
        $showQ203       = $showAfterQ201 && in_array($jaringanUnitKegiatan, ['pusat_ada_kegiatan_produksi', 'kantor_pusat_administrasi_perwakilan'], true);
        $showQ204       = $showAfterQ201 && $jaringanUnitKegiatan === 'pabrik_unit_produksi';
        $showQ205to211  = $showAfterQ201 && !$isUnitPembantuPenunjang;
        $showQ210a      = $showQ205to211 && (($surveyResponse->penggunaan_internet ?? null) === 'ya');
        $showQ210b      = $showQ210a;

        $blok2Visibility = [
            'showAfterQ201'  => $showAfterQ201,
            'showQ203'       => $showQ203,
            'showQ204'       => $showQ204,
            'showQ205to211'  => $showQ205to211,
            'showQ210a'      => $showQ210a,
            'showQ210b'      => $showQ210b,
        ];

        return view('mitra.sibstr.show', compact(
            'surveyResponse',
            'user',
            'bpsRiData',
            'jenisKawasanOptions',
            'kondisiPerusahaan',
            'jaringanUnitKegiatan',
            'kbliPrefix',
            'isMasihAktif',
            'isIndustri',
            'isUnitPembantuPenunjang',
            'showBlocks',
            'blok2Visibility'
        ));
    }

    /**
     * Download SIBSTR PDF for Mitra users. Delegates to BPS SibstrController.
     */
    public function mitraSibstrDownload($id)
    {
        abort_if(!Auth::user()->is_mitra, 403);
        return app(\App\Http\Controllers\BPS\SibstrController::class)->download($id);
    }
}