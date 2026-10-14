<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Mail\SibstrTriwulanReminder;
use App\Models\SurveyReminderBlast;
use App\Models\SurveyReminderLog;
use App\Models\SurveyReminderSetting;
use App\Models\SurveyReminderTemplate;
use App\Services\SibstrReminderSender;
use App\Support\SibstrFormat;
use App\Support\SibstrReminder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * /bps/pengingat — BPS sets the automatic SIBSTR reminder schedule, sends a
 * manual blast to a chosen subset, and reviews what went out. Templates are
 * edited in PengingatTemplateController.
 */
class PengingatController extends Controller
{
    private const TABS = ['kirim', 'template', 'pengaturan', 'riwayat'];

    public function index(Request $request)
    {
        $tab      = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'kirim';
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $periods  = SibstrReminder::availablePeriods();
        $current  = SibstrReminder::period();
        $templates = SurveyReminderTemplate::allFor('sibstr');

        $data = [
            'tab'           => $tab,
            'settings'      => $settings,
            'serverEnabled' => (bool) config('sibstr.reminder.enabled'),
            'fromAddress'   => config('sibstr.reminder.from_address') ?: config('mail.from.address'),
            'nextRun'       => SibstrReminder::nextRun($settings),
            'currentPeriod' => $current,
            'periods'       => $periods,
            'statusLabels'  => SurveyReminderSetting::statusLabels(),
            'kondisiLabels' => $this->kondisiLabels(),
            'templates'     => $templates,
            'autoTemplate'  => $templates->firstWhere('id', $settings->template_id) ?? $templates->first(),
        ];

        if ($tab === 'kirim' && $periods !== []) {
            [$tahun, $triwulan] = $this->selectedPeriod($request, $periods, $current);
            $options = SibstrReminder::sourceOptions($tahun, $triwulan);
            // A fresh visit, or a change of quarter, starts from what the
            // automatic run uses. The source chips send `pilih`, so a list
            // the user unticked down to nothing stays empty.
            $sources = array_values(array_intersect(
                array_keys($options),
                $request->has('pilih') ? (array) $request->query('sumber', []) : SibstrReminder::defaultSources($tahun, $triwulan),
            ));

            // Load every unfinished company once: the chips count from it and
            // the list is picked from it.
            $candidates = SibstrReminder::candidates($tahun, $triwulan, $settings->excluded_kondisi ?? []);
            $recipients = SibstrReminder::select($candidates, $sources);
            $logs = SurveyReminderLog::where('survey_type', 'sibstr')
                ->where('tahun', $tahun)->where('triwulan', $triwulan)
                ->get(['email', 'status', 'sent_on', 'sent_at']);

            $data += [
                'tahun'         => $tahun,
                'triwulan'      => $triwulan,
                'sourceOptions' => $options,
                'sources'       => $sources,
                'sourceCounts'  => $candidates->flatMap(fn ($c) => $c['sources'])->countBy(),
                'recipients'    => $recipients,
                // Keyed by address, like the sender's once-a-day rule. A
                // failed attempt never hides a later success the same day.
                'sentToday'     => $logs->filter(fn ($l) => $l->sent_on && $l->sent_on->isToday())
                    ->sortBy(fn ($l) => $l->status === SurveyReminderLog::STATUS_FAILED ? 0 : 1)
                    ->mapWithKeys(fn ($l) => [mb_strtolower($l->email) => $l->status]),
                'history'       => $logs->where('status', SurveyReminderLog::STATUS_SENT)
                    ->groupBy(fn ($l) => mb_strtolower($l->email))
                    ->map(fn ($g) => (object) ['total' => $g->count(), 'last_sent_at' => $g->max('sent_at')]),
            ];

            // Step 1 choices swap this section in place instead of reloading
            // the page. A separate URL, so the browser never caches the
            // fragment as the page itself.
            if ($request->boolean('partial')) {
                return view('bps.pengingat.partials.kirim', $data);
            }
        }

        if ($tab === 'riwayat') {
            $data['blasts'] = SurveyReminderBlast::where('survey_type', 'sibstr')
                ->withStatusCounts()
                ->with('createdBy:id,name')
                ->latest()
                ->paginate(20)
                ->withQueryString();
        }

        return view('bps.pengingat.index', $data);
    }

