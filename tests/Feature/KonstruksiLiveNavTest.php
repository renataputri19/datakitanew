<?php

namespace Tests\Feature;

use App\Models\KonstruksiSurveyResponse as K;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The blok rail refreshes itself after an autosave.
 *
 * "Keberadaan Proyek" autosaves the moment it is picked, and the questionnaire
 * shortens on that answer — Blok III on SKTR, Blok III–IX on SKTH. konstruksi.js
 * re-fetches survey.konstruksi.nav rather than reimplementing the rule, so what
 * matters here is that the endpoint reflects the freshly autosaved answer
 * immediately, and for the right period.
 */
class KonstruksiLiveNavTest extends TestCase
{
    use RefreshDatabase;

    private function responden(): User
    {
        return User::factory()->create();
    }

    private function autosaveUrl(string $period, string $section): string
    {
        return "/survei/konstruksi/" . ($period === 'tahunan' ? 2025 : 2026) . "/{$period}/{$section}/autosave";
    }

    private function navUrl(string $period, string $current = 'status'): string
    {
        $year = $period === 'tahunan' ? 2025 : 2026;

        return "/survei/konstruksi/{$year}/{$period}/nav?current={$current}";
    }

    /** The rail rows the endpoint currently serves, as blok keys. */
    private function navKeys(User $user, string $period, string $current = 'status'): array
    {
        $html = $this->actingAs($user)->get($this->navUrl($period, $current))->getContent();

        preg_match_all('/data-k-nav="([a-z0-9]+)"/', $html, $m);

        return $m[1];
    }

    private function pickKeberadaan(User $user, string $period, string $answer): void
    {
        $this->actingAs($user)
            ->postJson($this->autosaveUrl($period, 'status'), [
                'field' => 'keberadaan_proyek',
                'value' => $answer,
            ])
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_quarterly_rail_drops_blok3_as_soon_as_no_project_is_autosaved(): void
    {
        $user = $this->responden();

        // Opening the page creates the row and seeds the preamble.
        $this->actingAs($user)->get('/survei/konstruksi/2026/1/status')->assertOk();

        $this->assertSame(['status', 'blok1', 'blok2', 'blok3', 'penutup'], $this->navKeys($user, '1'));

        $this->pickKeberadaan($user, '1', '2'); // Tidak Ada Proyek Konstruksi
        $this->assertSame(['status', 'blok1', 'blok2', 'penutup'], $this->navKeys($user, '1'));

        // ...and comes straight back when they change their mind.
        $this->pickKeberadaan($user, '1', '1');
        $this->assertSame(['status', 'blok1', 'blok2', 'blok3', 'penutup'], $this->navKeys($user, '1'));
    }

    public function test_annual_rail_collapses_to_blok1_blok2_and_blok10_when_no_project_is_autosaved(): void
    {
        $user = $this->responden();

        $this->actingAs($user)->get('/survei/konstruksi/2025/tahunan/status')->assertOk();

        $this->assertSame(
            ['status', 'blok1', 'blok2', 'blok3', 'blok4', 'blok5', 'blok6', 'blok7', 'blok8', 'blok9', 'blok10'],
            $this->navKeys($user, 'tahunan')
        );

        $this->pickKeberadaan($user, 'tahunan', '2');
        $this->assertSame(['status', 'blok1', 'blok2', 'blok10'], $this->navKeys($user, 'tahunan'));

        $this->pickKeberadaan($user, 'tahunan', '1');
        $this->assertCount(11, $this->navKeys($user, 'tahunan'));
    }

    public function test_autosaving_a_closing_status_ends_the_rail_at_the_closing_block(): void
    {
        $user = $this->responden();
        $this->actingAs($user)->get('/survei/konstruksi/2025/tahunan/status')->assertOk();

        $this->actingAs($user)->postJson($this->autosaveUrl('tahunan', 'status'), [
            'field' => 'status_perusahaan',
            'value' => '3', // Tutup
        ])->assertOk();

        $this->assertSame(['status', 'blok10'], $this->navKeys($user, 'tahunan'));
    }

    /**
     * The endpoint takes the period from the URL, not from the response row.
     * Reading it off a missing row would fall back to the annual path and render
     * a ten-block rail on a quarterly page.
     */
    public function test_the_rail_uses_the_period_in_the_url_even_with_no_response_row_yet(): void
    {
        $user = $this->responden();

        $this->assertSame(0, K::where('user_id', $user->id)->count());

        $keys = $this->navKeys($user, '1');
        $this->assertSame(['status', 'blok1', 'blok2', 'blok3', 'penutup'], $keys);
    }

    /** The page ships the two attributes konstruksi.js reads to wire itself. */
    public function test_the_rail_carries_the_attributes_the_refresher_needs(): void
    {
        $user = $this->responden();

        $this->actingAs($user)
            ->get('/survei/konstruksi/2025/tahunan/status')
            ->assertOk()
            ->assertSee('data-k-nav-url="' . route('survey.konstruksi.nav', ['year' => 2025, 'period' => 'tahunan']) . '"', false)
            ->assertSee('data-k-nav-current="status"', false);
    }
}
