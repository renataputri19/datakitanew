<?php

namespace App\Support;

use App\Models\SurveyReminderSetting;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Who should be reminded to fill SIBSTR Triwulanan, for which quarter, and
 * when the automatic schedule next fires.
 *
 * Who is reminded about a quarter comes from "sources", the survey data BPS
 * picks the list from:
 *  - "2026-1", "2025-0" …  finished that quarter (triwulan 0 = Tahunan);
 *  - "mulai"               started the quarter being chased, not finished.
 * Only companies that have not finished the chased quarter are listed. The
 * automatic run uses every earlier quarter plus "mulai", so Tahunan-only
 * companies are reminded only when BPS ticks Tahunan by hand.
 *
 * Several companies can share one Blok I email; they are merged so an
 * address receives one email naming all of them.
 */
class SibstrReminder
{
    public const SOURCE_STARTED = 'mulai';

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
     * The sources BPS can pick from when chasing ($tahun, $triwulan): each
     * Tahunan year on record, every other open quarter, and having started
     * this one. Ordered oldest first, "mulai" last.
     *
     * @return array<string, string> key => label
     */
    public static function sourceOptions(int $tahun, int $triwulan): array
    {
        $options = [];
        $tahunan = SurveyResponse::where('survey_type', 'sibstr')->where('triwulan', 0)
            ->distinct()->orderBy('tahun')->pluck('tahun');
        foreach ($tahunan as $year) {
            $options[$year . '-0'] = 'Selesai Tahunan ' . $year;
        }
        foreach (array_reverse(self::availablePeriods()) as $p) {
            if ($p['tahun'] !== $tahun || $p['triwulan'] !== $triwulan) {
                $options[$p['tahun'] . '-' . $p['triwulan']] = 'Selesai ' . self::shortLabel($p['tahun'], $p['triwulan']);
            }
        }
        $options[self::SOURCE_STARTED] = 'Sudah mulai ' . self::shortLabel($tahun, $triwulan) . ', belum selesai';

        return $options;
    }

    /**
     * What the automatic run (and a fresh manual page) chases: everyone who
     * finished an earlier quarter, plus those who started this one.
     *
     * @return list<string>
     */
    public static function defaultSources(int $tahun, int $triwulan): array
    {
        $sources = [];
        for ($y = 2026; $y <= $tahun; $y++) {
            for ($q = 1; $q <= 4 && ($y < $tahun || $q < $triwulan); $q++) {
                $sources[] = $y . '-' . $q;
            }
        }
        $sources[] = self::SOURCE_STARTED;

        return $sources;
    }

    /** "TW I 2026", "Tahunan 2025", "Mulai TW III" — the short tag shown per row. */
    public static function sourceTag(string $source, int $triwulan): string
    {
        if ($source === self::SOURCE_STARTED) {
            return 'Mulai TW ' . ReminderTemplate::roman($triwulan);
        }
        [$y, $q] = array_map('intval', explode('-', $source) + [1 => 0]);

        return $q === 0 ? "Tahunan {$y}" : 'TW ' . ReminderTemplate::roman($q) . " {$y}";
    }

