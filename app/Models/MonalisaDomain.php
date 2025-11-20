<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaDomain extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'name',
        'description',
        'domain_number',
        'weight',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'domain_number' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the aspeks for this domain.
     */
    public function aspeks()
    {
        return $this->hasMany(MonalisaAspek::class, 'domain_id')->orderBy('order');
    }

    /**
     * Get all indikators through aspeks.
     */
    public function indikators()
    {
        return $this->hasManyThrough(MonalisaIndikator::class, MonalisaAspek::class, 'domain_id', 'aspek_id');
    }
}

