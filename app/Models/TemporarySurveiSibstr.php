<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TemporarySurveiSibstr extends Model
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'temporary_survei_sibstr';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'jabatan',
        'no_hp',
        'email',
        'company_id',
        'perusahaan',
        'alamat',
        'jenis_perusahaan',
        'file_paths',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'file_paths' => 'array',
    ];

    /**
     * Get the company that owns the survey response.
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Get the jenis perusahaan options.
     *
     * @return array
     */
    public static function getJenisPerusahaanOptions()
    {
        return [
            'industri' => 'Industri',
            'non-industri' => 'Non-Industri',
        ];
    }
}
