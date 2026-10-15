<?php

namespace Tests\Feature;

use App\Mail\SibstrTriwulanReminder;
use App\Models\SurveyReminderBlast;
use App\Models\SurveyReminderLog;
use App\Models\SurveyReminderSetting;
use App\Models\SurveyReminderTemplate;
use App\Models\SurveyResponse;
use App\Models\User;
use App\Services\SibstrReminderSender;
use App\Support\ReminderTemplate;
use App\Support\SibstrReminder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SibstrReminderTest extends TestCase
{
    use RefreshDatabase;

    private const BOTH = [SurveyReminderSetting::STATUS_BELUM_MULAI, SurveyReminderSetting::STATUS_DALAM_PROSES];

    protected function setUp(): void
    {
        parent::setUp();

        // 13 Oktober 2026 08.00: TW III 2026 is open, and it is a reminder day.
        $this->travelTo(Carbon::parse('2026-10-13 08:00', 'Asia/Jakarta'));
        config([
            'sibstr.reminder.enabled'       => true,
            'sibstr.reminder.send_delay_ms' => 0,
        ]);
    }

    private function user(string $role = User::ROLE_USER): User
    {
        $user = User::factory()->create();
        $user->setRole($role);
        $user->save();

        return $user->fresh();
    }

    private function sibstr(User $user, int $tahun, int $triwulan, array $attrs = []): SurveyResponse
    {
        return SurveyResponse::create(array_merge([
            'user_id'        => $user->id,
            'survey_type'    => 'sibstr',
            'survey_section' => 'blok1',
            'tahun'          => $tahun,
            'triwulan'       => $triwulan,
        ], $attrs));
    }

    /** A Triwulanan respondent: finished TW II 2026, has not started TW III. */
    private function respondent(User $user, array $attrs = []): SurveyResponse
    {
        return $this->sibstr($user, 2026, 2, array_merge(['is_completed' => true], $attrs));
    }

    private function template(): SurveyReminderTemplate
    {
        return SurveyReminderTemplate::allFor('sibstr')->first();
    }

    private function autoSettings(array $overrides = []): SurveyReminderSetting
    {
        $settings = SurveyReminderSetting::forSurvey('sibstr');
        $settings->fill(array_merge([
            'auto_enabled'    => true,
            'days'            => [2, 7, 13, 17, 22],
            'send_time'       => '08:00',
            'target_statuses' => self::BOTH,
        ], $overrides))->save();

        return $settings;
    }

    // ─────────────────────────────────────────────────────────
    //  Period & schedule
    // ─────────────────────────────────────────────────────────

    public function test_period_is_the_quarter_that_just_ended(): void
    {
        $this->assertSame(['tahun' => 2026, 'triwulan' => 3], SibstrReminder::period());
        $this->assertSame(['tahun' => 2026, 'triwulan' => 4], SibstrReminder::period(Carbon::parse('2027-01-02')));
    }

    public function test_no_period_before_a_quarter_opens(): void
    {
        // TW II 2026 was held until 6 July; Triwulanan starts with 2026.
        $this->assertNull(SibstrReminder::period(Carbon::parse('2026-07-02 08:00')));
        $this->assertNull(SibstrReminder::period(Carbon::parse('2026-01-02 08:00')));
    }

    public function test_next_run_skips_days_before_the_quarter_opens(): void
    {
        $settings = $this->autoSettings();

        $this->assertEquals(Carbon::parse('2026-10-17 08:00'), SibstrReminder::nextRun($settings));
        $this->assertEquals(Carbon::parse('2027-01-02 08:00'), SibstrReminder::nextRun($settings, Carbon::parse('2026-10-22 09:00')));
        $this->assertEquals(Carbon::parse('2026-07-07 08:00'), SibstrReminder::nextRun($settings, Carbon::parse('2026-06-30')));

        $settings->auto_enabled = false;
        $this->assertNull(SibstrReminder::nextRun($settings));
    }

    public function test_scheduler_runs_the_queue_every_minute(): void
    {
        $this->artisan('schedule:list')
            ->expectsOutputToContain('sibstr:proses-pengingat')
            ->assertSuccessful();
    }

    // ─────────────────────────────────────────────────────────
    //  Recipients
    // ─────────────────────────────────────────────────────────

    public function test_recipients_come_from_the_chosen_sources(): void
    {
        // Finished TW II; the Blok I contact comes from the (unfinished) Tahunan record.
        $finishedTw2 = $this->user();
        $this->respondent($finishedTw2);
        $this->sibstr($finishedTw2, 2025, 0, [
            'nama_perusahaan' => 'PT Selesai TW2', 'penghubung' => 'Ani', 'email' => 'ani@tw2.example',
        ]);

        $finishedTw1 = $this->user();
        $this->sibstr($finishedTw1, 2026, 1, ['email' => 'bukan-email', 'telepon_fax' => '0778-123', 'is_completed' => true]);

        $tahunanOnly = $this->user();
        $this->sibstr($tahunanOnly, 2025, 0, ['email' => 'x@tahunan.example', 'is_completed' => true]);

        $inProgress = $this->user();
        $this->sibstr($inProgress, 2026, 3, ['email' => 'kontak@proses.example', 'is_completed' => false]);

        $finishedTw3 = $this->user();
        $this->sibstr($finishedTw3, 2026, 3, ['email' => 'x@selesai.example', 'is_completed' => true]);

        // Finished TW II and already TW III: nothing to chase.
        $finishedBoth = $this->user();
        $this->respondent($finishedBoth, ['email' => 'x@keduanya.example']);
        $this->sibstr($finishedBoth, 2026, 3, ['is_completed' => true]);

        // Opened TW I but never finished anything: matches no source.
        $abandoned = $this->user();
        $this->sibstr($abandoned, 2026, 1, ['email' => 'x@abandoned.example', 'is_completed' => false]);

        $closed = $this->user();
        $this->respondent($closed, ['email' => 'x@tutup.example', 'kondisi_perusahaan' => 'tutup']);

        $mitra = $this->user(User::ROLE_MITRA);
        $this->respondent($mitra, ['email' => 'x@mitra.example']);

        $ids = fn ($c) => $c->map(fn ($r) => $r['user']->id)->sort()->values()->all();
        $sorted = fn (array $a) => collect($a)->sort()->values()->all();
        $chase = fn (array $sources, array $excluded = ['tutup'], ?array $statuses = null)
            => $ids(SibstrReminder::recipients(2026, 3, $sources, $excluded, $statuses));

        $this->assertSame(
            ['2025-0', '2026-1', '2026-2', 'mulai'],
            array_keys(SibstrReminder::sourceOptions(2026, 3))
        );

        // The automatic default for TW III: finished TW I or TW II, or started TW III.
        $this->assertSame(['2026-1', '2026-2', 'mulai'], SibstrReminder::defaultSources(2026, 3));
        $all = SibstrReminder::recipients(2026, 3, SibstrReminder::defaultSources(2026, 3), ['tutup']);
        $this->assertSame($sorted([$finishedTw2->id, $finishedTw1->id, $inProgress->id]), $ids($all));

        // The TW II card: 2 respondents finished it (the Mitra and the closed
        // company do not count), 1 of them already finished TW III, so 1 to chase.
        $totals = SibstrReminder::sourceTotals(SibstrReminder::history(), 2026, 3, ['tutup']);
        $this->assertSame(['finished' => 2, 'done' => 1], $totals['2026-2']);

        // Each source on its own; Tahunan only when ticked.
        $this->assertSame([$finishedTw2->id], $chase(['2026-2']));
        $this->assertSame([$finishedTw1->id], $chase(['2026-1']));
        $this->assertSame([$tahunanOnly->id], $chase(['2025-0']));
        $this->assertSame([$inProgress->id], $chase(['mulai']));

        // The saved statuses narrow the automatic run.
        $this->assertSame([$inProgress->id], $chase(SibstrReminder::defaultSources(2026, 3), ['tutup'], ['dalam_proses']));

        // Chasing TW II: finishing the later TW III is a source too.
        $this->assertContains($finishedTw3->id, $ids(SibstrReminder::recipients(2026, 2, ['2026-3'], [])));

        $first = $all->firstWhere('user.id', $finishedTw2->id);
        $this->assertSame('ani@tw2.example', $first['email']);
        $this->assertSame('Ani', $first['penghubung']);
        $this->assertSame('belum_mulai', $first['status']);
        $this->assertSame('0778-123', $all->firstWhere('user.id', $finishedTw1->id)['telepon']);
        $this->assertNull($all->firstWhere('user.id', $finishedTw1->id)['email']);

        // Without the exclusion the closed company is back.
        $this->assertContains($closed->id, $chase(['2026-2'], []));
    }

    public function test_one_email_per_address_even_when_companies_share_it(): void
    {
        Mail::fake();
        $alfa = $this->user();
        $this->respondent($alfa, ['nama_perusahaan' => 'PT Alfa', 'email' => 'Admin@Grup.example']);
        $beta = $this->user();
        $this->respondent($beta, ['nama_perusahaan' => 'PT Beta', 'email' => ' admin@grup.example ']);
        $gamma = $this->user();
        $this->sibstr($gamma, 2025, 0, ['nama_perusahaan' => 'PT Gamma', 'email' => 'admin@grup.example', 'is_completed' => true]);

        // Three companies from two sources, one address: one entry naming all.
        $list = SibstrReminder::recipients(2026, 3, ['2025-0', '2026-2'], []);
        $this->assertCount(1, $list);
        $this->assertSame(['PT Alfa', 'PT Beta', 'PT Gamma'], $list->first()['companies']);
        $this->assertSame('PT Alfa, PT Beta dan PT Gamma', $list->first()['nama_perusahaan']);

        $bps  = $this->user(User::ROLE_ADMIN);
        $send = fn (array $sources, User $via) => $this->actingAs($bps)->post(route('bps.pengingat.send'), [
            'tahun' => 2026, 'triwulan' => 3, 'sumber' => $sources,
            'user_ids' => [$via->id], 'template_id' => $this->template()->id,
        ]);

        $send(['2026-2'], $alfa)->assertSessionHas('success');
        // Later the same day the address comes up again through PT Gamma: refused.
        $send(['2025-0'], $gamma)->assertSessionHas('error');

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        Mail::assertSent(SibstrTriwulanReminder::class, 1);
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => $m->hasTo('Admin@Grup.example')
            && $m->namaPerusahaan === 'PT Alfa dan PT Beta');
    }

    // ─────────────────────────────────────────────────────────
    //  Automatic run
    // ─────────────────────────────────────────────────────────

    public function test_automatic_run_emails_blok1_address_once_per_reminder_day(): void
    {
        Mail::fake();
        $this->autoSettings(['extra_message' => 'Batas akhir 25 Oktober.']);
        $this->respondent($this->user(), ['nama_perusahaan' => 'PT Satu', 'email' => 'kontak@satu.example']);

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertSent(SibstrTriwulanReminder::class, 1);
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => $m->hasTo('kontak@satu.example')
            && $m->tahun === 2026 && $m->triwulan === 3 && $m->extraMessage === 'Batas akhir 25 Oktober.');
        $this->assertSame(1, SurveyReminderBlast::where('trigger', 'otomatis')->count());
        $this->assertSame('sent', SurveyReminderLog::sole()->status);

        // Not a reminder day.
        $this->travelTo(Carbon::parse('2026-10-14 08:00'));
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        Mail::assertSent(SibstrTriwulanReminder::class, 1);

        // The next reminder day sends again.
        $this->travelTo(Carbon::parse('2026-10-17 08:30'));
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        Mail::assertSent(SibstrTriwulanReminder::class, 2);
    }

    public function test_automatic_run_waits_for_send_time_and_auto_switch(): void
    {
        Mail::fake();
        $this->respondent($this->user(), ['email' => 'kontak@satu.example']);

        $this->autoSettings(['auto_enabled' => false]);
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        $this->autoSettings(['send_time' => '09:00']);
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertNothingSent();
    }

    public function test_server_switch_off_sends_nothing(): void
    {
        Mail::fake();
        $this->autoSettings();
        $this->respondent($this->user(), ['email' => 'kontak@satu.example']);
        config(['sibstr.reminder.enabled' => false]);

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertNothingSent();
        $this->assertSame(0, SurveyReminderLog::count());
    }

    public function test_failed_send_is_recorded_and_can_be_retried(): void
    {
        // A real SMTP mailer pointed at a closed port fails inside send().
        config([
            'mail.mailers.reminder.host' => '127.0.0.1',
            'mail.mailers.reminder.port' => 1,
            'sibstr.reminder.mailer'     => 'reminder',
        ]);
        $this->autoSettings();
        $this->respondent($this->user(), ['email' => 'kontak@satu.example']);

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        $log = SurveyReminderLog::sole();
        $this->assertSame('failed', $log->status);
        $this->assertNotEmpty($log->error);

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('bps.pengingat.retry', $log->blast_id))
            ->assertSessionHas('success');
        $this->assertSame('queued', $log->fresh()->status);
    }

    // ─────────────────────────────────────────────────────────
    //  BPS page
    // ─────────────────────────────────────────────────────────

    public function test_bps_can_send_a_manual_blast_to_selected_respondents(): void
    {
        Mail::fake();
        $picked  = $this->user();
        $skipped = $this->user();
        $this->respondent($picked, ['nama_perusahaan' => 'PT Dipilih', 'email' => 'a@dipilih.example']);
        $this->respondent($skipped, ['nama_perusahaan' => 'PT Tidak', 'email' => 'b@tidak.example']);
        $bps = $this->user(User::ROLE_ADMIN);

        $payload = [
            'tahun'         => 2026,
            'triwulan'      => 3,
            'sumber'        => ['2026-2'],
            'user_ids'      => [$picked->id],
            'template_id'   => $this->template()->id,
            'extra_message' => 'Mohon segera diisi.',
        ];

        $response = $this->actingAs($bps)->post(route('bps.pengingat.send'), $payload);
        $blast = SurveyReminderBlast::sole();
        $response->assertRedirect(route('bps.pengingat.blast', $blast));
        $this->assertSame('manual', $blast->trigger);
        $this->assertSame($bps->id, $blast->created_by);
        $this->assertSame([$picked->id], SurveyReminderLog::pluck('user_id')->all());

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => $m->hasTo('a@dipilih.example')
            && $m->extraMessage === 'Mohon segera diisi.');
        Mail::assertNotSent(SibstrTriwulanReminder::class, fn ($m) => $m->hasTo('b@tidak.example'));

        // The same respondent is not emailed twice on one day.
        $this->actingAs($bps)->post(route('bps.pengingat.send'), $payload)->assertSessionHas('error');
        $this->assertSame(1, SurveyReminderBlast::count());
    }

    public function test_manual_blast_is_refused_when_server_may_not_send(): void
    {
        $user = $this->user();
        $this->respondent($user, ['email' => 'a@dipilih.example']);
        config(['sibstr.reminder.enabled' => false]);

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('bps.pengingat.send'), [
                'tahun' => 2026, 'triwulan' => 3, 'sumber' => ['2026-2'], 'user_ids' => [$user->id],
            ])
            ->assertSessionHas('error');

        $this->assertSame(0, SurveyReminderLog::count());
    }

    public function test_bps_can_save_settings(): void
    {
        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->put(route('bps.pengingat.settings'), [
                'auto_enabled'     => '1',
                'days'             => ['13', '2', '7'],
                'send_time'        => '09:30',
                'target_statuses'  => ['belum_mulai'],
                'excluded_kondisi' => ['tutup'],
                'from_name'        => 'Tim SIBSTR BPS Kota Batam',
                'reply_to'         => 'sibstr@bps.example',
                'extra_message'    => 'Batas akhir 25 Oktober.',
            ])
            ->assertRedirect(route('bps.pengingat.index', ['tab' => 'pengaturan']));

        $s = SurveyReminderSetting::where('survey_type', 'sibstr')->sole();
        $this->assertTrue($s->auto_enabled);
        $this->assertSame([2, 7, 13], $s->days);
        $this->assertSame('09:30', $s->send_time);
        $this->assertSame(['belum_mulai'], $s->target_statuses);
        $this->assertSame('sibstr@bps.example', $s->reply_to);
    }

    public function test_pages_render_for_bps_and_are_closed_to_others(): void
    {
        $this->respondent($this->user(), ['nama_perusahaan' => 'PT Tampil', 'email' => 'a@tampil.example']);
        $this->respondent($this->user(), ['nama_perusahaan' => 'PT Tanpa Email']);
        $bps = $this->user(User::ROLE_ADMIN);

        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'kirim', 'periode' => '2026-3', 'pilih' => 1, 'sumber' => ['2026-2']]))
            ->assertOk()->assertSee('PT Tampil')->assertSee('a@tampil.example')->assertSee('PT Tanpa Email');
        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'pengaturan']))->assertOk()->assertSee('Simpan Pengaturan');
        $this->actingAs($bps)->get(route('bps.pengingat.preview'))->assertOk()->assertSee('PT Contoh Industri Batam');

        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'template']))->assertOk()->assertSee('Pengingat standar');
        $this->actingAs($bps)->get(route('bps.pengingat.template.create'))->assertOk()->assertSee('Kirim Email Uji');
        $this->actingAs($bps)->get(route('bps.pengingat.template.edit', $this->template()))->assertOk()->assertSee('{kalimat_status}');

        $this->actingAs($bps)->post(route('bps.pengingat.send'), [
            'tahun' => 2026, 'triwulan' => 3, 'sumber' => ['2026-2'],
            'user_ids' => SurveyResponse::pluck('user_id')->all(),
            'template_id' => $this->template()->id,
        ]);
        $blast = SurveyReminderBlast::sole();
        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'riwayat']))->assertOk()->assertSee('Manual');
        $this->actingAs($bps)->get(route('bps.pengingat.blast', $blast))->assertOk()->assertSee('Dalam antrean')->assertSee('Pengingat standar');
        $this->actingAs($bps)->get(route('bps.pengingat.blast.email', $blast))->assertOk()->assertSee('PT Tampil');

        $this->actingAs($this->user())->get(route('bps.pengingat.index'))->assertRedirect();
    }

    public function test_send_tab_lists_the_ticked_sources_and_previews_the_chosen_text(): void
    {
        $this->respondent($this->user(), ['nama_perusahaan' => 'PT Selesai TW2', 'email' => 'a@tw2.example']);
        $this->sibstr($this->user(), 2026, 3, ['nama_perusahaan' => 'PT Sedang Isi', 'email' => 'b@proses.example', 'is_completed' => false]);
        $this->sibstr($this->user(), 2025, 0, ['nama_perusahaan' => 'PT Tahunan Saja', 'email' => 'c@tahunan.example', 'is_completed' => true]);
        $bps = $this->user(User::ROLE_ADMIN);
        $kirim = fn (array $query) => $this->actingAs($bps)
            ->get(route('bps.pengingat.index', ['tab' => 'kirim', 'periode' => '2026-3'] + $query))->assertOk();

        // A fresh visit ticks nothing and points at the cards, suggesting the
        // quarter before the chased one; no one is listed yet.
        $this->actingAs($bps)->get(route('bps.pengingat.index'))
            ->assertOk()
            ->assertDontSee('aria-checked="true"', false)
            ->assertSee('Mulai dari sini:')->assertSee('Misalnya <b>Triwulan II 2026</b>', false)
            ->assertDontSee('PT Selesai TW2')->assertDontSee('PT Sedang Isi');

        // Ticking what the automatic run uses lists those companies; Tahunan stays out.
        $kirim(['pilih' => 1, 'sumber' => SibstrReminder::defaultSources(2026, 3)])
            ->assertSee('PT Selesai TW2')->assertSee('PT Sedang Isi')->assertDontSee('PT Tahunan Saja');

        // Ticking only Tahunan lists the Tahunan-only company.
        $kirim(['pilih' => 1, 'sumber' => ['2025-0']])->assertSee('PT Tahunan Saja')->assertDontSee('PT Selesai TW2');

        // Unticking everything stays empty rather than falling back; junk is ignored.
        $kirim(['pilih' => 1, 'sumber' => ['bukan-sumber']])
            ->assertSee('Belum ada sumber data yang dipilih')->assertDontSee('PT Selesai TW2');

        // A choice in step 1 fetches just the section, without the page around it.
        $kirim(['pilih' => 1, 'sumber' => ['2026-2'], 'partial' => 1])
            ->assertSee('id="pg-kirim"', false)->assertSee('PT Selesai TW2')
            ->assertDontSee('<html', false)->assertDontSee('Jadwal &amp; Pengaturan', false);

        // Pratinjau shows the template, quarter and message picked in the form.
        $other = SurveyReminderTemplate::create([
            'survey_type' => 'sibstr', 'name' => 'Khusus', 'subject' => 'Khusus {periode}', 'body' => 'Halo {penghubung}',
        ]);
        $this->actingAs($bps)
            ->get(route('bps.pengingat.preview', ['template' => $other->id, 'periode' => '2026-2', 'pesan' => 'Batas 25 Juli']))
            ->assertOk()
            ->assertSee('Subjek:</strong> Khusus Triwulan II 2026', false)
            ->assertSee('Batas 25 Juli');
    }

    public function test_email_carries_reply_to_and_extra_message(): void
    {
        $mail = new SibstrTriwulanReminder(2026, 3, 'PT Satu', 'Ani', true, "Batas akhir\n25 <b>Oktober</b>", 'Tim SIBSTR', 'sibstr@bps.example');

        $mail->assertHasReplyTo('sibstr@bps.example');
        $mail->assertSeeInHtml('Yth. Ani');
        $mail->assertSeeInHtml('Triwulan III 2026 (Juli–September)');
        // Line breaks kept, HTML typed by BPS escaped.
        $mail->assertSeeInHtml("Batas akhir<br>\n25 &lt;b&gt;Oktober&lt;/b&gt;", false);
        $mail->assertSeeInHtml(route('survey.sibstr.entry'));
        $mail->assertSeeInText('Sdri. Ridha Amalia Hakim (085255557116)');
    }

    // ─────────────────────────────────────────────────────────
    //  Templates
    // ─────────────────────────────────────────────────────────

    public function test_template_fills_placeholders_for_any_quarter(): void
    {
        $vars = ReminderTemplate::variables(2026, 4, '', null, false, 'https://angkabatam.id/survei/sibstr');

        $this->assertSame(
            'SIBSTR Triwulan IV 2026 — Oktober–Desember',
            ReminderTemplate::subject('SIBSTR **Triwulan {triwulan}** {tahun} — {bulan}', $vars)
        );

        [$before, $after] = ReminderTemplate::body(
            "Yth. {penghubung}\n{nama_perusahaan}\n\nIsi **{periode}** <script>x</script>\n\n{tombol}\n\nBuka {link}",
            $vars
        );

        // Empty company name drops its line; penghubung falls back.
        $this->assertSame(
            "<p>Yth. Bapak/Ibu</p>\n<p>Isi <strong>Triwulan IV 2026 (Oktober–Desember)</strong> &lt;script&gt;x&lt;/script&gt;</p>",
            $before
        );
        $this->assertSame(
            '<p>Buka <a href="https://angkabatam.id/survei/sibstr">https://angkabatam.id/survei/sibstr</a></p>',
            $after
        );

        $this->assertSame(['nama'], ReminderTemplate::unknownPlaceholders('Halo {nama} {periode}'));
    }

    public function test_during_tw4_collection_older_quarters_can_still_be_chased(): void
    {
        Mail::fake();
        $this->travelTo(Carbon::parse('2027-01-13 08:00', 'Asia/Jakarta'));
        $this->autoSettings();
        $user = $this->user();
        $this->sibstr($user, 2026, 1, ['nama_perusahaan' => 'PT Lama', 'email' => 'lama@pt.example', 'is_completed' => true]);

        $this->assertSame(
            [['tahun' => 2026, 'triwulan' => 4], ['tahun' => 2026, 'triwulan' => 3],
             ['tahun' => 2026, 'triwulan' => 2], ['tahun' => 2026, 'triwulan' => 1]],
            SibstrReminder::availablePeriods()
        );

        // Automatic run in January reminds about TW IV 2026.
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => $m->triwulan === 4 && $m->tahun === 2026);

        // A manual blast the same day can chase TW II 2026 — a different period.
        $this->actingAs($this->user(User::ROLE_ADMIN))->post(route('bps.pengingat.send'), [
            'tahun' => 2026, 'triwulan' => 2, 'sumber' => ['2026-1'],
            'user_ids' => [$user->id], 'template_id' => $this->template()->id,
        ])->assertSessionHas('success');
        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertSent(SibstrTriwulanReminder::class, function ($m) {
            return $m->triwulan === 2
                && str_contains($m->envelope()->subject, 'Triwulan II 2026')
                && str_contains($m->render(), 'Triwulan II 2026 (April–Juni)');
        });
    }

    public function test_bps_can_create_update_and_delete_templates(): void
    {
        $bps = $this->user(User::ROLE_ADMIN);
        $standard = $this->template();

        $this->actingAs($bps)->post(route('bps.pengingat.template.store'), [
            'name' => 'Batas akhir', 'subject' => 'Batas {tanggal}', 'body' => 'Halo',
        ])->assertSessionHasErrors('subject');

        $this->actingAs($bps)->post(route('bps.pengingat.template.store'), [
            'name' => 'Batas akhir', 'subject' => 'Segera isi SIBSTR {periode}', 'body' => "Yth. {penghubung}\n\nBatas akhir sudah dekat.",
        ])->assertRedirect(route('bps.pengingat.index', ['tab' => 'template']));
        $new = SurveyReminderTemplate::where('name', 'Batas akhir')->sole();
        $this->assertSame($bps->id, $new->created_by);

        $this->actingAs($bps)->put(route('bps.pengingat.template.update', $new), [
            'name' => 'Batas akhir v2', 'subject' => 'Segera isi {periode}', 'body' => 'Teks baru',
        ])->assertRedirect();
        $this->assertSame('Batas akhir v2', $new->fresh()->name);

        // The template used by the automatic run cannot be deleted.
        $this->autoSettings(['template_id' => $new->id]);
        $this->actingAs($bps)->delete(route('bps.pengingat.template.destroy', $new))->assertSessionHas('error');

        $this->actingAs($bps)->delete(route('bps.pengingat.template.destroy', $standard))->assertSessionHas('success');
        $this->assertSame(1, SurveyReminderTemplate::count());

        // ...nor the last one.
        $this->autoSettings(['template_id' => null]);
        $this->actingAs($bps)->delete(route('bps.pengingat.template.destroy', $new))->assertSessionHas('error');
        $this->assertSame(1, SurveyReminderTemplate::count());
    }

    public function test_test_email_sends_unsaved_text_to_the_given_address_only(): void
    {
        Mail::fake();
        config(['sibstr.reminder.enabled' => false]); // a test email is fine on any server
        $this->respondent($this->user(), ['email' => 'responden@pt.example']);

        $form = [
            '_method'         => 'PUT', // as sent from the edit form
            'name'            => 'Draft',
            'subject'         => 'Coba {periode}',
            'body'            => "Halo {penghubung} dari {nama_perusahaan}",
            'test_email'      => 'petugas@bps.example',
            'test_periode'    => '2026-4',
            'test_status'     => 'dalam_proses',
            'test_nama'       => 'PT Uji',
            'test_penghubung' => 'Budi',
        ];

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('bps.pengingat.template.test'), $form)
            ->assertSessionHas('success');

        Mail::assertSent(SibstrTriwulanReminder::class, 1);
        Mail::assertSent(SibstrTriwulanReminder::class, function ($m) {
            return $m->hasTo('petugas@bps.example')
                && $m->envelope()->subject === '[UJI COBA] Coba Triwulan IV 2026 (Oktober–Desember)'
                && str_contains($m->render(), 'Halo Budi dari PT Uji');
        });
        $this->assertSame(0, SurveyReminderTemplate::where('name', 'Draft')->count());

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('bps.pengingat.template.preview'), $form)
            ->assertOk()
            ->assertSee('Subjek:</strong> Coba Triwulan IV 2026', false)
            ->assertSee('Halo Budi dari PT Uji', false);
    }

    public function test_blast_keeps_the_text_it_was_queued_with(): void
    {
        Mail::fake();
        $template = $this->template();
        $template->update(['subject' => 'Versi lama {periode}']);
        $this->respondent($this->user(), ['email' => 'kontak@satu.example']);

        app(SibstrReminderSender::class)->queue(
            2026, 3, SibstrReminder::recipients(2026, 3, ['2026-2'], []),
            SurveyReminderBlast::TRIGGER_MANUAL, self::BOTH, $template, null, null
        );
        $template->update(['subject' => 'Versi baru {periode}']);

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertSent(SibstrTriwulanReminder::class, 1);
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => str_starts_with($m->envelope()->subject, 'Versi lama'));
    }
}
