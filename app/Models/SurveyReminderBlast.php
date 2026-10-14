<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** One reminder send run — automatic (scheduler) or manual (a BPS user). */
class SurveyReminderBlast extends Model
{
    use HasUuid;

    public const TRIGGER_OTOMATIS = 'otomatis';
    public const TRIGGER_MANUAL   = 'manual';

    protected $fillable = [
        'survey_type',
        'tahun',
        'triwulan',
        'trigger',
        'target_statuses',
        'sources',
        'template_id',
        'subject',
        'body',
        'extra_message',
        'created_by',
    ];

    protected $casts = [
        'tahun'           => 'integer',
        'triwulan'        => 'integer',
        'target_statuses' => 'array',
        'sources'         => 'array',
    ];

    public function logs()
    {
        return $this->hasMany(SurveyReminderLog::class, 'blast_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function template()
    {
        return $this->belongsTo(SurveyReminderTemplate::class, 'template_id');
    }

    /** Adds queued/sending/sent/failed counts as `{status}_count`. */
    public function scopeWithStatusCounts($query)
    {
        return $query->withCount([
            'logs',
            'logs as queued_count'  => fn ($q) => $q->whereIn('status', [SurveyReminderLog::STATUS_QUEUED, SurveyReminderLog::STATUS_SENDING]),
            'logs as sent_count'    => fn ($q) => $q->where('status', SurveyReminderLog::STATUS_SENT),
            'logs as failed_count'  => fn ($q) => $q->where('status', SurveyReminderLog::STATUS_FAILED),
        ]);
    }
}