    /**
     * One entry per company that has not finished ($tahun, $triwulan) and
     * matches at least one source, before the source choice is applied: each
     * entry lists every source it matches, so the page can count them.
     * Entries with `email` = null have no usable Blok I email and are kept so
     * BPS can follow up by phone.
     *
     * @param  list<string>  $excludedKondisi  latest Blok II R201 answers to skip
     * @return Collection<int, array{user: User, nama_perusahaan: ?string, penghubung: ?string, email: ?string, telepon: ?string, status: string, in_progress: bool, sources: list<string>}>
     */
    public static function candidates(int $tahun, int $triwulan, array $excludedKondisi): Collection
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
            ->map(function (Collection $rows) use ($tahun, $triwulan, $excludedKondisi) {
                $isTarget = fn ($r) => (int) $r->tahun === $tahun && (int) $r->triwulan === $triwulan;
                $current  = $rows->first($isTarget);
                if ($current && $current->is_completed) {
                    return null;
                }

                $sources = $rows->filter(fn ($r) => $r->is_completed && ! $isTarget($r))
                    ->map(fn ($r) => (int) $r->tahun . '-' . (int) $r->triwulan)
                    ->unique()->values()->all();
                if ($current) {
                    $sources[] = self::SOURCE_STARTED;
                }
                if ($sources === []) {
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
                    'status'          => $current ? SurveyReminderSetting::STATUS_DALAM_PROSES : SurveyReminderSetting::STATUS_BELUM_MULAI,
                    'in_progress'     => $current !== null,
                    'sources'         => $sources,
                ];
            })
            ->filter()
            ->sortBy(fn ($r) => mb_strtolower((string) $r['nama_perusahaan']))
            ->values();
    }

    /**
     * The candidates matching any of $sources (and $statuses, when given),
     * merged so each email address appears once. A merged entry names all its
     * companies in `nama_perusahaan`; `companies` and `user_ids` list them.
     *
     * @param  list<string>  $sources
     * @param  list<string>|null  $statuses  SurveyReminderSetting::STATUS_*
     */
    public static function select(Collection $candidates, array $sources, ?array $statuses = null): Collection
    {
        $picked = $candidates->filter(fn ($c) => array_intersect($c['sources'], $sources) !== []
            && ($statuses === null || in_array($c['status'], $statuses, true)));

        $byEmail = $picked->whereNotNull('email')
            ->groupBy(fn ($c) => mb_strtolower($c['email']))
            ->map(function (Collection $group) {
                $names     = $group->pluck('nama_perusahaan')->filter()->unique()->values();
                $allActive = $group->every(fn ($c) => $c['in_progress']);

                return array_merge($group->first(), [
                    'nama_perusahaan' => $names->count() > 1
                        ? Str::limit($names->slice(0, -1)->implode(', ') . ' dan ' . $names->last(), 250)
                        : $group->first()['nama_perusahaan'],
                    'companies'   => $names->all(),
                    'user_ids'    => $group->map(fn ($c) => $c['user']->id)->values()->all(),
                    // The "you have started" sentence must be true for every company named.
                    'in_progress' => $allActive,
                    'status'      => $allActive ? SurveyReminderSetting::STATUS_DALAM_PROSES : SurveyReminderSetting::STATUS_BELUM_MULAI,
                    'sources'     => $group->flatMap(fn ($c) => $c['sources'])->unique()->values()->all(),
                ]);
            });

        $noEmail = $picked->whereNull('email')->map(fn ($c) => $c + [
            'companies' => array_filter([$c['nama_perusahaan']]),
            'user_ids'  => [$c['user']->id],
        ]);

        return $byEmail->values()->concat($noEmail)
            ->sortBy(fn ($r) => mb_strtolower((string) $r['nama_perusahaan']))
            ->values();
    }

    /**
     * Recipients for chasing ($tahun, $triwulan) from $sources.
     *
     * @param  list<string>  $sources
     * @param  list<string>  $excludedKondisi
     * @param  list<string>|null  $statuses
     */
    public static function recipients(int $tahun, int $triwulan, array $sources, array $excludedKondisi, ?array $statuses = null): Collection
    {
        return self::select(self::candidates($tahun, $triwulan, $excludedKondisi), $sources, $statuses);
    }

    /** Who the automatic run emails under the saved settings. */
    public static function automaticRecipients(SurveyReminderSetting $settings, int $tahun, int $triwulan): Collection
    {
        return self::recipients(
            $tahun,
            $triwulan,
            self::defaultSources($tahun, $triwulan),
            $settings->excluded_kondisi ?? [],
            $settings->target_statuses ?? [],
        );
    }

    private static function shortLabel(int $tahun, int $triwulan): string
    {
        return 'Triwulan ' . ReminderTemplate::roman($triwulan) . ' ' . $tahun;
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
