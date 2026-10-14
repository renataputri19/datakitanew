<?php

namespace Tests\Unit;

use App\Models\KonstruksiSurveyResponse as K;
use App\Support\KonstruksiBlokPath as Path;
use Tests\TestCase;

/**
 * The "Keberadaan Proyek" branch, on both instruments.
 *
 * Answering "Tidak Ada Proyek Konstruksi" ends the questionnaire after Blok II
 * — SKTR drops Blok III, SKTH drops Blok III–IX — because everything past Blok
 * II describes the work itself. Answering "Ada" makes the project block
 * mandatory with at least one fully described project.
 *
 * Every assertion here works on an unsaved model, so the suite's MySQL
 * requirement does not apply.
 */
class KonstruksiKeberadaanProyekTest extends TestCase
{
    private const AKTIF_BERSEDIA = [
        'status_perusahaan'   => '1',
        'kesediaan_responden' => '1',
    ];

    private function response(array $attrs = []): K
    {
        $r = new K();
        $r->tahun    = 2026;
        $r->triwulan = 1;

        foreach ($attrs as $key => $value) {
            $r->{$key} = $value;
        }

        return $r;
    }

    /** One project with every column the questionnaire asks for. */
    private function completeProyek(): array
    {
        return [[
            'nama_pekerjaan'  => 'Pembangunan Jalan A',
            'kbli'            => '42101',
            'provinsi_lokasi' => 'Kepulauan Riau',
            'kabkota_lokasi'  => 'Batam',
            'kode_kabkota'    => '2171',
            'sumber_dana'     => '1',
            'nilai_kontrak'   => '1.000.000',
            'pct_subkontrak'  => '0',
            'mulai'           => '01/2026',
            'selesai'         => '06/2026',
            'realisasi_2lalu' => '0',
            'realisasi_lalu'  => '10',
            'realisasi_ini'   => '30',
            'realisasi_depan' => '50',
            'upah_lalu'       => '10.000',
            'upah_ini'        => '20.000',
            'upah_rata_lalu'  => '150',
            'upah_rata_ini'   => '160',
        ]];
    }

    public function test_no_project_drops_blok3_from_the_path(): void
    {
        $r = $this->response(self::AKTIF_BERSEDIA + ['keberadaan_proyek' => '2']);

        $this->assertSame(['status', 'blok1', 'blok2', 'penutup'], Path::path(1, $r));
        $this->assertFalse($r->proyekBlokApplies());
        // The responden still has to fill the rest before finishing.
        $this->assertTrue(Path::isComplete($r, 'blok3'));
        $this->assertSame('blok1', Path::firstIncomplete($r));
    }

    public function test_having_projects_keeps_blok3_and_requires_at_least_one_row(): void
    {
        $r = $this->response(self::AKTIF_BERSEDIA + ['keberadaan_proyek' => '1']);

        $this->assertSame(['status', 'blok1', 'blok2', 'blok3', 'penutup'], Path::path(1, $r));
        $this->assertFalse($r->isBlok3TwComplete(), 'Blok III with no project rows must not count as complete.');

        $r->data_proyek_tw = [K::emptyProyekTwRow()];
        $this->assertFalse($r->isBlok3TwComplete(), 'An untouched blank row is not a project.');

        $r->data_proyek_tw = $this->completeProyek();
        $this->assertTrue($r->isBlok3TwComplete());
    }

    public function test_a_project_row_missing_any_figure_is_incomplete(): void
    {
        foreach (['nilai_kontrak', 'pct_subkontrak', 'realisasi_ini', 'upah_ini', 'upah_rata_ini'] as $col) {
            $rows          = $this->completeProyek();
            $rows[0][$col] = '';

            $r = $this->response(self::AKTIF_BERSEDIA + [
                'keberadaan_proyek' => '1',
                'data_proyek_tw'    => $rows,
            ]);

            $this->assertFalse($r->isBlok3TwComplete(), "Blok III should be incomplete when {$col} is blank.");
        }
    }

    /**
     * Rows typed before the answer was changed to "Tidak Ada" must stop being
     * reported — otherwise a no-project submission shows projects on the BPS
     * listing, the PDF and the export.
     */
    public function test_project_rows_are_not_reported_once_the_answer_says_there_are_none(): void
    {
        $r = $this->response(self::AKTIF_BERSEDIA + [
            'keberadaan_proyek' => '1',
            'data_proyek_tw'    => $this->completeProyek(),
        ]);
        $this->assertCount(1, $r->proyekTwRows());
        $this->assertGreaterThan(0, $r->totalProyekTw()['nilai_kontrak']);

        $r->keberadaan_proyek = '2';
        $this->assertSame([], $r->proyekTwRows());
        $this->assertSame(0.0, $r->totalProyekTw()['nilai_kontrak']);
        $this->assertSame(0, $r->proyekCount());

        // Non-destructive: switching back restores the responden's work.
        $r->keberadaan_proyek = '1';
        $this->assertCount(1, $r->proyekTwRows());
    }

    /**
     * A blank keberadaan proyek is not a "no". Blok III stays on the rail until
     * the question is actually answered, or a first visit would bounce.
     */
    public function test_blank_keberadaan_keeps_blok3_reachable(): void
    {
        foreach ([$this->response(), $this->response(self::AKTIF_BERSEDIA)] as $r) {
            $this->assertTrue($r->proyekBlokApplies());
            $this->assertContains('blok3', Path::path(1, $r));
            $this->assertFalse(Path::isComplete($r, 'blok3'));
        }
    }

