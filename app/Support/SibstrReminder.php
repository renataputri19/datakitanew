<?php

namespace App\Support;

use App\Models\SurveyReminderSetting;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Who should be reminded to fill SIBSTR Triwulanan, for which quarter, and
 * when the automatic schedule next fires.
 *
 * The respondent population is every non-staff user who has ever started a
 * SIBSTR record (Tahunan or any quarter): a company with no account has no
 * Blok I email to write to, so it cannot be reached from here anyway.
 */
class SibstrReminder
{
    /**
     * The quarter whose collection is running on $date: the one that has just
     * ended (Jan–Mar collects TW IV of last year, Apr–Jun TW I, and so on).
     * Null when that quarter is not open yet — TW II 2026 was held until
     * SurveyResponse::TW2_LAUNCH_DATE, so a 2 July reminder would point to a
     * survey the respondent cannot open.
     *
     * @return array{tahun: int, triwulan: int}|null
     */
    public static function period(?Carbon $date = null): ?array
    {
        $date ??= now();
        $month = (int) $date->format('n');

        $tahun    = $month <= 3 ? (int) $date->format('Y') - 1 : (int) $date->format('Y');
        $triwulan = $month <= 3 ? 4 : intdiv($month - 1, 3);

        if (! in_array($triwulan, SurveyResponse::availableTriwulan($tahun, $date), true)) {
            return null;
        }

        return ['tahun' => $tahun, 'triwulan' => $triwulan];
    }

    /**
     * Every quarter a manual reminder can target: those open today, newest
     * first.
     *
     * @return list<array{tahun: int, triwulan: int}>
     */
    public static function availablePeriods(): array
    {
        $periods = [];
        for ($tahun = (int) now()->format('Y'); $tahun >= 2026; $tahun--) {
            foreach (array_reverse(SurveyResponse::availableTriwulan($tahun)) as $triwulan) {
                $periods[] = ['tahun' => $tahun, 'triwulan' => $triwulan];
            }
        }

        return $periods;
    }

    /**
     * One entry per respondent who has not finished ($tahun, $triwulan) and
     * whose status is in $statuses. Entries with `email` = null have no usable
     * Blok I email and are returned so BPS can follow up by phone.
     *
     * @param  list<string>  $statuses  SurveyReminderSetting::STATUS_*
     * @param  list<string>  $excludedKondisi  latest Blok II R201 answers to skip
     * @return Collection<int, array{user: User, nama_perusahaan: ?string, penghubung: ?string, email: ?string, telepon: ?string, status: string, in_progress: bool}>
     */
    public static function recipients(int $tahun, int $triwulan, array $statuses, array $excludedKondisi): Collection
    {
        return SurveyResponse::with('user')
            ->where('survey_type', 'sibstr')
            ->orderByDesc('tahun')
            ->orderByDesc('triwulan')
            ->orderByDesc('updated_at')
            ->get(['id', 'user_id', 'tahun', 'triwulan', 'nama_perusahaan', 'penghubung', 'email',
                   'telepon_fax', 'kondisi_perusahaan', 'is_completed', 'updated_at'])
            ->filter(fn (SurveyResponse $r) => $r->user && $r->user->role === User::ROLE_USER)
            ->groupBy('user_id')
            ->map(function (Collection $rows) use ($tahun, $triwulan, $statuses, $excludedKondisi) {
                $current = $rows->first(fn ($r) => (int) $r->tahun === $tahun && (int) $r->triwulan === $triwulan);
                if ($current && $current->is_completed) {
                    return null;
                }

                $status = $current ? SurveyReminderSetting::STATUS_DALAM_PROSES : SurveyReminderSetting::STATUS_BELUM_MULAI;
                if (! in_array($status, $statuses, true)) {
                    return null;
                }

                // Rows are newest period first, so the first answer found is
                // the company's latest.
                $kondisi = $rows->first(fn ($r) => filled($r->kondisi_perusahaan))?->kondisi_perusahaan;
                if (in_array($kondisi, $excludedKondisi, true)) {
                    return null;
                }

                $contact = $rows->first(fn ($r) => filter_var(trim((string) $r->email), FILTER_VALIDATE_EMAIL));

                return [
                    'user'            => $rows->first()->user,
                    'nama_perusahaan' => $rows->first(fn ($r) => filled($r->nama_perusahaan))?->nama_perusahaan,
                    // Prefer the penghubung written next to the email we use.
                    'penghubung'      => filled($contact?->penghubung)
                        ? $contact->penghubung
                        : $rows->first(fn ($r) => filled($r->penghubung))?->penghubung,
                    'email'           => $contact ? trim($contact->email) : null,
                    'telepon'         => $rows->first(fn ($r) => filled($r->telepon_fax))?->telepon_fax,
                    'status'          => $status,
                    'in_progress'     => $current !== null,
                ];
            })
            ->filter()
            ->sortBy(fn ($r) => mb_strtolower((string) $r['nama_perusahaan']))
            ->values();
    }

    /** Recipients for a period under the saved BPS settings. */
    public static function recipientsFor(SurveyReminderSetting $settings, int $tahun, int $triwulan, ?array $statuses = null): Collection
    {
        return self::recipients(
            $tahun,
            $triwulan,
            $statuses ?? ($settings->target_statuses ?? []),
            $settings->excluded_kondisi ?? []
        );
    }

    /**
     * Whether $at is a scheduled reminder moment: a configured day of a
     * collection month, at or after the send time, with a quarter open.
     * The caller still checks that today's automatic run has not happened.
     */
    public static function isDue(SurveyReminderSetting $settings, ?Carbon $at = null): bool
    {
        $at ??= now();

        return $settings->auto_enabled
            && in_array((int) $at->format('n'), config('sibstr.reminder.collection_months'), true)
            && in_array((int) $at->format('j'), array_map('intval', $settings->days ?? []), true)
            && $at->format('H:i') >= $settings->send_time
            && self::period($at) !== null;
    }

    /**
     * The next scheduled reminder moment after $after, skipping days that
     * fall before a quarter opens. Null when automatic sending is off or no
     * day is configured.
     */
    public static function nextRun(SurveyReminderSetting $settings, ?Carbon $after = null): ?Carbon
    {
        $after ??= now();
        $days = collect($settings->days ?? [])->map(fn ($d) => (int) $d)->sort()->values();

        if (! $settings->auto_enabled || $days->isEmpty()) {
            return null;
        }

        [$hour, $minute] = array_map('intval', explode(':', $settings->send_time) + [1 => 0]);
        $month = $after->copy()->startOfMonth();

        for ($i = 0; $i < 15; $i++, $month->addMonthNoOverflow()) {
            if (! in_array((int) $month->format('n'), config('sibstr.reminder.collection_months'), true)) {
                continue;
            }
            foreach ($days as $day) {
                if ($day > $month->daysInMonth) {
                    continue;
                }
                $run = $month->copy()->setDay($day)->setTime($hour, $minute);
                if ($run->gt($after) && self::period($run) !== null) {
                    return $run;
                }
            }
        }

        return null;
    }
}
