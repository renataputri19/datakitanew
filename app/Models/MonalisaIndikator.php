<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaIndikator extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'aspek_id',
        'name',
        'description',
        'indikator_code',
        'weight',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'order' => 'integer',
    ];

    /**
     * Get the aspek that owns this indikator.
     */
    public function aspek()
    {
        return $this->belongsTo(MonalisaAspek::class, 'aspek_id');
    }

    /**
     * Get the assessments for this indikator.
     */
    public function assessments()
    {
        return $this->hasMany(MonalisaAssessment::class, 'indikator_id');
    }

    /**
     * Get the domain through aspek.
     */
    public function domain()
    {
        return $this->aspek->domain();
    }
}

