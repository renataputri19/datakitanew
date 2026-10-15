<?php

namespace Tests\Feature;

use App\Models\SurveyReminderBlast;
use App\Models\SurveyReminderLog;
use App\Models\SurveyReminderTemplate;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * /bps/pengingat asks before it emails or deletes anything, in its own
 * dialog (bps.pengingat.partials.confirm) instead of the browser's confirm().
 */
class PengingatConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // TW III 2026 is open for reminders.
        $this->travelTo(Carbon::parse('2026-10-13 08:00', 'Asia/Jakarta'));
        config(['sibstr.reminder.enabled' => true, 'sibstr.reminder.send_delay_ms' => 0]);
    }

    private function user(string $role = User::ROLE_USER): User
    {
        $user = User::factory()->create();
        $user->setRole($role);
        $user->save();

        return $user->fresh();
    }

    /** Finished TW II 2026, so it is chased for TW III. */
    private function respondent(string $name, string $email): User
    {
        $user = $this->user();
        SurveyResponse::create([
            'user_id' => $user->id, 'survey_type' => 'sibstr', 'survey_section' => 'blok1',
            'tahun' => 2026, 'triwulan' => 2, 'is_completed' => true,
            'nama_perusahaan' => $name, 'email' => $email,
        ]);

        return $user;
    }

    public function test_send_form_asks_in_the_page_dialog(): void
    {
        $this->respondent('PT Satu', 'a@satu.example');
        $bps = $this->user(User::ROLE_ADMIN);
        $query = ['tab' => 'kirim', 'periode' => '2026-3', 'pilih' => 1, 'sumber' => ['2026-2']];

        $page = $this->actingAs($bps)->get(route('bps.pengingat.index', $query));
        $page->assertOk()
            ->assertSee('<dialog id="pg-confirm"', false)
            ->assertSee('autofocus>Batal</button>', false)
            ->assertSee('pgConfirm({', false)
            ->assertDontSee('return confirm(', false)
            ->assertDontSee('if (!confirm(', false);
        $this->assertSame(1, substr_count($page->getContent(), 'id="pg-confirm"'));

        // Step 1 swaps only #pg-kirim; the dialog lives outside it and survives.
        $this->actingAs($bps)->get(route('bps.pengingat.index', $query + ['partial' => 1]))
            ->assertOk()
            ->assertSee('id="pg-send-form"', false)
            ->assertDontSee('id="pg-confirm"', false);
    }

    public function test_template_delete_asks_with_the_escaped_name(): void
    {
        // Hapus shows on every template but the automatic one (the oldest),
        // so seed the default first and create ours a minute later.
        SurveyReminderTemplate::allFor('sibstr');
        $this->travel(1)->minutes();
        SurveyReminderTemplate::create([
            'survey_type' => 'sibstr', 'name' => 'Khusus "A" & <b>', 'subject' => 'S {periode}', 'body' => 'Halo',
        ]);

        $this->actingAs($this->user(User::ROLE_ADMIN))
            ->get(route('bps.pengingat.index', ['tab' => 'template']))
            ->assertOk()
            ->assertSee('data-confirm="Hapus template ini?"', false)
            ->assertSee('data-confirm-tone="danger"', false)
            ->assertSee('Template “Khusus &quot;A&quot; &amp; &lt;b&gt;” dihapus permanen', false)
            ->assertDontSee('return confirm(', false);
    }

    public function test_retry_asks_first_and_auto_refresh_waits_for_the_dialog(): void
    {
        $a = $this->respondent('PT Gagal', 'a@gagal.example');
        $this->respondent('PT Antre', 'b@antre.example');
        $bps = $this->user(User::ROLE_ADMIN);

        $this->actingAs($bps)->post(route('bps.pengingat.send'), [
            'tahun' => 2026, 'triwulan' => 3, 'sumber' => ['2026-2'],
            'user_ids' => SurveyResponse::pluck('user_id')->all(),
            'template_id' => SurveyReminderTemplate::allFor('sibstr')->first()->id,
        ]);
        $blast = SurveyReminderBlast::sole();
        SurveyReminderLog::where('user_id', $a->id)->update(['status' => SurveyReminderLog::STATUS_FAILED]);

        // One failed, one still queued: the retry button and the auto-refresh are both live.
        $page = $this->actingAs($bps)->get(route('bps.pengingat.blast', $blast));
        $page->assertOk()
            ->assertSee('data-confirm="Kirim ulang email yang gagal?"', false)
            ->assertSee('1 email yang gagal masuk antrean lagi', false)
            ->assertSee('<dialog id="pg-confirm"', false)
            ->assertSee("document.querySelector('dialog[open]')", false)
            ->assertDontSee('return confirm(', false);

        // The meta refresh would reload out from under the dialog; it is now no-JS only.
        $html = $page->getContent();
        $this->assertSame(1, substr_count($html, 'http-equiv="refresh"'));
        $this->assertStringContainsString('<noscript><meta http-equiv="refresh" content="20"></noscript>', $html);
    }
}
