<?php

namespace App\Models;

use App\Helpers\StorageHelper;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'date',
        'thumbnail',
        'url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Get the thumbnail URL.
     *
     * @return string|null
     */
    public function getThumbnailUrlAttribute()
    {
        return StorageHelper::url($this->thumbnail);
    }

    /**
     * Get the formatted date.
     *
     * @return string
     */
    public function getFormattedDateAttribute()
    {
        return $this->date->format('d M Y');
    }
}
