<?php

namespace Tests\Feature;

use App\Models\UbSurveyResponse;
use App\Models\User;
use App\Support\PerPage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator;
use Tests\TestCase;

/**
 * The shared BPS pagination bar (bps.partials.pagination) and the page-size
 * clamp behind the "Tampilkan" dropdown.
 */
class BpsPaginationTest extends TestCase
{
    use RefreshDatabase;

    private function bpsUser(): User
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_ADMIN); // Admin BPS — setRole() does not persist
        $user->save();

        return $user->fresh();
    }

    /** Render the bar for a list of $total rows, viewed at $page. */
    private function bar(int $total, int $page, array $query = []): string
    {
        $perPage = PerPage::DEFAULT;
        $onPage  = max(0, min($perPage, $total - ($page - 1) * $perPage));

        return (new LengthAwarePaginator(array_fill(0, $onPage, 'row'), $total, $perPage, $page, [
            'path'  => 'http://localhost/bps/ub',
            'query' => $query,
        ]))->links('bps.partials.pagination')->toHtml();
    }

    /** The visible page slots in order, gaps as "…". */
    private function slots(string $html): array
    {
        preg_match_all('/bps-pg-gap"|bps-pg-num"[^>]*>([\d.]+)</', $html, $m);

        return array_map(fn ($num) => $num === '' ? '…' : $num, $m[1]);
    }

    public function test_page_size_is_clamped_to_the_dropdown_options(): void
    {
        $this->assertSame(15, PerPage::from(null));
        $this->assertSame(15, PerPage::from(''));
        $this->assertSame(50, PerPage::from('50'));
        $this->assertSame(100, PerPage::from(100));
        $this->assertSame(15, PerPage::from('100000'));
        $this->assertSame(15, PerPage::from('25abc'));
        $this->assertSame(15, PerPage::from(['25']));
    }

    public function test_short_lists_show_every_page_and_no_jump_box(): void
    {
        $html = $this->bar(82, 2); // 6 pages

        $this->assertSame(['1', '2', '3', '4', '5', '6'], $this->slots($html));
        $this->assertStringNotContainsString('bps-pg-jump', $html);
        $this->assertStringContainsString('<b>16–30</b>', $html);
    }

    public function test_long_lists_keep_a_fixed_seven_slot_window(): void
    {
        // 512 rows at 15 per page = 35 pages.
        $this->assertSame(['1', '2', '3', '4', '5', '…', '35'], $this->slots($this->bar(512, 1)));
        $this->assertSame(['1', '2', '3', '4', '5', '…', '35'], $this->slots($this->bar(512, 4)));
        $this->assertSame(['1', '…', '9', '10', '11', '…', '35'], $this->slots($this->bar(512, 10)));
        $this->assertSame(['1', '…', '31', '32', '33', '34', '35'], $this->slots($this->bar(512, 32)));
        $this->assertSame(['1', '…', '31', '32', '33', '34', '35'], $this->slots($this->bar(512, 35)));
    }

    public function test_summary_uses_indonesian_thousands_separator(): void
    {
        $html = $this->bar(1500, 100);

        $this->assertStringContainsString('<b>1.486–1.500</b>', $html);
        $this->assertStringContainsString('<b>1.500</b> data', $html);
    }

    public function test_jump_box_resends_the_filters_but_not_the_page(): void
    {
        $html = $this->bar(512, 10, ['search' => 'PT Maju & Co', 'tags' => ['a', 'b']]);

        $this->assertStringContainsString('class="bps-pg-jump"', $html);
        $this->assertStringContainsString('max="35"', $html);
        $this->assertStringContainsString('name="search" value="PT Maju &amp; Co"', $html);
        $this->assertStringContainsString('name="tags[0]" value="a"', $html);
        $this->assertStringContainsString('name="tags[1]" value="b"', $html);
        $this->assertStringNotContainsString('type="hidden" name="page"', $html);
    }

    public function test_empty_list_renders_nothing_and_page_past_the_end_does_not_crash(): void
    {
        $this->assertSame('', trim($this->bar(0, 1)));

        $html = $this->bar(40, 9);
        $this->assertStringContainsString('Halaman ini kosong', $html);
        $this->assertStringContainsString('aria-label="Halaman 1"', $html);
    }

    public function test_survey_page_defaults_to_15_and_ignores_unknown_sizes(): void
    {
        $bps = $this->bpsUser();
        foreach (range(1, 20) as $i) {
            UbSurveyResponse::create([
                'user_id' => User::factory()->create()->id, 'tahun' => 2026, 'survey_section' => 'blok1a',
                'nama_perusahaan' => "PT Halaman {$i}",
            ]);
        }

        $default = $this->actingAs($bps)->get(route('bps.ub.index'));
        $default->assertOk();
        $default->assertViewHas('surveyResponses', fn ($p) => $p->perPage() === 15 && $p->lastPage() === 2);
        $default->assertSee('bps-pg-nav', false);
        // The partial pushes its CSS from inside ->links(); it must still land in the layout.
        $this->assertMatchesRegularExpression('/<head>.*\.bps-pg-btn \{.*<\/head>/s', $default->getContent());
        $default->assertSee('<option value="15" selected>15 per halaman</option>', false);

        $this->actingAs($bps)->get(route('bps.ub.index', ['per_page' => 100000]))
            ->assertViewHas('surveyResponses', fn ($p) => $p->perPage() === 15);

        $this->actingAs($bps)->get(route('bps.ub.index', ['per_page' => 50]))
            ->assertViewHas('surveyResponses', fn ($p) => $p->perPage() === 50);
    }

    public function test_mitra_survey_lists_use_the_shared_bar(): void
    {
        $mitra = User::factory()->create();
        $mitra->setRole(User::ROLE_MITRA); // setRole() does not persist
        $mitra->save();
        $mitra = $mitra->fresh();

        foreach (range(1, 20) as $i) {
            UbSurveyResponse::create([
                'user_id' => User::factory()->create()->id, 'tahun' => 2026, 'survey_section' => 'blok1a',
                'nama_perusahaan' => "PT Mitra {$i}",
            ]);
        }

        $ub = $this->actingAs($mitra)->get(route('survey.ub.entry', ['per_page' => 100000]));
        $ub->assertOk();
        $ub->assertViewHas('surveyResponses', fn ($p) => $p->perPage() === 15 && $p->lastPage() === 2);
        $ub->assertSee('bps-pg-nav', false);
        // Mitra pages sit on layouts.user-dashboard → layouts.app; the CSS must reach that <head> too.
        $this->assertMatchesRegularExpression('/<head>.*\.bps-pg-btn \{.*<\/head>/s', $ub->getContent());

        $this->actingAs($mitra)->get(route('survey.sibstr.entry', ['per_page' => 50]))
            ->assertOk()
            ->assertViewHas('surveyResponses', fn ($p) => $p->perPage() === 50);
    }

    public function test_every_bps_list_renders_the_shared_bar(): void
    {
        $bps = $this->bpsUser();

        // Users always has at least the viewer, so its summary must show.
        $this->actingAs($bps)->get(route('bps.users.index'))
            ->assertOk()
            ->assertSee('bps-pg-summary', false)
            ->assertSee('</b> pengguna', false);

        // A page past the end must not 500.
        $this->actingAs($bps)->get(route('bps.users.index', ['page' => 999]))
            ->assertOk()
            ->assertSee('Halaman ini kosong');

        foreach (['bps.news.index', 'bps.videos.index', 'bps.sibstr.index', 'bps.listrik.index', 'bps.konstruksi.index'] as $route) {
            $this->actingAs($bps)->get(route($route))->assertOk();
        }
        $this->actingAs($bps)->get(route('bps.pengingat.index', ['tab' => 'riwayat']))->assertOk();
    }
}
