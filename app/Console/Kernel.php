<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // SIBSTR reminder emails. Runs every minute because the schedule
        // (days, time) is set by BPS in the database at /bps/pengingat, and
        // because manual blasts are sent from the same queue. Only servers
        // with SIBSTR_REMINDER_ENABLED (prod) run it at all.
        $schedule->command('sibstr:proses-pengingat')
            ->everyMinute()
            ->when(fn () => (bool) config('sibstr.reminder.enabled'))
            ->withoutOverlapping(10)
            ->appendOutputTo(storage_path('logs/sibstr-reminder.log'));
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
