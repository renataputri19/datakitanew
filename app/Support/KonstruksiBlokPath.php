<?php

namespace App\Support;

use App\Models\KonstruksiSurveyResponse;
use Illuminate\Support\Facades\Route;

/**
 * Presentation-only view model for the Survei Konstruksi blok navigation —
 * the konstruksi counterpart of SibstrBlokPath.
 *
 * Konstruksi is two instruments sharing one table. Both open on the same
 * preamble, and the preamble decides the rest the way SIBSTR's R201 does:
 *
 *   Tahunan (SKTH, triwulan = 0) .... status → blok1 … blok10
 *   Triwulanan (SKTR, triwulan 1–4) . status → blok1 → blok2 → blok3 → penutup
 *
 * and both shorten the same two ways:
 *
 *   not active / refused / non-response → status → closing block
 *   no projects ....................... → status → blok1 → blok2 → closing
 *
 * The "no projects" stop is identical on purpose. Blok I is identity, and Blok
 * II is "Pekerja Tetap dan Kontrak" on both forms — payroll that does not
 * depend on there being a project, and which both questionnaires keep separate
 * from the pekerja tidak tetap/harian that project work brings in. Only what
 * comes after Blok II describes the work itself.
 *
 * NOTE: SKTH-2025 on paper has no preamble at all — it opens on Blok I. The
 * status page is a DataKita addition, so a submission recorded before it
 * existed has no status_perusahaan; the backfill migration
 * 2026_09_04_000001 gives those rows the answers their data already implies.
 *
 * The completion predicates below are the same ones
 * SurveyKonstruksiController::runFinishValidation() enforces server-side, so
 * the rail never offers a link the finish flow would bounce back.
 */
class KonstruksiBlokPath
{
    /**
     * Annual (SKTH) labels, keyed by route-name suffix.
     *
     * 'badge' is what the nav rail puts in the bubble. It is the block's OWN
     * number, not its position in the sequence — on the quarterly path the
     * preamble occupies the first slot, so a positional badge labelled
     * "Blok I" as step 2 and read as a contradiction.
     */
    private const META_TAHUNAN = [
        'status' => ['label' => 'Status',    'sub' => 'Updating Status Perusahaan', 'badge' => '•'],
        'blok1'  => ['label' => 'Blok I',    'sub' => 'Keterangan Umum',            'badge' => '1'],
        'blok2'  => ['label' => 'Blok II',   'sub' => 'Tenaga Kerja & Balas Jasa',  'badge' => '2'],
        'blok3'  => ['label' => 'Blok III',  'sub' => 'Kepemilikan Modal',          'badge' => '3'],
        'blok4'  => ['label' => 'Blok IV',   'sub' => 'Pengeluaran Operasional',    'badge' => '4'],
        'blok5'  => ['label' => 'Blok V',    'sub' => 'Barang Modal',               'badge' => '5'],
        'blok6'  => ['label' => 'Blok VI',   'sub' => 'Pekerjaan Konstruksi',       'badge' => '6'],
        'blok7'  => ['label' => 'Blok VII',  'sub' => 'Struktur Bahan/Material',    'badge' => '7'],
        'blok8'  => ['label' => 'Blok VIII', 'sub' => 'Pendapatan Lain',            'badge' => '8'],
        'blok9'  => ['label' => 'Blok IX',   'sub' => 'Ringkasan',                  'badge' => '9'],
        'blok10' => ['label' => 'Blok X',    'sub' => 'Catatan & Selesai',          'badge' => '10'],
    ];

    /**
     * Quarterly (SKTR) labels. blok1–blok3 are reused route names but mean
     * different blocks here, which is why META is resolved per period.
     *
     * The two bookend steps are not numbered blocks in the questionnaire, so
     * they carry a dot rather than a number that would clash with "Blok I".
     */
    private const META_TRIWULANAN = [
        'status'  => ['label' => 'Status',   'sub' => 'Updating Status Perusahaan', 'badge' => '•'],
        'blok1'   => ['label' => 'Blok I',   'sub' => 'Keterangan Umum',            'badge' => '1'],
        'blok2'   => ['label' => 'Blok II',  'sub' => 'Tenaga Kerja & Balas Jasa',  'badge' => '2'],
        'blok3'   => ['label' => 'Blok III', 'sub' => 'Kontrak & Realisasi Fisik',  'badge' => '3'],
        'penutup' => ['label' => 'Penutup',  'sub' => 'Catatan & Selesai',          'badge' => '•'],
    ];

