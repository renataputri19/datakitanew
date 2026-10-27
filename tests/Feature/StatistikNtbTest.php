<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * NTB on /bps/statistik follows the BPS formula 301 + 302 − 312 − 313.
 * Royalti, bunga, dividen (304) stays out of it, and out of the per-company
 * "Pendapatan" (nilaiProduksi) that the Rincian table now shows.
 */
class StatistikNtbTest extends TestCase
{
    use RefreshDatabase;

    private function respondent(): User
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_USER);
        $user->save();

        return $user->fresh();
    }

    private function sibstr(string $nama, string $kbli, array $attrs): void
    {
        SurveyResponse::create(array_merge([
            'user_id'         => $this->respondent()->id,
            'survey_type'     => 'sibstr',
            'survey_section'  => 'blok1',
            'tahun'           => 2026,
            'triwulan'        => 2,
            'nama_perusahaan' => $nama,
            'kbli_utama'      => $kbli,
            'is_completed'    => true,
        ], $attrs));
    }

    private function rows(): array
    {
        $bps = User::factory()->create();
        $bps->setRole(User::ROLE_ADMIN);
        $bps->save();

        $rows = $this->actingAs($bps)->get(route('bps.statistik.index', ['tahun' => 2026]))
            ->assertOk()
            ->viewData('payload')['rows'];

        return collect($rows)->keyBy('perusahaan')->all();
    }

    public function test_ntb_is_output_minus_biaya_antara_without_royalti(): void
    {
        $this->sibstr('PT Industri', '26199', [
            'blok3a_products' => [[
                'jenis_barang' => 'Komponen',
                'satuan'       => 'unit',
                'banyaknya'    => ['2026_apr' => 1, '2026_mei' => 1, '2026_jun' => 1],
                // the December bridge month is not part of the quarter
                'nilai'        => ['2025_des' => 999, '2026_apr' => 100, '2026_mei' => 200, '2026_jun' => 300],
            ]],
            'blok3a_lainnya'       => ['uraian' => 'Jasa', 'nilai' => ['2026_apr' => 50]],
            'blok3b_industri_data' => ['q304' => 1000, 'q310' => 77, 'q311' => 88, 'q312_tw' => 120, 'q313_tw' => 30],
        ]);
        $this->sibstr('PT Dagang', '47111', [
            'blok3b_nonindustri_data' => ['q303' => 400, 'q304' => 500, 'q312_tw' => 100],
        ]);
        $this->sibstr('PT Tanpa Biaya', '26199', [
            'blok3a_products' => [['jenis_barang' => 'X', 'satuan' => 'unit', 'nilai' => ['2026_apr' => 10]]],
            'blok3b_industri_data' => ['q304' => 5],
        ]);

        $rows = $this->rows();

        // 301 + 302 − 312 − 313 = (600 + 50) − (120 + 30)
        $this->assertEquals(650, $rows['PT Industri']['nilaiProduksi']);
        $this->assertEquals(150, $rows['PT Industri']['biayaAntara']);
        $this->assertEquals(500, $rows['PT Industri']['ntb']);
        // the total still carries royalti for the KPI tile and surplus
        $this->assertEquals(1650, $rows['PT Industri']['pendapatanTotal']);

        // non-industri has no Blok IIIA: 303 − 312 − 313, 304 left out
        $this->assertEquals(400, $rows['PT Dagang']['nilaiProduksi']);
        $this->assertEquals(300, $rows['PT Dagang']['ntb']);

        // nothing reported on the cost side: NTB is unknown, not output − 0
        $this->assertNull($rows['PT Tanpa Biaya']['biayaAntara']);
        $this->assertNull($rows['PT Tanpa Biaya']['ntb']);
    }
}
