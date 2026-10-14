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

    public function test_recipients_respect_status_and_kondisi_filters(): void
    {
        $notStarted = $this->user();
        $this->sibstr($notStarted, 2025, 0, [
            'nama_perusahaan' => 'PT Belum Mulai', 'penghubung' => 'Ani', 'email' => 'ani@belum.example',
        ]);

        $inProgress = $this->user();
        $this->sibstr($inProgress, 2026, 3, ['email' => 'kontak@proses.example', 'is_completed' => false]);

        $done = $this->user();
        $this->sibstr($done, 2026, 3, ['email' => 'x@selesai.example', 'is_completed' => true]);

        $closed = $this->user();
        $this->sibstr($closed, 2026, 2, ['email' => 'x@tutup.example', 'kondisi_perusahaan' => 'tutup']);

        $mitra = $this->user(User::ROLE_MITRA);
        $this->sibstr($mitra, 2026, 2, ['email' => 'x@mitra.example']);

        $noEmail = $this->user();
        $this->sibstr($noEmail, 2026, 1, ['email' => 'bukan-email', 'telepon_fax' => '0778-123']);

        $ids = fn ($c) => $c->map(fn ($r) => $r['user']->id)->sort()->values()->all();
        $sorted = fn (array $a) => collect($a)->sort()->values()->all();

        $all = SibstrReminder::recipients(2026, 3, self::BOTH, ['tutup']);
        $this->assertSame($sorted([$notStarted->id, $inProgress->id, $noEmail->id]), $ids($all));

        $first = $all->firstWhere('user.id', $notStarted->id);
        $this->assertSame('ani@belum.example', $first['email']);
        $this->assertSame('Ani', $first['penghubung']);
        $this->assertSame('belum_mulai', $first['status']);
        $this->assertSame('0778-123', $all->firstWhere('user.id', $noEmail->id)['telepon']);
        $this->assertNull($all->firstWhere('user.id', $noEmail->id)['email']);

        // Only those who started but did not finish.
        $this->assertSame([$inProgress->id], $ids(SibstrReminder::recipients(2026, 3, ['dalam_proses'], ['tutup'])));

        // Without the exclusion the closed company is back.
        $this->assertContains($closed->id, $ids(SibstrReminder::recipients(2026, 3, self::BOTH, [])));
    }

    // ─────────────────────────────────────────────────────────
    //  Automatic run
    // ─────────────────────────────────────────────────────────

    public function test_automatic_run_emails_blok1_address_once_per_reminder_day(): void
    {
        Mail::fake();
        $this->autoSettings(['extra_message' => 'Batas akhir 25 Oktober.']);
        $this->sibstr($this->user(), 2025, 0, ['nama_perusahaan' => 'PT Satu', 'email' => 'kontak@satu.example']);

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
        $this->sibstr($this->user(), 2025, 0, ['email' => 'kontak@satu.example']);

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
        $this->sibstr($this->user(), 2025, 0, ['email' => 'kontak@satu.example']);
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
        $this->sibstr($this->user(), 2025, 0, ['email' => 'kontak@satu.example']);

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
        $this->sibstr($picked, 2025, 0, ['nama_perusahaan' => 'PT Dipilih', 'email' => 'a@dipilih.example']);
        $this->sibstr($skipped, 2025, 0, ['nama_perusahaan' => 'PT Tidak', 'email' => 'b@tidak.example']);
        $bps = $this->user(User::ROLE_ADMIN);

        $payload = [
            'tahun'         => 2026,
            'triwulan'      => 3,
            'status'        => ['belum_mulai'],
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
        $this->sibstr($user, 2025, 0, ['email' => 'a@dipilih.example']);
        config(['sibstr.reminder.enabled' => false]);

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->post(route('bps.pengingat.send'), [
                'tahun' => 2026, 'triwulan' => 3, 'status' => ['belum_mulai'], 'user_ids' => [$user->id],
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
        $this->sibstr($this->user(), 2025, 0, ['nama_perusahaan' => 'PT Tampil', 'email' => 'a@tampil.example']);
        $this->sibstr($this->user(), 2025, 0, ['nama_perusahaan' => 'PT Tanpa Email']);
        $bps = $this->user(User::ROLE_ADMIN);

        $this->actingAs($bps)->get(route('bps.pengingat.index'))
            ->assertOk()->assertSee('PT Tampil')->assertSee('a@tampil.example')->assertSee('PT Tanpa Email');
        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'pengaturan']))->assertOk()->assertSee('Simpan Pengaturan');
        $this->actingAs($bps)->get(route('bps.pengingat.preview'))->assertOk()->assertSee('PT Contoh Industri Batam');

        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'template']))->assertOk()->assertSee('Pengingat standar');
        $this->actingAs($bps)->get(route('bps.pengingat.template.create'))->assertOk()->assertSee('Kirim Email Uji');
        $this->actingAs($bps)->get(route('bps.pengingat.template.edit', $this->template()))->assertOk()->assertSee('{kalimat_status}');

        $this->actingAs($bps)->post(route('bps.pengingat.send'), [
            'tahun' => 2026, 'triwulan' => 3, 'status' => ['belum_mulai'],
            'user_ids' => SurveyResponse::pluck('user_id')->all(),
            'template_id' => $this->template()->id,
        ]);
        $blast = SurveyReminderBlast::sole();
        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'riwayat']))->assertOk()->assertSee('Manual');
        $this->actingAs($bps)->get(route('bps.pengingat.blast', $blast))->assertOk()->assertSee('Dalam antrean')->assertSee('Pengingat standar');
        $this->actingAs($bps)->get(route('bps.pengingat.blast.email', $blast))->assertOk()->assertSee('PT Tampil');

        $this->actingAs($this->user())->get(route('bps.pengingat.index'))->assertRedirect();
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
            'tahun' => 2026, 'triwulan' => 2, 'status' => ['belum_mulai'],
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
        $this->sibstr($this->user(), 2025, 0, ['email' => 'responden@pt.example']);

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
        $this->sibstr($this->user(), 2025, 0, ['email' => 'kontak@satu.example']);

        app(SibstrReminderSender::class)->queue(
            2026, 3, SibstrReminder::recipients(2026, 3, self::BOTH, []),
            SurveyReminderBlast::TRIGGER_MANUAL, self::BOTH, $template, null, null
        );
        $template->update(['subject' => 'Versi baru {periode}']);

        $this->artisan('sibstr:proses-pengingat')->assertSuccessful();

        Mail::assertSent(SibstrTriwulanReminder::class, 1);
        Mail::assertSent(SibstrTriwulanReminder::class, fn ($m) => str_starts_with($m->envelope()->subject, 'Versi lama'));
    }
}
