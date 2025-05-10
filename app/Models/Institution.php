<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Institution extends Model
{
    use HasFactory, HasUuid;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'type',
        'institution_type',
        'academic_type',
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
