<?php

namespace Tests\Feature;

use App\Models\ListrikSurveyResponse;
use App\Models\SurveyResponse;
use App\Models\UbSurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Feature tests for the BPS "Export Excel" monitoring downloads on the three
 * survey data pages.
 *
 * The filters are the fragile part — SIBSTR reads "selesai" from a different
 * column per period type, and /export sits next to the /{id} show routes — so
 * these cover the filter semantics and the route ordering rather than the
 * spreadsheet formatting.
 */
class BpsSurveyExportTest extends TestCase
{
    use RefreshDatabase;

    private ?User $bps = null;

    private function bpsUser(): User
    {
        if ($this->bps === null) {
            $user = User::factory()->create();
            $user->setRole(User::ROLE_ADMIN); // Admin BPS — setRole() does not persist
            $user->save();
            $this->bps = $user->fresh();
        }

        return $this->bps;
    }

    /**
     * A SIBSTR row for one period; $finished applies the right per-type column.
     *
     * $name must be unique across a test — exportedIds() matches rows back to
     * records by company name.
     */
    private function sibstr(User $owner, string $name, int $tahun, int $triwulan, bool $finished): SurveyResponse
    {
        return SurveyResponse::create([
            'user_id'              => $owner->id,
            'survey_type'          => 'sibstr',
            'survey_section'       => 'blok1',
            'tahun'                => $tahun,
            'triwulan'             => $triwulan,
            'nama_perusahaan'      => $name,
            'is_completed'         => $triwulan > 0 ? $finished : false,
            'annual_survey_status' => ($triwulan === 0 && $finished) ? 'FINISH_SURVEY' : null,
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  Access
    // ─────────────────────────────────────────────────────────

    public function test_export_routes_require_a_bps_user(): void
    {
        $plain = User::factory()->create();

        foreach (['bps.sibstr.export', 'bps.ub.export', 'bps.listrik.export'] as $route) {
            $this->actingAs($plain)->get(route($route))->assertRedirect();
        }
    }

    public function test_every_survey_data_page_renders_the_export_dialog(): void
    {
        foreach (['bps.sibstr.index', 'bps.ub.index', 'bps.listrik.index'] as $route) {
            $response = $this->actingAs($this->bpsUser())->get(route($route));

            $response->assertOk();
            $response->assertSee('bpsOpenExportModal', false);
            $response->assertSee('bpsExportForm', false);
        }
    }

    public function test_export_url_is_not_swallowed_by_the_show_route(): void
    {
        // /bps/sibstr/export must resolve to export(), not show('export').
        $response = $this->actingAs($this->bpsUser())->get(route('bps.sibstr.export'));

        $response->assertOk();
        $this->assertStringContainsString(
            'spreadsheetml',
            $response->headers->get('content-type') ?? ''
        );
    }

    // ─────────────────────────────────────────────────────────
    //  SIBSTR filters
    // ─────────────────────────────────────────────────────────

    public function test_sibstr_completed_filter_reads_the_right_column_per_period_type(): void
    {
        $a = $this->bpsUser();
        $b = User::factory()->create();
        $c = User::factory()->create();
        $d = User::factory()->create();

        $doneAnnual  = $this->sibstr($a, 'PT Tahunan Selesai',   2025, 0, true);
        $openAnnual  = $this->sibstr($b, 'PT Tahunan Berjalan',  2025, 0, false);
        $doneQuarter = $this->sibstr($c, 'PT Triwulan Selesai',  2026, 2, true);
        $openQuarter = $this->sibstr($d, 'PT Triwulan Berjalan', 2026, 2, false);

        // A Tahunan row with is_completed set but no FINISH_SURVEY is still open.
        $openAnnual->update(['is_completed' => true]);

        $completed = $this->exportedIds('bps.sibstr.export', ['status' => 'completed'], SurveyResponse::class);
        $this->assertEqualsCanonicalizing([$doneAnnual->id, $doneQuarter->id], $completed);

        $inProgress = $this->exportedIds('bps.sibstr.export', ['status' => 'in_progress'], SurveyResponse::class);
        $this->assertEqualsCanonicalizing([$openAnnual->id, $openQuarter->id], $inProgress);
    }

    public function test_sibstr_period_and_year_filters_narrow_the_export(): void
    {
        $a = $this->bpsUser();
        $b = User::factory()->create();

        $annual2025 = $this->sibstr($a, 'PT Tahunan Dua Lima', 2025, 0, false);
        $tw1_2026   = $this->sibstr($a, 'PT Triwulan Satu',   2026, 1, false);
        $tw3_2026   = $this->sibstr($b, 'PT Triwulan Tiga',   2026, 3, false);

        $this->assertEqualsCanonicalizing(
            [$annual2025->id],
            $this->exportedIds('bps.sibstr.export', ['type' => 'tahunan'], SurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$tw1_2026->id, $tw3_2026->id],
            $this->exportedIds('bps.sibstr.export', ['type' => 'triwulanan'], SurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$tw3_2026->id],
            $this->exportedIds('bps.sibstr.export', ['triwulan' => '3'], SurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$annual2025->id],
            $this->exportedIds('bps.sibstr.export', ['year' => '2025'], SurveyResponse::class)
        );

        // No filters at all means every period of every year.
        $this->assertCount(3, $this->exportedIds('bps.sibstr.export', [], SurveyResponse::class));
    }

    public function test_sibstr_date_range_filters_on_last_update_in_wib(): void
    {
        $old = $this->sibstr($this->bpsUser(), 'PT Lama', 2025, 0, false);
        $new = $this->sibstr(User::factory()->create(), 'PT Baru', 2025, 0, false);

        // 1 Jan 2026 00:30 WIB is still 31 Dec 2025 in UTC — the WIB day is what
        // the dialog offers, so the row must fall inside a 2026-01-01 range.
        $old->forceFill(['updated_at' => '2025-06-01 03:00:00'])->save();
        $new->forceFill(['updated_at' => '2025-12-31 17:30:00'])->save();

        $this->assertEqualsCanonicalizing(
            [$new->id],
            $this->exportedIds('bps.sibstr.export', ['date_from' => '2026-01-01'], SurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$old->id],
            $this->exportedIds('bps.sibstr.export', ['date_to' => '2025-12-31'], SurveyResponse::class)
        );
    }

    // ─────────────────────────────────────────────────────────
    //  UB + Listrik filters
    // ─────────────────────────────────────────────────────────

    public function test_ub_blok1_filter_requires_all_four_sub_blocks(): void
    {
        $full = UbSurveyResponse::create([
            'user_id' => $this->bpsUser()->id, 'tahun' => 2026, 'survey_section' => 'blok1a',
            'nama_perusahaan' => 'PT Lengkap', 'kabupaten_kota' => 'Kota Batam',
            'blok1a_completed' => true, 'blok1b_completed' => true,
            'blok1c_completed' => true, 'blok1d_completed' => true,
        ]);

        $partial = UbSurveyResponse::create([
            'user_id' => User::factory()->create()->id, 'tahun' => 2026, 'survey_section' => 'blok1a',
            'nama_perusahaan' => 'PT Separuh', 'kabupaten_kota' => 'Kab. Bintan',
            'blok1a_completed' => true, 'blok1b_completed' => true,
            'blok1c_completed' => false, 'blok1d_completed' => false,
        ]);

        $this->assertEqualsCanonicalizing(
            [$full->id],
            $this->exportedIds('bps.ub.export', ['blok1' => 'complete'], UbSurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$partial->id],
            $this->exportedIds('bps.ub.export', ['blok1' => 'incomplete'], UbSurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$partial->id],
            $this->exportedIds('bps.ub.export', ['kabupaten_kota' => 'Kab. Bintan'], UbSurveyResponse::class)
        );
    }

    public function test_listrik_grid_and_plant_filters_narrow_the_export(): void
    {
        $done = ListrikSurveyResponse::create([
            'user_id' => $this->bpsUser()->id, 'tahun' => 2026, 'survey_section' => 'blok1',
            'nama_perusahaan' => 'PT Listrik A', 'jenis_pembangkit' => 'PLTU',
            'blok1_completed' => true, 'blok2_completed' => true,
        ]);

        $open = ListrikSurveyResponse::create([
            'user_id' => User::factory()->create()->id, 'tahun' => 2026, 'survey_section' => 'blok1',
            'nama_perusahaan' => 'PT Listrik B', 'jenis_pembangkit' => 'PLTD',
            'blok1_completed' => true, 'blok2_completed' => false,
        ]);

        $this->assertEqualsCanonicalizing(
            [$done->id],
            $this->exportedIds('bps.listrik.export', ['grid' => 'complete'], ListrikSurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$open->id],
            $this->exportedIds('bps.listrik.export', ['grid' => 'incomplete'], ListrikSurveyResponse::class)
        );

        $this->assertEqualsCanonicalizing(
            [$open->id],
            $this->exportedIds('bps.listrik.export', ['jenis_pembangkit' => 'PLTD'], ListrikSurveyResponse::class)
        );
    }

    public function test_csv_format_is_honoured(): void
    {
        $this->sibstr($this->bpsUser(), 'PT Format', 2025, 0, false);

        $response = $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.export', ['format' => 'csv']));

        $response->assertOk();
        $this->assertStringContainsString('.csv', $response->headers->get('content-disposition') ?? '');
    }

    // ─────────────────────────────────────────────────────────
    //  Helper
    // ─────────────────────────────────────────────────────────

    /**
     * Hit an export route and report which records landed in the sheet.
     *
     * Forces the CSV writer so the download is greppable — an xlsx is a zip —
     * then matches rows back by company name, unique per fixture here.
     *
     * @return list<int|string>
     */
    private function exportedIds(string $route, array $params, string $model): array
    {
        $response = $this->actingAs($this->bpsUser())
            ->get(route($route, $params + ['format' => 'csv']));
        $response->assertOk();

        $body = file_get_contents($response->baseResponse->getFile()->getPathname());

        return $model::all()
            ->filter(fn ($record) => str_contains($body, $record->nama_perusahaan))
            ->pluck('id')
            ->values()
            ->all();
    }
}
