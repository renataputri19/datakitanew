<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaAssessment extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'indikator_id',
        'kominfo_created_by',
        'kominfo_submitted_by',
        'bps_user_id',
        'kominfo_maturity_level',
        'kominfo_justification',
        'kominfo_submitted_at',
        'kominfo_updated_at',
        'kominfo_updated_by',
        'kominfo_scored_by',
        'bps_maturity_level',
        'bps_audit_comment',
        'bps_verified_at',
        'bps_updated_at',
        'bps_updated_by',
        'bps_scored_by',
        'status',
    ];

    protected $casts = [
        'kominfo_maturity_level' => 'integer',
        'bps_maturity_level' => 'integer',
        'kominfo_submitted_at' => 'datetime',
        'kominfo_updated_at' => 'datetime',
        'bps_verified_at' => 'datetime',
        'bps_updated_at' => 'datetime',
    ];

    /**
     * Get the indikator for this assessment.
     */
    public function indikator()
    {
        return $this->belongsTo(MonalisaIndikator::class, 'indikator_id');
    }

    /**
     * Get the Kominfo user who created the assessment.
     */
    public function kominfoCreatedBy()
    {
        return $this->belongsTo(User::class, 'kominfo_created_by');
    }

    /**
     * Get the Kominfo user who submitted the assessment.
     */
    public function kominfoSubmittedBy()
    {
        return $this->belongsTo(User::class, 'kominfo_submitted_by');
    }

    /**
     * Get the BPS user who verified the assessment.
     */
    public function bpsUser()
    {
        return $this->belongsTo(User::class, 'bps_user_id');
    }

    /**
     * Get the documents for this assessment.
     */
    public function documents()
    {
        return $this->hasMany(MonalisaDocument::class, 'assessment_id');
    }

    /**
     * Get the Kominfo user who last updated the assessment.
     */
    public function kominfoUpdatedBy()
    {
        return $this->belongsTo(User::class, 'kominfo_updated_by');
    }

    /**
     * Get the BPS user who last updated the assessment.
     */
    public function bpsUpdatedBy()
    {
        return $this->belongsTo(User::class, 'bps_updated_by');
    }

    /**
     * Get the Kominfo user who scored the assessment.
     */
    public function kominfoScoredBy()
    {
        return $this->belongsTo(User::class, 'kominfo_scored_by');
    }

    /**
     * Get the BPS user who scored the assessment.
     */
    public function bpsScoredBy()
    {
        return $this->belongsTo(User::class, 'bps_scored_by');
    }

    /**
     * Get the notifications for this assessment.
     */
    public function notifications()
    {
        return $this->hasMany(MonalisaNotification::class, 'assessment_id');
    }

    /**
     * Get the BPS comment history for this assessment.
     */
    public function bpsCommentHistory()
    {
        return $this->hasMany(MonalisaBpsCommentHistory::class, 'assessment_id')
                    ->orderBy('created_at', 'desc');
    }

    /**
     * Get or create assessment for a specific indikator (organization-wide).
     * CORRECTED: No longer creates per-user assessments.
     * One assessment per indicator shared by all Kominfo users.
     */
    public static function getOrCreateForIndicator($indikatorId, $userId = null)
    {
        $assessment = static::firstOrCreate(
            [
                'indikator_id' => $indikatorId,  // Only indikator_id (organization-wide)
            ],
            [
                'status' => 'draft',
                'kominfo_created_by' => $userId,  // Track who created it
            ]
        );

        return $assessment;
    }

    /**
     * Track Kominfo update.
     */
    public function trackKominfoUpdate($userId)
    {
        $this->kominfo_updated_at = now();
        $this->kominfo_updated_by = $userId;
        $this->kominfo_scored_by = $userId;
        $this->save();
    }

    /**
     * Track BPS update.
     */
    public function trackBpsUpdate($userId)
    {
        $this->bps_updated_at = now();
        $this->bps_updated_by = $userId;
        $this->bps_scored_by = $userId;
        $this->save();
    }

    /**
     * Check if assessment can be edited by Kominfo user.
     */
    public function canBeEditedByKominfo()
    {
        // Can edit if not verified (draft, submitted, or rejected)
        // Rejected assessments can be edited and resubmitted
        return $this->status !== 'verified';
    }

    /**
     * Check if assessment is rejected by BPS.
     */
    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    /**
     * Check if assessment can be resubmitted.
     */
    public function canBeResubmitted()
    {
        // Can resubmit if rejected and has required data
        return $this->status === 'rejected'
            && $this->kominfo_maturity_level
            && $this->kominfo_justification;
    }

    /**
     * Get the last update timestamp (either Kominfo or BPS).
     */
    public function getLastUpdatedAtAttribute()
    {
        $timestamps = array_filter([
            $this->kominfo_updated_at,
            $this->bps_updated_at,
            $this->updated_at,
        ]);

        return $timestamps ? max($timestamps) : null;
    }

    /**
     * Get the last updater (either Kominfo or BPS user).
     */
    public function getLastUpdatedByAttribute()
    {
        if ($this->bps_updated_at && (!$this->kominfo_updated_at || $this->bps_updated_at > $this->kominfo_updated_at)) {
            return $this->bpsUpdatedBy;
        }

        if ($this->kominfo_updated_at) {
            return $this->kominfoUpdatedBy;
        }

        return null;
    }
}

