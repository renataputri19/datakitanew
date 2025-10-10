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
        $blok2Response = SurveyResponse::where('user_id', $user->id)
            ->where('survey_type', 'sibstr')
            ->where('survey_section', 'blok2')
            ->first();

        if (!$blok2Response || $blok2Response->kondisi_perusahaan !== 'masih_aktif') {
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

        return view('survey.sibstr.blok6', compact('surveyResponse'));
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
                // Required fields in LEGALISASI PERUSAHAAN section
                'legalisasi_nama' => 'required|string|max:255',
                'legalisasi_jabatan' => 'required|string|max:255',
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
                'legalisasi_nama.required' => 'Nama penanggung jawab wajib diisi.',
                'legalisasi_jabatan.required' => 'Jabatan penanggung jawab wajib diisi.',
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
            if ($fieldName === 'rata_rata_tenaga_kerja') {
                // Convert to integer for numeric fields, null if empty
                $fieldValue = $fieldValue === '' || $fieldValue === null ? null : (int) $fieldValue;
            } elseif (in_array($fieldName, ['kegiatan_utama_perusahaan'])) {
                // Text fields - ensure they're strings and limit length
                $fieldValue = $fieldValue === null ? null : (string) substr($fieldValue, 0, 1000);
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
                    'jaringan_unit_kegiatan' => 'required|string|in:tunggal,pabrik_unit_produksi,pusat_ada_kegiatan_produksi,kantor_pusat_administrasi_perwakilan',
                    'rata_rata_tenaga_kerja' => 'nullable|numeric|min:0',
                    'kegiatan_utama_perusahaan' => 'nullable|string|max:1000',
                    'kbli_utama' => 'nullable|string|regex:/^\d{5}$/',
                ]);

                $messages = array_merge($messages, [
                    'jaringan_unit_kegiatan.required' => 'Jaringan atau unit kegiatan perusahaan wajib dipilih',
                    'jaringan_unit_kegiatan.in' => 'Pilihan jaringan atau unit kegiatan perusahaan tidak valid',
                    'kbli_utama.regex' => 'KBLI harus berupa 5 digit angka (contoh: 12345)',
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

            // Determine next block based on kondisi_perusahaan
            $nextBlock = $isMasihAktif ? 'blok3a' : 'blok6';

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
            $blok2Response = SurveyResponse::where('user_id', $user->id)
                ->where('survey_type', 'sibstr')
                ->where('survey_section', 'blok2')
                ->first();

            if (!$blok2Response || $blok2Response->kondisi_perusahaan !== 'masih_aktif') {
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

            return response()->json([
                'success' => true,
                'message' => 'Blok IIIA data saved successfully',
                'last_saved_at' => $surveyResponse->last_saved_at->format('H:i:s'),
                'is_completed' => $surveyResponse->is_completed
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