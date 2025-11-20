<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaAspek extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'domain_id',
        'name',
        'description',
        'aspek_number',
        'weight',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'aspek_number' => 'integer',
        'order' => 'integer',
    ];

    /**
     * Get the domain that owns this aspek.
     */
    public function domain()
    {
        return $this->belongsTo(MonalisaDomain::class, 'domain_id');
    }

    /**
     * Get the indikators for this aspek.
     */
    public function indikators()
    {
        return $this->hasMany(MonalisaIndikator::class, 'aspek_id')->orderBy('order');
    }
}

