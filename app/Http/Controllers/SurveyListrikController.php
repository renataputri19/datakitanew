<?php

namespace App\Http\Controllers;

use App\Models\ListrikSurveyResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

/**
 * Survei Listrik — produksi & nilai produksi listrik bulanan per kategori
 * pelanggan. Mirrors the Survei UB flow: per-field autosave, per-blok
 * completion flags, and finish() re-validating every blok and bouncing the
 * user back to the first incomplete one.
 */
class SurveyListrikController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /** Blok I required-field rules (shared by save, autosave-mark, finish). */
    private const BLOK1_RULES = [
        'nama_perusahaan'   => 'required|string',
        'alamat_perusahaan' => 'required|string',
        'provinsi'          => 'required|string',
        'kabupaten_kota'    => 'required|string',
        'kecamatan'         => 'required|string',
        'kelurahan_desa'    => 'required|string',
        'nomor_hp'          => 'required|string',
        'jenis_pembangkit'  => 'required|string',
        'nama_pengusaha'    => 'required|string',
        'jenis_kelamin'     => 'required|integer',
        'umur'              => 'required|integer',
        'nik'               => 'required|string',
    ];

    private const FIELD_MAX_LENGTHS = [
        'provinsi'         => 100,
        'kabupaten_kota'   => 100,
        'kecamatan'        => 100,
        'kelurahan_desa'   => 100,
        'nama_perusahaan'  => 255,
        'nama_komersial'   => 255,
        'kode_pos'         => 10,
        'nomor_telepon'    => 30,
        'nomor_hp'         => 30,
        'email_perusahaan' => 255,
        'jenis_pembangkit' => 30,
        'nama_pengusaha'   => 255,
        'nik'              => 20,
        'rt'               => 5,
        'rw'               => 5,
    ];

    // ── Shared helpers ────────────────────────────────────────────────────

    private function response(): ?ListrikSurveyResponse
    {
        return ListrikSurveyResponse::where('user_id', Auth::id())
            ->where('tahun', ListrikSurveyResponse::TAHUN)
            ->first();
    }

    private function checkCompletion(): ?\Illuminate\Http\RedirectResponse
    {
        $resp = $this->response();
        if ($resp && $resp->is_completed) {
            return redirect()->route('survey.listrik.entry')
                ->with('info', 'Survei Listrik sudah diselesaikan.');
        }
        return null;
    }

    /** Maximum wilayah-tujuan rows accepted per month. */
    private const MAX_WILAYAH_ROWS = 10;

    /**
     * Sanitize the monthly grid. Each month is a LIST of wilayah-tujuan rows
     * ({w: {jenis, area, kabkota, negara}} + per-category {kwh, rp}). Only
     * known month keys/categories survive; values are numeric ≥ 0 or null;
     * wilayah fields are normalized (legacy object months are wrapped first).
     */
    private function sanitizeGrid(mixed $raw): array
    {
        if (is_string($raw)) {
            $raw = json_decode($raw, true);
        }
        if (!is_array($raw)) {
            return [];
        }

        $validMonths = array_flip(ListrikSurveyResponse::availableMonthKeys());
        $clean = [];
        foreach ($raw as $ym => $monthData) {
            if (!isset($validMonths[$ym])) {
                continue;
            }
            $rows = ListrikSurveyResponse::normalizeMonthRows($monthData);
            $cleanRows = [];
            foreach (array_slice($rows, 0, self::MAX_WILAYAH_ROWS) as $row) {
                if (!is_array($row)) {
                    continue;
                }
                $cleanRows[] = array_merge(
                    ['w' => $this->sanitizeWilayah($row['w'] ?? null)],
                    $this->sanitizeCells($row)
                );
            }
            if ($cleanRows !== []) {
                $clean[$ym] = $cleanRows;
            }
        }
        return $clean;
    }

    private function sanitizeWilayah(mixed $w): array
    {
        $w = is_array($w) ? $w : [];
        $jenis = ($w['jenis'] ?? 'dn') === 'ln' ? 'ln' : 'dn';
        if ($jenis === 'ln') {
            return [
                'jenis'   => 'ln',
                'area'    => null,
                'kabkota' => null,
                'negara'  => mb_substr(trim((string) ($w['negara'] ?? '')), 0, 100),
            ];
        }
        $area = ($w['area'] ?? 'kepri') === 'luar_kepri' ? 'luar_kepri' : 'kepri';
        $kabkota = mb_substr(trim((string) ($w['kabkota'] ?? '')), 0, 100);
        if ($area === 'kepri' && !in_array($kabkota, ListrikSurveyResponse::KEPRI_KABKOTA, true)) {
            $kabkota = $kabkota !== '' ? $kabkota : 'Kota Batam';
            if (!in_array($kabkota, ListrikSurveyResponse::KEPRI_KABKOTA, true)) {
                $kabkota = 'Kota Batam';
            }
        }
        return ['jenis' => 'dn', 'area' => $area, 'kabkota' => $kabkota, 'negara' => null];
    }

    private function sanitizeCells(array $row): array
    {
        $cells = [];
        foreach (ListrikSurveyResponse::CATEGORIES as $cat => $label) {
            foreach (['kwh', 'rp'] as $f) {
                $v = $row[$cat][$f] ?? null;
                if ($v === null || $v === '') {
                    $cells[$cat][$f] = null;
                    continue;
                }
                if (is_string($v)) {
                    $v = str_replace(['.', ','], ['', '.'], trim($v));
                }
                $cells[$cat][$f] = is_numeric($v) ? max(0, (float) $v) : null;
            }
        }
        return $cells;
    }

    /** Re-evaluate a blok's completion flag after an autosave. */
    private function autoMarkBlokCompleted(ListrikSurveyResponse $resp, string $section): void
    {
        $resp->refresh();

        $complete = match ($section) {
            'blok1' => !Validator::make($resp->toArray(), self::BLOK1_RULES)->fails(),
            'blok2' => $resp->isBlok2GridComplete(),
            default => null,
        };
        if ($complete === null) {
            return;
        }

        $flag = "{$section}_completed";
        if ($resp->{$flag} !== $complete) {
            $resp->{$flag} = $complete;
            $resp->saveQuietly();
        }
    }

    private function doAutoSave(Request $request, string $section): \Illuminate\Http\JsonResponse
    {
        try {
            $v = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);
            if ($v->fails()) {
                return response()->json(['success' => false, 'errors' => $v->errors()], 422);
            }

            $field = $request->input('field');
            $value = $request->input('value');

            $allowed = match ($section) {
                'blok1' => array_merge(array_keys(self::FIELD_MAX_LENGTHS),
                    ['alamat_perusahaan', 'jenis_kelamin', 'umur', 'daya_terpasang_kw']),
                'blok2' => ['data_listrik'],
                'blok3' => ['catatan'],
                default => [],
            };
            if (!in_array($field, $allowed, true)) {
                return response()->json(['success' => false, 'message' => "Kolom '{$field}' tidak dikenal."], 422);
            }

            if (isset(self::FIELD_MAX_LENGTHS[$field]) && is_string($value) && mb_strlen($value) > self::FIELD_MAX_LENGTHS[$field]) {
                $max = self::FIELD_MAX_LENGTHS[$field];
                return response()->json([
                    'success' => false,
                    'message' => "Kolom '{$field}' maksimal {$max} karakter.",
                    'errors'  => [$field => ["Maksimal {$max} karakter."]],
                ], 422);
            }

            if ($field === 'data_listrik') {
                $value = $this->sanitizeGrid($value);
            }

            $resp = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), $section);
            $resp->updateWithAutoSave([$field => $value]);
            $this->autoMarkBlokCompleted($resp, $section);

            $flag = "{$section}_completed";
            return response()->json([
                'success'        => true,
                'message'        => 'Data auto-saved',
                'last_saved_at'  => $resp->last_saved_at->format('H:i:s'),
                'blok_completed' => (bool) $resp->fresh()->{$flag},
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function doGetStatus(string $completedField): \Illuminate\Http\JsonResponse
    {
        try {
            $resp = $this->response();
            return response()->json([
                'success'       => true,
                'last_saved_at' => $resp?->last_saved_at?->format('H:i:s'),
                'is_completed'  => (bool) ($resp?->{$completedField} ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    // ── Landing ───────────────────────────────────────────────────────────

    public function entry()
    {
        $response = $this->response();
        return view('survey.listrik.landing', compact('response'));
    }

    public function startEdit(Request $request)
    {
        $resp = $this->response();
        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.listrik.entry')
                ->with('error', 'Survei tidak ditemukan atau belum diselesaikan.');
        }

        $resp->is_completed = false;
        $resp->save();

        return redirect()->route('survey.listrik.blok1')
            ->with('info', 'Mode edit aktif. Perbaiki data yang diperlukan, lalu selesaikan kembali di Blok III.');
    }

    // ── Blok I : Identitas ───────────────────────────────────────────────

    public function blok1()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), 'blok1');
        return view('survey.listrik.blok1', compact('response'));
    }

    public function autoSaveBlok1(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok1');
    }

    public function getStatusBlok1(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok1_completed');
    }

    public function saveBlok1(Request $request)
    {
        $v = Validator::make($request->all(), [
            'provinsi'          => 'required|string|max:100',
            'kabupaten_kota'    => 'required|string|max:100',
            'kecamatan'         => 'required|string|max:100',
            'kelurahan_desa'    => 'required|string|max:100',
            'nama_perusahaan'   => 'required|string|max:255',
            'nama_komersial'    => 'nullable|string|max:255',
            'alamat_perusahaan' => 'required|string',
            'rt'                => 'nullable|string|max:5',
            'rw'                => 'nullable|string|max:5',
            'kode_pos'          => 'nullable|string|max:10',
            'nomor_telepon'     => 'nullable|string|max:30',
            'nomor_hp'          => 'required|string|max:30',
            'email_perusahaan'  => 'nullable|email|max:255',
            'jenis_pembangkit'  => 'required|string|max:30',
            'daya_terpasang_kw' => 'nullable|numeric|min:0',
            'nama_pengusaha'    => 'required|string|max:255',
            'jenis_kelamin'     => 'required|integer|between:1,2',
            'umur'              => 'required|integer|min:1|max:120',
            'nik'               => 'required|string|max:20',
        ], [
            'required'       => ':attribute wajib diisi.',
            'email'          => 'Format email tidak valid.',
            'umur.between'   => 'Umur harus antara 1–120 tahun.',
        ], [
            'nama_perusahaan'   => 'Nama perusahaan',
            'alamat_perusahaan' => 'Alamat perusahaan',
            'provinsi'          => 'Provinsi',
            'kabupaten_kota'    => 'Kabupaten/Kota',
            'kecamatan'         => 'Kecamatan',
            'kelurahan_desa'    => 'Kelurahan/Desa',
            'nomor_hp'          => 'Nomor HP',
            'jenis_pembangkit'  => 'Jenis pembangkit',
            'nama_pengusaha'    => 'Nama penanggung jawab',
            'jenis_kelamin'     => 'Jenis kelamin',
            'umur'              => 'Umur',
            'nik'               => 'NIK',
        ]);

        if ($v->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
            }
            return back()->withErrors($v)->withInput();
        }

        $resp = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), 'blok1');
        $resp->fill($request->only([
            'provinsi', 'kabupaten_kota', 'kecamatan', 'kelurahan_desa',
            'nama_perusahaan', 'nama_komersial', 'alamat_perusahaan', 'rt', 'rw',
            'kode_pos', 'nomor_telepon', 'nomor_hp', 'email_perusahaan',
            'jenis_pembangkit', 'daya_terpasang_kw',
            'nama_pengusaha', 'jenis_kelamin', 'umur', 'nik',
        ]));
        $resp->blok1_completed = true;
        $resp->last_saved_at   = now();
        $resp->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('survey.listrik.blok2')]);
        }
        return redirect()->route('survey.listrik.blok2')->with('success', 'Blok I berhasil disimpan.');
    }

    // ── Blok II : Grid produksi listrik bulanan ──────────────────────────

    public function blok2()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), 'blok2');
        $monthsByYear = ListrikSurveyResponse::availableMonths();
        $categories   = ListrikSurveyResponse::CATEGORIES;
        $kepriKabkota = ListrikSurveyResponse::KEPRI_KABKOTA;
        return view('survey.listrik.blok2', compact('response', 'monthsByYear', 'categories', 'kepriKabkota'));
    }

    public function autoSaveBlok2(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok2');
    }

    public function getStatusBlok2(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok2_completed');
    }

    public function saveBlok2(Request $request)
    {
        $grid = $this->sanitizeGrid($request->input('data_listrik'));

        $resp = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), 'blok2');
        $resp->data_listrik  = $grid;
        $resp->last_saved_at = now();
        $resp->save();

        $complete = $resp->isBlok2GridComplete();
        $resp->blok2_completed = $complete;
        $resp->saveQuietly();

        if (!$complete) {
            $msg = 'Masih ada sel yang kosong. Lengkapi seluruh bulan (isi 0 jika tidak ada) sebelum melanjutkan.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('survey.listrik.blok3')]);
        }
        return redirect()->route('survey.listrik.blok3')->with('success', 'Blok II berhasil disimpan.');
    }

    // ── Blok III : Catatan + finish ──────────────────────────────────────

    public function blok3()
    {
        $response = ListrikSurveyResponse::getOrCreateForUser(Auth::id(), 'blok3');
        if ($response->blok3_completed && !$response->is_completed) {
            $response->blok3_completed = false;
            $response->saveQuietly();
        }
        return view('survey.listrik.blok3', compact('response'));
    }

    public function autoSaveBlok3(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok3');
    }

    public function getStatusBlok3(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok3_completed');
    }

    /**
     * Re-validate every blok before finalising. Returns the first failing
     * blok (route + label) or null when all pass — the finish flow uses this
     * to send the user straight back to whatever is incomplete.
     */
    private function runFinishValidation(ListrikSurveyResponse $resp): ?array
    {
        $blok1Fail = Validator::make($resp->toArray(), self::BLOK1_RULES)->fails();
        if (!$resp->blok1_completed || $blok1Fail) {
            $resp->blok1_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.listrik.blok1', 'label' => 'Blok I (Identitas & Lokasi)'];
        }

        if (!$resp->blok2_completed || !$resp->isBlok2GridComplete()) {
            $resp->blok2_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.listrik.blok2', 'label' => 'Blok II (Produksi Listrik Bulanan)'];
        }

        return null;
    }

    public function finish(Request $request)
    {
        $resp = $this->response();

        if (!$resp) {
            $msg = 'Mulai pengisian survei dari Blok I terlebih dahulu.';
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'redirect' => route('survey.listrik.blok1')], 422);
            }
            return redirect()->route('survey.listrik.blok1')->with('error', $msg);
        }

        $failing = $this->runFinishValidation($resp);
        if ($failing) {
            $msg = "{$failing['label']} belum dilengkapi. Silakan lengkapi terlebih dahulu sebelum menyelesaikan survei.";
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'message' => $msg, 'redirect' => route($failing['route'])], 422);
            }
            return redirect()->route($failing['route'])->with('error', $msg);
        }

        $v = Validator::make($request->all(), [
            'catatan' => 'required|string|min:1',
        ], [
            'catatan.required' => 'Catatan wajib diisi. Jika tidak ada catatan, isi dengan tanda "-".',
        ]);
        if ($v->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
            }
            return back()->withErrors($v)->withInput();
        }

        $resp->catatan         = $request->input('catatan');
        $resp->blok3_completed = true;
        $resp->is_completed    = true;
        $resp->last_saved_at   = now();
        $resp->save();

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'redirect' => route('survey.listrik.entry')]);
        }
        return redirect()->route('survey.listrik.entry')
            ->with('success', 'Selamat! Survei Listrik berhasil diselesaikan. Terima kasih atas partisipasi Anda.');
    }

    /**
     * Responder's submission receipt ("Bukti Pengisian Terkirim") — mirrors
     * the UB flow. This is deliberately a lightweight proof-of-completion,
     * not the data dump; BPS gets the full data PDF via BPS\ListrikController.
     */
    public function downloadPdf()
    {
        $resp = $this->response();

        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.listrik.entry')
                ->with('error', 'Survei belum diselesaikan. Selesaikan survei terlebih dahulu untuk mengunduh PDF.');
        }

        $completedAt = $resp->last_saved_at
            ? $resp->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
            : '—';

        $monthKeys = ListrikSurveyResponse::availableMonthKeys();
        $periode   = $monthKeys === []
            ? '—'
            : ListrikSurveyResponse::monthLabel($monthKeys[0]) . ' — ' . ListrikSurveyResponse::monthLabel(end($monthKeys));

        $pdf = Pdf::loadView('survey.listrik.pdf', [
                'response'    => $resp,
                'user'        => Auth::user(),
                'completedAt' => $completedAt,
                'periode'     => $periode,
                'jumlahBulan' => count($monthKeys),
            ])
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $filename = 'Survei_Listrik_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $resp->nama_perusahaan ?? 'survei') . '.pdf';

        return $pdf->download($filename);
    }
}
