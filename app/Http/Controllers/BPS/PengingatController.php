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
use Illuminate\Support\Facades\DB;
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
            // The filter form always sends `periode`; only a fresh visit falls
            // back to the saved targets, so unticking every status sticks.
            $statuses = array_values(array_intersect(
                (array) ($request->has('periode') ? $request->query('status', []) : ($settings->target_statuses ?? [])),
                array_keys(SurveyReminderSetting::statusLabels())
            ));

            $recipients = SibstrReminder::recipientsFor($settings, $tahun, $triwulan, $statuses);
            $userIds    = $recipients->map(fn ($r) => $r['user']->id)->all();

            $data += [
                'tahun'      => $tahun,
                'triwulan'   => $triwulan,
                'statuses'   => $statuses,
                'recipients' => $recipients,
                'sentToday'  => SurveyReminderLog::where('survey_type', 'sibstr')
                    ->where('tahun', $tahun)->where('triwulan', $triwulan)
                    ->whereDate('sent_on', now()->toDateString())
                    ->whereIn('user_id', $userIds)
                    ->pluck('status', 'user_id'),
                'history'    => SurveyReminderLog::where('survey_type', 'sibstr')
                    ->where('tahun', $tahun)->where('triwulan', $triwulan)
                    ->where('status', SurveyReminderLog::STATUS_SENT)
                    ->whereIn('user_id', $userIds)
                    ->groupBy('user_id')
                    ->select('user_id', DB::raw('COUNT(*) as total'), DB::raw('MAX(sent_at) as last_sent_at'))
                    ->get()
                    ->keyBy('user_id'),
            ];
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
            'status'        => ['required', 'array', 'min:1'],
            'status.*'      => [Rule::in(array_keys(SurveyReminderSetting::statusLabels()))],
            'user_ids'      => ['required', 'array', 'min:1'],
            'user_ids.*'    => ['string'],
            'template_id'   => ['required', Rule::exists('survey_reminder_templates', 'id')->where('survey_type', 'sibstr')],
            'extra_message' => ['nullable', 'string', 'max:1000'],
        ], [
            'user_ids.required' => 'Pilih minimal satu responden.',
        ]);

        $tahun    = (int) $validated['tahun'];
        $triwulan = (int) $validated['triwulan'];
        if (! in_array(['tahun' => $tahun, 'triwulan' => $triwulan], SibstrReminder::availablePeriods(), true)) {
            return back()->with('error', 'Triwulan tersebut belum dibuka untuk pengisian.');
        }

        // Recipients are recomputed here, never taken from the form, so a
        // tampered request can only narrow the list, not add addresses.
        $settings   = SurveyReminderSetting::forSurvey('sibstr');
        $recipients = SibstrReminder::recipientsFor($settings, $tahun, $triwulan, $validated['status']);

        $result = $sender->queue(
            $tahun,
            $triwulan,
            $recipients,
            SurveyReminderBlast::TRIGGER_MANUAL,
            $validated['status'],
            SurveyReminderTemplate::findOrFail($validated['template_id']),
            $validated['extra_message'] ?? null,
            $request->user(),
            $validated['user_ids'],
        );

        if ($result['blast'] === null) {
            return back()->with('error', 'Tidak ada email yang masuk antrean — responden terpilih sudah dikirimi pengingat hari ini.');
        }

        $message = "{$result['queued']} email pengingat masuk antrean dan akan terkirim bertahap dalam beberapa menit.";
        if ($result['skipped'] > 0) {
            $message .= " {$result['skipped']} responden dilewati karena sudah dikirimi hari ini.";
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

    /** The automatic run's template with sample data, as respondents see it. */
    public function preview(Request $request)
    {
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $template = $settings->resolvedTemplate();
        $period   = SibstrReminder::period() ?? (SibstrReminder::availablePeriods()[0] ?? ['tahun' => (int) now()->format('Y'), 'triwulan' => 1]);

        return response((new SibstrTriwulanReminder(
            $period['tahun'],
            $period['triwulan'],
            'PT Contoh Industri Batam',
            'Nama Penghubung',
            $request->boolean('dalam_proses'),
            $settings->extra_message,
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
