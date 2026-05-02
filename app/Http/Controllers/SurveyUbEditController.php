<?php

namespace App\Http\Controllers;

use App\Models\UbSurveyResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * Edit controller for completed UB surveys.
 *
 * Mirrors SurveyUbController GET/save pairs but bypasses the checkCompletion()
 * guard so users can amend already-submitted data. All saves delegate to the
 * main controller with _edit_mode=true, which redirects to the entry/dashboard
 * page instead of advancing to the next block.
 */
class SurveyUbEditController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Private helper ─────────────────────────────────────────────────────────

    private function requiresCompletedSurvey(): ?\Illuminate\Http\RedirectResponse
    {
        $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();
        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.ub.entry')
                ->with('warning', 'Hanya survei yang telah diselesaikan dapat diedit melalui halaman ini.');
        }
        return null;
    }

    // ── Blok 1-A ──────────────────────────────────────────────────────────────

    public function blok1a()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1a');
        $editMode = true;
        return view('survey.ub.blok1a', compact('response', 'editMode'));
    }

    public function saveBlok1a(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $request->merge(['_edit_mode' => true]);
        return app(SurveyUbController::class)->saveBlok1a($request);
    }

    // ── Blok 1-B ──────────────────────────────────────────────────────────────

    public function blok1b()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1b');
        $editMode = true;
        return view('survey.ub.blok1b', compact('response', 'editMode'));
    }

    public function saveBlok1b(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $request->merge(['_edit_mode' => true]);
        return app(SurveyUbController::class)->saveBlok1b($request);
    }

    // ── Blok 1-C ──────────────────────────────────────────────────────────────

    public function blok1c()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1c');
        $editMode = true;
        return view('survey.ub.blok1c', compact('response', 'editMode'));
    }

    public function saveBlok1c(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $request->merge(['_edit_mode' => true]);
        return app(SurveyUbController::class)->saveBlok1c($request);
    }

    // ── Blok 1-D ──────────────────────────────────────────────────────────────

    public function blok1d()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1d');
        $editMode = true;
        return view('survey.ub.blok1d', compact('response', 'editMode'));
    }

    public function saveBlok1d(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $request->merge(['_edit_mode' => true]);
        return app(SurveyUbController::class)->saveBlok1d($request);
    }

    // ── Blok 2 ────────────────────────────────────────────────────────────────

    public function blok2()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok2');
        $editMode = true;
        return view('survey.ub.blok2', compact('response', 'editMode'));
    }

    public function saveBlok2(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $request->merge(['_edit_mode' => true]);
        return app(SurveyUbController::class)->saveBlok2($request);
    }

    // ── Blok 3 ────────────────────────────────────────────────────────────────

    public function blok3()
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok3');
        $editMode = true;
        return view('survey.ub.blok3', compact('response', 'editMode'));
    }

    public function finish(Request $request)
    {
        if ($r = $this->requiresCompletedSurvey()) return $r;
        return app(SurveyUbController::class)->finish($request);
    }
}
