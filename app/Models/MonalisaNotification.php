<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MonalisaNotification extends Model
{
    use HasFactory, HasUuid;

    protected $fillable = [
        'user_id',
        'assessment_id',
        'triggered_by',
        'type',
        'title',
        'message',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Get the user who should receive this notification.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the assessment related to this notification.
     */
    public function assessment()
    {
        return $this->belongsTo(MonalisaAssessment::class, 'assessment_id');
    }

    /**
     * Get the user who triggered this notification.
     */
    public function triggeredBy()
    {
        return $this->belongsTo(User::class, 'triggered_by');
    }

    /**
     * Mark notification as read.
     */
    public function markAsRead()
    {
        $this->update([
            'is_read' => true,
            'read_at' => now(),
        ]);
    }

    /**
     * Mark notification as unread.
     */
    public function markAsUnread()
    {
        $this->update([
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * Create notification for assessment submission.
     */
    public static function createForSubmission($assessment, $triggeredBy)
    {
        // Notify all BPS users
        $bpsUsers = User::where('is_bps', true)->get();
        
        foreach ($bpsUsers as $bpsUser) {
            static::create([
                'user_id' => $bpsUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'assessment_submitted',
                'title' => 'Assessment Baru Disubmit',
                'message' => 'Assessment untuk indikator ' . $assessment->indikator->indikator_code . ' telah disubmit oleh ' . ($assessment->kominfoSubmittedBy ? $assessment->kominfoSubmittedBy->name : 'Kominfo') . ' dan menunggu verifikasi.',
            ]);
        }
    }

    /**
     * Create notification for assessment update.
     */
    public static function createForUpdate($assessment, $triggeredBy)
    {
        // Notify all BPS users
        $bpsUsers = User::where('is_bps', true)->get();
        
        foreach ($bpsUsers as $bpsUser) {
            static::create([
                'user_id' => $bpsUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'assessment_updated',
                'title' => 'Assessment Diperbarui',
                'message' => 'Assessment untuk indikator ' . $assessment->indikator->indikator_code . ' telah diperbarui oleh ' . ($assessment->kominfoUpdatedBy ? $assessment->kominfoUpdatedBy->name : 'Kominfo') . '.',
            ]);
        }
    }

    /**
     * Create notification for assessment verification.
     */
    public static function createForVerification($assessment, $triggeredBy)
    {
        // Notify ALL Kominfo users when BPS verifies an assessment
        $kominfoUsers = User::where('is_kominfo_user', true)->get();

        foreach ($kominfoUsers as $kominfoUser) {
            static::create([
                'user_id' => $kominfoUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'assessment_verified',
                'title' => 'Assessment Terverifikasi',
                'message' => 'Assessment untuk indikator ' . $assessment->indikator->indikator_code . ' telah diverifikasi oleh BPS.',
            ]);
        }
    }

    /**
     * Create notification for BPS score update.
     */
    public static function createForBpsScoreUpdate($assessment, $triggeredBy)
    {
        // Notify ALL Kominfo users when BPS updates a score
        $kominfoUsers = User::where('is_kominfo_user', true)->get();

        foreach ($kominfoUsers as $kominfoUser) {
            static::create([
                'user_id' => $kominfoUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'bps_score_updated',
                'title' => 'Skor BPS Diperbarui',
                'message' => 'Skor BPS untuk indikator ' . $assessment->indikator->indikator_code . ' telah diperbarui.',
            ]);
        }
    }

    /**
     * Create notification for assessment rejection.
     */
    public static function createForRejection($assessment, $triggeredBy)
    {
        // Notify ALL Kominfo users when BPS rejects an assessment
        $kominfoUsers = User::where('is_kominfo_user', true)->get();

        foreach ($kominfoUsers as $kominfoUser) {
            static::create([
                'user_id' => $kominfoUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'assessment_rejected',
                'title' => 'Assessment Ditolak',
                'message' => 'Assessment untuk indikator ' . $assessment->indikator->indikator_code . ' ditolak oleh BPS. Silakan lakukan revisi dan submit ulang.',
            ]);
        }
    }

    /**
     * Create notification for assessment resubmission after rejection.
     */
    public static function createForResubmission($assessment, $triggeredBy)
    {
        // Notify all BPS users when Kominfo resubmits a rejected assessment
        $bpsUsers = User::where('is_bps', true)->get();

        foreach ($bpsUsers as $bpsUser) {
            static::create([
                'user_id' => $bpsUser->id,
                'assessment_id' => $assessment->id,
                'triggered_by' => $triggeredBy,
                'type' => 'assessment_updated',
                'title' => 'Assessment Diresubmit',
                'message' => 'Assessment untuk indikator ' . $assessment->indikator->indikator_code . ' telah direvisi dan diresubmit oleh ' . ($assessment->kominfoSubmittedBy ? $assessment->kominfoSubmittedBy->name : 'Kominfo') . ' setelah penolakan.',
            ]);
        }
    }

    /**
     * Get unread notifications for a user.
     */
    public static function getUnreadForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->with(['assessment.indikator', 'triggeredBy'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get all notifications for a user.
     */
    public static function getAllForUser($userId, $limit = 50)
    {
        return static::where('user_id', $userId)
            ->with(['assessment.indikator', 'triggeredBy'])
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unread count for a user.
     */
    public static function getUnreadCountForUser($userId)
    {
        return static::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark all notifications as read for a user.
     */
    public static function markAllAsReadForUser($userId)
    {
        static::where('user_id', $userId)
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);
    }
}

