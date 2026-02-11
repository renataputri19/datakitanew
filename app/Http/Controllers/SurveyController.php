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
     * Display the SIBSTR survey form (Block 1).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok1()
    {
        $user = Auth::user();
        
        // Get or create survey response for this user
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok1');
        
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
        
        return view('survey.sibstr.blok1', compact('surveyResponse', 'jenisKawasanOptions', 'bpsRiData'));
    }

    /**
     * Display the SIBSTR survey form (Block 2).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok2()
    {
        $user = Auth::user();

        // Get or create survey response for this user
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok2');

        return view('survey.sibstr.blok2', compact('surveyResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIA).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3a()
    {
        $user = Auth::user();

        // Get or create survey response for this user
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a');

        // Check if user should access this block (kondisi_perusahaan must be 'masih_aktif')
        // Use the latest survey response regardless of section, since getOrCreateForUser
        // updates the single row's survey_section as the user navigates.
        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse || $latestResponse->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()->route('survey.sibstr.blok6')->with('warning', 'Blok IIIA hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".');
        }

        return view('survey.sibstr.blok3a', compact('surveyResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block 6).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok6()
    {
        $user = Auth::user();

        // Get or create survey response for this user
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok6');

        // Fetch latest response values to control conditional navigation and hints
        $latestResponse = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        $kondisiPerusahaan = $latestResponse?->kondisi_perusahaan;
        // Also fetch R202 (jaringan_unit_kegiatan) to control back navigation when option 'e' is selected
        $jaringanUnitKegiatan = $latestResponse?->jaringan_unit_kegiatan;

        return view('survey.sibstr.blok6', compact('surveyResponse', 'kondisiPerusahaan', 'jaringanUnitKegiatan'));
    }

    /**
     * Display the SIBSTR survey form (Block IV - Fenomena dan Catatan).
     */
    public function sibstrBlok4()
    {
        $user = Auth::user();

        // Get or create survey response for Blok 4
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4');

        // Fetch KBLI from latest response to help decide back navigation to 3B variant
        $latestResponseBlok = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        $kbli = $latestResponseBlok?->kbli_utama;
        $kbliPrefix = null;
        if ($kbli && preg_match('/^(\d{2})/', $kbli, $m)) {
            $kbliPrefix = (int) $m[1];
        }

        return view('survey.sibstr.blok4', compact('surveyResponse', 'kbliPrefix'));
    }

    /**
     * Display the SIBSTR survey form (Block V - Kondisi dan Prospek Usaha).
     */
    public function sibstrBlok5()
    {
        $user = Auth::user();

        // Get or create survey response for Blok 5
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5');

        return view('survey.sibstr.blok5', compact('surveyResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIB Industri).
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3bIndustri()
    {
        $user = Auth::user();

        // Ensure perusahaan masih aktif menggunakan latest response
        $latestResponse3b = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse3b || $latestResponse3b->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()->route('survey.sibstr.blok6')->with('warning', 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.');
        }

        // Create or get Blok 3B Industri response
        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri');

        return view('survey.sibstr.blok3b-industri', compact('surveyResponse'));
    }

    /**
     * Display the SIBSTR survey form (Block IIIB Non-Industri) - placeholder.
     *
     * @return \Illuminate\View\View
     */
    public function sibstrBlok3bNonIndustri()
    {
        $user = Auth::user();
        // Pastikan perusahaan masih aktif menggunakan latest response
        $latestResponse3bNon = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$latestResponse3bNon || $latestResponse3bNon->kondisi_perusahaan !== 'masih_aktif') {
            return redirect()->route('survey.sibstr.blok6')->with('warning', 'Blok IIIB hanya dapat diakses jika perusahaan masih aktif.');
        }

        $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri');
        return view('survey.sibstr.blok3b-nonindustri', compact('surveyResponse'));
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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

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
                // Optional NIK: validate only if provided (16 digits numeric)
                'legalisasi_nik' => 'nullable|string|regex:/^[0-9]{16}$/|size:16',
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
                'legalisasi_nik.regex' => 'NIK harus berupa 16 digit angka.',
                'legalisasi_nik.size' => 'NIK harus berupa 16 digit angka.',
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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

            // Mark as completed if requested
            if ($request->has('is_completed')) {
                $updateData['is_completed'] = $request->boolean('is_completed');
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
            ];

            if (in_array($fieldName, $numericFields, true)) {
                // Convert to integer for numeric fields, null if empty
                $fieldValue = ($fieldValue === '' || $fieldValue === null) ? null : (int) $fieldValue;
            } elseif ($fieldName === 'kegiatan_utama_perusahaan') {
                // Text fields - ensure they're strings and limit length
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 1000);
            } elseif ($fieldName === 'kbli_utama') {
                // KBLI should be 5 digits when provided; store as trimmed string
                $fieldValue = $fieldValue === null ? null : substr(preg_replace('/\D/', '', (string) $fieldValue), 0, 5);
            } else {
                // Other fields (radio buttons, text inputs) - ensure they're strings and limit length
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 255);
            }

            // Blok 2 specific
            $surveyType = 'sibstr';
            $surveySection = 'blok2';

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

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
                $rules = array_merge($rules, [
                    'jaringan_unit_kegiatan' => 'required|string|in:tunggal,pabrik_unit_produksi,pusat_ada_kegiatan_produksi,kantor_pusat_administrasi_perwakilan,unit_pembantu_penunjang',

                    // Q203 required only when 202 = c or d
                    'jumlah_cabang_dan_unit_usaha' => 'nullable|integer|min:0|required_if:jaringan_unit_kegiatan,pusat_ada_kegiatan_produksi|required_if:jaringan_unit_kegiatan,kantor_pusat_administrasi_perwakilan',

                    // Q204 sub-fields required only when 202 = b
                    'info_kantor_pusat_nama' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                    'info_kantor_pusat_alamat' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                    'info_kantor_pusat_email' => 'nullable|email|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                    'info_kantor_pusat_negara' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                    'info_kantor_pusat_provinsi' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',
                    'info_kantor_pusat_kabkota' => 'nullable|string|required_if:jaringan_unit_kegiatan,pabrik_unit_produksi',

                    // 205 onwards should NOT be required when 202 = e (unit_pembantu_penunjang)
                    'jumlah_bulan_aktif_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:12',
                    'rata_hari_kerja_bulanan_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0|max:31',
                    'rata_jam_kerja_per_hari_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|numeric|min:0',
                    'rata_shift_per_hari_2025' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_laki_laki' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_perempuan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_produksi' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_lainnya' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_asing' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'tenaga_kerja_outsourcing' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|integer|min:0',
                    'kegiatan_utama_perusahaan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|max:1000',
                    'kbli_utama' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|regex:/^\d{5}$/',
                    'memproduksi_barang_sendiri' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                    'menyediakan_layanan_makan_minum' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                    'penjualan_barang_pihak_lain' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                    'aktivitas_jasa' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                    'penggunaan_internet' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',

                    // When 210 = Ya and not 202 = e, require 210a and 210b
                    'internet_a1_menerima_pesanan' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'internet_a2_produksi' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'internet_a3_distribusi' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'internet_a4_beli_bahan_baku' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'internet_a5_promosi' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'internet_a6_lainnya' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',
                    'pemanfaatan_teknologi_digital' => 'exclude_if:jaringan_unit_kegiatan,unit_pembantu_penunjang|required_if:penggunaan_internet,ya|in:ya,tidak',

                    'produksi_ramah_lingkungan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya_seluruh,ya_sebagian,tidak',
                    'penggunaan_input_ramah_lingkungan' => 'required_unless:jaringan_unit_kegiatan,unit_pembantu_penunjang|string|in:ya,tidak',
                ]);

                $messages = array_merge($messages, [
                    'jaringan_unit_kegiatan.required' => 'Jaringan atau unit kegiatan perusahaan wajib dipilih',
                    'jaringan_unit_kegiatan.in' => 'Pilihan jaringan atau unit kegiatan perusahaan tidak valid',

                    // Q203 messages
                    'jumlah_cabang_dan_unit_usaha.required_if' => 'Pertanyaan 203 wajib diisi saat R202 = c atau d',

                    // Q204 messages
                    'info_kantor_pusat_nama.required_if' => 'Nama Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_alamat.required_if' => 'Alamat Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_email.required_if' => 'Email Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_email.email' => 'Format email Kantor Pusat tidak valid',
                    'info_kantor_pusat_negara.required_if' => 'Negara Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_provinsi.required_if' => 'Provinsi Kantor Pusat wajib diisi saat R202 = b',
                    'info_kantor_pusat_kabkota.required_if' => 'Kabupaten/Kota Kantor Pusat wajib diisi saat R202 = b',

                    // Other messages
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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

            // Mark as completed if requested
            if ($request->has('is_completed')) {
                $updateData['is_completed'] = $request->boolean('is_completed');
            }

            $surveyResponse->updateWithAutoSave($updateData);

            // Determine next block strictly by kondisi_perusahaan per BLOK3A_IMPLEMENTATION.md
            // If perusahaan masih aktif → proceed to Blok 3A
            // Otherwise → skip to Blok 6
            if ($isMasihAktif) {
                $nextBlock = 'blok3a';
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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4');

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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5');

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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

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

            // Validate the request for Blok 4 fields (text areas)
            $validator = Validator::make($request->all(), [
                'blok4.triwulan1' => 'nullable|string|max:3000',
                'blok4.triwulan2' => 'nullable|string|max:3000',
                'blok4.triwulan3' => 'nullable|string|max:3000',
                'blok4.triwulan4' => 'nullable|string|max:3000',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4');

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

            // Minimal validation: ensure array shape for blok5
            $validator = Validator::make($request->all(), [
                'blok5' => 'nullable|array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5');

            $data = $request->input('blok5', []);

            $surveyResponse->updateWithAutoSave([
                'blok5_data' => $data,
                'blok5_completed' => $request->boolean('is_completed', false),
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
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok4');
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
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok5');
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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

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
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a');

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
                                'banyaknya' => [],
                                'nilai' => [],
                                'harga_satuan' => [],
                            ];
                        }

                        $currentData[$productIndex]['jenis_barang'] = $fieldValue;
                        $surveyResponse->updateWithAutoSave(['blok3a_products' => $currentData]);
                    }
                    // Pattern: blok3a_products[0][uraian] or blok3a_products[0][satuan]
                    elseif (preg_match('/^blok3a_products\[(\d+)\]\[(uraian|satuan)\]$/', $fieldName, $matches)) {
                        $productIndex = (int) $matches[1];
                        $fieldType = $matches[2]; // uraian or satuan

                        if (!isset($currentData[$productIndex])) {
                            $currentData[$productIndex] = [
                                'jenis_barang' => '',
                                'uraian' => '',
                                'satuan' => '',
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
                else {
                    // Fallback for other blok3a fields
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

            // Check conditional access
            $latestResponse3a = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->orderBy('updated_at', 'desc')
                ->first();

            if (!$latestResponse3a || $latestResponse3a->kondisi_perusahaan !== 'masih_aktif') {
                return response()->json([
                    'success' => false,
                    'message' => 'Blok IIIA hanya dapat diakses jika kondisi perusahaan adalah "Masih Aktif".'
                ], 403);
            }

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3a');

            // Prepare update data (exclude _token and other non-field data)
            $updateData = $request->except(['_token']);

            // Mark as completed if requested
            if ($request->has('is_completed')) {
                $updateData['is_completed'] = $request->boolean('is_completed');
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

            // Get or create survey response
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, $surveyType, $surveySection);

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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri');

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

            // Guard: only allow if perusahaan masih aktif (use latest response)
            $latestResponse3bI = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri');
            $data = $sanitized;

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

            // Compute asset total Q318c = q318a + q318b
            $q318a = (float) ($data['q318a'] ?? 0);
            $q318b = (float) ($data['q318b'] ?? 0);
            $data['q318c'] = $q318a + $q318b;

            // Compute ownership total Q319g = sum of 319a..319f
            $q319a = (float) ($data['q319a'] ?? 0);
            $q319b = (float) ($data['q319b'] ?? 0);
            $q319c = (float) ($data['q319c'] ?? 0);
            $q319d = (float) ($data['q319d'] ?? 0);
            $q319e = (float) ($data['q319e'] ?? 0);
            $q319f = (float) ($data['q319f'] ?? 0);
            $data['q319g'] = $q319a + $q319b + $q319c + $q319d + $q319e + $q319f;

            // Merge sanitized payload back into the request for validation if completing
            $request->merge(['blok3b_industri' => $data]);

            if ($isCompleted) {
                // Validation rules for numeric currency-like fields (non-negative) and percentages (0..100)
                $rules = [
                    'blok3b_industri.q304a' => 'nullable|numeric|min:0',
                    'blok3b_industri.q304b' => 'nullable|numeric|min:0',
                    'blok3b_industri.q305_online' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q306_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q306_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q306_year_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q306_year_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_year_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q307_year_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_year_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q308_year_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q309_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q309_akhir' => 'nullable|numeric|min:0',
                    'blok3b_industri.q310b_awal' => 'nullable|numeric|min:0',
                    'blok3b_industri.q310b_akhir' => 'nullable|numeric|min:0',
            'blok3b_industri.q310' => 'nullable|numeric|min:0',
            'blok3b_industri.q310_year' => 'nullable|numeric|min:0',
            // Q311 updated structure: require all four subfields
            'blok3b_industri.q311a' => 'required|numeric|min:0',
            'blok3b_industri.q311b' => 'required|numeric|min:0',
            'blok3b_industri.q311b1' => 'required|numeric|min:0',
            'blok3b_industri.q311b2' => 'required|numeric|min:0',
            'blok3b_industri.q312' => 'nullable|numeric|min:0',
            'blok3b_industri.q312_year' => 'nullable|numeric|min:0',
            'blok3b_industri.q313' => 'nullable|numeric|min:0',
            'blok3b_industri.q313_year' => 'nullable|numeric|min:0',
                    'blok3b_industri.q315a' => 'nullable|numeric|min:0',
                    'blok3b_industri.q315b' => 'nullable|numeric|min:0',
                    'blok3b_industri.q314' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q315' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q318a' => 'nullable|numeric|min:0',
                    'blok3b_industri.q318b' => 'nullable|numeric|min:0',
                    'blok3b_industri.q318c' => 'nullable|numeric|min:0',
                    'blok3b_industri.q318c_range' => 'nullable|in:1,2,3,4,5',
                    'blok3b_industri.q318d_area' => 'nullable|numeric|min:0',
                    'blok3b_industri.q319a' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319b' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319c' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319d' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319e' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319f' => 'nullable|numeric|min:0|max:100',
                    'blok3b_industri.q319g' => 'nullable|numeric|min:0|max:100',
                ];

                // Human-friendly attribute labels and messages
                $attributes = [
                    'blok3b_industri.q319g' => 'Total kepemilikan (Q319g)',
                ];

                $messages = [
                    // Make the q319g message more human-friendly
                    'blok3b_industri.q319g.max' => 'Total kepemilikan tidak boleh melebihi 100%.'
                ];

                $validator = Validator::make($request->all(), $rules, $messages, $attributes);

                // Ensure Q311.b (total selama tahun 2025) is at least b.1 + b.2
                $validator->after(function($v) use ($data) {
                    $b  = (float) ($data['q311b']  ?? 0);
                    $b1 = (float) ($data['q311b1'] ?? 0);
                    $b2 = (float) ($data['q311b2'] ?? 0);
                    if ($b < ($b1 + $b2)) {
                        $v->errors()->add('blok3b_industri[q311b]', 'Nilai b (tahun 2025) harus ≥ b.1 + b.2');
                    }
                });

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

            // Next block after 3B is Blok 4
            $nextBlock = 'blok4';

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
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_industri');
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
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri');
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

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri');

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

            // Guard: only allow if perusahaan masih aktif (use latest response)
            $latestResponse3bN = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
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
                'blok3b_nonindustri.q303' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q304' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q305' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q306a' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q306b' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q307a' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q307b' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q308a' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q308b' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q309a' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q309b' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q310' => 'nullable|numeric|min:0',
                // Q311 updated structure: require all four subfields
                'blok3b_nonindustri.q311a' => 'required|numeric|min:0',
                'blok3b_nonindustri.q311b' => 'required|numeric|min:0',
                'blok3b_nonindustri.q311b1' => 'required|numeric|min:0',
                'blok3b_nonindustri.q311b2' => 'required|numeric|min:0',
                'blok3b_nonindustri.q312' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q313' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q315a' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q315b' => 'nullable|numeric|min:0',
                'blok3b_nonindustri.q314' => 'nullable|numeric|min:0|max:100',
                'blok3b_nonindustri.q315' => 'nullable|numeric|min:0|max:100',
            ];

            $validator = Validator::make($request->all(), $rules);

            // Ensure Q311.b (total selama tahun 2025) is at least b.1 + b.2
            $validator->after(function($v) use ($sanitized) {
                $b  = (float) ($sanitized['q311b']  ?? 0);
                $b1 = (float) ($sanitized['q311b1'] ?? 0);
                $b2 = (float) ($sanitized['q311b2'] ?? 0);
                if ($b < ($b1 + $b2)) {
                    $v->errors()->add('blok3b_nonindustri[q311b]', 'Nilai b (tahun 2025) harus ≥ b.1 + b.2');
                }
            });

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok3b_nonindustri');

            // Use sanitized and computed data for storage
            $data = $sanitized;

            $surveyResponse->updateWithAutoSave([
                'blok3b_nonindustri_data' => $data,
                'blok3b_nonindustri_completed' => $request->boolean('is_completed', false),
            ]);

            $nextBlock = 'blok4';

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
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function finishSurvey(Request $request)
    {
        try {
            $user = Auth::user();

            // Get the survey response for Blok 6
            $surveyResponse = SurveyResponse::getOrCreateForUser($user->id, 'sibstr', 'blok6');

            // Save any final data and mark as completed
            $updateData = $request->except(['_token']);
            $updateData['is_completed'] = true;

            $surveyResponse->updateWithAutoSave($updateData);

            return response()->json([
                'success' => true,
                'message' => 'Survey completed successfully',
                'completed_at' => $surveyResponse->last_saved_at->format('Y-m-d H:i:s')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to complete survey: ' . $e->getMessage()
            ], 500);
        }
    }
}