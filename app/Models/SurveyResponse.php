<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurveyResponse extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'survey_type',
        'survey_section',
        'tahun',
        'triwulan',
        'kip',
        'idsbr',
        'nama_perusahaan',
        'alamat_pabrik',
        'kabupaten_kota',
        'telepon_fax',
        'penghubung',
        'email',
        'homepage',
        'tahun_mulai_beroperasi',
        'nib',
        'jenis_kawasan',
        'nama_kawasan',
        'nama_pengelola_kawasan',
        'legalisasi_nama',
        'legalisasi_jabatan',
        'legalisasi_jenis_kelamin',
        'legalisasi_nik',
        'bps_provinsi_penghubung',
        'bps_provinsi_telepon',
        'bps_provinsi_fax',
        'bps_provinsi_email',
        'bps_provinsi_alamat',
        // Blok II fields
        'kondisi_perusahaan',
        'jaringan_unit_kegiatan',
        'rata_rata_tenaga_kerja',
        'kegiatan_utama_perusahaan',
        'kbli_utama',
        'jumlah_cabang_dan_unit_usaha',
        'info_kantor_pusat_nama',
        'info_kantor_pusat_alamat',
        'info_kantor_pusat_email',
        'info_kantor_pusat_negara',
        'info_kantor_pusat_provinsi',
        'info_kantor_pusat_kabkota',
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
        // Q207 Tahunan 2025 — detailed worker breakdown (new fields)
        'jumlah_seluruh_pekerja',
        'pekerja_bukan_outsourcing_produksi',
        'pekerja_bukan_outsourcing_lainnya',
        'pekerja_outsourcing_produksi',
        'pekerja_outsourcing_lainnya',
        'produk_utama_perusahaan',
        'memproduksi_barang_sendiri',
        'menyediakan_layanan_makan_minum',
        'penjualan_barang_pihak_lain',
        'aktivitas_jasa',
        // Q212: Sertifikasi produk
        'sertifikasi_keamanan_produk',
        'sertifikasi_kesehatan_keberlanjutan',
        'sertifikasi_kualitas_manajemen',
        'sertifikasi_tidak_ada',
        'sertifikasi_lainnya',
        // Q213: Model industri manufaktur
        'model_industri_oem',
        'model_industri_odm',
        'model_industri_obm',
        'model_industri_tidak_ada',
        'model_industri_lainnya',
        'penggunaan_internet',
        'internet_a1_menerima_pesanan',
        'internet_a2_produksi',
        'internet_a3_distribusi',
        'internet_a4_beli_bahan_baku',
        'internet_a5_promosi',
        'internet_a6_lainnya',
        'pemanfaatan_teknologi_digital',
        'produksi_ramah_lingkungan',
        'penggunaan_input_ramah_lingkungan',
        // Blok VI fields
        'blok6_data',
        'blok6_completed',
        // Blok IIIA fields
        'blok3a_products',
        'blok3a_lainnya',
        'blok3a_totals',
        'blok3a_pendapatan_lainnya',
        'blok3a_q305_online',
        'blok3a_q305a_maklun_nilai',
        'blok3a_q305b_maklun_pct',
        // Blok IIIA-2 / IIIC fields
        'blok3a2_materials',
        'blok3a2_completed',
        // Blok IIIB Industri fields
        'blok3b_industri_data',
        'blok3b_industri_completed',
        // Blok IIIB Non-Industri fields
        'blok3b_nonindustri_data',
        'blok3b_nonindustri_completed',
        // Blok IV fields
        'blok4_data',
        'blok4_completed',
        // Blok V fields
        'blok5_data',
        'blok5_completed',
        'last_saved_at',
        'is_completed',
        'annual_survey_status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_saved_at' => 'datetime',
        'is_completed' => 'boolean',
        'tahun' => 'integer',
        'triwulan' => 'integer',
        // Blok I numeric fields
        'tahun_mulai_beroperasi' => 'integer',
        // Blok II numeric fields
        'rata_rata_tenaga_kerja' => 'integer',
        'jumlah_cabang_dan_unit_usaha' => 'integer',
        'jumlah_bulan_aktif_2025' => 'integer',
        'rata_hari_kerja_bulanan_2025' => 'integer',
        'rata_jam_kerja_per_hari_2025' => 'integer',
        'rata_shift_per_hari_2025' => 'integer',
        'tenaga_kerja_laki_laki' => 'integer',
        'tenaga_kerja_perempuan' => 'integer',
        'tenaga_kerja_produksi' => 'integer',
        'tenaga_kerja_lainnya' => 'integer',
        'tenaga_kerja_asing' => 'integer',
        'tenaga_kerja_outsourcing' => 'integer',
        'jumlah_seluruh_pekerja' => 'integer',
        'pekerja_bukan_outsourcing_produksi' => 'integer',
        'pekerja_bukan_outsourcing_lainnya' => 'integer',
        'pekerja_outsourcing_produksi' => 'integer',
        'pekerja_outsourcing_lainnya' => 'integer',
        'blok3a_products' => 'array',
        'blok3a_lainnya' => 'array',
        'blok3a_totals' => 'array',
        'blok3a_pendapatan_lainnya' => 'array',
        'blok3a_q305_online' => 'decimal:2',
        'blok3a_q305a_maklun_nilai' => 'decimal:2',
        'blok3a_q305b_maklun_pct' => 'decimal:2',
        'blok3a2_materials' => 'array',
        'blok3a2_completed' => 'boolean',
        'blok3b_industri_data' => 'array',
        'blok3b_industri_completed' => 'boolean',
        'blok3b_nonindustri_data' => 'array',
        'blok3b_nonindustri_completed' => 'boolean',
        'blok4_data' => 'array',
        'blok4_completed' => 'boolean',
        'blok5_data' => 'array',
        'blok5_completed' => 'boolean',
        'blok6_data' => 'array',
        'blok6_completed' => 'boolean',
    ];

    /**
     * Get the user that owns the survey response.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope a query to only include responses for a specific survey type.
     */
    public function scopeForSurveyType($query, string $surveyType)
    {
        return $query->where('survey_type', $surveyType);
    }

    /**
     * Scope a query to only include responses for a specific section.
     */
    public function scopeForSection($query, string $section)
    {
        return $query->where('survey_section', $section);
    }

    /**
     * Scope a query to only include completed responses.
     */
    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    /**
     * Return true when the KBLI falls in the Industri range (KBLI prefix 10–33).
     */
    public function isKbliIndustri(): bool
    {
        $kbli = $this->kbli_utama;
        if (empty($kbli) || !preg_match('/^(\d{2})/', $kbli, $m)) {
            return false;
        }
        $prefix = (int) $m[1];
        return $prefix >= 10 && $prefix <= 33;
    }

    /**
     * Return true when Block I (identity) has all required fields filled.
     * Mirrors the required-field rules in SurveyController::saveAll().
     * Used by the sequential block access guard.
     */
    public function isBlok1Complete(): bool
    {
        $required = [
            'nama_perusahaan',
            'alamat_pabrik',
            'kabupaten_kota',
            'telepon_fax',
            'penghubung',
            'email',
            'nib',
            'jenis_kawasan',
            'nama_kawasan',
            'nama_pengelola_kawasan',
            'legalisasi_nama',
            'legalisasi_jabatan',
        ];

        foreach ($required as $field) {
            if (empty($this->{$field})) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true when Block II has been fully submitted.
     * Mirrors the required-field rules in SurveyController::saveAllBlok2().
     * Uses !== null (not empty()) for integer fields because 0 is a valid entry.
     */
    public function isBlok2Complete(): bool
    {
        // R201: always required
        if (empty($this->kondisi_perusahaan)) {
            return false;
        }

        // Non-active companies: only kondisi_perusahaan is required
        if ($this->kondisi_perusahaan !== 'masih_aktif') {
            return true;
        }

        // Active companies: R202 required
        if (empty($this->jaringan_unit_kegiatan)) {
            return false;
        }

        // unit_pembantu_penunjang: no further fields required
        if ($this->jaringan_unit_kegiatan === 'unit_pembantu_penunjang') {
            return true;
        }

        // All other jaringan types: kbli_utama and kegiatan_utama_perusahaan required
        if (empty($this->kbli_utama) || empty($this->kegiatan_utama_perusahaan)) {
            return false;
        }

        $isTahunan = ((int) $this->triwulan) === 0;

        // 208b: produk_utama_perusahaan — required in the blade form for tahunan
        // (nullable in saveAllBlok2() but shown as required on the page)
        if ($isTahunan && empty($this->produk_utama_perusahaan)) {
            return false;
        }

        if ($isTahunan) {
            // Q205/206: bulan & hari kerja (integer, 0 is valid → !== null)
            if ($this->jumlah_bulan_aktif_2025 === null || $this->rata_hari_kerja_bulanan_2025 === null) {
                return false;
            }

            // Q207: detailed worker breakdown (integers, 0 is valid → !== null)
            $workerFields = [
                'jumlah_seluruh_pekerja',
                'tenaga_kerja_laki_laki',
                'tenaga_kerja_perempuan',
                'pekerja_bukan_outsourcing_produksi',
                'pekerja_bukan_outsourcing_lainnya',
                'pekerja_outsourcing_produksi',
                'pekerja_outsourcing_lainnya',
                'tenaga_kerja_asing',
            ];
            foreach ($workerFields as $field) {
                if ($this->{$field} === null) {
                    return false;
                }
            }

            // Q209: produksi & layanan flags (ya/tidak strings)
            if (empty($this->memproduksi_barang_sendiri) ||
                empty($this->menyediakan_layanan_makan_minum) ||
                empty($this->penjualan_barang_pihak_lain) ||
                empty($this->aktivitas_jasa)) {
                return false;
            }

            // Q212: penggunaan internet
            if (empty($this->penggunaan_internet)) {
                return false;
            }

            // Q213: ramah lingkungan
            if (empty($this->produksi_ramah_lingkungan) ||
                empty($this->penggunaan_input_ramah_lingkungan)) {
                return false;
            }
        } else {
            // Triwulanan: single rata_rata_tenaga_kerja (integer, 0 is valid → !== null)
            if ($this->rata_rata_tenaga_kerja === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true when Block IIIA (production revenue) has been submitted.
     * Mirrors the required-field rules shown in the page's 'Data belum lengkap'
     * section: at least one product, and (tahunan only) q302a–f and q305 fields
     * must each be non-null (0 is a valid entry; use !== null, not empty()).
     */
    public function isBlok3aComplete(): bool
    {
        // Use the raw stored attribute to bypass getBlok3aProductsAttribute(),
        // which always injects a default empty-product row even when nothing was saved.
        $rawProducts = $this->attributes['blok3a_products'] ?? null;
        if (empty($rawProducts)) {
            return false;
        }
        $products = is_array($rawProducts) ? $rawProducts : json_decode($rawProducts, true);
        if (!is_array($products) || count($products) === 0) {
            return false;
        }

        // Tahunan-only required fields (section only rendered when triwulan = 0)
        if (((int) $this->triwulan) === 0) {
            // Q302a–f stored in blok3a_pendapatan_lainnya array
            $pl = $this->blok3a_pendapatan_lainnya ?? [];
            foreach (['q302a', 'q302b', 'q302c', 'q302d', 'q302e', 'q302f'] as $key) {
                $val = $pl[$key] ?? null;
                if ($val === null || $val === '') {
                    return false;
                }
            }

            // Q305a, Q305b, Q306 (decimal fields; 0.0 is valid → !== null)
            if ($this->blok3a_q305a_maklun_nilai === null ||
                $this->blok3a_q305b_maklun_pct  === null ||
                $this->blok3a_q305_online        === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true when Blok IIIB Industri has been genuinely completed.
     *
     * The completion flag alone is insufficient because `saveAllBlok3bIndustri`
     * accepts empty-form submissions (all fields are nullable) and still sets
     * blok3b_industri_completed = true.  On an empty submission the computed
     * fields (q309_awal, q309_akhir, q310b_*) are always written as 0.0 while
     * every user-entered field remains null.
     *
     * Required fields differ completely between tahunan and triwulan because
     * blok3b-industri.blade.php renders entirely different question sets under
     * @if(!$isTriwulanan) vs @if($isTriwulanan) — checking the wrong period's
     * fields would always return false (or always true) for the other period.
     *
     * Tahunan required (representative subset from @if(!$isTriwulanan)):
     *   q306_year_awal  – stok bahan baku 1-Jan (Q307a)
     *   q310_beli_modal – nilai pembelian barang modal (Q310)
     *   q311_jual_modal – nilai penjualan barang modal (Q311)
     *   q312_taksir_modal – nilai taksiran barang modal (Q312)
     *   q313_a1, q313_b1 – upah TK non-outsourcing (Q313.a.1, b.1)
     *   q314_a1, q314_b1 – upah TK outsourcing (Q314.a.1, b.1)
     *
     * Triwulan required (representative subset from @if($isTriwulanan)):
     *   q304     – pendapatan royalti/bunga/dividen (Q304)
     *   q306_awal – persediaan bahan baku awal triwulan (Q306)
     *   q310     – total upah dan gaji (Q310)
     *   q311     – penambahan aset tetap (Q311)
     */
    public function isBlok3bIndustriComplete(): bool
    {
        if (!$this->blok3b_industri_completed) {
            return false;
        }

        $data = is_array($this->blok3b_industri_data)
            ? $this->blok3b_industri_data
            : [];

        $isNull = fn (string $key): bool =>
            !isset($data[$key]) || $data[$key] === null || $data[$key] === '';

        $isTahunan = ((int) $this->triwulan) === 0;

        $required = $isTahunan
            ? ['q306_year_awal', 'q310_beli_modal', 'q311_jual_modal',
               'q312_taksir_modal', 'q313_a1', 'q313_b1', 'q314_a1', 'q314_b1']
            : ['q304', 'q306_awal', 'q310', 'q311'];

        foreach ($required as $field) {
            if ($isNull($field)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Return true when Blok IV (Fenomena dan Catatan) is genuinely complete.
     *
     * `saveAllBlok4` sets blok4_completed = true whenever is_completed is sent
     * as true, regardless of whether the four quarterly phenomenon textareas are
     * filled.  A user who clears the textareas after a prior valid save will
     * leave blok4_completed = true while all four data fields are empty.
     *
     * We therefore require the flag AND that all four textarea fields each hold
     * a non-blank string.
     */
    public function isBlok4Complete(): bool
    {
        if (!$this->blok4_completed) {
            return false;
        }

        $data = is_array($this->blok4_data) ? $this->blok4_data : [];

        foreach (['triwulan1', 'triwulan2', 'triwulan3', 'triwulan4'] as $key) {
            $val = $data[$key] ?? null;
            if ($val === null || trim((string) $val) === '') {
                return false;
            }
        }

        return true;
    }

    /**
     * Check whether the 2025 annual Q207 detailed worker breakdown is fully filled.
     * Used to gate Triwulanan 2026 access for existing users.
     */
    public function isQ207TahunanComplete(): bool
    {
        $required = [
            'jumlah_seluruh_pekerja',
            'tenaga_kerja_laki_laki',
            'tenaga_kerja_perempuan',
            'pekerja_bukan_outsourcing_produksi',
            'pekerja_bukan_outsourcing_lainnya',
            'pekerja_outsourcing_produksi',
            'pekerja_outsourcing_lainnya',
            'tenaga_kerja_asing',
        ];
        foreach ($required as $field) {
            if (is_null($this->{$field})) {
                return false;
            }
        }
        return true;
    }

    /**
     * Static helper — returns true when the given user's 2025 tahunan row is
     * completed AND all Q207 worker-detail fields are filled.
     */
    public static function isTahunanQ207CompleteForUser(int|string $userId): bool
    {
        $row = static::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', 2025)
            ->where('triwulan', 0)
            ->where('is_completed', true)
            ->first();

        return $row ? $row->isQ207TahunanComplete() : false;
    }

    /**
     * Authoritative gate for 2026 Triwulan access (FINISH_SURVEY status).
     *
     * Returns true only when the user's 2025 Tahunan survey record has
     * annual_survey_status = 'FINISH_SURVEY', which is set exclusively by
     * SurveyController::finishSurvey() when the user explicitly submits
     * Block 6 through the finish flow.
     *
     * Legacy rows where is_completed was set by the old mechanism will have
     * annual_survey_status = null and will NOT pass this check — forcing those
     * users to go through Block 6 again before Triwulanan is unlocked.
     */
    public static function isTahunanFullyCompletedForUser(int|string $userId): bool
    {
        return static::where('user_id', $userId)
            ->where('survey_type', 'sibstr')
            ->where('tahun', 2025)
            ->where('triwulan', 0)
            ->where('annual_survey_status', 'FINISH_SURVEY')
            ->exists();
    }

    /**
     * Get or create a survey response for the given user, type, and period.
     * tahun defaults to 2025. triwulan=0 means annual/legacy; 1–4 means quarterly.
     */
    public static function getOrCreateForUser($userId, $surveyType = 'sibstr', $section = 'blok1', $tahun = 2025, $triwulan = 0)
    {
        $existing = static::where('user_id', $userId)
            ->where('survey_type', $surveyType)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->first();

        if (!$existing) {
            $existing = static::create([
                'user_id'        => $userId,
                'survey_type'    => $surveyType,
                'survey_section' => $section,
                'tahun'          => $tahun,
                'triwulan'       => $triwulan,
                'last_saved_at'  => now(),
            ]);
        } else {
            $existing->survey_section = $section;
            $existing->last_saved_at  = now();
            $existing->save();
        }

        return $existing;
    }

    /**
     * Build a unified view of a user's survey response for a specific period.
     * triwulan=0 = annual/legacy; 1–4 = quarterly.
     */
    public static function unifiedForUser(int|string $userId, string $surveyType = 'sibstr', int $tahun = 2025, int $triwulan = 0): ?self
    {
        $responses = static::where('user_id', $userId)
            ->where('survey_type', $surveyType)
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->orderBy('updated_at', 'desc')
            ->get();

        if ($responses->isEmpty()) {
            return null;
        }

        // Use the latest as the base
        $base = $responses->first();

        // Determine which columns to merge
        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($base->getTable());
        $skip = ['id', 'user_id', 'survey_type', 'survey_section', 'created_at', 'updated_at'];

        foreach ($responses->slice(1) as $resp) {
            foreach ($columns as $col) {
                if (in_array($col, $skip, true)) {
                    continue;
                }
                $current = $base->{$col};
                $candidate = $resp->{$col};
                if (self::isEmptyValue($current) && !self::isEmptyValue($candidate)) {
                    $base->{$col} = $candidate;
                }
            }
        }

        return $base;
    }

    /**
     * Determine emptiness for merging purposes.
     */
    protected static function isEmptyValue($value): bool
    {
        if (is_null($value)) {
            return true;
        }
        if (is_string($value)) {
            return trim($value) === '';
        }
        if (is_array($value)) {
            return empty($value);
        }
        if (is_bool($value)) {
            return false;
        }
        if (is_numeric($value)) {
            // Treat 0 as a legitimate value
            return false;
        }
        return false;
    }

    /**
     * Update the survey response with auto-save data.
     */
    public function updateWithAutoSave(array $data)
    {
        // Merge blok3a_lainnya month values instead of replacing the whole array
        if (isset($data['blok3a_lainnya'])) {
            $existing = $this->blok3a_lainnya ?? ['uraian' => '', 'nilai' => []];
            $incoming = $data['blok3a_lainnya'];
            if (isset($incoming['nilai']) && is_array($incoming['nilai'])) {
                $existing['nilai'] = array_merge($existing['nilai'] ?? [], $incoming['nilai']);
            }
            if (isset($incoming['uraian'])) {
                $existing['uraian'] = $incoming['uraian'];
            }
            $data['blok3a_lainnya'] = $existing;
        }

        // Fill incoming changes without saving yet
        $this->fill($data);
        $this->last_saved_at = now();

        // If Blok IIIA data changes, recompute totals server-side to ensure accuracy
        $keys = array_keys($data);
        $shouldRecalcTotals = array_intersect($keys, ['blok3a_products', 'blok3a_totals', 'blok3a_lainnya']);
        if (!empty($shouldRecalcTotals)) {
            $this->blok3a_totals = $this->calculateBlok3aTotals();
        }

        $this->save();
        return $this;
    }

    /**
     * Determine the current available triwulan number based on the current month.
     * A quarter is available for data entry once it has ended.
     * Month 4–6  → TW1 is available (TW1 ended in March)
     * Month 7–9  → TW1 + TW2 available
     * Month 10–12 → TW1 + TW2 + TW3 available
     * Month 1–3 of next year → TW4 of previous year available (handled by caller).
     *
     * Returns an array of available triwulan numbers (1–4) for the given year.
     */
    public static function availableTriwulan(int $tahun): array
    {
        // Triwulanan reporting is only available starting from 2026.
        if ($tahun < 2026) {
            return [];
        }

        $now = now();
        $currentYear  = (int) $now->format('Y');
        $currentMonth = (int) $now->format('n');

        if ($tahun < $currentYear) {
            return [1, 2, 3, 4];
        }

        if ($tahun > $currentYear) {
            return [];
        }

        // Same year — return quarters whose last month < current month
        $available = [];
        if ($currentMonth >= 4)  { $available[] = 1; }
        if ($currentMonth >= 7)  { $available[] = 2; }
        if ($currentMonth >= 10) { $available[] = 3; }
        // TW4 becomes available from January of the next year

        return $available;
    }

    /**
     * Return the triwulan label string.
     */
    public static function triwulanLabel(int $triwulan): string
    {
        return match ($triwulan) {
            1 => 'Triwulan I (Jan–Mar)',
            2 => 'Triwulan II (Apr–Jun)',
            3 => 'Triwulan III (Jul–Sep)',
            4 => 'Triwulan IV (Okt–Des)',
            default => 'Tahunan',
        };
    }

    /**
     * Return the 3 calendar months (as ['YYYY_mmm' => 'Label'] pairs)
     * that belong to a triwulan, using the same key format as blok3a_products.
     */
    public static function triwulanMonthKeys(int $tahun, int $triwulan): array
    {
        $monthMap = [
            1 => ['jan' => 'Jan', 'feb' => 'Feb', 'mar' => 'Mar'],
            2 => ['apr' => 'Apr', 'mei' => 'Mei', 'jun' => 'Jun'],
            3 => ['jul' => 'Jul', 'agu' => 'Agu', 'sep' => 'Sep'],
            4 => ['okt' => 'Okt', 'nov' => 'Nov', 'des' => 'Des'],
        ];

        $months = $monthMap[$triwulan] ?? [];
        $result = [];
        foreach ($months as $abbr => $label) {
            $result["{$tahun}_{$abbr}"] = "{$label} {$tahun}";
        }
        return $result;
    }

    /**
     * Get the jenis kawasan options.
     */
    public static function getJenisKawasanOptions()
    {
        return [
            'ekonomi_khusus' => 'Kawasan Ekonomi Khusus',
            'industri' => 'Kawasan Industri',
            'luar_kawasan' => 'Di Luar Kawasan',
        ];
    }

    /**
     * Get Blok IIIA products data with default structure.
     */
    public function getBlok3aProductsAttribute($value)
    {
        // Support both casted arrays and raw JSON strings
        $products = is_array($value)
            ? $value
            : (is_string($value) && strlen($value) > 0 ? json_decode($value, true) : []);

        // Ensure we have at least one empty product row when none saved
        if (empty($products)) {
            $monthKeys = $this->getBlok3aMonthKeys();
            $products = [
                [
                    'jenis_barang' => '',
                    'uraian' => '',
                    'satuan' => '',
                    'kbli_5digit' => '',
                    'persen_ekspor' => '',
                    'negara_ekspor' => '',
                    'banyaknya'    => array_fill_keys($monthKeys, ''),
                    'nilai'        => array_fill_keys($monthKeys, ''),
                    'harga_satuan' => array_fill_keys($monthKeys, ''),
                ]
            ];
        }

        return $products;
    }

    /**
     * Get Blok IIIA lainnya data with default structure.
     */
    public function getBlok3aLainnyaAttribute($value)
    {
        // Support both casted arrays and raw JSON strings
        $lainnya = is_array($value)
            ? $value
            : (is_string($value) && strlen($value) > 0 ? json_decode($value, true) : []);

        // Default structure for "Lainnya" row
        if (empty($lainnya)) {
            $lainnya = [
                'uraian' => '',
                'nilai'  => array_fill_keys($this->getBlok3aMonthKeys(), ''),
            ];
        }

        return $lainnya;
    }

    /**
     * Get Blok IIIA totals data with default structure.
     */
    public function getBlok3aTotalsAttribute($value)
    {
        // Support both casted arrays and raw JSON strings
        $totals = is_array($value)
            ? $value
            : (is_string($value) && strlen($value) > 0 ? json_decode($value, true) : []);

        // Default structure for totals row
        if (empty($totals)) {
            $totals = array_fill_keys($this->getBlok3aMonthKeys(), 0);
        }

        return $totals;
    }

    /**
     * Return the ordered month keys for Blok IIIA based on this response's tahun/triwulan.
     * For triwulanan: Dec of previous year + 3 months of the current quarter.
     * For tahunan:    Dec of previous year + all 12 months of the current year.
     */
    private function getBlok3aMonthKeys(): array
    {
        $tahun    = (int) ($this->tahun    ?? date('Y'));
        $triwulan = (int) ($this->triwulan ?? 0);
        $prevYear = $tahun - 1;

        if ($triwulan > 0) {
            $twMonths = [
                1 => ['jan', 'feb', 'mar'],
                2 => ['apr', 'mei', 'jun'],
                3 => ['jul', 'agu', 'sep'],
                4 => ['okt', 'nov', 'des'],
            ];
            $keys = ["{$prevYear}_des"];
            foreach (($twMonths[$triwulan] ?? []) as $m) {
                $keys[] = "{$tahun}_{$m}";
            }
            return $keys;
        }

        $allMonths = ['jan','feb','mar','apr','mei','jun','jul','agu','sep','okt','nov','des'];
        $keys = ["{$prevYear}_des"];
        foreach ($allMonths as $m) {
            $keys[] = "{$tahun}_{$m}";
        }
        return $keys;
    }

    /**
     * Calculate totals for Blok IIIA based on products data.
     * Uses dynamic month keys to avoid hardcoded year references.
     */
    public function calculateBlok3aTotals()
    {
        $products = $this->blok3a_products ?? [];
        $totals = [];

        // Sum all "nilai" values from products dynamically
        foreach ($products as $product) {
            if (isset($product['nilai']) && is_array($product['nilai'])) {
                foreach ($product['nilai'] as $month => $value) {
                    if (!array_key_exists($month, $totals)) {
                        $totals[$month] = 0;
                    }
                    $totals[$month] += (float) ($value ?: 0);
                }
            }
        }

        // Add lainnya (302) monthly nilai to totals
        $lainnyaNilai = ($this->blok3a_lainnya['nilai'] ?? []);
        foreach ($lainnyaNilai as $month => $nilai) {
            if ($nilai !== null && $nilai !== '' && (float)$nilai != 0) {
                $totals[$month] = ($totals[$month] ?? 0) + (float)$nilai;
            }
        }

        // If no data at all, return zero-filled structure for this period
        if (empty($totals)) {
            $totals = array_fill_keys($this->getBlok3aMonthKeys(), 0);
        }

        return $totals;
    }

    /**
     * Get the catatan from blok6_data.
     */
    public function getCatatanAttribute()
    {
        return $this->blok6_data['catatan'] ?? null;
    }
}