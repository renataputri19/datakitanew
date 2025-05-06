<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Institution extends Model
{
    use HasFactory, HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'institution_type',
        'address',
        'phone',
        'website',
        'description',
    ];

    /**
     * Get the users associated with the institution.
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }
}