    public function send(Request $request, SibstrReminderSender $sender)
    {
        if (! config('sibstr.reminder.enabled')) {
            return back()->with('error', 'Server ini tidak diizinkan mengirim email pengingat (SIBSTR_REMINDER_ENABLED nonaktif).');
        }

        $validated = $request->validate([
            'tahun'         => ['required', 'integer'],
            'triwulan'      => ['required', 'integer', 'between:1,4'],
            'sumber'        => ['required', 'array', 'min:1'],
            'sumber.*'      => ['string'],
            'user_ids'      => ['required', 'array', 'min:1'],
            'user_ids.*'    => ['string'],
            'template_id'   => ['required', Rule::exists('survey_reminder_templates', 'id')->where('survey_type', 'sibstr')],
            'extra_message' => ['nullable', 'string', 'max:1000'],
        ], [
            'sumber.required'   => 'Pilih minimal satu sumber data responden.',
            'user_ids.required' => 'Pilih minimal satu responden.',
        ]);

        $tahun    = (int) $validated['tahun'];
        $triwulan = (int) $validated['triwulan'];
        if (! in_array(['tahun' => $tahun, 'triwulan' => $triwulan], SibstrReminder::availablePeriods(), true)) {
            return back()->with('error', 'Triwulan tersebut belum dibuka untuk pengisian.');
        }
        $sources = array_values(array_intersect(array_keys(SibstrReminder::sourceOptions($tahun, $triwulan)), $validated['sumber']));
        if ($sources === []) {
            return back()->with('error', 'Pilih minimal satu sumber data responden.');
        }

        // Recipients are recomputed here, never taken from the form, so a
        // tampered request can only narrow the list, not add addresses.
        $settings   = SurveyReminderSetting::forSurvey('sibstr');
        $recipients = SibstrReminder::recipients($tahun, $triwulan, $sources, $settings->excluded_kondisi ?? []);

        $result = $sender->queue(
            $tahun,
            $triwulan,
            $recipients,
            SurveyReminderBlast::TRIGGER_MANUAL,
            array_keys(SurveyReminderSetting::statusLabels()),
            SurveyReminderTemplate::findOrFail($validated['template_id']),
            $validated['extra_message'] ?? null,
            $request->user(),
            $validated['user_ids'],
            $sources,
        );

        if ($result['blast'] === null) {
            return back()->with('error', 'Tidak ada email yang masuk antrean — responden terpilih sudah dikirimi pengingat hari ini.');
        }

        $message = "{$result['queued']} email pengingat masuk antrean dan akan terkirim bertahap dalam beberapa menit.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} alamat email dilewati karena sudah dikirimi pengingat triwulan ini hari ini.";
        }

