<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'news';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'date',
        'category',
        'excerpt',
        'source_url',
        'thumbnail',
        'has_video',
        'video_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'has_video' => 'boolean',
        'date' => 'date',
    ];

    /**
     * Get related news items.
     *
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRelatedNews($limit = 3)
    {
        return self::where('id', '!=', $this->id)
            ->where('category', $this->category)
            ->orWhere(function ($query) {
                $query->where('id', '!=', $this->id)
                      ->whereIn('category', ['BRS', 'Publikasi']);
            })
            ->latest('date')
            ->limit($limit)
            ->get();
    }
}
