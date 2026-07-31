<?php

namespace App\Support;

use App\Models\SurveyResponse;
use Illuminate\Support\Facades\Route;

/**
 * Presentation-only view model for the SIBSTR blok navigation.
 *
 * Turns one SurveyResponse into the ordered list of blocks that apply to it,
 * with each block's completion / unlocked state and URL. Two views consume it:
 * the blok sidebar rail and the Blok VI completeness checklist — sharing this
 * class keeps them from drifting apart.
 *
 * The ordering rules and the completion predicates are the same ones
 * SurveyController::resolveFirstIncompleteBlock() and
 * SurveyController::runFinishSurveyValidation() enforce server-side, so a link
 * is never offered that the server would bounce back:
 *
 *   Blok II not complete yet ................ blok1 → blok2   (rest not resolvable)
 *   Not "masih aktif", or unit penunjang .... blok1 → blok2 → blok6
 *   Aktif + Industri (KBLI 10–33), tahunan .. 1 → 2 → 3a → 3b-ind → 3c-ind → 4 → 5 → 6
 *   Aktif + Industri, triwulanan ............ 1 → 2 → 3a → 3b-ind → 5 → 6
 *   Aktif + Non-Industri, tahunan ........... 1 → 2 → 3b-non → 4 → 5 → 6
 *   Aktif + Non-Industri, triwulanan ........ 1 → 2 → 3b-non → 5 → 6
 */
class SibstrBlokPath
{
    /** Route-name suffix => display label + subtitle. */
    public const META = [
        'blok1'              => ['label' => 'Blok I',    'sub' => 'Keterangan Umum'],
        'blok2'              => ['label' => 'Blok II',   'sub' => 'Keterangan Perusahaan'],
        'blok3a'             => ['label' => 'Blok IIIA', 'sub' => 'Barang Diproduksi'],
        'blok3b.industri'    => ['label' => 'Blok IIIB', 'sub' => 'Pendapatan & Pengeluaran'],
        'blok3c.industri'    => ['label' => 'Blok IIIC', 'sub' => 'Bahan Baku & Penolong'],
        'blok3b.nonindustri' => ['label' => 'Blok IIIB', 'sub' => 'Pendapatan (Non-Industri)'],
        'blok4'              => ['label' => 'Blok IV',   'sub' => 'Fenomena & Indikator'],
        'blok5'              => ['label' => 'Blok V',    'sub' => 'Kondisi & Prospek Usaha'],
        'blok6'              => ['label' => 'Blok VI',   'sub' => 'Catatan & Selesai'],
    ];

    /**
     * Build the nav rows for one response.
     *
     * In the normal fill flow a row unlocks only once every row before it is
     * complete, matching the server-side guard. The edit flow has no such guard
     * — SibstrEditController lets a responden jump between blocks freely — so
     * every row is unlocked there.
     *
     * @param  string  $currentKey  Route-name suffix of the page being viewed, '' when none.
     * @return array<int, array{key:string,label:string,sub:string,done:bool,active:bool,unlocked:bool,url:?string}>
     */
    public static function rows(
        ?SurveyResponse $response,
        int $triwulan,
        bool $editMode,
        array $routeParams,
        string $currentKey = ''
    ): array {
        $prefix = $editMode ? 'survey.sibstr.edit.' : 'survey.sibstr.';
        $path   = self::path($response, $triwulan, $editMode);

        // Whatever page is on screen belongs in the list even if the stored
        // answers say otherwise (e.g. the user is mid-edit on Blok II).
        if ($currentKey !== '' && !in_array($currentKey, $path, true)) {
            $path[] = $currentKey;
        }

        $rows        = [];
        $allPrevDone = true;

        foreach ($path as $key) {
            $done = self::isComplete($response, $key);

            $rows[] = [
                'key'      => $key,
                'label'    => self::META[$key]['label'] ?? $key,
                'sub'      => self::META[$key]['sub'] ?? '',
                'done'     => $done,
                'active'   => $key === $currentKey,
                'unlocked' => $editMode || $allPrevDone || $key === $currentKey,
                'url'      => Route::has($prefix . $key) ? route($prefix . $key, $routeParams) : null,
            ];

            $allPrevDone = $allPrevDone && $done;
        }

        return $rows;
    }

    /**
     * The ordered block sequence that applies to this response.
     *
     * @return list<string>
     */
    public static function path(?SurveyResponse $response, int $triwulan, bool $editMode = false): array
    {
        $isTahunan = $triwulan === 0;

        // R201 is what the whole downstream path branches on. Until it is
        // answered there is nothing to resolve, so only Blok I & II are listed.
        if (!$response || empty($response->kondisi_perusahaan)) {
            return ['blok1', 'blok2'];
        }

        // Normal fill flow: keep the list at Blok I & II until Blok II is saved
        // complete, so the responden isn't shown a road map they cannot walk.
        // Skipped in the edit flow, where every block is reachable — some
        // legacy completed rows do not satisfy today's isBlok2Complete() rules
        // and would otherwise lose access to the rest of their own submission.
        if (!$editMode && !$response->isBlok2Complete()) {
            return ['blok1', 'blok2'];
        }

        $isAktif     = $response->kondisi_perusahaan === 'masih_aktif';
        $isPenunjang = $response->jaringan_unit_kegiatan === 'unit_pembantu_penunjang';

        if (!$isAktif || $isPenunjang) {
            return ['blok1', 'blok2', 'blok6'];
        }

        if ($response->isKbliIndustri()) {
            return $isTahunan
                ? ['blok1', 'blok2', 'blok3a', 'blok3b.industri', 'blok3c.industri', 'blok4', 'blok5', 'blok6']
                : ['blok1', 'blok2', 'blok3a', 'blok3b.industri', 'blok5', 'blok6'];
        }

        return $isTahunan
            ? ['blok1', 'blok2', 'blok3b.nonindustri', 'blok4', 'blok5', 'blok6']
            : ['blok1', 'blok2', 'blok3b.nonindustri', 'blok5', 'blok6'];
    }

    /** Is one block complete for this response? */
    public static function isComplete(?SurveyResponse $r, string $key): bool
    {
        if (!$r) {
            return false;
        }

        return match ($key) {
            'blok1'              => $r->isBlok1Complete(),
            'blok2'              => $r->isBlok2Complete(),
            'blok3a'             => $r->isBlok3aComplete(),
            'blok3b.industri'    => (bool) $r->blok3b_industri_completed,
            'blok3c.industri'    => (bool) $r->blok3a2_completed,
            'blok3b.nonindustri' => (bool) $r->blok3b_nonindustri_completed,
            'blok4'              => (bool) $r->blok4_completed,
            'blok5'              => (bool) $r->blok5_completed,
            'blok6'              => (bool) $r->is_completed,
            default              => false,
        };
    }

    /**
     * Normalise a route name into a block key. Handles both the survey and the
     * edit route groups, plus the legacy blok3a2 alias that renders blok3c.
     */
    public static function keyFromRouteName(?string $routeName): string
    {
        $key = preg_replace('/^survey\.sibstr\.(edit\.)?/', '', (string) $routeName);

        return $key === 'blok3a2' ? 'blok3c.industri' : (string) $key;
    }
}
