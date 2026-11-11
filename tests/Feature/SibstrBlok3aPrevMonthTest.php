<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Blok IIIA opens with a "bulan sebelumnya" column: the month just before
 * the reporting period. TW I (and tahunan) use Des of the previous year;
 * TW II/III/IV use the previous quarter's last month (Mar/Jun/Sep), not a
 * Des that is a whole year stale by TW IV.
 *
 * The column is optional, and old answers stored under the previous year's
 * Des key are left in the JSON untouched; they are just no longer shown.
 */
class SibstrBlok3aPrevMonthTest extends TestCase
{
    use RefreshDatabase;

    public function test_prev_month_follows_the_quarter(): void
    {
        $this->assertSame(['2025_des' => 'Des 2025'], SurveyResponse::blok3aPrevMonth(2026, 0));
        $this->assertSame(['2025_des' => 'Des 2025'], SurveyResponse::blok3aPrevMonth(2026, 1));
        $this->assertSame(['2026_mar' => 'Mar 2026'], SurveyResponse::blok3aPrevMonth(2026, 2));
        $this->assertSame(['2026_jun' => 'Jun 2026'], SurveyResponse::blok3aPrevMonth(2026, 3));
        $this->assertSame(['2026_sep' => 'Sep 2026'], SurveyResponse::blok3aPrevMonth(2026, 4));
    }

    private function response(int $tahun, int $triwulan, array $attrs = []): SurveyResponse
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_USER);
        $user->save();
        $this->actingAs($user);

        return SurveyResponse::create(array_merge([
            'user_id'            => $user->id,
            'survey_type'        => 'sibstr',
            'survey_section'     => 'blok3a',
            'tahun'              => $tahun,
            'triwulan'           => $triwulan,
            'kondisi_perusahaan' => 'masih_aktif',
        ], $attrs));
    }

    private function quarterConf(int $tahun, int $triwulan): array
    {
        $html = view('survey.sibstr.blok3a', [
            'surveyResponse'      => $this->response($tahun, $triwulan),
            'kbliPrefix'          => 26,
            'tahun'               => $tahun,
            'triwulan'            => $triwulan,
            'period'              => $triwulan ? (string) $triwulan : 'tahunan',
            'historicalResponses' => collect(),
        ])->render();

        $this->assertSame(1, preg_match('/quarterConf:\s*(\{.*?\}),\s*\n\s*firstQuarter/s', $html, $m), 'quarterConf not found in page');

        return json_decode($m[1], true);
    }

    public function test_form_tw2_opens_with_march_not_last_december(): void
    {
        $conf = $this->quarterConf(2026, 2);

        $this->assertSame(['prev_month', 'q2'], array_keys($conf));
        $this->assertSame('Mar 2026', $conf['prev_month']['label']);
        $this->assertSame(['2026_mar'], $conf['prev_month']['months']);
        $this->assertSame(['2026_apr', '2026_mei', '2026_jun'], $conf['q2']['months']);
    }

    public function test_form_tw1_and_tahunan_keep_last_december(): void
    {
        $tw1 = $this->quarterConf(2026, 1);
        $this->assertSame(['2025_des'], $tw1['prev_month']['months']);
        $this->assertSame('Des 2025', $tw1['prev_month']['label']);

        $tahunan = $this->quarterConf(2025, 0);
        $this->assertSame(['prev_month', 'q1', 'q2', 'q3', 'q4'], array_keys($tahunan));
        $this->assertSame(['2024_des'], $tahunan['prev_month']['months']);
    }

    public function test_bps_view_shows_the_quarters_prev_month_and_hides_the_old_december(): void
    {
        $response = $this->response(2026, 3, [
            'blok3a_products' => [[
                'jenis_barang' => 'Komponen',
                'satuan'       => 'unit',
                // 2025_des was filled before this change; it stays stored but unseen
                'nilai'        => ['2025_des' => '111.111', '2026_jun' => '222.222', '2026_jul' => '333.333'],
            ]],
        ]);

        $html = view('bps.sibstr.data.blok3a', ['surveyResponse' => $response])->render();

        $this->assertStringContainsString('Jun 2026', $html);
        $this->assertStringContainsString('222.222', $html);
        $this->assertStringContainsString('333.333', $html);
        $this->assertStringNotContainsString('Des 2025', $html);
        $this->assertStringNotContainsString('111.111', $html);

        // nothing was rewritten: the old value is still in the row
        $this->assertSame('111.111', $response->fresh()->blok3a_products[0]['nilai']['2025_des']);
    }
}
