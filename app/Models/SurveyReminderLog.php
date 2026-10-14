<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class SurveyReminderLog extends Model
{
    use HasUuid;

    public const STATUS_QUEUED  = 'queued';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT    = 'sent';
    public const STATUS_FAILED  = 'failed';

    protected $fillable = [
        'blast_id',
        'user_id',
        'survey_type',
        'tahun',
        'triwulan',
        'sent_on',
        'email',
        'nama_perusahaan',
        'penghubung',
        'in_progress',
        'status',
        'error',
        'sent_at',
    ];

    protected $casts = [
        'sent_on'     => 'date',
        'sent_at'     => 'datetime',
        'tahun'       => 'integer',
        'triwulan'    => 'integer',
        'in_progress' => 'boolean',
    ];

    public function blast()
    {
        return $this->belongsTo(SurveyReminderBlast::class, 'blast_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
