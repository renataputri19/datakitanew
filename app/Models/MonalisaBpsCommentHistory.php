<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaBpsCommentHistory extends Model
{
    use HasFactory, HasUuid;

    protected $table = 'monalisa_bps_comment_history';

    protected $fillable = [
        'assessment_id',
        'bps_user_id',
        'comment',
        'action_type',
        'bps_maturity_level',
    ];

    protected $casts = [
        'bps_maturity_level' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the assessment that this comment belongs to.
     */
    public function assessment()
    {
        return $this->belongsTo(MonalisaAssessment::class, 'assessment_id');
    }

    /**
     * Get the BPS user who made this comment.
     */
    public function bpsUser()
    {
        return $this->belongsTo(User::class, 'bps_user_id');
    }

    /**
     * Get formatted action type for display.
     */
    public function getActionTypeDisplayAttribute()
    {
        return match($this->action_type) {
            'verified' => 'Diverifikasi',
            'rejected' => 'Ditolak',
            'score_updated' => 'Skor Diperbarui',
            'verification_cancelled' => 'Verifikasi Dibatalkan',
            default => ucfirst($this->action_type),
        };
    }

    /**
     * Get badge color class based on action type.
     */
    public function getActionTypeBadgeClassAttribute()
    {
        return match($this->action_type) {
            'verified' => 'monalisa-badge-verified',
            'rejected' => 'monalisa-badge-rejected',
            'score_updated' => 'monalisa-badge-submitted',
            'verification_cancelled' => 'monalisa-badge-draft',
            default => 'monalisa-badge-draft',
        };
    }
}

