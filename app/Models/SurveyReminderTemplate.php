<?php

namespace App\Models;

use App\Support\ReminderTemplate;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

/** A BPS-written reminder email: subject and body with {placeholders}. */
class SurveyReminderTemplate extends Model
{
    use HasUuid;

    protected $fillable = [
        'survey_type',
        'name',
        'subject',
        'body',
    ];

    /**
     * Every template for the survey, oldest first, creating the standard one
     * when BPS has none yet — so there is always something to send.
     */
    public static function allFor(string $surveyType)
    {
        $templates = static::where('survey_type', $surveyType)->orderBy('created_at')->get();

        if ($templates->isEmpty()) {
            $templates->push(static::create([
                'survey_type' => $surveyType,
                'name'        => ReminderTemplate::DEFAULT_NAME,
                'subject'     => ReminderTemplate::DEFAULT_SUBJECT,
                'body'        => ReminderTemplate::DEFAULT_BODY,
            ]));
        }

        return $templates;
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
