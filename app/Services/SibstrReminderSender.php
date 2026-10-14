<?php

namespace App\Services;

use App\Mail\SibstrTriwulanReminder;
use App\Models\SurveyReminderBlast;
use App\Models\SurveyReminderLog;
use App\Models\SurveyReminderSetting;
use App\Models\SurveyReminderTemplate;
use App\Models\User;
use App\Support\SibstrReminder;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Queues and sends SIBSTR reminder emails.
 *
 * Queueing only writes `queued` log rows; the scheduler's
 * sibstr:proses-pengingat sends them a batch per minute. That keeps a manual
 * blast from holding a PHP worker for minutes of SMTP round-trips and paces
 * the provider's rate limit.
 */
class SibstrReminderSender
{
    /**
     * Queue one email per reachable recipient, skipping anyone already
     * emailed (or queued) for this period today. A failed email from earlier
     * today is moved into this blast and retried.
     *
     * @param  Collection  $recipients  from SibstrReminder::recipients()
     * @param  list<string>|null  $onlyUserIds  restrict to these respondents
     * @return array{blast: ?SurveyReminderBlast, queued: int, skipped: int}
     */
    public function queue(
        int $tahun,
        int $triwulan,
        Collection $recipients,
        string $trigger,
        array $statuses,
        SurveyReminderTemplate $template,
        ?string $extraMessage,
        ?User $by,
        ?array $onlyUserIds = null,
    ): array {
        $reachable = $recipients->whereNotNull('email');
        if ($onlyUserIds !== null) {
            $reachable = $reachable->filter(fn ($r) => in_array($r['user']->id, $onlyUserIds, true));
        }

        $today    = now()->toDateString();
        $existing = SurveyReminderLog::where('survey_type', 'sibstr')
            ->where('tahun', $tahun)
            ->where('triwulan', $triwulan)
            ->whereDate('sent_on', $today)
            ->get()
            ->keyBy('user_id');

        $candidates = $reachable->reject(function ($r) use ($existing) {
            $log = $existing->get($r['user']->id);

            return $log && $log->status !== SurveyReminderLog::STATUS_FAILED;
        });
        $skipped = $reachable->count() - $candidates->count();

        // An automatic run is recorded even when it finds nobody, so the
        // scheduler knows today's run has happened.
        if ($candidates->isEmpty() && $trigger === SurveyReminderBlast::TRIGGER_MANUAL) {
            return ['blast' => null, 'queued' => 0, 'skipped' => $skipped];
        }

        $blast = SurveyReminderBlast::create([
            'survey_type'     => 'sibstr',
            'tahun'           => $tahun,
            'triwulan'        => $triwulan,
            'trigger'         => $trigger,
            'target_statuses' => array_values($statuses),
            // A copy, not just the id: editing the template later must not
            // change what this blast sends or what its history shows.
            'template_id'     => $template->id,
            'subject'         => $template->subject,
            'body'            => $template->body,
            'extra_message'   => filled($extraMessage) ? trim($extraMessage) : null,
            'created_by'      => $by?->id,
        ]);

        $queued = 0;
        foreach ($candidates as $r) {
            $row = [
                'blast_id'        => $blast->id,
                'email'           => $r['email'],
                'nama_perusahaan' => $r['nama_perusahaan'],
                'penghubung'      => $r['penghubung'],
                'in_progress'     => $r['in_progress'],
                'status'          => SurveyReminderLog::STATUS_QUEUED,
                'error'           => null,
            ];

            if ($failed = $existing->get($r['user']->id)) {
                $failed->update($row);
                $queued++;
                continue;
            }

            try {
                SurveyReminderLog::create($row + [
                    'user_id'     => $r['user']->id,
                    'survey_type' => 'sibstr',
                    'tahun'       => $tahun,
                    'triwulan'    => $triwulan,
                    'sent_on'     => $today,
                ]);
                $queued++;
            } catch (UniqueConstraintViolationException) {
                $skipped++; // queued by a concurrent blast a moment ago
            }
        }

        return ['blast' => $blast, 'queued' => $queued, 'skipped' => $skipped];
    }

