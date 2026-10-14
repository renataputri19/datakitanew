<?php

namespace App\Http\Controllers;

use App\Models\KonstruksiSurveyResponse;
use App\Support\KonstruksiBlokPath;
use App\Support\SibstrFormat;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Survei Perusahaan Konstruksi — annual questionnaire (SKTH-2025).
 *
 * URLs carry the period the SIBSTR way (/survei/konstruksi/{year}/{period})
 * so the quarterly form can be added later without moving anything. Only
 * (2025, tahunan) is served today; every other period bounces to the landing
 * page rather than 404ing, so a guessed URL reads as "belum tersedia".
 *
 * Flow mirrors Survei UB and Listrik: per-field autosave, per-blok completion
 * flags, and finish() re-validating every blok and sending the responden back
 * to the first one that fails.
 */
class SurveyKonstruksiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Periods this controller serves: tahun => list of period strings.
     *
     * SKTH-2025 is the annual questionnaire in the field; SKTR-2026 is the
     * quarterly one. A period outside this table bounces to the landing page
     * rather than 404ing, so a guessed URL reads as "belum tersedia".
     */
    private const SUPPORTED = [
        KonstruksiSurveyResponse::TAHUN_TAHUNAN     => ['tahunan'],
        KonstruksiSurveyResponse::TAHUN_TRIWULANAN  => ['1', '2', '3', '4'],
    ];

    /** Blok I free-text length caps, shared by autosave and save. */
    private const FIELD_MAX_LENGTHS = [
        'kip'                  => 30,
        'provinsi'             => 100,
        'kabupaten_kota'       => 100,
        'kecamatan'            => 100,
        'kelurahan_desa'       => 100,
        'nus'                  => 20,
        'nama_perusahaan'      => 255,
        'punya_nib'            => 10,
        'nib'                  => 30,
        'nama_pengusaha'       => 255,
        'hp_pengusaha'         => 30,
        'rt'                   => 5,
        'rw'                   => 5,
        'kode_pos'             => 10,
        'nomor_telepon'        => 30,
        'website'              => 255,
        'email_perusahaan'     => 255,
        'rt_operasional'       => 5,
        'rw_operasional'       => 5,
        'kode_pos_operasional' => 10,
        'badan_hukum'          => 5,
        'badan_hukum_lainnya'  => 255,
        'status_penanaman_modal' => 5,
        'kualifikasi'          => 5,
        'modal_asing_negara'   => 100,
        'barang_modal_lainnya' => 255,
        'pct_bahan'            => 20,
        'pct_upah'             => 20,
        'pendapatan_lain_lainnya' => 255,
        'metode_pendataan'     => 5,
        'pj_nama'              => 255,
        'pj_hp'                => 30,
        'pj_jabatan'           => 255,
        'pj_email'             => 255,
        // Quarterly preamble
        'status_perusahaan'     => 5,
        'kesediaan_responden'   => 5,
        'keberadaan_proyek'     => 5,
        'pindah_kabupaten_kota' => 100,
        'pindah_kecamatan'      => 100,
        'pindah_kelurahan_desa' => 100,
        'pindah_rt'             => 5,
        'pindah_rw'             => 5,
        'pindah_kode_pos'       => 10,
    ];

    /** Fields each blok's autosave endpoint will accept. */
    private const AUTOSAVE_FIELDS = [
        'blok1' => [
            'kip', 'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa', 'nus',
            'nama_perusahaan', 'punya_nib', 'nib', 'nama_pengusaha', 'hp_pengusaha',
            'alamat_perusahaan', 'rt', 'rw', 'kode_pos', 'nomor_telepon', 'website',
            'email_perusahaan', 'alamat_operasional', 'rt_operasional', 'rw_operasional',
            'kode_pos_operasional', 'badan_hukum', 'badan_hukum_lainnya',
            'status_penanaman_modal', 'kualifikasi',
        ],
        'blok2'  => ['blok2_pekerja', 'blok2_balas_jasa'],
        'blok3'  => ['blok3_modal', 'modal_asing_negara'],
        'blok4'  => ['blok4_pengeluaran'],
        'blok5'  => ['blok5_barang_modal', 'barang_modal_lainnya'],
        'blok6'  => ['data_proyek', 'pct_bahan', 'pct_upah', 'blok6_sumber_modal'],
        'blok7'  => ['blok7_bahan'],
        'blok8'  => ['blok8_pendapatan_lain', 'pendapatan_lain_lainnya'],
        'blok9'  => [],
        'blok10' => ['metode_pendataan', 'catatan', 'pj_nama', 'pj_hp', 'pj_jabatan', 'pj_email'],

        // ── Quarterly (SKTR) ──
        // blok1 is shared with the annual form above; blok2 and blok3 are not,
        // so the quarterly variants get their own entries keyed by section name.
        'status'  => [
            'status_perusahaan', 'kesediaan_responden', 'keberadaan_proyek',
            'pindah_kabupaten_kota', 'pindah_kecamatan', 'pindah_kelurahan_desa',
            'pindah_alamat', 'pindah_rt', 'pindah_rw', 'pindah_kode_pos',
        ],
        'penutup' => ['metode_pendataan', 'catatan', 'pj_nama', 'pj_hp', 'pj_jabatan', 'pj_email'],
    ];

    /** Quarterly overrides — same section names, different fields. */
    private const AUTOSAVE_FIELDS_TW = [
        'blok2' => ['tw_pekerja', 'tw_balas_jasa'],
        'blok3' => ['data_proyek_tw'],
    ];

    // ── Period plumbing ───────────────────────────────────────────────────

    /**
     * Resolve {year}/{period} from the route into ['tahun', 'triwulan'].
     * Returns null when the period exists in the URL grammar but has no
     * questionnaire behind it yet.
     */
    private function resolvePeriod(Request $request): ?array
    {
        $tahun  = (int) $request->route('year', KonstruksiSurveyResponse::TAHUN_TAHUNAN);
        $period = (string) $request->route('period', 'tahunan');

        if (!in_array($period, self::SUPPORTED[$tahun] ?? [], true)) {
            return null;
        }

        return [
            'tahun'    => $tahun,
            'triwulan' => $period === 'tahunan' ? KonstruksiSurveyResponse::TAHUNAN : (int) $period,
            'period'   => $period,
        ];
    }

    /** Route params for every link inside the flow. */
    private function routeParams(array $p): array
    {
        return ['year' => $p['tahun'], 'period' => $p['period']];
    }

    private function response(array $p): ?KonstruksiSurveyResponse
    {
        return KonstruksiSurveyResponse::where('user_id', Auth::id())
            ->where('tahun', $p['tahun'])
            ->where('triwulan', $p['triwulan'])
            ->first();
    }

    private function unsupported(): RedirectResponse
    {
        return redirect()->route('survey.konstruksi.entry')
            ->with('info', 'Periode survei tersebut belum tersedia. Saat ini yang aktif adalah SKTH Tahunan '
                . KonstruksiSurveyResponse::TAHUN_TAHUNAN . '.');
    }

    /**
     * Guard shared by every blok page. Returns a redirect to bounce with, or
     * null plus the resolved period + response by reference.
     */
    private function guard(Request $request, string $section, ?array &$p, ?KonstruksiSurveyResponse &$resp): ?RedirectResponse
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return $this->unsupported();
        }

        $resp = KonstruksiSurveyResponse::getOrCreateForUser(Auth::id(), $section, $p['tahun'], $p['triwulan']);

        // A finished submission is read-only until the responden reopens it
        // from the landing page, matching the UB/Listrik flow.
        if ($resp->is_completed) {
            return redirect()->route('survey.konstruksi.entry')
                ->with('info', 'Survei Konstruksi periode ini sudah diselesaikan.');
        }

        return null;
    }

    // ── Sanitizers ────────────────────────────────────────────────────────

    /**
     * Keep an id-ID figure exactly as typed but bounded, so SibstrFormat::num()
     * still reads it later. Anything that is not a number-ish string is dropped.
     */
    private function cleanNumeric(mixed $v): ?string
    {
        if ($v === null || is_array($v) || is_bool($v)) {
            return null;
        }
        $s = trim((string) $v);
        if ($s === '') {
            return null;
        }

        return SibstrFormat::num($s) === null ? null : mb_substr($s, 0, 30);
    }

    private function cleanText(mixed $v, int $max = 255): string
    {
        return mb_substr(trim((string) (is_scalar($v) ? $v : '')), 0, $max);
    }

    /** {jenjang: {l, p}} — only the known jenjang survive. */
    private function sanitizePekerja(mixed $raw): array
    {
        $raw   = $this->asArray($raw);
        $clean = [];
        foreach (array_keys(KonstruksiSurveyResponse::PENDIDIKAN) as $jenjang) {
            foreach (['l', 'p'] as $jk) {
                $clean[$jenjang][$jk] = $this->cleanNumeric($raw[$jenjang][$jk] ?? null);
            }
        }

        return $clean;
    }

    /** A flat {key: figure} map restricted to $keys. */
    private function sanitizeFigures(mixed $raw, array $keys): array
    {
        $raw   = $this->asArray($raw);
        $clean = [];
        foreach ($keys as $k) {
            $clean[$k] = $this->cleanNumeric($raw[$k] ?? null);
        }

        return $clean;
    }

    /** {jenis: {tambah, kurang}} for Blok V. */
    private function sanitizeBarangModal(mixed $raw): array
    {
        $raw   = $this->asArray($raw);
        $clean = [];
        foreach (array_keys(KonstruksiSurveyResponse::BARANG_MODAL) as $jenis) {
            foreach (['tambah', 'kurang'] as $arah) {
                $clean[$jenis][$arah] = $this->cleanNumeric($raw[$jenis][$arah] ?? null);
            }
        }

        return $clean;
    }

    /** Blok VI grid — a list of at most MAX_PROYEK rows with fixed columns. */
    private function sanitizeProyek(mixed $raw): array
    {
        $raw = $this->asArray($raw);
        if (!array_is_list($raw)) {
            $raw = array_values($raw);
        }

        $textCols = [
            'nama_pekerjaan'  => 500,
            'kbli'            => 10,
            'provinsi_lokasi' => 100,
            'kabkota_lokasi'  => 100,
            'kode_kabkota'    => 10,
            'satuan'          => 20,
        ];
        $numCols = ['volume', 'nilai_kontrak', 'nilai_subkontrak', 'realisasi_awal',
            'realisasi_akhir', 'bahan_perusahaan', 'bahan_pemilik', 'upah_harian', 'upah_rata_rata'];

        $clean = [];
        foreach (array_slice($raw, 0, KonstruksiSurveyResponse::MAX_PROYEK) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out = [];
            foreach ($textCols as $col => $max) {
                $out[$col] = $this->cleanText($row[$col] ?? null, $max);
            }
            $out['sumber_dana'] = in_array(
                (string) ($row['sumber_dana'] ?? ''),
                KonstruksiSurveyResponse::codes(KonstruksiSurveyResponse::SUMBER_DANA),
                true
            ) ? (string) $row['sumber_dana'] : '';
            foreach ($numCols as $col) {
                $out[$col] = $this->cleanNumeric($row[$col] ?? null) ?? '';
            }
            foreach (['mulai', 'selesai'] as $col) {
                $v = $this->cleanText($row[$col] ?? null, 7);
                $out[$col] = preg_match('#^(0[1-9]|1[0-2])/[0-9]{4}$#', $v) ? $v : '';
            }
            $clean[] = $out;
        }

        return $clean;
    }

    /** Blok VII — a list of at most MAX_BAHAN {nama, nilai} rows. */
    private function sanitizeBahan(mixed $raw): array
    {
        $raw = $this->asArray($raw);
        if (!array_is_list($raw)) {
            $raw = array_values($raw);
        }

        $clean = [];
        foreach (array_slice($raw, 0, KonstruksiSurveyResponse::MAX_BAHAN) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $clean[] = [
                'nama'  => $this->cleanText($row['nama'] ?? null, 255),
                'nilai' => $this->cleanNumeric($row['nilai'] ?? null) ?? '',
            ];
        }

        return $clean;
    }

    /** Blok III (SKTR) grid — at most MAX_PROYEK_TW rows with fixed columns. */
    private function sanitizeProyekTw(mixed $raw): array
    {
        $raw = $this->asArray($raw);
        if (!array_is_list($raw)) {
            $raw = array_values($raw);
        }

        $textCols = [
            'nama_pekerjaan'  => 500,
            'kbli'            => 10,
            'provinsi_lokasi' => 100,
            'kabkota_lokasi'  => 100,
            'kode_kabkota'    => 10,
        ];
        $numCols = array_merge(
            ['nilai_kontrak', 'pct_subkontrak'],
            array_keys(KonstruksiSurveyResponse::REALISASI_SLOTS),
            array_keys(KonstruksiSurveyResponse::UPAH_SLOTS),
            array_keys(KonstruksiSurveyResponse::UPAH_RATA_SLOTS)
        );

        $clean = [];
        foreach (array_slice($raw, 0, KonstruksiSurveyResponse::MAX_PROYEK_TW) as $row) {
            if (!is_array($row)) {
                continue;
            }
            $out = [];
            foreach ($textCols as $col => $max) {
                $out[$col] = $this->cleanText($row[$col] ?? null, $max);
            }
            $out['sumber_dana'] = in_array(
                (string) ($row['sumber_dana'] ?? ''),
                KonstruksiSurveyResponse::codes(KonstruksiSurveyResponse::SUMBER_DANA),
                true
            ) ? (string) $row['sumber_dana'] : '';
            foreach ($numCols as $col) {
                $out[$col] = $this->cleanNumeric($row[$col] ?? null) ?? '';
            }
            foreach (['mulai', 'selesai'] as $col) {
                $v = $this->cleanText($row[$col] ?? null, 7);
                $out[$col] = preg_match('#^(0[1-9]|1[0-2])/[0-9]{4}$#', $v) ? $v : '';
            }
            $clean[] = $out;
        }

        return $clean;
    }

    private function asArray(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }

        return is_array($raw) ? $raw : [];
    }

    /** Route one incoming JSON field through the right sanitizer. */
    private function sanitizeField(string $field, mixed $value): mixed
    {
        return match ($field) {
            'blok2_pekerja'         => $this->sanitizePekerja($value),
            'blok2_balas_jasa'      => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::BALAS_JASA)),
            'blok3_modal'           => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::SUMBER_MODAL)),
            'blok4_pengeluaran'     => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::PENGELUARAN)),
            'blok5_barang_modal'    => $this->sanitizeBarangModal($value),
            'data_proyek'           => $this->sanitizeProyek($value),
            'blok6_sumber_modal'    => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::MODAL_PROYEK)),
            'blok7_bahan'           => $this->sanitizeBahan($value),
            'blok8_pendapatan_lain' => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::PENDAPATAN_LAIN)),
            // Quarterly
            'tw_pekerja',
            'tw_balas_jasa'         => $this->sanitizeFigures($value, array_keys(KonstruksiSurveyResponse::TW_SLOTS)),
            'data_proyek_tw'        => $this->sanitizeProyekTw($value),
            default                 => $value,
        };
    }

    /** Fields a section's autosave accepts, for the period being filled. */
    private function autosaveFields(string $section, int $triwulan): array
    {
        if ($triwulan !== KonstruksiSurveyResponse::TAHUNAN
            && isset(self::AUTOSAVE_FIELDS_TW[$section])) {
            return self::AUTOSAVE_FIELDS_TW[$section];
        }

        return self::AUTOSAVE_FIELDS[$section] ?? [];
    }

    // ── Autosave / status ─────────────────────────────────────────────────

    /** Re-evaluate a blok's completion flag after an autosave. */
    private function autoMarkBlokCompleted(KonstruksiSurveyResponse $resp, string $section): void
    {
        $resp->refresh();

        $complete = KonstruksiBlokPath::isComplete($resp, $section);
        $flag     = "{$section}_completed";

        if ($resp->{$flag} !== $complete) {
            $resp->{$flag} = $complete;
            $resp->saveQuietly();
        }
    }

    private function doAutoSave(Request $request, string $section): JsonResponse
    {
        try {
            $p = $this->resolvePeriod($request);
            if ($p === null) {
                return response()->json(['success' => false, 'message' => 'Periode survei tidak tersedia.'], 422);
            }

            $v = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);
            if ($v->fails()) {
                return response()->json(['success' => false, 'errors' => $v->errors()], 422);
            }

            $field = (string) $request->input('field');
            $value = $request->input('value');

            if (!in_array($field, $this->autosaveFields($section, $p['triwulan']), true)) {
                return response()->json(['success' => false, 'message' => "Kolom '{$field}' tidak dikenal."], 422);
            }

            $max = self::FIELD_MAX_LENGTHS[$field] ?? null;
            if ($max !== null && is_string($value) && mb_strlen($value) > $max) {
                return response()->json([
                    'success' => false,
                    'message' => "Kolom '{$field}' maksimal {$max} karakter.",
                    'errors'  => [$field => ["Maksimal {$max} karakter."]],
                ], 422);
            }

            $resp = KonstruksiSurveyResponse::getOrCreateForUser(Auth::id(), $section, $p['tahun'], $p['triwulan']);

            // The grid autosaves every keystroke, so this is the path a stale
            // Blok III tab would actually take once the preamble was changed to
            // "Tidak Ada Proyek Konstruksi". Refuse rather than write.
            if (!$this->sectionApplies($resp, $p, $section)) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Blok tersebut tidak berlaku untuk jawaban yang Anda isi pada Updating Status Perusahaan.',
                    'redirect' => route(
                        'survey.konstruksi.' . (KonstruksiBlokPath::firstIncomplete($resp) ?: 'penutup'),
                        $this->routeParams($p)
                    ),
                ], 409);
            }

            $resp->updateWithAutoSave([$field => $this->sanitizeField($field, $value)]);
            $this->autoMarkBlokCompleted($resp, $section);

            return response()->json([
                'success'        => true,
                'message'        => 'Data auto-saved',
                'last_saved_at'  => $resp->last_saved_at?->format('H:i:s'),
                'blok_completed' => (bool) $resp->fresh()->{"{$section}_completed"},
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function doGetStatus(Request $request, string $section): JsonResponse
    {
        try {
            $p = $this->resolvePeriod($request);
            if ($p === null) {
                return response()->json(['success' => false, 'message' => 'Periode survei tidak tersedia.'], 422);
            }
            $resp = $this->response($p);

            return response()->json([
                'success'       => true,
                'last_saved_at' => $resp?->last_saved_at?->format('H:i:s'),
                'is_completed'  => (bool) ($resp?->{"{$section}_completed"} ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Landing & navigation ──────────────────────────────────────────────

    /**
     * Landing page: one card per period this responden can fill.
     *
     * Konstruksi is two instruments — the annual SKTH and the quarterly SKTR —
     * so the landing lists every supported period with its own progress rather
     * than assuming a single "the survey". The quarter matching today's date is
     * marked so the responden lands on the one actually being collected.
     */
    public function entry()
    {
        $rows = KonstruksiSurveyResponse::where('user_id', Auth::id())
            ->get()
            ->keyBy(fn ($r) => $r->tahun . '-' . ($r->triwulan === 0 ? 'tahunan' : $r->triwulan));

        $currentQuarter = (int) ceil((int) now()->format('n') / 3);
        $currentYear    = (int) now()->format('Y');

        $periods = [];
        foreach (self::SUPPORTED as $tahun => $periodList) {
            foreach ($periodList as $period) {
                $triwulan = $period === 'tahunan' ? KonstruksiSurveyResponse::TAHUNAN : (int) $period;
                $key      = "{$tahun}-{$period}";
                $resp     = $rows->get($key);

                $periods[] = [
                    'tahun'       => $tahun,
                    'period'      => $period,
                    'triwulan'    => $triwulan,
                    'label'       => KonstruksiBlokPath::periodLabel($tahun, $triwulan),
                    'instrumen'   => $triwulan === KonstruksiSurveyResponse::TAHUNAN ? 'SKTH' : 'SKTR',
                    'response'    => $resp,
                    'percent'     => $resp ? $resp->completionPercent() : 0,
                    'completed'   => (bool) ($resp?->is_completed),
                    // The period is passed explicitly: with no row yet there is
                    // nothing to read the period off. Both instruments now open
                    // on the preamble, so an untouched period lands on 'status'.
                    'nextBlok'    => KonstruksiBlokPath::firstIncomplete($resp, $triwulan) ?: 'status',
                    'isCurrent'   => $triwulan !== KonstruksiSurveyResponse::TAHUNAN
                        && $tahun === $currentYear
                        && $triwulan === $currentQuarter,
                    'routeParams' => ['year' => $tahun, 'period' => $period],
                ];
            }
        }

        return view('survey.konstruksi.landing', ['periods' => $periods]);
    }

    /** Re-render the blok navigation rail (called after an autosave). */
    public function nav(Request $request)
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return response('', 204);
        }

        $response   = $this->response($p);
        $currentKey = (string) $request->query('current', '');

        return view('survey.konstruksi.partials.nav-rows', [
            'navBlocks' => KonstruksiBlokPath::rows(
                $response,
                $this->routeParams($p),
                $currentKey,
                $p['triwulan']
            ),
        ]);
    }

    public function startEdit(Request $request)
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return $this->unsupported();
        }

        $resp = $this->response($p);
        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.konstruksi.entry')
                ->with('error', 'Survei tidak ditemukan atau belum diselesaikan.');
        }

        $resp->is_completed = false;
        $resp->save();

        return redirect()->route('survey.konstruksi.blok1', $this->routeParams($p))
            ->with('info', 'Mode edit aktif. Perbaiki data yang diperlukan, lalu selesaikan kembali di Blok X.');
    }

    // ── Generic blok page + save ──────────────────────────────────────────

    /**
     * Render one blok. Every page shares the same shape, so the only thing
     * that differs is the view name and the extra data it needs.
     */
    /**
     * View name for a section. The quarterly pages live under a `tw.` prefix
     * because blok1–blok3 are reused route names that mean different blocks in
     * the two instruments.
     */
    private function viewFor(string $section, int $triwulan): string
    {
        return $triwulan === KonstruksiSurveyResponse::TAHUNAN
            ? "survey.konstruksi.{$section}"
            : "survey.konstruksi.tw.{$section}";
    }

    /** Data every blok page needs, whichever instrument it belongs to. */
    private function pageData(array $p, ?KonstruksiSurveyResponse $resp, string $section): array
    {
        return [
            'response'      => $resp,
            'tahun'         => $p['tahun'],
            'triwulan'      => $p['triwulan'],
            'period'        => $p['period'],
            'routeParams'   => $this->routeParams($p),
            'navBlocks'     => KonstruksiBlokPath::rows($resp, $this->routeParams($p), $section),
            // Rolling quarter captions for the SKTR grids; unused by SKTH.
            'quarterLabels' => $resp && $resp->isTriwulanan() ? $resp->quarterLabels() : [],
        ];
    }

    /**
     * Does this section belong to the questionnaire this response is actually
     * filling? Both instruments branch on the preamble, so neither is exempt.
     */
    private function sectionApplies(?KonstruksiSurveyResponse $resp, array $p, string $section): bool
    {
        return !$resp
            || in_array($section, KonstruksiBlokPath::path($p['triwulan'], $resp), true);
    }

    /**
     * Bounce a request aimed at a block the responden's own answers ruled out —
     * Blok III after "Tidak Ada Proyek Konstruksi", or anything past the
     * preamble for a company that is closed, moved, or not in construction.
     *
     * Returns null when the section applies, so callers can `if ($r = …)`.
     */
    private function refuseSkippedSection(Request $request, array $p, ?KonstruksiSurveyResponse $resp, string $section)
    {
        if ($this->sectionApplies($resp, $p, $section)) {
            return null;
        }

        $target = KonstruksiBlokPath::firstIncomplete($resp) ?: 'penutup';
        $url    = route("survey.konstruksi.{$target}", $this->routeParams($p));
        $msg    = 'Blok tersebut tidak berlaku untuk jawaban yang Anda isi pada Updating Status Perusahaan.';

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => $msg, 'redirect' => $url], 409);
        }

        return redirect($url)->with('info', $msg);
    }

    private function showBlok(Request $request, string $section)
    {
        if ($r = $this->guard($request, $section, $p, $resp)) {
            return $r;
        }

        // A submission that stops at the preamble must not offer the rest of
        // the form — the rail hides it, and this stops a typed URL too.
        if ($r = $this->refuseSkippedSection($request, $p, $resp, $section)) {
            return $r;
        }

        return view($this->viewFor($section, $p['triwulan']), $this->pageData($p, $resp, $section));
    }

    /**
     * Persist a whole blok from a normal form POST and move on.
     *
     * Validation is the model's own completion predicate rather than a second
     * rule set: one definition means the rail, the finish flow and this cannot
     * disagree about whether a blok is done.
     */
    private function saveBlok(Request $request, string $section, array $payload, string $label)
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return $this->unsupported();
        }

        $resp = KonstruksiSurveyResponse::getOrCreateForUser(Auth::id(), $section, $p['tahun'], $p['triwulan']);

        // showBlok() already refuses to render a block the preamble ruled out;
        // the POST needs the same guard or a stale tab — one left open on Blok
        // III while the answer was changed to "Tidak Ada Proyek" in another —
        // writes project rows into a submission that reports none.
        if ($r = $this->refuseSkippedSection($request, $p, $resp, $section)) {
            return $r;
        }

        $resp->fill($payload);
        $resp->last_saved_at = now();
        $resp->save();

        $complete = KonstruksiBlokPath::isComplete($resp->fresh(), $section);
        $resp->{"{$section}_completed"} = $complete;
        $resp->saveQuietly();

        if (!$complete) {
            $msg = "{$label} belum lengkap. Periksa kembali isian yang masih kosong atau tidak sesuai.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }

            return back()->with('error', $msg)->withInput();
        }

        $next = $this->nextSection($section, $resp->fresh());
        $url  = $next === null
            ? route('survey.konstruksi.entry')
            : route("survey.konstruksi.{$next}", $this->routeParams($p));

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => $url]);
        }

        return redirect($url)->with('success', "{$label} berhasil disimpan.");
    }

    /**
     * The block after this one, along the path that actually applies to this
     * response — so a quarterly submission with no projects skips Blok III
     * rather than being sent to a page it must not fill.
     */
    private function nextSection(string $section, ?KonstruksiSurveyResponse $resp): ?string
    {
        $path = KonstruksiBlokPath::path((int) ($resp->triwulan ?? KonstruksiSurveyResponse::TAHUNAN), $resp);
        $i    = array_search($section, $path, true);

        return ($i === false || !isset($path[$i + 1])) ? null : $path[$i + 1];
    }

    // ── Blok I ────────────────────────────────────────────────────────────

    public function blok1(Request $request)
    {
        return $this->showBlok($request, 'blok1');
    }

    public function saveBlok1(Request $request)
    {
        $fields  = self::AUTOSAVE_FIELDS['blok1'];
        $payload = [];
        foreach ($fields as $f) {
            $payload[$f] = $this->cleanText($request->input($f), self::FIELD_MAX_LENGTHS[$f] ?? 1000);
        }

        return $this->saveBlok($request, 'blok1', $payload, 'Blok I (Keterangan Umum Perusahaan)');
    }

    // ── Blok II ───────────────────────────────────────────────────────────

    public function blok2(Request $request)
    {
        return $this->showBlok($request, 'blok2');
    }

    public function saveBlok2(Request $request)
    {
        // SKTR's Blok II asks two scalars per quarter; SKTH's is a pendidikan ×
        // jenis-kelamin matrix plus six balas-jasa rincian. Same route, two
        // different instruments.
        if ($this->isTriwulananRequest($request)) {
            $slots = array_keys(KonstruksiSurveyResponse::TW_SLOTS);

            return $this->saveBlok($request, 'blok2', [
                'tw_pekerja'    => $this->sanitizeFigures($request->input('tw_pekerja'), $slots),
                'tw_balas_jasa' => $this->sanitizeFigures($request->input('tw_balas_jasa'), $slots),
            ], 'Blok II (Tenaga Kerja & Balas Jasa)');
        }

        return $this->saveBlok($request, 'blok2', [
            'blok2_pekerja'    => $this->sanitizePekerja($request->input('blok2_pekerja')),
            'blok2_balas_jasa' => $this->sanitizeFigures($request->input('blok2_balas_jasa'), array_keys(KonstruksiSurveyResponse::BALAS_JASA)),
        ], 'Blok II (Tenaga Kerja & Balas Jasa)');
    }

    // ── Blok III ──────────────────────────────────────────────────────────

    public function blok3(Request $request)
    {
        return $this->showBlok($request, 'blok3');
    }

    public function saveBlok3(Request $request)
    {
        // SKTR's Blok III is the project grid; SKTH's is kepemilikan modal.
        if ($this->isTriwulananRequest($request)) {
            return $this->saveBlok($request, 'blok3', [
                'data_proyek_tw' => $this->sanitizeProyekTw($request->input('data_proyek_tw')),
            ], 'Blok III (Kontrak & Realisasi Fisik)');
        }

        return $this->saveBlok($request, 'blok3', [
            'blok3_modal'        => $this->sanitizeFigures($request->input('blok3_modal'), array_keys(KonstruksiSurveyResponse::SUMBER_MODAL)),
            'modal_asing_negara' => $this->cleanText($request->input('modal_asing_negara'), 100),
        ], 'Blok III (Kepemilikan Modal)');
    }

    // ── SKTR : Updating Status Perusahaan (preamble) ──────────────────────

    /** Is the request addressing a quarterly period? */
    private function isTriwulananRequest(Request $request): bool
    {
        $p = $this->resolvePeriod($request);

        return $p !== null && $p['triwulan'] !== KonstruksiSurveyResponse::TAHUNAN;
    }

    public function status(Request $request)
    {
        return $this->showBlok($request, 'status');
    }

    public function saveStatus(Request $request)
    {
        $payload = [];
        foreach (self::AUTOSAVE_FIELDS['status'] as $f) {
            $payload[$f] = $this->cleanText($request->input($f), self::FIELD_MAX_LENGTHS[$f] ?? 1000);
        }

        // Kesediaan is never asked online — filling the form is the consent.
        $payload['kesediaan_responden'] = KonstruksiSurveyResponse::KESEDIAAN_BERSEDIA;

        // Only the self-reportable status codes are accepted from this form;
        // anything else (notably "Tidak Ditemukan") falls back to Aktif.
        if (!in_array($payload['status_perusahaan'], KonstruksiSurveyResponse::STATUS_PERUSAHAAN_MANDIRI, true)) {
            $payload['status_perusahaan'] = KonstruksiSurveyResponse::STATUS_AKTIF;
        }

        return $this->saveBlok($request, 'status', $payload, 'Updating Status Perusahaan');
    }

    // ── SKTR : Penutup (catatan, pemberi jawaban, finish) ─────────────────

    public function penutup(Request $request)
    {
        if ($r = $this->guard($request, 'penutup', $p, $resp)) {
            return $r;
        }

        return view('survey.konstruksi.tw.penutup', $this->pageData($p, $resp, 'penutup') + [
            'checklist' => $this->checklist($resp, $this->routeParams($p)),
        ]);
    }

    // ── Blok IV ───────────────────────────────────────────────────────────

    public function blok4(Request $request)
    {
        return $this->showBlok($request, 'blok4');
    }

    public function saveBlok4(Request $request)
    {
        return $this->saveBlok($request, 'blok4', [
            'blok4_pengeluaran' => $this->sanitizeFigures($request->input('blok4_pengeluaran'), array_keys(KonstruksiSurveyResponse::PENGELUARAN)),
        ], 'Blok IV (Pengeluaran Operasional)');
    }

    // ── Blok V ────────────────────────────────────────────────────────────

    public function blok5(Request $request)
    {
        return $this->showBlok($request, 'blok5');
    }

    public function saveBlok5(Request $request)
    {
        return $this->saveBlok($request, 'blok5', [
            'blok5_barang_modal'   => $this->sanitizeBarangModal($request->input('blok5_barang_modal')),
            'barang_modal_lainnya' => $this->cleanText($request->input('barang_modal_lainnya'), 255),
        ], 'Blok V (Barang Modal)');
    }

    // ── Blok VI ───────────────────────────────────────────────────────────

    public function blok6(Request $request)
    {
        return $this->showBlok($request, 'blok6');
    }

    public function saveBlok6(Request $request)
    {
        return $this->saveBlok($request, 'blok6', [
            'data_proyek'        => $this->sanitizeProyek($request->input('data_proyek')),
            'pct_bahan'          => $this->cleanNumeric($request->input('pct_bahan')) ?? '',
            'pct_upah'           => $this->cleanNumeric($request->input('pct_upah')) ?? '',
            'blok6_sumber_modal' => $this->sanitizeFigures($request->input('blok6_sumber_modal'), array_keys(KonstruksiSurveyResponse::MODAL_PROYEK)),
        ], 'Blok VI (Pekerjaan Konstruksi)');
    }

    // ── Blok VII ──────────────────────────────────────────────────────────

    public function blok7(Request $request)
    {
        return $this->showBlok($request, 'blok7');
    }

    public function saveBlok7(Request $request)
    {
        return $this->saveBlok($request, 'blok7', [
            'blok7_bahan' => $this->sanitizeBahan($request->input('blok7_bahan')),
        ], 'Blok VII (Struktur Bahan/Material)');
    }

    // ── Blok VIII ─────────────────────────────────────────────────────────

    public function blok8(Request $request)
    {
        return $this->showBlok($request, 'blok8');
    }

    public function saveBlok8(Request $request)
    {
        return $this->saveBlok($request, 'blok8', [
            'blok8_pendapatan_lain'   => $this->sanitizeFigures($request->input('blok8_pendapatan_lain'), array_keys(KonstruksiSurveyResponse::PENDAPATAN_LAIN)),
            'pendapatan_lain_lainnya' => $this->cleanText($request->input('pendapatan_lain_lainnya'), 255),
        ], 'Blok VIII (Pendapatan Lain)');
    }

    // ── Blok IX : ringkasan, read-only ────────────────────────────────────

    public function blok9(Request $request)
    {
        if ($r = $this->guard($request, 'blok9', $p, $resp)) {
            return $r;
        }

        return view('survey.konstruksi.blok9', [
            'response'    => $resp,
            'ringkasan'   => $resp->ringkasan(),
            'tahun'       => $p['tahun'],
            'triwulan'    => $p['triwulan'],
            'period'      => $p['period'],
            'routeParams' => $this->routeParams($p),
            'navBlocks'   => KonstruksiBlokPath::rows($resp, $this->routeParams($p), 'blok9'),
        ]);
    }

    // ── Blok X : catatan, pemberi jawaban, finish ─────────────────────────

    public function blok10(Request $request)
    {
        if ($r = $this->guard($request, 'blok10', $p, $resp)) {
            return $r;
        }

        return view('survey.konstruksi.blok10', $this->pageData($p, $resp, 'blok10') + [
            'checklist' => $this->checklist($resp, $this->routeParams($p)),
        ]);
    }

    /**
     * Per-blok done/not-done list rendered above the finish button.
     * The closing block itself is excluded — it is the page showing the list.
     */
    private function checklist(KonstruksiSurveyResponse $resp, array $routeParams): array
    {
        $triwulan = (int) $resp->triwulan;
        $closing  = $resp->isTriwulanan() ? 'penutup' : 'blok10';

        $out = [];
        foreach (KonstruksiBlokPath::path($triwulan, $resp) as $key) {
            if ($key === $closing) {
                continue;
            }
            $meta  = KonstruksiBlokPath::metaFor($triwulan, $key);
            $out[] = [
                'label' => $meta['label'] . ' — ' . $meta['sub'],
                'done'  => KonstruksiBlokPath::isComplete($resp, $key),
                'url'   => route("survey.konstruksi.{$key}", $routeParams),
            ];
        }

        return $out;
    }

    /**
     * Re-validate every blok before finalising. Returns the first failing one
     * (key + label) or null when all pass.
     *
     * Flags are rewritten as a side effect so a blok that silently stopped
     * being valid — an edit to Blok VI invalidating Blok IX, say — shows as
     * incomplete on the rail the moment the responden is bounced back to it.
     */
    private function runFinishValidation(KonstruksiSurveyResponse $resp): ?array
    {
        $triwulan = (int) $resp->triwulan;

        foreach (KonstruksiBlokPath::path($triwulan, $resp) as $key) {
            $complete = KonstruksiBlokPath::isComplete($resp, $key);
            $flag     = "{$key}_completed";

            if ($resp->{$flag} !== $complete) {
                $resp->{$flag} = $complete;
                $resp->saveQuietly();
            }

            if (!$complete) {
                $meta = KonstruksiBlokPath::metaFor($triwulan, $key);

                return [
                    'key'   => $key,
                    'label' => $meta['label'] . ' (' . $meta['sub'] . ')',
                ];
            }
        }

        return null;
    }

    public function finish(Request $request)
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return $this->unsupported();
        }

        $resp = $this->response($p);
        if (!$resp) {
            $msg = 'Mulai pengisian survei dari Updating Status Perusahaan terlebih dahulu.';
            $url = route('survey.konstruksi.status', $this->routeParams($p));

            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'redirect' => $url], 422);
            }

            return redirect($url)->with('error', $msg);
        }

        // The closing block's own answers have to land before it can be judged
        // complete — same questions in both instruments.
        $resp->fill([
            'metode_pendataan' => $this->cleanText($request->input('metode_pendataan'), 5),
            'catatan'          => $this->cleanText($request->input('catatan'), 5000),
            'pj_nama'          => $this->cleanText($request->input('pj_nama'), 255),
            'pj_hp'            => $this->cleanText($request->input('pj_hp'), 30),
            'pj_jabatan'       => $this->cleanText($request->input('pj_jabatan'), 255),
            'pj_email'         => $this->cleanText($request->input('pj_email'), 255),
        ]);
        $resp->last_saved_at = now();
        $resp->save();

        $failing = $this->runFinishValidation($resp);
        if ($failing) {
            $msg = "{$failing['label']} belum dilengkapi. Silakan lengkapi terlebih dahulu sebelum menyelesaikan survei.";
            $url = route("survey.konstruksi.{$failing['key']}", $this->routeParams($p));
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'redirect' => $url], 422);
            }

            return redirect($url)->with('error', $msg);
        }

        $resp->is_completed = true;
        $resp->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('survey.konstruksi.entry')]);
        }

        return redirect()->route('survey.konstruksi.entry')
            ->with('success', 'Selamat! Survei Konstruksi berhasil diselesaikan. Terima kasih atas partisipasi Anda.');
    }

    // ── Autosave & status endpoints ───────────────────────────────────────

    public function autoSave(Request $request, $year, $period, string $section): JsonResponse
    {
        return $this->doAutoSave($request, $section);
    }

    /**
     * Poll one blok's saved-at / completion state.
     *
     * Named fieldStatus rather than status because SKTR's preamble page owns
     * the `status` route name and controller method.
     */
    public function fieldStatus(Request $request, $year, $period, string $section): JsonResponse
    {
        return $this->doGetStatus($request, $section);
    }

    // ── PDF receipt ───────────────────────────────────────────────────────

    /**
     * The responden's submission receipt, matching UB and Listrik: a
     * proof-of-completion, not the data dump. BPS gets the full data PDF from
     * the BPS-side controller.
     */
    public function downloadPdf(Request $request)
    {
        $p = $this->resolvePeriod($request);
        if ($p === null) {
            return $this->unsupported();
        }

        $resp = $this->response($p);
        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.konstruksi.entry')
                ->with('error', 'Survei belum diselesaikan. Selesaikan survei terlebih dahulu untuk mengunduh PDF.');
        }

        $pdf = Pdf::loadView($resp->isTriwulanan() ? 'survey.konstruksi.tw.pdf' : 'survey.konstruksi.pdf', [
                'response'    => $resp,
                'user'        => Auth::user(),
                'periode'     => KonstruksiBlokPath::periodLabel($resp->tahun, $resp->triwulan),
                'ringkasan'   => $resp->ringkasan(),
                'proyekTotal' => $resp->totalProyekTw(),
                'quarterLabels' => $resp->isTriwulanan() ? $resp->quarterLabels() : [],
                'completedAt' => $resp->last_saved_at
                    ? $resp->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
                    : '—',
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $name = preg_replace('/[^a-zA-Z0-9_]/', '_', $resp->nama_perusahaan ?: 'survei');

        return $pdf->download("Survei_Konstruksi_{$resp->tahun}_{$name}.pdf");
    }
}