    /** Kept for callers that only have a period. */
    public const META = self::META_TAHUNAN;

    private const TAHUNAN = [
        'status',
        'blok1', 'blok2', 'blok3', 'blok4', 'blok5',
        'blok6', 'blok7', 'blok8', 'blok9', 'blok10',
    ];

    /** SKTH's closing block; SKTR's is 'penutup'. */
    private const TAHUNAN_CLOSING = 'blok10';

    /** Every route-name suffix either instrument can use — used to build routes. */
    public static function keys(): array
    {
        return array_values(array_unique(array_merge(
            self::TAHUNAN,
            array_keys(self::META_TRIWULANAN)
        )));
    }

    /** Label set that applies to a period. */
    public static function meta(int $triwulan): array
    {
        return $triwulan === KonstruksiSurveyResponse::TAHUNAN
            ? self::META_TAHUNAN
            : self::META_TRIWULANAN;
    }

    /** Label + subtitle for one block within a period. */
    public static function metaFor(int $triwulan, string $key): array
    {
        return self::meta($triwulan)[$key] ?? ['label' => $key, 'sub' => ''];
    }

    /**
     * The ordered block sequence for a period.
     *
     * Both paths shorten once the preamble is answered; until it is, the full
     * sequence is shown so the responden sees the road ahead.
     *
     * @return list<string>
     */
    public static function path(
        int $triwulan = KonstruksiSurveyResponse::TAHUNAN,
        ?KonstruksiSurveyResponse $response = null
    ): array {
        $tahunan = $triwulan === KonstruksiSurveyResponse::TAHUNAN;
        $full    = $tahunan ? self::TAHUNAN : ['status', 'blok1', 'blok2', 'blok3', 'penutup'];
        $closing = $tahunan ? self::TAHUNAN_CLOSING : 'penutup';

        // Nothing answered yet — show the whole path.
        if (!$response || trim((string) $response->status_perusahaan) === '') {
            return $full;
        }

        // Not active, refused, or non-response: the form ends at the preamble.
        if (!$response->continuesPastStatus()) {
            return ['status', $closing];
        }

        // The project block drops out only once the responden has actually
        // recorded "Tidak Ada Proyek Konstruksi". While the question is merely
        // blank the block stays in place — dropping it then pushed it to the end
        // of the rail and made a direct visit bounce, which reads as a broken
        // link rather than a rule.
        if ($response->proyekBlokApplies()) {
            return $full;
        }

        // No projects — and both instruments stop at the same place, because
        // both keep asking the same two things.
        //
        // Blok I is identity: BPS still needs to know which company reported
        // the nil period. Blok II is "Pekerja Tetap dan Kontrak", which the
        // questionnaire defines as staff paid "tidak tergantung pada kehadiran"
        // and explicitly separates from the pekerja tidak tetap/harian tied to
        // projects (SKTR keeps those in Blok III kolom 13–16). A construction
        // company between jobs still carries that payroll, so it is exactly the
        // figure worth having for a period with no projects.
        //
        // Everything after Blok II describes the work itself, so it goes.
        return ['status', 'blok1', 'blok2', $closing];
    }