        return redirect()->route('bps.pengingat.blast', $result['blast'])->with('success', $message);
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'auto_enabled'       => ['nullable', 'boolean'],
            'template_id'        => ['nullable', Rule::exists('survey_reminder_templates', 'id')->where('survey_type', 'sibstr')],
            'days'               => ['array', Rule::requiredIf($request->boolean('auto_enabled'))],
            'days.*'             => ['integer', 'between:1,31'],
            'send_time'          => ['required', 'date_format:H:i'],
            'target_statuses'    => ['required', 'array', 'min:1'],
            'target_statuses.*'  => [Rule::in(array_keys(SurveyReminderSetting::statusLabels()))],
            'excluded_kondisi'   => ['nullable', 'array'],
            'excluded_kondisi.*' => [Rule::in(array_keys($this->kondisiLabels()))],
            'from_name'          => ['nullable', 'string', 'max:100'],
            'reply_to'           => ['nullable', 'email', 'max:255'],
            'extra_message'      => ['nullable', 'string', 'max:1000'],
        ], [
            'days.required'            => 'Pilih minimal satu tanggal pengiriman otomatis.',
            'target_statuses.required' => 'Pilih minimal satu kelompok responden.',
        ]);

        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $settings->fill([
            'auto_enabled'     => $request->boolean('auto_enabled'),
            'template_id'      => $validated['template_id'] ?? null,
            'days'             => collect($validated['days'] ?? [])->map(fn ($d) => (int) $d)->unique()->sort()->values()->all(),
            'send_time'        => $validated['send_time'],
            'target_statuses'  => array_values($validated['target_statuses']),
            'excluded_kondisi' => array_values($validated['excluded_kondisi'] ?? []),
            'from_name'        => $validated['from_name'] ?? null,
            'reply_to'         => $validated['reply_to'] ?? null,
            'extra_message'    => filled($validated['extra_message'] ?? null) ? trim($validated['extra_message']) : null,
        ]);
        $settings->forceFill(['updated_by' => $request->user()->id])->save();

        return redirect()->route('bps.pengingat.index', ['tab' => 'pengaturan'])
            ->with('success', 'Pengaturan pengingat SIBSTR disimpan.');
    }

    public function blast(SurveyReminderBlast $blast)
    {
        abort_unless($blast->survey_type === 'sibstr', 404);

        $blast->loadCount([
            'logs as queued_count' => fn ($q) => $q->whereIn('status', [SurveyReminderLog::STATUS_QUEUED, SurveyReminderLog::STATUS_SENDING]),
            'logs as sent_count'   => fn ($q) => $q->where('status', SurveyReminderLog::STATUS_SENT),
            'logs as failed_count' => fn ($q) => $q->where('status', SurveyReminderLog::STATUS_FAILED),
        ])->load('createdBy:id,name', 'template:id,name');

        return view('bps.pengingat.blast', [
            'blast'         => $blast,
            'logs'          => $blast->logs()->orderBy('nama_perusahaan')->get(),
            'serverEnabled' => (bool) config('sibstr.reminder.enabled'),
            'statusLabels'  => SurveyReminderSetting::statusLabels(),
            'sourceLabels'  => SibstrReminder::sourceOptions($blast->tahun, $blast->triwulan),
        ]);
    }

    public function retry(SurveyReminderBlast $blast, SibstrReminderSender $sender)
    {
        abort_unless($blast->survey_type === 'sibstr', 404);

        if (! config('sibstr.reminder.enabled')) {
            return back()->with('error', 'Server ini tidak diizinkan mengirim email pengingat (SIBSTR_REMINDER_ENABLED nonaktif).');
        }

        $count = $sender->retryFailed($blast);

        return back()->with('success', "{$count} email yang gagal dimasukkan kembali ke antrean.");
    }

    /**
     * A template filled with sample data, as respondents see it. Defaults to
     * the automatic run's template and message; the send form passes its own
     * `template`, `periode` and `pesan` so BPS previews what it is about to send.
     */
    public function preview(Request $request)
    {
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $template = $request->filled('template')
            ? SurveyReminderTemplate::where('survey_type', 'sibstr')->find($request->query('template'))
            : null;
        $template ??= $settings->resolvedTemplate();

        $period = SibstrReminder::period() ?? (SibstrReminder::availablePeriods()[0] ?? ['tahun' => (int) now()->format('Y'), 'triwulan' => 1]);
        if (preg_match('/^(\d{4})-([1-4])$/', (string) $request->query('periode'), $m)) {
            $period = ['tahun' => (int) $m[1], 'triwulan' => (int) $m[2]];
        }

        // An emptied message box means "no message", not "use the default".
        $message = $request->has('pesan')
            ? Str::limit(trim((string) $request->query('pesan')), 1000, '')
            : $settings->extra_message;

        return response((new SibstrTriwulanReminder(
            $period['tahun'],
            $period['triwulan'],
            'PT Contoh Industri Batam',
            'Nama Penghubung',
            $request->boolean('dalam_proses'),
            $message !== '' ? $message : null,
            $settings->from_name,
            $settings->reply_to,
            $template->subject,
            $template->body,
        ))->previewHtml());
    }

    /**
     * What a past blast sent, from its own copy of the template, filled with
     * its first recipient's data.
     */
    public function blastEmail(SurveyReminderBlast $blast)
    {
        abort_unless($blast->survey_type === 'sibstr', 404);

        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $log      = $blast->logs()->orderBy('nama_perusahaan')->first();

        return response((new SibstrTriwulanReminder(
            $blast->tahun,
            $blast->triwulan,
            $log?->nama_perusahaan ?? 'PT Contoh Industri Batam',
            $log?->penghubung ?? 'Nama Penghubung',
            (bool) $log?->in_progress,
            $blast->extra_message,
            $settings->from_name,
            $settings->reply_to,
            $blast->subject,
            $blast->body,
        ))->previewHtml());
    }

    /** Blok II R201 answers other than "masih aktif" — the ones BPS may exclude. */
    private function kondisiLabels(): array
    {
        return collect(['belum_beroperasi', 'tutup', 'pindah', 'tidak_ditemukan', 'double_ganda_duplikat'])
            ->mapWithKeys(fn ($k) => [$k => SibstrFormat::kondisiPerusahaan($k)])
            ->all();
    }

    /**
     * The `periode` query value ("2026-3") if it names an open quarter,
     * else the quarter being collected now, else the newest open one.
     *
     * @return array{0: int, 1: int}
     */
    private function selectedPeriod(Request $request, array $periods, ?array $current): array
    {
        [$tahun, $triwulan] = array_map('intval', explode('-', (string) $request->query('periode')) + [1 => 0]);
        $requested = ['tahun' => $tahun, 'triwulan' => $triwulan];
        $period    = in_array($requested, $periods, true) ? $requested : ($current ?? $periods[0]);

        return [$period['tahun'], $period['triwulan']];
    }
}
