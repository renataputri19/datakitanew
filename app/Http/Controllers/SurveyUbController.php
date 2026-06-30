<?php

namespace App\Http\Controllers;

use App\Models\UbSurveyResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class SurveyUbController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    // ── Shared helpers ────────────────────────────────────────────────────────

    private function checkCompletion(): ?\Illuminate\Http\RedirectResponse
    {
        $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();
        if ($resp && $resp->is_completed) {
            return redirect()->route('survey.ub.entry')->with('info', 'Survei UB sudah diselesaikan.');
        }
        return null;
    }

    private function guardBlock(string $block): ?\Illuminate\Http\RedirectResponse
    {
        $resp  = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();
        $order = ['blok1a', 'blok1b', 'blok1c', 'blok1d', 'blok2', 'blok3'];
        $idx   = array_search($block, $order, true);

        if ($idx === false || $idx === 0) {
            return null;
        }

        $checkers = [
            'blok1a' => fn () => $resp && $resp->blok1a_completed,
            'blok1b' => fn () => $resp && $resp->blok1b_completed,
            'blok1c' => fn () => $resp && $resp->blok1c_completed,
            'blok1d' => fn () => $resp && $resp->blok1d_completed,
            'blok2'  => fn () => $resp && $resp->blok2_completed,
        ];

        for ($i = 0; $i < $idx; $i++) {
            $prev    = $order[$i];
            $checker = $checkers[$prev] ?? fn () => true;
            if (!$checker()) {
                return redirect()
                    ->route("survey.ub.{$prev}")
                    ->with('warning', 'Silakan lengkapi bagian sebelumnya terlebih dahulu.');
            }
        }

        return null;
    }

    /** Per-field max-length constraints used by auto-save validation. */
    private const FIELD_MAX_LENGTHS = [
        'kategori_lapangan_usaha' => 3,
        'kode_kbli'               => 10,
        'provinsi'                => 100,
        'kabupaten_kota'          => 100,
        'kecamatan'               => 100,
        'kelurahan_desa'          => 100,
        'nama_perusahaan'         => 255,
        'nama_komersial'          => 255,
        'nomor_hp'                => 30,
        'nib'                     => 30,
        'nama_pengusaha'          => 255,
        'nik'                     => 20,
        'kp_nama'                 => 255,
        'kp_negara'               => 100,
        'kp_provinsi'             => 100,
        'kp_kabkota'              => 100,
        'kp_email'                => 255,
    ];

    /** Shared auto-save JSON handler. */
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

            if (isset(self::FIELD_MAX_LENGTHS[$field]) && is_string($value)) {
                $maxLen = self::FIELD_MAX_LENGTHS[$field];
                if (mb_strlen($value) > $maxLen) {
                    return response()->json([
                        'success' => false,
                        'message' => "Kolom '{$field}' maksimal {$maxLen} karakter (saat ini " . mb_strlen($value) . " karakter).",
                        'errors'  => [$field => ["Maksimal {$maxLen} karakter."]],
                    ], 422);
                }
            }

            $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, $section);
            $resp->updateWithAutoSave([$field => $value]);

            $this->autoMarkBlokCompleted($resp, $section);

            $flagField = "{$section}_completed";
            return response()->json([
                'success'        => true,
                'message'        => 'Data auto-saved',
                'last_saved_at'  => $resp->last_saved_at->format('H:i:s'),
                'blok_completed' => (bool) $resp->fresh()->{$flagField},
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * After each autosave, check whether all required fields for the given blok
     * are now filled. If yes, mark blok as completed automatically so the user
     * does not need to physically click "Simpan dan Lanjutkan" to pass finish validation.
     */
    private function autoMarkBlokCompleted(UbSurveyResponse $resp, string $section): void
    {
        $resp->refresh();

        $rules = match ($section) {
            'blok1a' => [
                'nama_perusahaan'    => 'required|string',
                'nama_komersial'     => 'required|string',
                'alamat_perusahaan'  => 'required|string',
                'provinsi'           => 'required|string',
                'kabupaten_kota'     => 'required|string',
                'kecamatan'          => 'required|string',
                'kelurahan_desa'     => 'required|string',
                'nomor_hp'           => 'required|string',
                'jenis_kawasan'      => 'required|integer',
                'has_nib'            => 'required|integer',
                'status_badan_usaha' => 'required|integer',
                'nama_pengusaha'     => 'required|string',
                'jenis_kelamin'      => 'required|integer',
                'umur'               => 'required|integer',
                'nik'                => 'required|string',
            ],
            'blok1b' => [
                'kegiatan_utama'      => 'required|string',
                'produksi_di_lokasi'  => 'required|integer',
                'layanan_makan_minum' => 'required|integer',
                'produk_utama'        => 'required|string',
                'jaringan_usaha'      => 'required|integer',
            ],
            'blok1c' => [
                'bermitra_kdkmp'      => 'required|integer',
                'terlibat_mbg'        => 'required|integer',
                'ekspor_impor_barang' => 'required|integer',
                'ekspor_impor_jasa'   => 'required|integer',
            ],
            'blok1d' => [
                'pekerja_laki'      => 'required|integer',
                'pekerja_perempuan' => 'required|integer',
                'tahun_beroperasi'  => 'required|integer',
            ],
            'blok2' => [
                'catatan' => 'required|string|min:1',
            ],
            default => null,
        };

        if ($rules === null) {
            return;
        }

        $flagField  = "{$section}_completed";
        $allFilled  = !Validator::make($resp->toArray(), $rules)->fails();

        if ($allFilled && !$resp->{$flagField}) {
            $resp->{$flagField} = true;
            $resp->saveQuietly();
        } elseif (!$allFilled && $resp->{$flagField}) {
            $resp->{$flagField} = false;
            $resp->saveQuietly();
        }
    }

    /** Shared status JSON handler. */
    private function doGetStatus(string $completedField): \Illuminate\Http\JsonResponse
    {
        try {
            $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();
            return response()->json([
                'success'       => true,
                'last_saved_at' => $resp?->last_saved_at?->format('H:i:s'),
                'is_completed'  => (bool) ($resp?->{$completedField} ?? false),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /** Strip Indonesian thousands-separator dots from currency strings. */
    private static function parseCurrency(mixed $value): ?float
    {
        $str = str_replace('.', '', (string) ($value ?? ''));
        return $str !== '' ? (float) $str : null;
    }

    // ── Landing ───────────────────────────────────────────────────────────────

    public function entry()
    {
        $user = Auth::user();

        // Mitra users see the full UB results index at this URL (mirrors SIBSTR)
        if ($user->is_mitra) {
            return $this->mitraUbIndex(request());
        }

        $response = UbSurveyResponse::where('user_id', $user->id)->where('tahun', 2026)->first();
        return view('survey.ub.landing', compact('response'));
    }

    // ── Edit (Sequential Re-flow) ─────────────────────────────────────────────

    public function startEdit(Request $request)
    {
        $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();

        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.ub.entry')
                ->with('error', 'Survei tidak ditemukan atau belum diselesaikan.');
        }

        $resp->is_completed = false;
        $resp->save();

        return redirect()->route('survey.ub.blok1a')
            ->with('info', 'Mode edit aktif. Gunakan navigasi samping untuk berpindah antar blok, lalu selesaikan kembali di Blok III.');
    }

    // ── Blok 1-A ─────────────────────────────────────────────────────────────

    public function blok1a()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response  = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1a');
        $crossFill = self::sibstrCrossFillForUb(Auth::id());
        return view('survey.ub.blok1a', compact('response', 'crossFill'));
    }

    /**
     * Build the cross-fill payload for a UB Blok I-A form: overlapping answers
     * from this user's most-recently-updated SIBSTR response.
     *
     * @return array{items: array, sourceBadge: string, sourceLabel: string}|null
     */
    public static function sibstrCrossFillForUb(int|string $userId): ?array
    {
        $sibstr = \App\Models\SurveyResponse::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->orderByDesc('updated_at')
            ->first();

        if (!$sibstr) {
            return null;
        }

        $items = \App\Support\SurveyCrossFill::sibstrToUb($sibstr);
        if (!\App\Support\SurveyCrossFill::hasCopyable($items)) {
            return null;
        }

        $periodLabel = (int) $sibstr->triwulan === 0
            ? 'Tahunan ' . $sibstr->tahun
            : \App\Models\SurveyResponse::triwulanLabel((int) $sibstr->triwulan) . ' ' . $sibstr->tahun;

        return [
            'items'       => $items,
            'sourceBadge' => 'SIBSTR',
            'sourceLabel' => 'Data dari SIBSTR (' . $periodLabel . ') yang sudah Anda isi',
        ];
    }

    public function autoSaveBlok1a(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok1a');
    }

    public function getStatusBlok1a(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok1a_completed');
    }

    public function saveBlok1a(Request $request)
    {
        $kawasan = (int) $request->input('jenis_kawasan');

        $v = Validator::make($request->all(), [
            'provinsi'             => 'required|string|max:100',
            'kabupaten_kota'       => 'required|string|max:100',
            'kecamatan'            => 'required|string|max:100',
            'kelurahan_desa'       => 'required|string|max:100',
            'nama_perusahaan'      => 'required|string|max:255',
            'nama_komersial'       => 'required|string|max:255',
            'alamat_perusahaan'    => 'required|string',
            'nomor_hp'             => 'required|string|max:30',
            'jenis_kawasan'        => 'required|integer|between:1,10',
            // Nama kawasan required for codes 1–9 (not 10 = di luar kawasan)
            'nama_kawasan'         => [Rule::requiredIf($kawasan >= 1 && $kawasan <= 9), 'nullable', 'string', 'max:255'],
            'has_nib'              => 'required|integer|between:1,2',
            // NIB number required only when has_nib = 1 (Ya)
            'nib'                  => ['required_if:has_nib,1', 'nullable', 'string', 'max:30'],
            // Alasan required only when has_nib = 2 (Tidak)
            'alasan_tidak_nib'     => ['required_if:has_nib,2', 'nullable', 'integer', 'between:1,5'],
            'status_badan_usaha'   => 'required|integer|between:1,13',
            'is_koperasi_kdkmp'    => 'required_if:status_badan_usaha,3|nullable|integer|between:1,2',
            'jenis_koperasi'       => 'required_if:status_badan_usaha,3|nullable|integer|between:1,2',
            'has_laporan_keuangan' => 'required|integer|between:1,2',
            'nama_pengusaha'       => 'required|string|max:255',
            'jenis_kelamin'        => 'required|integer|between:1,2',
            'umur'                 => 'required|integer|min:1|max:120',
            'nik'                  => 'required|string|max:20',
        ], [
            'provinsi.required'              => 'Provinsi wajib diisi.',
            'kabupaten_kota.required'        => 'Kabupaten/Kota wajib diisi.',
            'kecamatan.required'             => 'Kecamatan wajib diisi.',
            'kelurahan_desa.required'        => 'Kelurahan/Desa wajib diisi.',
            'nama_perusahaan.required'       => 'Nama perusahaan wajib diisi.',
            'nama_komersial.required'        => 'Nama komersial/merek wajib diisi.',
            'alamat_perusahaan.required'     => 'Alamat perusahaan wajib diisi.',
            'nomor_hp.required'              => 'Nomor HP/WhatsApp wajib diisi.',
            'jenis_kawasan.required'         => 'Jenis kawasan beroperasi wajib dipilih.',
            'jenis_kawasan.between'          => 'Pilihan jenis kawasan tidak valid.',
            'nama_kawasan.required'          => 'Nama kawasan wajib diisi untuk kawasan kode 1–9.',
            'has_nib.required'               => 'Status kepemilikan NIB wajib dipilih.',
            'nib.required_if'                => 'Nomor NIB wajib diisi jika memiliki NIB.',
            'alasan_tidak_nib.required_if'   => 'Alasan tidak memiliki NIB wajib dipilih.',
            'status_badan_usaha.required'    => 'Status badan usaha wajib dipilih.',
            'is_koperasi_kdkmp.required_if'  => 'Status keanggotaan KDKMP wajib dipilih untuk koperasi.',
            'jenis_koperasi.required_if'     => 'Jenis koperasi berdasarkan layanannya wajib dipilih.',
            'has_laporan_keuangan.required'  => 'Status kepemilikan laporan/catatan keuangan wajib dipilih.',
            'nama_pengusaha.required'        => 'Nama pengusaha/pemilik wajib diisi.',
            'jenis_kelamin.required'         => 'Jenis kelamin wajib dipilih.',
            'umur.required'                  => 'Umur pengusaha wajib diisi.',
            'umur.min'                       => 'Umur tidak valid (min. 1 tahun).',
            'umur.max'                       => 'Umur tidak valid (maks. 120 tahun).',
            'nik.required'                   => 'NIK pengusaha wajib diisi.',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1a');
        $resp->fill($request->only($this->safeFillable($resp)));
        $resp->blok1a_completed = true;
        $resp->last_saved_at    = now();
        $resp->save();

        if ($request->expectsJson()) {
            $json = ['success' => true, 'last_saved_at' => $resp->last_saved_at->format('H:i:s')];
            if ($request->input('_edit_mode')) {
                $json['redirect'] = route('survey.ub.entry');
            }
            return response()->json($json);
        }

        if ($request->input('_edit_mode')) {
            return redirect()->route('survey.ub.entry')->with('success', 'Data Blok I-A berhasil diperbarui.');
        }
        return redirect()->route('survey.ub.blok1b')->with('success', 'Blok I-A berhasil disimpan.');
    }

    // ── Blok 1-B ─────────────────────────────────────────────────────────────

    public function blok1b()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1b');
        return view('survey.ub.blok1b', compact('response'));
    }

    public function autoSaveBlok1b(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok1b');
    }

    public function getStatusBlok1b(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok1b_completed');
    }

    public function saveBlok1b(Request $request)
    {
        $b1       = (int) $request->input('produksi_di_lokasi');
        $b2       = (int) $request->input('layanan_makan_minum');
        $b3       = (int) $request->input('penjualan_barang');
        $jaringan = (int) $request->input('jaringan_usaha');

        // Q9b3 visible when b1=Tidak AND b2=Tidak
        $showB3 = ($b1 === 2 && $b2 === 2);
        // Q9b4 visible when b1=Tidak AND b2=Tidak AND b3=Tidak
        $showB4 = ($showB3 && $b3 === 2);
        // Q9c visible when b2=Ya OR (b3 visible AND b3=Ya)
        $needLokasi = ($b2 === 1 || ($showB3 && $b3 === 1));
        // Q9d/9e visible when b1=Ya AND b2=Tidak
        $needProduksi = ($b1 === 1 && $b2 === 2);
        // Q12-Q14 NOT required for unit pembantu/penunjang (code 6)
        $needQ12to14 = ($jaringan !== 6);

        $v = Validator::make($request->all(), [
            // Q9a
            'kegiatan_utama'          => 'required|string',
            // Q9b1, Q9b2 always required
            'produksi_di_lokasi'      => 'required|integer|between:1,2',
            'layanan_makan_minum'     => 'required|integer|between:1,2',
            // Q9b3: required when b1=Tidak AND b2=Tidak
            'penjualan_barang'        => [Rule::requiredIf($showB3), 'nullable', 'integer', Rule::in([1, 2])],
            // Q9b4: required when b1=Tidak, b2=Tidak, b3=Tidak
            'aktivitas_jasa_pertanian'=> [Rule::requiredIf($showB4), 'nullable', 'integer', Rule::in([1, 2])],
            // Q9c: required when b2=Ya OR (b3 section visible AND b3=Ya)
            'lokasi_usaha'            => [Rule::requiredIf($needLokasi), 'nullable', 'integer', 'between:1,11'],
            // Q9d & 9e: required when b1=Ya AND b2=Tidak
            'input_produksi'          => [Rule::requiredIf($needProduksi), 'nullable', 'string'],
            'proses_produksi'         => [Rule::requiredIf($needProduksi), 'nullable', 'string'],
            // Q9f always required
            'produk_utama'            => 'required|string',
            // Q9g, 9h, 9i optional (filled by BPS)
            'kode_kbli'               => 'nullable|string|max:10',
            'kategori_lapangan_usaha' => 'nullable|string|max:3',
            'klasifikasi_akomodasi'   => 'nullable|integer|between:1,6',
            // Q10
            'jaringan_usaha'          => 'required|integer|between:1,6',
            // Q10b: required only when Kantor pusat
            'jumlah_cabang'           => 'required_if:jaringan_usaha,2|nullable|integer|min:0',
            // Q11: required when Cabang/Perwakilan/Pabrik/Unit pembantu (codes 3-6)
            'kp_nama'    => 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|string|max:255',
            'kp_alamat'  => 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|string',
            'kp_email'   => 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|email|max:255',
            'kp_negara'  => 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|string|max:100',
            'kp_provinsi'=> 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|string|max:100',
            'kp_kabkota' => 'required_if:jaringan_usaha,3|required_if:jaringan_usaha,4|required_if:jaringan_usaha,5|required_if:jaringan_usaha,6|nullable|string|max:100',
            // Q12-Q14: required for all jaringan EXCEPT unit pembantu/penunjang (code 6)
            'uses_internet'           => [Rule::requiredIf($needQ12to14), 'nullable', 'integer', Rule::in([1, 2])],
            'uses_teknologi_digital'  => [Rule::requiredIf($needQ12to14), 'nullable', 'integer', Rule::in([1, 2])],
            'produk_ramah_lingkungan' => [Rule::requiredIf($needQ12to14), 'nullable', 'integer', 'between:1,3'],
            'uses_input_lingkungan'   => [Rule::requiredIf($needQ12to14), 'nullable', 'integer', Rule::in([1, 2])],
            'uses_karya_seni'         => [Rule::requiredIf($needQ12to14), 'nullable', 'integer', Rule::in([1, 2])],
        ], [
            'kegiatan_utama.required'          => 'Kegiatan utama perusahaan wajib diisi.',
            'produksi_di_lokasi.required'      => 'Pertanyaan 9b1 (produksi barang) wajib dijawab.',
            'layanan_makan_minum.required'     => 'Pertanyaan 9b2 (layanan makan minum) wajib dijawab.',
            'penjualan_barang.required'        => 'Pertanyaan 9b3 (penjualan barang) wajib dijawab.',
            'aktivitas_jasa_pertanian.required'=> 'Pertanyaan 9b4 (aktivitas jasa/pertanian) wajib dijawab.',
            'lokasi_usaha.required'            => 'Lokasi usaha (9c) wajib dipilih.',
            'input_produksi.required'          => 'Input produksi (9d) wajib diisi.',
            'proses_produksi.required'         => 'Proses produksi (9e) wajib diisi.',
            'produk_utama.required'            => 'Produk utama yang dihasilkan wajib diisi.',
            'jaringan_usaha.required'          => 'Jaringan usaha wajib dipilih.',
            'jumlah_cabang.required_if'        => 'Jumlah kantor cabang wajib diisi untuk kantor pusat.',
            'kp_nama.required_if'              => 'Nama kantor pusat wajib diisi.',
            'kp_alamat.required_if'            => 'Alamat kantor pusat wajib diisi.',
            'kp_email.required_if'             => 'Email kantor pusat wajib diisi.',
            'kp_negara.required_if'            => 'Negara kantor pusat wajib diisi.',
            'kp_provinsi.required_if'          => 'Provinsi kantor pusat wajib diisi.',
            'kp_kabkota.required_if'           => 'Kabupaten/Kota kantor pusat wajib diisi.',
            'uses_internet.required'           => 'Status penggunaan internet wajib dipilih.',
            'uses_teknologi_digital.required'  => 'Status penggunaan teknologi digital wajib dipilih.',
            'produk_ramah_lingkungan.required' => 'Status produk ramah lingkungan wajib dipilih.',
            'uses_input_lingkungan.required'   => 'Status penggunaan input lingkungan wajib dipilih.',
            'uses_karya_seni.required'         => 'Status penggunaan karya seni wajib dipilih.',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1b');
        $resp->fill($request->only($this->safeFillable($resp)));
        $resp->blok1b_completed = true;
        $resp->last_saved_at    = now();

        // ── Unit pembantu/penunjang (code 6): skip blok 1C/1D/2, go to blok3 ─
        // Auto-complete the skipped blocks so finish validation passes, then
        // redirect the user to Blok III to fill in responden identity before
        // officially finishing the survey.
        if ($jaringan === 6) {
            $resp->blok1c_completed = true;
            $resp->blok1d_completed = true;
            $resp->blok2_completed  = true;
            $resp->save();

            if ($request->expectsJson()) {
                return response()->json([
                    'success'       => true,
                    'last_saved_at' => $resp->last_saved_at->format('H:i:s'),
                    'redirect'      => route('survey.ub.blok3'),
                ]);
            }
            return redirect()->route('survey.ub.blok3')
                ->with('info', 'Data Blok I-B tersimpan. Lengkapi keterangan petugas di Blok III untuk menyelesaikan survei.');
        }

        $resp->save();

        if ($request->expectsJson()) {
            $json = ['success' => true, 'last_saved_at' => $resp->last_saved_at->format('H:i:s')];
            if ($request->input('_edit_mode')) {
                $json['redirect'] = route('survey.ub.entry');
            }
            return response()->json($json);
        }

        if ($request->input('_edit_mode')) {
            return redirect()->route('survey.ub.entry')->with('success', 'Data Blok I-B berhasil diperbarui.');
        }
        return redirect()->route('survey.ub.blok1c')->with('success', 'Blok I-B berhasil disimpan.');
    }

    // ── Blok 1-C ─────────────────────────────────────────────────────────────

    public function blok1c()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1c');
        return view('survey.ub.blok1c', compact('response'));
    }

    public function autoSaveBlok1c(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok1c');
    }

    public function getStatusBlok1c(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok1c_completed');
    }

    public function saveBlok1c(Request $request)
    {
        $v = Validator::make($request->all(), [
            'bermitra_kdkmp'      => 'required|integer|between:1,2',
            'terlibat_mbg'        => 'required|integer|between:1,5',
            'ekspor_impor_barang' => 'required|integer|between:1,2',
            'ekspor_impor_jasa'   => 'required|integer|between:1,2',
        ], [
            'bermitra_kdkmp.required'      => 'Status kemitraan KDKMP wajib dipilih.',
            'terlibat_mbg.required'        => 'Keterlibatan program MBG wajib dipilih.',
            'ekspor_impor_barang.required' => 'Status ekspor/impor barang wajib dipilih.',
            'ekspor_impor_jasa.required'   => 'Status ekspor/impor jasa wajib dipilih.',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1c');
        $resp->fill($request->only($this->safeFillable($resp)));
        $resp->blok1c_completed = true;
        $resp->last_saved_at    = now();
        $resp->save();

        if ($request->expectsJson()) {
            $json = ['success' => true, 'last_saved_at' => $resp->last_saved_at->format('H:i:s')];
            if ($request->input('_edit_mode')) {
                $json['redirect'] = route('survey.ub.entry');
            }
            return response()->json($json);
        }

        if ($request->input('_edit_mode')) {
            return redirect()->route('survey.ub.entry')->with('success', 'Data Blok I-C berhasil diperbarui.');
        }
        return redirect()->route('survey.ub.blok1d')->with('success', 'Blok I-C berhasil disimpan.');
    }

    // ── Blok 1-D ─────────────────────────────────────────────────────────────

    public function blok1d()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1d');
        return view('survey.ub.blok1d', compact('response'));
    }

    public function autoSaveBlok1d(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $v = Validator::make($request->all(), [
                'field' => 'required|string',
                'value' => 'nullable',
            ]);
            if ($v->fails()) {
                return response()->json(['success' => false, 'errors' => $v->errors()], 422);
            }

            $resp  = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1d');
            $field = $request->input('field');
            $value = $request->input('value');

            // Sanitize currency fields (strip Indonesian thousands-separator dots)
            $currencyFields = [
                'pengeluaran_upah_gaji', 'pengeluaran_biaya_produksi',
                'pengeluaran_pembelian_barang', 'pengeluaran_operasional',
                'pengeluaran_nonoperasional', 'nilai_produksi_barang_jasa',
                'pendapatan_lainnya', 'nilai_aset_tanah_bangunan', 'nilai_aset_lainnya',
            ];

            if (in_array($field, $currencyFields, true)) {
                $value = self::parseCurrency($value);
            }

            $resp->updateWithAutoSave([$field => $value]);

            $this->autoMarkBlokCompleted($resp, 'blok1d');

            return response()->json([
                'success'        => true,
                'message'        => 'Data auto-saved',
                'last_saved_at'  => $resp->last_saved_at->format('H:i:s'),
                'blok_completed' => (bool) $resp->fresh()->blok1d_completed,
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getStatusBlok1d(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok1d_completed');
    }

    public function saveBlok1d(Request $request)
    {
        // Sanitize Indonesian-formatted currency values (e.g. "1.000.000" → 1000000)
        $currencyFields = [
            'pengeluaran_upah_gaji', 'pengeluaran_biaya_produksi',
            'pengeluaran_pembelian_barang', 'pengeluaran_operasional',
            'pengeluaran_nonoperasional', 'nilai_produksi_barang_jasa',
            'pendapatan_lainnya', 'nilai_aset_tanah_bangunan', 'nilai_aset_lainnya',
        ];
        $sanitized = [];
        foreach ($currencyFields as $cf) {
            if ($request->has($cf)) {
                $sanitized[$cf] = self::parseCurrency($request->input($cf));
            }
        }
        if ($sanitized) {
            $request->merge($sanitized);
        }

        $v = Validator::make($request->all(), [
            'pekerja_laki'                 => 'required|integer|min:0',
            'pekerja_perempuan'            => 'required|integer|min:0',
            'tahun_beroperasi'             => 'required|integer|min:1900|max:2026',
            // Q22: all 5 expenditure items required (0 is valid)
            'pengeluaran_upah_gaji'        => 'required|numeric|min:0',
            'pengeluaran_biaya_produksi'   => 'required|numeric|min:0',
            'pengeluaran_pembelian_barang' => 'required|numeric|min:0',
            'pengeluaran_operasional'      => 'required|numeric|min:0',
            'pengeluaran_nonoperasional'   => 'required|numeric|min:0',
            // Q23
            'nilai_produksi_barang_jasa'   => 'required|numeric|min:0',
            'persen_pendapatan_online'     => 'required|numeric|min:0|max:100',
            // Q24 — nominal values are required unless the user chose a range instead
            'nilai_aset_tanah_bangunan'    => 'required_without:range_total_aset|nullable|numeric|min:0',
            'nilai_aset_lainnya'           => 'required_without:range_total_aset|nullable|numeric|min:0',
            'range_total_aset'             => 'nullable|integer|between:1,5',
            'luas_tanah'                   => 'required|numeric|min:0',
            // Q25
            'modal_pribadi'                => 'required|numeric|min:0|max:100',
            'modal_nonprofit'              => 'required|numeric|min:0|max:100',
            'modal_korporasi_publik'       => 'required|numeric|min:0|max:100',
            'modal_korporasi_nonpublik'    => 'required|numeric|min:0|max:100',
            'modal_pemerintah'             => 'required|numeric|min:0|max:100',
            'modal_asing'                  => 'required|numeric|min:0|max:100',
        ], [
            'pekerja_laki.required'                => 'Jumlah pekerja laki-laki wajib diisi.',
            'pekerja_perempuan.required'           => 'Jumlah pekerja perempuan wajib diisi.',
            'tahun_beroperasi.required'            => 'Tahun mulai beroperasi wajib diisi.',
            'tahun_beroperasi.min'                 => 'Tahun beroperasi tidak valid (min. 1900).',
            'pengeluaran_upah_gaji.required'       => 'Pengeluaran upah/gaji wajib diisi (isi 0 jika tidak ada).',
            'pengeluaran_biaya_produksi.required'  => 'Biaya produksi wajib diisi (isi 0 jika tidak ada).',
            'pengeluaran_pembelian_barang.required'=> 'Biaya pembelian barang wajib diisi (isi 0 jika tidak ada).',
            'pengeluaran_operasional.required'     => 'Pengeluaran operasional wajib diisi (isi 0 jika tidak ada).',
            'pengeluaran_nonoperasional.required'  => 'Pengeluaran non-operasional wajib diisi (isi 0 jika tidak ada).',
            'nilai_produksi_barang_jasa.required'  => 'Nilai produksi/pendapatan usaha wajib diisi.',
            'persen_pendapatan_online.required'    => 'Persentase pendapatan online wajib diisi (isi 0 jika tidak ada).',
            'nilai_aset_tanah_bangunan.required_without' => 'Nilai aset tanah & bangunan wajib diisi (atau pilih rentang nilai aset).',
            'nilai_aset_lainnya.required_without'          => 'Nilai aset lainnya wajib diisi (atau pilih rentang nilai aset)..',
            'luas_tanah.required'                  => 'Luas tanah wajib diisi.',
            'modal_pribadi.required'               => 'Persentase modal pribadi wajib diisi.',
            'modal_nonprofit.required'             => 'Persentase modal nirlaba wajib diisi.',
            'modal_korporasi_publik.required'      => 'Persentase modal korporasi publik wajib diisi.',
            'modal_korporasi_nonpublik.required'   => 'Persentase modal korporasi non-publik wajib diisi.',
            'modal_pemerintah.required'            => 'Persentase modal pemerintah wajib diisi.',
            'modal_asing.required'                 => 'Persentase modal asing wajib diisi.',
        ]);

        if ($v->fails()) {
            return response()->json(['success' => false, 'errors' => $v->errors()], 422);
        }

        // Q25: total modal ownership must equal 100%
        $modalSum = array_sum(array_map('floatval', [
            $request->modal_pribadi, $request->modal_nonprofit,
            $request->modal_korporasi_publik, $request->modal_korporasi_nonpublik,
            $request->modal_pemerintah, $request->modal_asing,
        ]));
        if (abs($modalSum - 100) > 0.5) {
            return response()->json(['success' => false, 'errors' => [
                'modal_pribadi' => ['Total persentase kepemilikan modal harus 100%. Saat ini: ' . round($modalSum, 2) . '%.'],
            ]], 422);
        }

        $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok1d');
        $data = $request->only($this->safeFillable($resp));

        $data['total_pekerja']       = (int) $request->pekerja_laki + (int) $request->pekerja_perempuan;
        $data['total_pengeluaran']   = array_sum(array_map('floatval', array_filter([
            $request->pengeluaran_upah_gaji, $request->pengeluaran_biaya_produksi,
            $request->pengeluaran_pembelian_barang, $request->pengeluaran_operasional,
            $request->pengeluaran_nonoperasional,
        ])));
        $data['total_nilai_produksi'] = floatval($request->nilai_produksi_barang_jasa) + floatval($request->pendapatan_lainnya);
        $data['nilai_total_aset']     = floatval($request->nilai_aset_tanah_bangunan) + floatval($request->nilai_aset_lainnya);

        $resp->fill($data);
        $resp->blok1d_completed = true;
        $resp->last_saved_at    = now();
        $resp->save();

        if ($request->expectsJson()) {
            $json = ['success' => true, 'last_saved_at' => $resp->last_saved_at->format('H:i:s')];
            if ($request->input('_edit_mode')) {
                $json['redirect'] = route('survey.ub.entry');
            }
            return response()->json($json);
        }

        if ($request->input('_edit_mode')) {
            return redirect()->route('survey.ub.entry')->with('success', 'Data Blok I-D berhasil diperbarui.');
        }
        return redirect()->route('survey.ub.blok2')->with('success', 'Blok I-D berhasil disimpan.');
    }

    // ── Blok 2 ───────────────────────────────────────────────────────────────

    public function blok2()
    {
        if ($r = $this->checkCompletion()) return $r;
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok2');
        return view('survey.ub.blok2', compact('response'));
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
        $v = Validator::make($request->all(), [
            'catatan' => 'required|string|min:1',
        ], [
            'catatan.required' => 'Catatan wajib diisi. Jika tidak ada catatan, isi dengan tanda "-".',
        ]);
        if ($v->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $v->errors()], 422);
            }
            return back()->withErrors($v)->withInput();
        }

        $resp = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok2');
        $resp->catatan         = $request->input('catatan');
        $resp->blok2_completed = true;
        $resp->last_saved_at   = now();
        $resp->save();

        if ($request->expectsJson()) {
            $json = ['success' => true, 'last_saved_at' => $resp->last_saved_at->format('H:i:s')];
            if ($request->input('_edit_mode')) {
                $json['redirect'] = route('survey.ub.entry');
            }
            return response()->json($json);
        }

        if ($request->input('_edit_mode')) {
            return redirect()->route('survey.ub.entry')->with('success', 'Data Blok II berhasil diperbarui.');
        }
        return redirect()->route('survey.ub.blok3')->with('success', 'Blok II berhasil disimpan.');
    }

    // ── Blok 3 (Finish) ──────────────────────────────────────────────────────

    public function blok3()
    {
        $response = UbSurveyResponse::getOrCreateForUser(Auth::id(), 2026, 'blok3');
        // Reset blok3_completed so the sidebar shows it as in-progress while on this page.
        // It is only re-set to true after the user successfully submits the finish form.
        if ($response->blok3_completed && !$response->is_completed) {
            $response->blok3_completed = false;
            $response->saveQuietly();
        }
        $user  = Auth::user();
        $today = now()->format('Y-m-d');
        return view('survey.ub.blok3', compact('response', 'user', 'today'));
    }

    public function autoSaveBlok3(Request $request): \Illuminate\Http\JsonResponse
    {
        return $this->doAutoSave($request, 'blok3');
    }

    public function getStatusBlok3(): \Illuminate\Http\JsonResponse
    {
        return $this->doGetStatus('blok3_completed');
    }

    private function safeFillable(UbSurveyResponse $model): array
    {
        return array_diff($model->getFillable(), [
            'user_id', 'tahun', 'survey_section', 'is_completed', 'last_saved_at',
        ]);
    }

    /**
     * Validate all required blocks before finalising the survey.
     *
     * Conditional rule (mirrors questionnaire spec):
     *   Q10a (jaringan_usaha) = 6 → Unit pembantu/penunjang: only Blok 1-A and 1-B are required.
     *   Any other value          → All blocks (1-A, 1-B, 1-C, 1-D, 2, 3) must be complete.
     *
     * @return array{route:string,label:string}|null  First failing block, or null when all pass.
     */
    /**
     * Re-validate stored field values for each blok using the same rules as the save methods.
     * Returns the first failing blok, or null when all pass.
     */
    private function runFinishValidation(UbSurveyResponse $resp): ?array
    {
        // ── Blok I-A ──────────────────────────────────────────────────────────
        $blok1aFail = Validator::make($resp->toArray(), [
            'nama_perusahaan'    => 'required|string',
            'nama_komersial'     => 'required|string',
            'alamat_perusahaan'  => 'required|string',
            'provinsi'           => 'required|string',
            'kabupaten_kota'     => 'required|string',
            'kecamatan'          => 'required|string',
            'kelurahan_desa'     => 'required|string',
            'nomor_hp'           => 'required|string',
            'jenis_kawasan'      => 'required|integer',
            'has_nib'            => 'required|integer',
            'status_badan_usaha' => 'required|integer',
            'nama_pengusaha'     => 'required|string',
            'jenis_kelamin'      => 'required|integer',
            'umur'               => 'required|integer',
            'nik'                => 'required|string',
        ])->fails();

        if (!$resp->blok1a_completed || $blok1aFail) {
            $resp->blok1a_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.ub.blok1a', 'label' => 'Blok I-A (Identitas & Lokasi)'];
        }

        // ── Blok I-B ──────────────────────────────────────────────────────────
        $blok1bFail = Validator::make($resp->toArray(), [
            'kegiatan_utama'     => 'required|string',
            'produksi_di_lokasi' => 'required|integer',
            'layanan_makan_minum'=> 'required|integer',
            'produk_utama'       => 'required|string',
            'jaringan_usaha'     => 'required|integer',
        ])->fails();

        if (!$resp->blok1b_completed || $blok1bFail) {
            $resp->blok1b_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.ub.blok1b', 'label' => 'Blok I-B (Kegiatan & Digital)'];
        }

        // Q10a = jaringan_usaha; value 6 (Unit pembantu/penunjang) → pendataan selesai after blok1b
        if ((int) $resp->jaringan_usaha === 6) {
            return null;
        }

        // ── Blok I-C ──────────────────────────────────────────────────────────
        $blok1cFail = Validator::make($resp->toArray(), [
            'bermitra_kdkmp'      => 'required|integer',
            'terlibat_mbg'        => 'required|integer',
            'ekspor_impor_barang' => 'required|integer',
            'ekspor_impor_jasa'   => 'required|integer',
        ])->fails();

        if (!$resp->blok1c_completed || $blok1cFail) {
            $resp->blok1c_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.ub.blok1c', 'label' => 'Blok I-C (Sertifikasi & Kemitraan)'];
        }

        // ── Blok I-D ──────────────────────────────────────────────────────────
        $blok1dFail = Validator::make($resp->toArray(), [
            'pekerja_laki'       => 'required|integer',
            'pekerja_perempuan'  => 'required|integer',
            'tahun_beroperasi'   => 'required|integer',
        ])->fails();

        if (!$resp->blok1d_completed || $blok1dFail) {
            $resp->blok1d_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.ub.blok1d', 'label' => 'Blok I-D (Tenaga Kerja & Keuangan)'];
        }

        // ── Blok II ───────────────────────────────────────────────────────────
        $blok2Fail = Validator::make($resp->toArray(), [
            'catatan' => 'required|string|min:1',
        ])->fails();

        if (!$resp->blok2_completed || $blok2Fail) {
            $resp->blok2_completed = false;
            $resp->saveQuietly();
            return ['route' => 'survey.ub.blok2', 'label' => 'Blok II (Catatan Petugas)'];
        }

        return null;
    }

    public function finish(Request $request)
    {
        $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();

        if (!$resp) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => 'Mulai pengisian survei dari Blok I-A terlebih dahulu.',
                    'redirect' => route('survey.ub.blok1a'),
                ], 422);
            }
            return redirect()->route('survey.ub.blok1a')
                ->with('error', 'Mulai pengisian survei dari Blok I-A terlebih dahulu.');
        }

        $failing = $this->runFinishValidation($resp);

        if ($failing) {
            $msg = "{$failing['label']} belum dilengkapi. Silakan lengkapi terlebih dahulu sebelum menyelesaikan survei.";

            if ($request->expectsJson()) {
                return response()->json([
                    'success'  => false,
                    'message'  => $msg,
                    'redirect' => route($failing['route']),
                ], 422);
            }

            return redirect()->route($failing['route'])->with('error', $msg);
        }

        $v = Validator::make($request->all(), [
            'resp_nip'     => 'required|string',
            'resp_telepon' => 'required|string',
        ], [
            'resp_nip.required'     => 'NIP/NMS responden wajib diisi.',
            'resp_telepon.required' => 'Nomor HP/Telepon responden wajib diisi.',
        ]);
        if ($v->fails()) {
            if ($request->expectsJson()) {
                return response()->json(['success' => false, 'errors' => $v->errors(), 'message' => $v->errors()->first()], 422);
            }
            return back()->withErrors($v)->withInput();
        }

        $resp->fill($request->only([
            'ppl_tanggal',
            'pml_tanggal',
            'resp_nama', 'resp_nip', 'resp_telepon', 'resp_email', 'resp_tanggal',
        ]));
        $resp->blok3_completed = true;
        $resp->is_completed    = true;
        $resp->last_saved_at   = now();
        $resp->save();

        if ($request->expectsJson()) {
            return response()->json([
                'success'       => true,
                'last_saved_at' => $resp->last_saved_at->format('H:i:s'),
                'redirect'      => route('survey.ub.entry'),
            ]);
        }

        return redirect()->route('survey.ub.entry')
            ->with('success', 'Selamat! Survei UB SE2026 berhasil diselesaikan. Silakan unduh PDF sebagai bukti pengisian.');
    }

    // ── PDF Download ─────────────────────────────────────────────────────────

    public function downloadPdf()
    {
        $resp = UbSurveyResponse::where('user_id', Auth::id())->where('tahun', 2026)->first();

        if (!$resp || !$resp->is_completed) {
            return redirect()->route('survey.ub.entry')
                ->with('error', 'Survei belum diselesaikan. Selesaikan survei terlebih dahulu untuk mengunduh PDF.');
        }

        // The responder receives a lightweight submission receipt ("Bukti Pengisian Terkirim").
        $completedAt = $resp->last_saved_at
            ? $resp->last_saved_at->locale('id')->isoFormat('D MMMM YYYY, HH:mm') . ' WIB'
            : '—';

        $pdf = Pdf::loadView('survey.ub.pdf', ['response' => $resp, 'user' => Auth::user(), 'completedAt' => $completedAt])
            ->setPaper('A4', 'portrait')
            ->setOptions(['defaultFont' => 'sans-serif', 'isRemoteEnabled' => false]);

        $filename = 'SE2026-L.UB_' . preg_replace('/[^a-zA-Z0-9_]/', '_', $resp->nama_perusahaan ?? 'survei') . '.pdf';

        return $pdf->download($filename);
    }

    // ── Mitra: read-only index, detail & PDF (mirrors SIBSTR) ──────────────────

    /**
     * Display all UB survey responses for Mitra users (read-only, user-dashboard layout).
     */
    private function mitraUbIndex(Request $request)
    {
        $user    = Auth::user();
        $search  = $request->input('search');
        $status  = $request->input('status');
        $sortBy  = $request->input('sort_by', 'updated_at');
        $perPage = $request->input('per_page', 25);

        $query = UbSurveyResponse::with('user');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_perusahaan', 'like', "%{$search}%")
                  ->orWhere('nama_komersial', 'like', "%{$search}%")
                  ->orWhere('kabupaten_kota', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        if ($status !== null && $status !== '') {
            if ($status === 'completed') {
                $query->where('is_completed', true);
            } elseif ($status === 'in_progress') {
                $query->where('is_completed', false);
            }
        }

        $allowed = ['updated_at', 'created_at', 'nama_perusahaan'];
        $query->orderBy(in_array($sortBy, $allowed) ? $sortBy : 'updated_at', 'desc');

        $surveyResponses = $query->paginate($perPage)->withQueryString();

        $stats = [
            'total'       => UbSurveyResponse::count(),
            'completed'   => UbSurveyResponse::where('is_completed', true)->count(),
            'in_progress' => UbSurveyResponse::where('is_completed', false)->count(),
        ];

        return view('mitra.ub.index', compact('surveyResponses', 'stats', 'user'));
    }

    /**
     * Show a single UB submission for Mitra users (read-only, user-dashboard layout).
     */
    public function mitraUbShow($id)
    {
        abort_if(!Auth::user()->is_mitra, 403);

        $response = UbSurveyResponse::with('user')->findOrFail($id);
        $user     = Auth::user();

        return view('mitra.ub.show', compact('response', 'user'));
    }

    /**
     * Download the full-data UB PDF for Mitra users. Delegates to BPS UbController.
     */
    public function mitraUbDownload($id)
    {
        abort_if(!Auth::user()->is_mitra, 403);

        return app(\App\Http\Controllers\BPS\UbController::class)->download($id);
    }
}
