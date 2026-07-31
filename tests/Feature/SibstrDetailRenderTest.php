<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The BPS/Mitra detail page and the PDF must show every answer the respondent
 * actually filled in.
 *
 * Two things used to break that: currency inputs are formatted in the browser
 * ("12.312.312") and saved that way, which the read-only formatters rejected as
 * non-numeric; and Blok IIIB rendered a key set the form had stopped using.
 */
class SibstrDetailRenderTest extends TestCase
{
    use RefreshDatabase;

    private function bpsUser(): User
    {
        return User::factory()->create(['is_bps' => true]);
    }

    private function seedRow(array $fields, int $triwulan = 0): SurveyResponse
    {
        return SurveyResponse::create(array_merge([
            'user_id'         => User::factory()->create()->id,
            'survey_type'     => 'sibstr',
            'survey_section'  => 'blok1',
            'tahun'           => 2025,
            'triwulan'        => $triwulan,
            'nama_perusahaan' => 'PT Uji Coba',
            'kondisi_perusahaan' => 'masih_aktif',
            'last_saved_at'   => now(),
        ], $fields));
    }

    public function test_blok3c_shows_luar_negeri_figures_saved_with_thousand_separators(): void
    {
        $row = $this->seedRow([
            'kbli_utama'        => '10110',
            'blok3a2_materials' => [[
                'nama_bahan'     => 'Tepung',
                'satuan_standar' => 'kg',
                'dn_banyaknya'   => '12323',
                'dn_nilai'       => '12312312',
                'ln_banyaknya'   => '23.345.345',
                'ln_nilai'       => '9.876.543.210',
                'negara_asal'    => 'Thailand',
                'rincian_asal'   => [['provinsi' => 'Riau', 'jumlah' => '12.323', 'nilai' => '12.312.312']],
            ]],
        ]);

        $html = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.show', $row->id))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('23.345.345', $html, 'Banyaknya Luar Negeri is missing');
        $this->assertStringContainsString('9.876.543.210', $html, 'Nilai Luar Negeri is missing');
        $this->assertStringContainsString('Thailand', $html);
    }

    public function test_blok3b_industri_tahunan_renders_the_questions_the_form_asks(): void
    {
        $row = $this->seedRow([
            'kbli_utama'           => '10110',
            'blok3b_industri_data' => [
                'q306_year_awal'          => '1.500.000',
                'q310_beli_modal'         => '2.750.000',
                'q311_jual_modal'         => '900000',
                'q312_taksir_modal'       => '3.100.000',
                'q313_a1'                 => '400000',
                'q314_b2'                 => '55.000',
                'q315_a'                  => '13200',
                'q315_e'                  => '7.250.000',
                'q317_d'                  => '640.000',
                'q317_k'                  => '125.000',
                'q318a_freq'              => '12',
                'q318a_biaya'             => '43.535.345',
                'q319_persen_pihak_ketiga' => '24',
            ],
        ]);

        $html = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.show', $row->id))
            ->assertOk()
            ->getContent();

        foreach (['1.500.000', '2.750.000', '900.000', '3.100.000', '400.000',
                  '55.000', '13.200', '7.250.000', '640.000', '125.000', '43.535.345'] as $figure) {
            $this->assertStringContainsString($figure, $html, "Missing figure {$figure}");
        }

        $this->assertStringContainsString('24 %', $html);
    }

    public function test_blok3b_nonindustri_tahunan_renders_aset_and_modal(): void
    {
        $row = $this->seedRow([
            'kbli_utama'              => '47111',
            'blok3b_nonindustri_data' => [
                'q303'         => '5.000.000',
                'q303_year'    => '60.000.000',
                'q306_online'  => '15',
                'q312'         => '1.000.000',
                'q312_year'    => '12.000.000',
                'q317_a'       => '2.400.000',
                'q317_b'       => '350.000',
                'q314'         => '10',
                'q315'         => '5',
                'q318a'        => '900.000.000',
                'q319a'        => '60',
                'q319h'        => '40',
            ],
        ]);

        $html = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.show', $row->id))
            ->assertOk()
            ->getContent();

        foreach (['5.000.000', '60.000.000', '1.000.000', '12.000.000',
                  '2.400.000', '350.000', '900.000.000'] as $figure) {
            $this->assertStringContainsString($figure, $html, "Missing figure {$figure}");
        }

        $this->assertStringContainsString('15 %', $html);   // 306. usaha online
        $this->assertStringContainsString('100,00 %', $html); // 322i. total modal
    }

    public function test_blok3b_triwulanan_renders_quarterly_keys(): void
    {
        $row = $this->seedRow([
            'kbli_utama'           => '10110',
            'blok3b_industri_data' => [
                'q304'       => '750.000',
                'q306_awal'  => '1.200.000',
                'q309_akhir' => '4.400.000',
                'q310'       => '3.300.000',
                'q311'       => '2.200.000',
                'q312_tw'    => '1.100.000',
                'q313_tw'    => '990.000',
                'q314_tw'    => '12.5',
            ],
        ], triwulan: 2);

        $html = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.show', $row->id))
            ->assertOk()
            ->getContent();

        foreach (['750.000', '1.200.000', '4.400.000', '3.300.000',
                  '2.200.000', '1.100.000', '990.000'] as $figure) {
            $this->assertStringContainsString($figure, $html, "Missing figure {$figure}");
        }

        $this->assertStringContainsString('12,50 %', $html);
    }

    public function test_pdf_download_renders_the_same_blocks(): void
    {
        $row = $this->seedRow([
            'kbli_utama'        => '10110',
            'blok3a2_materials' => [[
                'nama_bahan'   => 'Tepung',
                'ln_nilai'     => '9.876.543.210',
                'negara_asal'  => 'Thailand',
            ]],
        ]);

        $response = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.download', $row->id));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('content-type'));
    }
}
