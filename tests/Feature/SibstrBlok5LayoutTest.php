<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blok V renders one card per component instead of a 920px table, so the
 * Prospek answers are reachable on a phone without a sideways swipe.
 *
 * The stored field names (blok5[501][p1] …) must not change with the layout:
 * saved answers, autosave and the BPS views all read them.
 */
class SibstrBlok5LayoutTest extends TestCase
{
    use RefreshDatabase;

    private function render(int $triwulan, array $blok5Data = []): string
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_USER);
        $user->save();
        $this->actingAs($user);

        $response = SurveyResponse::create([
            'user_id'        => $user->id,
            'survey_type'    => 'sibstr',
            'survey_section' => 'blok5',
            'tahun'          => $triwulan ? 2026 : 2025,
            'triwulan'       => $triwulan,
            'blok5_data'     => $blok5Data,
        ]);

        return view('survey.sibstr.blok5', [
            'surveyResponse'    => $response,
            'tahun'             => $triwulan ? 2026 : 2025,
            'triwulan'          => $triwulan,
            'period'            => $triwulan ? (string) $triwulan : 'tahunan',
            'referenceResponse' => null,
            'kbliPrefix'        => 26,
        ])->render();
    }

    public function test_triwulanan_renders_kondisi_and_prospek_cards_without_a_table(): void
    {
        $html = $this->render(2, ['501' => ['p1' => 'naik', 'p2' => 'turun']]);

        $this->assertStringNotContainsString('<table', $html);
        $this->assertStringContainsString('TW II-2026 vs TW I-2026', $html);
        $this->assertStringContainsString('TW III-2026 vs TW II-2026', $html);

        // 7 components × 2 periods × 3 options, all required
        $this->assertSame(42, preg_match_all('/<input type="radio"[^>]*name="blok5\[\d{3}\]\[p[12]\]"[^>]*required/', $html));
        $this->assertSame(0, preg_match_all('/name="blok5\[\d{3}\]\[p[3-6]\]"/', $html));

        // saved answers come back selected
        $this->assertMatchesRegularExpression('/name="blok5\[501\]\[p1\]" value="naik" checked/', $html);
        $this->assertMatchesRegularExpression('/name="blok5\[501\]\[p2\]" value="turun" checked/', $html);

        // 506 keeps its own answer set
        $this->assertMatchesRegularExpression('/name="blok5\[506\]\[p2\]" value="lebih_lambat"/', $html);
    }

    public function test_triwulan_iv_prospek_rolls_into_next_year(): void
    {
        $html = $this->render(4);

        $this->assertStringContainsString('TW IV-2026 vs TW III-2026', $html);
        $this->assertStringContainsString('TW I-2027 vs TW IV-2026', $html);
    }

    public function test_tahunan_keeps_its_five_periods_optional(): void
    {
        $html = $this->render(0);

        foreach (['p1', 'p2', 'p3', 'p5', 'p6'] as $p) {
            $this->assertSame(21, preg_match_all('/name="blok5\[\d{3}\]\[' . $p . '\]"/', $html), $p);
        }
        $this->assertSame(0, preg_match_all('/name="blok5\[[^"]*" [^>]*required/', $html));
        $this->assertStringContainsString('TW I-2025 vs TW IV-2024', $html);
        $this->assertStringContainsString('TW I-2026 vs TW IV-2025', $html);
    }
}
