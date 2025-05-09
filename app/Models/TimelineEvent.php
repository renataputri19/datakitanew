<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TimelineEvent extends Model
{
    protected $fillable = ['task', 'start_date', 'end_date', 'category'];

    // Automatically cast start_date and end_date to Carbon instances
    protected $casts = [
        'start_date' => 'datetime:Y-m-d',
        'end_date' => 'datetime:Y-m-d',
    ];
}
