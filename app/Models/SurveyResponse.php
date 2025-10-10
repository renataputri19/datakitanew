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
        'nib',
        'jenis_kawasan',
        'nama_kawasan',
        'legalisasi_nama',
        'legalisasi_jabatan',
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
        // Blok VI fields
        'catatan',
        // Blok IIIA fields
        'blok3a_products',
        'blok3a_lainnya',
        'blok3a_totals',
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
        'blok3a_products' => 'array',
        'blok3a_lainnya' => 'array',
        'blok3a_totals' => 'array',
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
     * Get or create a survey response for the given user and section.
     */
    public static function getOrCreateForUser($userId, $surveyType = 'sibstr', $section = 'blok1')
    {
        return static::firstOrCreate(
            [
                'user_id' => $userId,
                'survey_type' => $surveyType,
                'survey_section' => $section,
            ],
            [
                'last_saved_at' => now(),
            ]
        );
    }

    /**
     * Update the survey response with auto-save data.
     */
    public function updateWithAutoSave(array $data)
    {
        $data['last_saved_at'] = now();
        
        return $this->update($data);
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
        $products = $value ? json_decode($value, true) : [];

        // Ensure we have at least one empty product row
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
        $lainnya = $value ? json_decode($value, true) : [];

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
        $totals = $value ? json_decode($value, true) : [];

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
}