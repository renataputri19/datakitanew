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
        // Fill incoming changes without saving yet
        $this->fill($data);
        $this->last_saved_at = now();

        // If Blok IIIA data changes, recompute totals server-side to ensure accuracy
        $keys = array_keys($data);
        $shouldRecalcTotals = array_intersect($keys, ['blok3a_products', 'blok3a_totals']);
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
            $products = [
                [
                    'jenis_barang' => '',
                    'uraian' => '',
                    'satuan' => '',
                    'kbli_5digit' => '',
                    'persen_ekspor' => '',
                    'negara_ekspor' => '',
                    'banyaknya' => array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], ''),
                    'nilai' => array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], ''),
                    'harga_satuan' => array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], ''),
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
                'nilai' => array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], ''),
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
            $totals = array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], 0);
        }

        return $totals;
    }

    /**
     * Calculate totals for Blok IIIA based on products and lainnya data.
     */
    public function calculateBlok3aTotals()
    {
        $products = $this->blok3a_products ?? [];
        $lainnya = $this->blok3a_lainnya ?? [];
        $totals = array_fill_keys(['2024_des', '2025_jan', '2025_feb', '2025_mar', '2025_apr', '2025_mei', '2025_jun', '2025_jul', '2025_agu', '2025_sep', '2025_okt', '2025_nov', '2025_des'], 0);

        // Sum all "nilai" values from products
        foreach ($products as $product) {
            if (isset($product['nilai']) && is_array($product['nilai'])) {
                foreach ($product['nilai'] as $month => $value) {
                    $totals[$month] += (float) ($value ?: 0);
                }
            }
        }

        // Note: blok3a_lainnya (old Rincian 302 monthly) removed; new blok3a_pendapatan_lainnya
        // fields are annual totals independent of the preview/totals logic.

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