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
     * Get the existing survey response for the authenticated user.
     * Only allow edit when the survey is completed (is_completed = true).
     * If not completed, return a RedirectResponse to the corresponding
     * non-edit survey route with a message.
     */
    private function getExistingSurveyResponse()
    {
        $user = Auth::user();
        $response = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('is_completed', true)
            ->first();

        if ($response) {
            return $response;
        }

        // Determine corresponding non-edit route name for this request.
        $currentName = optional(request()->route())->getName();
        $fallbackRoute = 'survey.sibstr.blok1';
        if ($currentName && str_contains($currentName, '.edit.')) {
            $fallbackRoute = str_replace('.edit.', '.', $currentName);
        }

        return redirect()
            ->route($fallbackRoute)
            ->with('warning', 'Hanya survei yang sudah selesai dapat diedit. Anda dialihkan ke halaman survei.');
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

    // ──────────────────────────────────────────────
    //  PER-TEMPLATE EDIT ROUTES
    //  Each returns the exact same keys the original
    //  window.surveyRoutes object uses for that block.
    // ──────────────────────────────────────────────

    private function editRoutesBlok1(): array
    {
        return [
            'autoSave' => route('survey.sibstr.edit.autosave'),
            'saveAll'  => route('survey.sibstr.edit.save'),
            'status'   => route('survey.sibstr.edit.status'),
            'nextBlok' => route('survey.sibstr.edit.blok2'),
        ];
    }

    private function editRoutesBlok2(): array
    {
        return [
            'autoSave'            => route('survey.sibstr.edit.blok2.autosave'),
            'saveAll'             => route('survey.sibstr.edit.blok2.save'),
            'status'              => route('survey.sibstr.edit.blok2.status'),
            'backToBlok1'         => route('survey.sibstr.edit.blok1'),
            'nextBlok'            => route('survey.sibstr.edit.blok3a'),
            'blok3a'              => route('survey.sibstr.edit.blok3a'),
            'blok6'               => route('survey.sibstr.edit.blok6'),
            'blok3b_industri'     => route('survey.sibstr.edit.blok3b.industri'),
            'blok3b_nonindustri'  => route('survey.sibstr.edit.blok3b.nonindustri'),
        ];
    }

    private function editRoutesBlok3a($kbliPrefix): array
    {
        $fallbackNext = ($kbliPrefix !== null && $kbliPrefix >= 10 && $kbliPrefix <= 33)
            ? route('survey.sibstr.edit.blok3b.industri')
            : route('survey.sibstr.edit.blok3b.nonindustri');

        return [
            'autoSave'            => route('survey.sibstr.edit.blok3a.autosave'),
            'saveAll'             => route('survey.sibstr.edit.blok3a.save'),
            'status'              => route('survey.sibstr.edit.blok3a.status'),
            'backToBlok2'         => route('survey.sibstr.edit.blok2'),
            'nextBlok'            => $fallbackNext,
            'blok6'               => route('survey.sibstr.edit.blok6'),
            'blok3b_industri'     => route('survey.sibstr.edit.blok3b.industri'),
            'blok3b_nonindustri'  => route('survey.sibstr.edit.blok3b.nonindustri'),
        ];
    }

    private function editRoutesBlok3bIndustri(): array
    {
        return [
            'autoSave'    => route('survey.sibstr.edit.blok3b.industri.autosave'),
            'saveAll'     => route('survey.sibstr.edit.blok3b.industri.save'),
            'status'      => route('survey.sibstr.edit.blok3b.industri.status'),
            'backToBlok3a'=> route('survey.sibstr.edit.blok3a'),
            'nextBlok'    => route('survey.sibstr.edit.blok4'),
        ];
    }

    private function editRoutesBlok3bNonIndustri(): array
    {
        return [
            'autoSave'            => route('survey.sibstr.edit.blok3b.nonindustri.autosave'),
            'saveAll'             => route('survey.sibstr.edit.blok3b.nonindustri.save'),
            'status'              => route('survey.sibstr.edit.blok3b.nonindustri.status'),
            'backToBlok2'         => route('survey.sibstr.edit.blok2'),
            'nextBlok'            => route('survey.sibstr.edit.blok4'),
            'blok3b_nonindustri'  => route('survey.sibstr.edit.blok3b.nonindustri'),
        ];
    }

    private function editRoutesBlok4(): array
    {
        return [
            'autoSave'                => route('survey.sibstr.edit.blok4.autosave'),
            'saveAll'                 => route('survey.sibstr.edit.blok4.save'),
            'status'                  => route('survey.sibstr.edit.blok4.status'),
            'backToBlok3bIndustri'    => route('survey.sibstr.edit.blok3b.industri'),
            'backToBlok3bNonIndustri' => route('survey.sibstr.edit.blok3b.nonindustri'),
            'blok5'                   => route('survey.sibstr.edit.blok5'),
            'nextBlok'                => route('survey.sibstr.edit.blok5'),
        ];
    }

    private function editRoutesBlok5(): array
    {
        return [
            'autoSave'   => route('survey.sibstr.edit.blok5.autosave'),
            'saveAll'     => route('survey.sibstr.edit.blok5.save'),
            'status'      => route('survey.sibstr.edit.blok5.status'),
            'backToBlok4' => route('survey.sibstr.edit.blok4'),
            'blok6'       => route('survey.sibstr.edit.blok6'),
            'nextBlok'    => route('survey.sibstr.edit.blok6'),
        ];
    }

    private function editRoutesBlok6(): array
    {
        return [
            'autoSave'     => route('survey.sibstr.edit.blok6.autosave'),
            'saveAll'      => route('survey.sibstr.edit.blok6.save'),
            'status'       => route('survey.sibstr.edit.blok6.status'),
            'backToBlok5'  => route('survey.sibstr.edit.blok5'),
            'backToBlok2'  => route('survey.sibstr.edit.blok2'),
            'finishSurvey' => route('survey.sibstr.edit.blok6.finish'),
        ];
    }

    // ──────────────────────────────────────────────
    //  PAGE METHODS
    // ──────────────────────────────────────────────

    public function blok1()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;
        $jenisKawasanOptions = SurveyResponse::getJenisKawasanOptions();
        $bpsRiData = $this->bpsRiData();
        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok1();

        return view('survey.sibstr.blok1', compact(
            'surveyResponse', 'jenisKawasanOptions', 'bpsRiData', 'isEditMode', 'editRoutes'
        ));
    }

    public function blok2()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;
        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok2();

        return view('survey.sibstr.blok2', compact('surveyResponse', 'isEditMode', 'editRoutes'));
    }

    public function blok3a()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok3a($kbliPrefix);

        return view('survey.sibstr.blok3a', compact('surveyResponse', 'kbliPrefix', 'isEditMode', 'editRoutes'));
    }

    public function blok3bIndustri()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;
        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok3bIndustri();

        return view('survey.sibstr.blok3b-industri', compact('surveyResponse', 'isEditMode', 'editRoutes'));
    }

    public function blok3bNonIndustri()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;
        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok3bNonIndustri();

        return view('survey.sibstr.blok3b-nonindustri', compact('surveyResponse', 'isEditMode', 'editRoutes'));
    }

    public function blok4()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kbliPrefix = null;
        if ($surveyResponse->kbli_utama && preg_match('/^(\d{2})/', $surveyResponse->kbli_utama, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok4();

        return view('survey.sibstr.blok4', compact('surveyResponse', 'kbliPrefix', 'isEditMode', 'editRoutes'));
    }

    public function blok5()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;
        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok5();

        return view('survey.sibstr.blok5', compact('surveyResponse', 'isEditMode', 'editRoutes'));
    }

    public function blok6()
    {
        $result = $this->getExistingSurveyResponse();
        if ($result instanceof \Illuminate\Http\RedirectResponse) {
            return $result;
        }
        $surveyResponse = $result;

        $kondisiPerusahaan = $surveyResponse->kondisi_perusahaan;
        $jaringanUnitKegiatan = $surveyResponse->jaringan_unit_kegiatan;

        $isEditMode = true;
        $editRoutes = $this->editRoutesBlok6();

        return view('survey.sibstr.blok6', compact(
            'surveyResponse', 'kondisiPerusahaan', 'jaringanUnitKegiatan', 'isEditMode', 'editRoutes'
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
        return app(SurveyController::class)->finishSurvey($request);
    }
}