    /**
     * Queue today's automatic reminder if the schedule says it is due and it
     * has not run yet today.
     *
     * @return array{blast: ?SurveyReminderBlast, queued: int, skipped: int}|null
     */
    public function queueAutomaticIfDue(SurveyReminderSetting $settings, ?Carbon $now = null): ?array
    {
        $now ??= now();

        if (! SibstrReminder::isDue($settings, $now)) {
            return null;
        }

        $ranToday = SurveyReminderBlast::where('survey_type', 'sibstr')
            ->where('trigger', SurveyReminderBlast::TRIGGER_OTOMATIS)
            ->whereDate('created_at', $now->toDateString())
            ->exists();
        if ($ranToday) {
            return null;
        }

        ['tahun' => $tahun, 'triwulan' => $triwulan] = SibstrReminder::period($now);

        return $this->queue(
            $tahun,
            $triwulan,
            SibstrReminder::recipientsFor($settings, $tahun, $triwulan),
            SurveyReminderBlast::TRIGGER_OTOMATIS,
            $settings->target_statuses ?? [],
            $settings->resolvedTemplate(),
            $settings->extra_message,
            null,
        );
    }

    /**
     * Send up to $limit queued emails, oldest first.
     *
     * @return array{sent: int, failed: int}
     */
    public function sendQueued(int $limit): array
    {
        // A row left in `sending` means the process died mid-send. Whether the
        // email went out is unknown, so mark it failed for BPS to decide
        // rather than risk emailing the respondent twice.
        SurveyReminderLog::where('status', SurveyReminderLog::STATUS_SENDING)
            ->where('updated_at', '<', now()->subMinutes(10))
            ->update([
                'status' => SurveyReminderLog::STATUS_FAILED,
                'error'  => 'Pengiriman terputus sebelum selesai; status tidak diketahui.',
            ]);

        $ids = SurveyReminderLog::where('status', SurveyReminderLog::STATUS_QUEUED)
            ->orderBy('created_at')
            ->limit($limit)
            ->pluck('id');

        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $mailer   = Mail::mailer(config('sibstr.reminder.mailer'));
        $delayMs  = max(0, (int) config('sibstr.reminder.send_delay_ms'));
        $sent = $failed = 0;

        foreach ($ids as $i => $id) {
            // Claim the row so an overlapping run cannot send it too.
            $claimed = SurveyReminderLog::whereKey($id)
                ->where('status', SurveyReminderLog::STATUS_QUEUED)
                ->update(['status' => SurveyReminderLog::STATUS_SENDING]);
            if (! $claimed) {
                continue;
            }

            $log = SurveyReminderLog::with('blast')->find($id);

            if ($i > 0 && $delayMs > 0) {
                usleep($delayMs * 1000);
            }

            try {
                $mailer->to($log->email)->send(new SibstrTriwulanReminder(
                    $log->tahun,
                    $log->triwulan,
                    $log->nama_perusahaan,
                    $log->penghubung,
                    $log->in_progress,
                    $log->blast?->extra_message,
                    $settings->from_name,
                    $settings->reply_to,
                    $log->blast?->subject,
                    $log->blast?->body,
                ));
                $log->update(['status' => SurveyReminderLog::STATUS_SENT, 'sent_at' => now(), 'error' => null]);
                $sent++;
            } catch (Throwable $e) {
                $log->update(['status' => SurveyReminderLog::STATUS_FAILED, 'error' => Str::limit($e->getMessage(), 1000)]);
                $failed++;
            }
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /** Put a blast's failed emails back in the queue. */
    public function retryFailed(SurveyReminderBlast $blast): int
    {
        return $blast->logs()
            ->where('status', SurveyReminderLog::STATUS_FAILED)
            ->update(['status' => SurveyReminderLog::STATUS_QUEUED, 'error' => null]);
    }
}