    /** A company that stops at the preamble never reaches Blok III at all. */
    public function test_a_company_that_stops_at_the_preamble_skips_every_blok(): void
    {
        $r = $this->response(['status_perusahaan' => '2', 'kesediaan_responden' => '1']);

        $this->assertSame(['status', 'penutup'], Path::path(1, $r));
        $this->assertFalse($r->proyekBlokApplies());
    }

    // ── Annual (SKTH) ─────────────────────────────────────────────────────
    //
    // Same preamble, same stop — Blok I and Blok II survive a nil year, and
    // Blok III–IX (modal, pengeluaran, barang modal, the project list, bahan,
    // pendapatan lain, ringkasan) all go.

    private function annual(array $attrs = []): K
    {
        return $this->response(['triwulan' => K::TAHUNAN] + $attrs);
    }

    public function test_the_annual_form_runs_all_eleven_steps_when_there_are_projects(): void
    {
        $r = $this->annual(self::AKTIF_BERSEDIA + ['keberadaan_proyek' => '1']);

        $this->assertSame(
            ['status', 'blok1', 'blok2', 'blok3', 'blok4', 'blok5', 'blok6', 'blok7', 'blok8', 'blok9', 'blok10'],
            Path::path(K::TAHUNAN, $r)
        );
        $this->assertTrue($r->proyekBlokApplies());
    }

    public function test_the_annual_form_opens_on_the_preamble_not_blok1(): void
    {
        $this->assertSame('status', Path::path(K::TAHUNAN)[0]);
        // With no row at all the landing page must still send them to 'status'.
        $this->assertSame('status', Path::firstIncomplete(null, K::TAHUNAN));
    }

    /**
     * Blok II is "Pekerja Tetap dan Kontrak" — payroll that exists whether or
     * not there was a project — so it survives a nil year on both instruments.
     * Blok III onwards all describe the work itself.
     */
    public function test_no_projects_ends_the_annual_survey_after_blok2(): void
    {
        $r = $this->annual(self::AKTIF_BERSEDIA + ['keberadaan_proyek' => '2']);

        $this->assertSame(['status', 'blok1', 'blok2', 'blok10'], Path::path(K::TAHUNAN, $r));
        $this->assertFalse($r->proyekBlokApplies());

        // Blok III–IX are never asked, so their emptiness cannot block finishing.
        foreach (['blok3', 'blok4', 'blok5', 'blok6', 'blok7', 'blok8', 'blok9'] as $skipped) {
            $this->assertNotContains($skipped, Path::path(K::TAHUNAN, $r));
        }
    }

    /** Both instruments stop at the same place when there are no projects. */
    public function test_both_instruments_stop_after_blok2_when_there_are_no_projects(): void
    {
        $attrs = self::AKTIF_BERSEDIA + ['keberadaan_proyek' => '2'];

        $annual    = Path::path(K::TAHUNAN, $this->annual($attrs));
        $quarterly = Path::path(1, $this->response($attrs));

        // Same shape, only the closing block's name differs between the forms.
        $this->assertSame(array_slice($annual, 0, 3), array_slice($quarterly, 0, 3));
        $this->assertSame(['status', 'blok1', 'blok2'], array_slice($annual, 0, 3));
        $this->assertSame('blok10', end($annual));
        $this->assertSame('penutup', end($quarterly));
    }

    public function test_an_annual_company_that_stops_at_the_preamble_only_files_the_closing_block(): void
    {
        $r = $this->annual(['status_perusahaan' => '3', 'kesediaan_responden' => '1']);

        $this->assertSame(['status', 'blok10'], Path::path(K::TAHUNAN, $r));
    }

    public function test_a_blank_annual_preamble_still_shows_the_whole_questionnaire(): void
    {
        $r = $this->annual();

        $this->assertCount(11, Path::path(K::TAHUNAN, $r));
        $this->assertTrue($r->proyekBlokApplies());
        $this->assertFalse(Path::isComplete($r, 'status'));
    }

    /** Blok VI's rows follow the same rule as Blok III's on the quarterly form. */
    public function test_annual_project_rows_are_hidden_once_the_answer_says_there_are_none(): void
    {
        $rows = [['nama_pekerjaan' => 'Gedung B', 'nilai_kontrak' => '500.000']];

        $r = $this->annual(self::AKTIF_BERSEDIA + [
            'keberadaan_proyek' => '1',
            'data_proyek'       => $rows,
        ]);
        $this->assertCount(1, $r->proyekRows());
        $this->assertSame(1, $r->proyekCount());

        $r->keberadaan_proyek = '2';
        $this->assertSame([], $r->proyekRows());
        $this->assertSame(0, $r->proyekCount());
    }

    /** The preamble is one question set, so completeness is judged identically. */
    public function test_the_annual_preamble_needs_keberadaan_proyek_before_it_counts_as_done(): void
    {
        $r = $this->annual(self::AKTIF_BERSEDIA);
        $this->assertFalse($r->isStatusComplete());
        $this->assertSame('status', Path::firstIncomplete($r));

        $r->keberadaan_proyek = '2';
        $this->assertTrue($r->isStatusComplete());
        $this->assertSame('blok1', Path::firstIncomplete($r));
    }
}