    /**
     * Build the nav rows for one response.
     *
     * Every block is reachable at any time, the way Survei UB works: a
     * responden can jump ahead to see what is coming, or go back to correct an
     * earlier answer, without finishing the block they are on. Nothing is lost
     * by moving — each field autosaves as it is typed.
     *
     * Ordering is still enforced where it actually matters: finish() re-checks
     * every block and refuses to submit an incomplete one, and the closing
     * block's checklist links straight to whatever is still missing.
     *
     * @param  string  $currentKey  Route-name suffix of the page on screen, '' when none.
     * @return array<int, array{key:string,label:string,sub:string,badge:string,done:bool,active:bool,url:?string}>
     */
    public static function rows(
        ?KonstruksiSurveyResponse $response,
        array $routeParams,
        string $currentKey = '',
        ?int $triwulan = null
    ): array {
        // Same reason firstIncomplete() takes one: with no row yet there is
        // nothing to read the period off, and defaulting to the annual path
        // would render a ten-block rail on a quarterly page.
        $triwulan = (int) ($triwulan ?? $response?->triwulan ?? KonstruksiSurveyResponse::TAHUNAN);
        $meta     = self::meta($triwulan);
        $path     = self::path($triwulan, $response);

        // Whatever page is on screen belongs in the list even if the stored
        // answers say otherwise (e.g. mid-edit on the preamble).
        if ($currentKey !== '' && !in_array($currentKey, $path, true)) {
            $path[] = $currentKey;
        }

        $rows = [];

        foreach ($path as $key) {
            $rows[] = [
                'key'    => $key,
                'label'  => $meta[$key]['label'] ?? $key,
                'sub'    => $meta[$key]['sub'] ?? '',
                'badge'  => $meta[$key]['badge'] ?? (string) (count($rows) + 1),
                'done'   => self::isComplete($response, $key),
                'active' => $key === $currentKey,
                'url'    => Route::has("survey.konstruksi.{$key}")
                    ? route("survey.konstruksi.{$key}", $routeParams)
                    : null,
            ];
        }

        return $rows;
    }

    /**
     * Is one block complete for this response?
     *
     * Deliberately re-derived from the answers rather than read off the
     * blok*_completed flags: an edit to Blok VI changes whether Blok IX still
     * holds, and a stale flag would let an inconsistent submission through.
     *
     * blok1–blok3 mean different blocks in the two instruments, so the period
     * is checked before the key.
     */
    public static function isComplete(?KonstruksiSurveyResponse $r, string $key): bool
    {
        if (!$r) {
            return false;
        }

        if ($r->isTriwulanan()) {
            return match ($key) {
                'status'  => $r->isStatusComplete(),
                'blok1'   => $r->isBlok1TwComplete(),
                'blok2'   => $r->isBlok2TwComplete(),
                'blok3'   => $r->isBlok3TwComplete(),
                'penutup' => $r->isPenutupComplete(),
                default   => false,
            };
        }

        return match ($key) {
            'status' => $r->isStatusComplete(),
            'blok1'  => $r->isBlok1Complete(),
            'blok2'  => $r->isBlok2Complete(),
            'blok3'  => $r->isBlok3Complete(),
            'blok4'  => $r->isBlok4Complete(),
            'blok5'  => $r->isBlok5Complete(),
            'blok6'  => $r->isBlok6Complete(),
            'blok7'  => $r->isBlok7Complete(),
            'blok8'  => $r->isBlok8Complete(),
            'blok9'  => $r->isBlok9Complete(),
            'blok10' => $r->isBlok10Complete(),
            default  => false,
        };
    }

    /**
     * The block key the responden should land on next, or '' when all done.
     *
     * $triwulan must be supplied when there is no response row yet: without it
     * a null $r reads as the annual path, and a quarterly period's "Mulai isi"
     * link would skip the preamble and open Blok I.
     */
    public static function firstIncomplete(?KonstruksiSurveyResponse $r, ?int $triwulan = null): string
    {
        $triwulan ??= (int) ($r?->triwulan ?? KonstruksiSurveyResponse::TAHUNAN);

        foreach (self::path($triwulan, $r) as $key) {
            if (!self::isComplete($r, $key)) {
                return $key;
            }
        }

        return '';
    }

    /** Normalise a route name into a block key. */
    public static function keyFromRouteName(?string $routeName): string
    {
        return (string) preg_replace('/^survey\.konstruksi\./', '', (string) $routeName);
    }

    /** Human label for a period, e.g. "Tahunan 2025" / "Triwulan II 2026". */
    public static function periodLabel(int $tahun, int $triwulan): string
    {
        if ($triwulan === KonstruksiSurveyResponse::TAHUNAN) {
            return "Tahunan {$tahun}";
        }

        return 'Triwulan ' . KonstruksiSurveyResponse::romanQuarter($triwulan) . " {$tahun}";
    }
}
