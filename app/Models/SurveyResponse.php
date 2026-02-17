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
        'memproduksi_barang_sendiri',
        'menyediakan_layanan_makan_minum',
        'penjualan_barang_pihak_lain',
        'aktivitas_jasa',
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
        'blok3a_products' => 'array',
        'blok3a_lainnya' => 'array',
        'blok3a_totals' => 'array',
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
     * Get or create a unified survey response for the given user and survey type.
     * This method ensures only ONE row per (user_id, survey_type) is used/created,
     * and updates the current section marker without creating duplicates.
     */
    public static function getOrCreateForUser($userId, $surveyType = 'sibstr', $section = 'blok1')
    {
        // Always work with the latest record for this user+survey type
        $existing = static::where('user_id', $userId)
            ->where('survey_type', $surveyType)
            ->orderBy('updated_at', 'desc')
            ->first();

        if (!$existing) {
            $existing = static::create([
                'user_id' => $userId,
                'survey_type' => $surveyType,
                'survey_section' => $section,
                'last_saved_at' => now(),
            ]);
        } else {
            // Update the current section marker and timestamp without creating a new row
            $existing->survey_section = $section;
            $existing->last_saved_at = now();
            $existing->save();
        }

        return $existing;
    }

    /**
     * Build a unified view of a user's survey response by merging values
     * from any duplicate rows (preferring latest updated_at values).
     * Returns the latest record instance with merged attributes for display.
     */
    public static function unifiedForUser(int|string $userId, string $surveyType = 'sibstr'): ?self
    {
        $responses = static::where('user_id', $userId)
            ->where('survey_type', $surveyType)
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
        $shouldRecalcTotals = array_intersect($keys, ['blok3a_products', 'blok3a_lainnya', 'blok3a_totals']);
        if (!empty($shouldRecalcTotals)) {
            $this->blok3a_totals = $this->calculateBlok3aTotals();
        }

        $this->save();
        return $this;
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

        // Add lainnya nilai values
        if (isset($lainnya['nilai']) && is_array($lainnya['nilai'])) {
            foreach ($lainnya['nilai'] as $month => $value) {
                $totals[$month] += (float) ($value ?: 0);
            }
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