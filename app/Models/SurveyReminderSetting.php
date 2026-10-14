<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/**
 * BPS-editable reminder settings for one survey. Whether this *server* may
 * send at all is not here but in config('sibstr.reminder.enabled'): dev is
 * restored from prod, so a flag in the database would be copied over and
 * turn dev into a second sender.
 */
class SurveyReminderSetting extends Model
{
    use HasUuid;

    public const STATUS_BELUM_MULAI  = 'belum_mulai';
    public const STATUS_DALAM_PROSES = 'dalam_proses';

    protected $fillable = [
        'survey_type', // firstOrNew() in forSurvey() fills it; unfillable, it would be dropped
        'template_id',
        'auto_enabled',
        'days',
        'send_time',
        'target_statuses',
        'excluded_kondisi',
        'from_name',
        'reply_to',
        'extra_message',
    ];

    protected $casts = [
        'auto_enabled'     => 'boolean',
        'days'             => 'array',
        'target_statuses'  => 'array',
        'excluded_kondisi' => 'array',
    ];

    public static function statusLabels(): array
    {
        return [
            self::STATUS_BELUM_MULAI  => 'Belum mulai mengisi',
            self::STATUS_DALAM_PROSES => 'Sudah mulai, belum selesai',
        ];
    }

    /** The saved settings, or an unsaved row holding the config defaults. */
    public static function forSurvey(string $surveyType): self
    {
        $defaults = config("{$surveyType}.reminder.defaults", []);

        return static::firstOrNew(['survey_type' => $surveyType], [
            'auto_enabled'     => false,
            'days'             => $defaults['days'] ?? [],
            'send_time'        => $defaults['time'] ?? '08:00',
            'target_statuses'  => array_keys(self::statusLabels()),
            'excluded_kondisi' => $defaults['excluded_kondisi'] ?? [],
            'from_name'        => $defaults['from_name'] ?? null,
            'reply_to'         => $defaults['reply_to'] ?? null,
        ]);
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function template()
    {
        return $this->belongsTo(SurveyReminderTemplate::class, 'template_id');
    }

    /** The template automatic runs use: the chosen one, else the oldest. */
    public function resolvedTemplate(): SurveyReminderTemplate
    {
        return $this->template ?? SurveyReminderTemplate::allFor($this->survey_type)->first();
    }
}
