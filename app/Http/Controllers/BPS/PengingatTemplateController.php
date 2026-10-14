<?php

namespace App\Http\Controllers\BPS;

use App\Http\Controllers\Controller;
use App\Mail\SibstrTriwulanReminder;
use App\Models\SurveyReminderSetting;
use App\Models\SurveyReminderTemplate;
use App\Support\ReminderTemplate;
use App\Support\SibstrReminder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Throwable;

/**
 * /bps/pengingat/template — BPS writes the reminder email text. The editor can
 * preview or send a test of the text as typed, before it is saved.
 */
class PengingatTemplateController extends Controller
{
    public function create(Request $request)
    {
        // "Duplikat" opens this form prefilled from an existing template.
        $source = $request->query('dari')
            ? SurveyReminderTemplate::where('survey_type', 'sibstr')->find($request->query('dari'))
            : null;

        $template = new SurveyReminderTemplate([
            'name'    => $source ? Str::limit($source->name . ' (salinan)', 100, '') : '',
            'subject' => $source->subject ?? ReminderTemplate::DEFAULT_SUBJECT,
            'body'    => $source->body ?? ReminderTemplate::DEFAULT_BODY,
        ]);

        return view('bps.pengingat.template-form', $this->formData($template));
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->templateRules(withName: true));

        $template = new SurveyReminderTemplate($validated + ['survey_type' => 'sibstr']);
        $template->forceFill(['created_by' => $request->user()->id, 'updated_by' => $request->user()->id])->save();

        return redirect()->route('bps.pengingat.index', ['tab' => 'template'])
            ->with('success', "Template \"{$template->name}\" disimpan.");
    }

    public function edit(SurveyReminderTemplate $template)
    {
        abort_unless($template->survey_type === 'sibstr', 404);

        return view('bps.pengingat.template-form', $this->formData($template));
    }

    public function update(Request $request, SurveyReminderTemplate $template)
    {
        abort_unless($template->survey_type === 'sibstr', 404);

        $template->fill($request->validate($this->templateRules(withName: true)));
        $template->forceFill(['updated_by' => $request->user()->id])->save();

        return redirect()->route('bps.pengingat.index', ['tab' => 'template'])
            ->with('success', "Template \"{$template->name}\" diperbarui. Pengiriman yang sudah dibuat tetap memakai teks lamanya.");
    }

    public function destroy(SurveyReminderTemplate $template)
    {
        abort_unless($template->survey_type === 'sibstr', 404);

        if (SurveyReminderTemplate::where('survey_type', 'sibstr')->count() <= 1) {
            return back()->with('error', 'Template terakhir tidak bisa dihapus — minimal harus ada satu template.');
        }

        if (SurveyReminderSetting::where('survey_type', 'sibstr')->where('template_id', $template->id)->exists()) {
            return back()->with('error', "Template \"{$template->name}\" dipakai pengiriman otomatis. Ganti dulu di tab Jadwal & Pengaturan.");
        }

        $template->delete();

        return redirect()->route('bps.pengingat.index', ['tab' => 'template'])
            ->with('success', "Template \"{$template->name}\" dihapus. Riwayat pengiriman yang memakainya tetap tersimpan.");
    }

    /** The text as typed (unsaved), rendered with the sample data from the test panel. */
    public function preview(Request $request)
    {
        $validated = $request->validate($this->templateRules(withName: false) + $this->sampleRules(requireEmail: false));

        return response($this->mailable($validated, isTest: false)->previewHtml());
    }

    /** Send the text as typed (unsaved) to one address BPS enters. */
    public function test(Request $request)
    {
        $validated = $request->validate($this->templateRules(withName: false) + $this->sampleRules(requireEmail: true));

        try {
            Mail::mailer(config('sibstr.reminder.mailer'))
                ->to($validated['test_email'])
                ->send($this->mailable($validated, isTest: true));
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'Email uji gagal dikirim: ' . Str::limit($e->getMessage(), 300));
        }

        return back()->withInput()->with('success', "Email uji terkirim ke {$validated['test_email']}. Periksa juga folder spam.");
    }

    private function mailable(array $v, bool $isTest): SibstrTriwulanReminder
    {
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        [$tahun, $triwulan] = array_map('intval', explode('-', $v['test_periode']));

        return new SibstrTriwulanReminder(
            $tahun,
            $triwulan,
            $v['test_nama'] ?? null,
            $v['test_penghubung'] ?? null,
            ($v['test_status'] ?? null) === SurveyReminderSetting::STATUS_DALAM_PROSES,
            $v['test_pesan'] ?? null,
            $settings->from_name,
            $settings->reply_to,
            $v['subject'],
            $v['body'],
            $isTest,
        );
    }

    private function templateRules(bool $withName): array
    {
        $knownPlaceholders = function (string $attribute, mixed $value, Closure $fail) {
            if ($unknown = ReminderTemplate::unknownPlaceholders((string) $value)) {
                $fail('Placeholder tidak dikenal: {' . implode('}, {', $unknown) . '}. Periksa ejaannya.');
            }
        };

        return array_filter([
            'name'    => $withName ? ['required', 'string', 'max:100'] : null,
            'subject' => ['required', 'string', 'max:200', $knownPlaceholders],
            'body'    => ['required', 'string', 'max:5000', $knownPlaceholders],
        ]);
    }

    private function sampleRules(bool $requireEmail): array
    {
        return [
            'test_email'      => $requireEmail ? ['required', 'email', 'max:255'] : ['nullable'],
            'test_periode'    => ['required', 'regex:/^\d{4}-[1-4]$/'],
            'test_status'     => ['required', Rule::in(array_keys(SurveyReminderSetting::statusLabels()))],
            'test_nama'       => ['nullable', 'string', 'max:200'],
            'test_penghubung' => ['nullable', 'string', 'max:200'],
            'test_pesan'      => ['nullable', 'string', 'max:1000'],
        ];
    }

    private function formData(SurveyReminderTemplate $template): array
    {
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $periods  = SibstrReminder::availablePeriods();
        $current  = SibstrReminder::period() ?? ($periods[0] ?? ['tahun' => (int) now()->format('Y'), 'triwulan' => 1]);

        return [
            'template'      => $template,
            'placeholders'  => ReminderTemplate::placeholders(),
            // Any quarter can be tried in a test, open or not.
            'testPeriods'   => collect(range((int) now()->format('Y'), 2026))
                ->flatMap(fn ($y) => collect([4, 3, 2, 1])->map(fn ($t) => ['tahun' => $y, 'triwulan' => $t]))
                ->all(),
            'defaultPeriod' => $current['tahun'] . '-' . $current['triwulan'],
            'statusLabels'  => SurveyReminderSetting::statusLabels(),
            'settings'       => $settings,
            'isAutoTemplate' => $template->exists && $settings->resolvedTemplate()->is($template),
        ];
    }
}
