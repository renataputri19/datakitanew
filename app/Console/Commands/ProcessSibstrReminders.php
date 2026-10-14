<?php

namespace App\Console\Commands;

use App\Models\SurveyReminderSetting;
use App\Services\SibstrReminderSender;
use Illuminate\Console\Command;

/**
 * Runs every minute from Console\Kernel: queues today's automatic SIBSTR
 * reminder when the BPS schedule says it is due, then sends the next batch
 * of queued emails (automatic and manual blasts alike).
 */
class ProcessSibstrReminders extends Command
{
    protected $signature = 'sibstr:proses-pengingat';

    protected $description = 'Antrekan pengingat SIBSTR otomatis yang jatuh tempo dan kirim email yang ada di antrean';

    public function handle(SibstrReminderSender $sender): int
    {
        if (! config('sibstr.reminder.enabled')) {
            $this->warn('SIBSTR_REMINDER_ENABLED belum aktif di server ini — tidak ada email yang dikirim.');

            return self::SUCCESS;
        }

        $auto = $sender->queueAutomaticIfDue(SurveyReminderSetting::forSurvey('sibstr'));
        if ($auto !== null) {
            $blast = $auto['blast'];
            $this->info(sprintf(
                '[%s] Pengingat otomatis SIBSTR TW %d %d: %d email masuk antrean, %d sudah dikirimi hari ini.',
                now()->format('Y-m-d H:i'), $blast->triwulan, $blast->tahun, $auto['queued'], $auto['skipped']
            ));
        }

        ['sent' => $sent, 'failed' => $failed] = $sender->sendQueued(max(1, (int) config('sibstr.reminder.batch_size')));
        if ($sent + $failed > 0) {
            $this->info(sprintf('[%s] Pengingat SIBSTR: %d terkirim, %d gagal.', now()->format('Y-m-d H:i'), $sent, $failed));
        }

        return self::SUCCESS;
    }
}
