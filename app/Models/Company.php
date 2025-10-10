<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_perusahaan',
        'alamat',
    ];

    /**
     * Search companies by name.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $search
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $search)
    {
        return $query->where('nama_perusahaan', 'LIKE', '%' . $search . '%');
    }

    /**
     * Get companies for dropdown with search and limit.
     *
     * @param string|null $search
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getForDropdown($search = null, $limit = 10)
    {
        $query = static::query();
        
        if ($search) {
            $query->search($search);
        }
        
        return $query->orderBy('nama_perusahaan')
                    ->limit($limit)
                    ->get(['id', 'nama_perusahaan', 'alamat']);
    }
}
