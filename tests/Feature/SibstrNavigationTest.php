<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Comprehensive feature tests for the SIBSTR sequential navigation guard.
 *
 * Covers:
 *  - No block-skipping: users cannot jump to a later block without completing earlier ones
 *  - KBLI branching: Industri (10–33) vs Non-Industri paths
 *  - Block 2 validation: kondisi_perusahaan (R201) and jaringan_unit_kegiatan (R202)
 *  - Non-active company fast-path to Blok 6
 *  - Backward-compat: existing surveys without blok3a_completed flag are not incorrectly blocked
 */
class SibstrNavigationTest extends TestCase
{
    use RefreshDatabase;

    private const YEAR   = 2025;
    private const PERIOD = 'tahunan';

    // ─────────────────────────────────────────────────────────
    //  Helpers
    // ─────────────────────────────────────────────────────────

    private function makeUser(): User
    {
        return User::factory()->create();
    }

    private function blokUrl(string $blok): string
    {
        return "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/{$blok}";
    }

    private function blokRoute(string $suffix): string
    {
        return route("survey.sibstr.{$suffix}", [
            'year'   => self::YEAR,
            'period' => self::PERIOD,
        ]);
    }

    /**
     * Create (or return) the single SurveyResponse row for a user in the
     * 2025 Tahunan period and merge $fields into it.
     */
    private function seedResponse(User $user, array $fields = []): SurveyResponse
    {
        $row = SurveyResponse::firstOrCreate(
            [
                'user_id'     => $user->id,
                'survey_type' => 'sibstr',
                'tahun'       => self::YEAR,
                'triwulan'    => 0,
            ],
            ['survey_section' => 'blok1', 'last_saved_at' => now()]
        );

        if (!empty($fields)) {
            $row->fill($fields)->save();
        }

        return $row->fresh();
    }

    // ─────────────────────────────────────────────────────────
    //  1. Unauthenticated access
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function unauthenticated_user_is_redirected_to_login(): void
    {
        $response = $this->get($this->blokUrl('blok1'));
        $response->assertRedirect('/login');
    }

    // ─────────────────────────────────────────────────────────
    //  2. Block 1 – always accessible
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok1_is_accessible_without_any_prior_data(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get($this->blokUrl('blok1'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    //  3. Block 2 – requires Block 1 (nama_perusahaan set)
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok2_redirects_to_blok1_when_blok1_not_complete(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get($this->blokUrl('blok2'));

        $response->assertRedirect($this->blokRoute('blok1'));
    }

    /** @test */
    public function blok2_is_accessible_after_blok1_is_complete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test Industri']);

        $response = $this->actingAs($user)->get($this->blokUrl('blok2'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    //  4. Block 2 validation – R201 (kondisi_perusahaan) and
    //     R202 (jaringan_unit_kegiatan) are the critical gates
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function saveAllBlok2_rejects_missing_kondisi_perusahaan(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test']);

        $response = $this->actingAs($user)->postJson(
            "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok2/save-all",
            []
        );

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['errors' => ['kondisi_perusahaan']]);
    }

    /** @test */
    public function saveAllBlok2_rejects_missing_jaringan_when_masih_aktif(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test']);

        $response = $this->actingAs($user)->postJson(
            "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok2/save-all",
            ['kondisi_perusahaan' => 'masih_aktif']
        );

        $response->assertStatus(422);
        $response->assertJsonPath('success', false);
        $response->assertJsonStructure(['errors' => ['jaringan_unit_kegiatan']]);
    }

    /** @test */
    public function saveAllBlok2_accepts_non_active_status_without_jaringan(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test']);

        $response = $this->actingAs($user)->postJson(
            "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok2/save-all",
            ['kondisi_perusahaan' => 'tutup']
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);
    }

    /** @test */
    public function saveAllBlok2_persists_kondisi_perusahaan_and_jaringan_to_database(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test']);

        // unit_pembantu_penunjang exempts all the required_unless downstream fields,
        // allowing a minimal payload to pass validation.
        $response = $this->actingAs($user)->postJson(
            "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok2/save-all",
            [
                'kondisi_perusahaan'     => 'masih_aktif',
                'jaringan_unit_kegiatan' => 'unit_pembantu_penunjang',
                'kbli_utama'             => '13100',
            ]
        );

        $response->assertStatus(200);
        $response->assertJsonPath('success', true);

        $this->assertDatabaseHas('survey_responses', [
            'user_id'                => $user->id,
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'unit_pembantu_penunjang',
            'kbli_utama'             => '13100',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    //  5. Non-active company: fast-path to Blok 6
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok6_is_accessible_for_non_active_company_after_blok2(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'    => 'PT Tutup',
            'kondisi_perusahaan' => 'tutup',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertStatus(200);
    }

    /** @test */
    public function blok3a_is_inaccessible_for_non_active_company(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'    => 'PT Tutup',
            'kondisi_perusahaan' => 'tutup',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok3a'));

        // Non-active path redirects wrong-path blocks to blok6
        $response->assertRedirect($this->blokRoute('blok6'));
    }

    /** @test */
    public function blok5_is_inaccessible_for_non_active_company(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'    => 'PT Tutup',
            'kondisi_perusahaan' => 'tutup',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok5'));

        $response->assertRedirect($this->blokRoute('blok6'));
    }

    // ─────────────────────────────────────────────────────────
    //  6. Active + Industri (KBLI 10–33) sequential path
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok6_redirects_to_blok2_when_no_survey_data_exists(): void
    {
        $user = $this->makeUser();

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertRedirect($this->blokRoute('blok1'));
    }

    /** @test */
    public function blok6_redirects_to_blok2_when_blok2_not_submitted(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, ['nama_perusahaan' => 'PT Test']);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertRedirect($this->blokRoute('blok2'));
    }

    /** @test */
    public function blok6_redirects_to_blok3a_for_industri_company_with_no_blok3a(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Tekstil',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '13100',  // KBLI 13 = tekstil (industri)
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertRedirect($this->blokRoute('blok3a'));
    }

    /** @test */
    public function blok3b_industri_redirects_to_blok3a_when_blok3a_not_complete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Tekstil',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '13100',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok3b-industri'));

        $response->assertRedirect($this->blokRoute('blok3a'));
    }

    /** @test */
    public function blok4_redirects_to_blok3b_industri_when_that_block_not_complete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Tekstil',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '13100',
            'blok3a_products'        => [['nama_produk' => 'Kain Tenun', 'nilai' => 1000]],
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok4'));

        $response->assertRedirect($this->blokRoute('blok3b.industri'));
    }

    /** @test */
    public function blok4_redirects_to_blok3c_industri_when_blok3c_not_complete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'             => 'PT Tekstil',
            'kondisi_perusahaan'          => 'masih_aktif',
            'jaringan_unit_kegiatan'      => 'tunggal',
            'kbli_utama'                  => '13100',
            'blok3a_products'             => [['jenis_barang' => 'Kain', 'uraian' => 'Kain Tenun']],
            'blok3b_industri_completed'   => true,
            // blok3a2_completed (blok3c) intentionally not set
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok4'));

        $response->assertRedirect($this->blokRoute('blok3c.industri'));
    }

    /** @test */
    public function blok5_redirects_to_blok4_when_blok4_not_complete_industri(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'             => 'PT Tekstil',
            'kondisi_perusahaan'          => 'masih_aktif',
            'jaringan_unit_kegiatan'      => 'tunggal',
            'kbli_utama'                  => '13100',
            'blok3a_products'             => [['jenis_barang' => 'Kain', 'uraian' => 'Kain Tenun']],
            'blok3b_industri_completed'   => true,
            'blok3a2_completed'           => true,
            // blok4_completed intentionally not set
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok5'));

        $response->assertRedirect($this->blokRoute('blok4'));
    }

    /** @test */
    public function blok6_is_accessible_after_full_industri_tahunan_sequence(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'             => 'PT Tekstil',
            'kondisi_perusahaan'          => 'masih_aktif',
            'jaringan_unit_kegiatan'      => 'tunggal',
            'kbli_utama'                  => '13100',
            'blok3a_products'             => [['jenis_barang' => 'Kain', 'uraian' => 'Kain Tenun']],
            'blok3b_industri_completed'   => true,
            'blok3a2_completed'           => true,
            'blok4_completed'             => true,
            'blok5_completed'             => true,
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    //  7. Active + Non-Industri sequential path
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok3a_is_inaccessible_for_non_industri_company(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Perdagangan',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '47100',  // KBLI 47 = perdagangan eceran (non-industri)
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok3a'));

        // Not in valid path → redirects to first incomplete block in non-industri path
        $response->assertRedirect($this->blokRoute('blok3b.nonindustri'));
    }

    /** @test */
    public function blok3b_industri_is_inaccessible_for_non_industri_company(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Perdagangan',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '47100',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok3b-industri'));

        $response->assertRedirect($this->blokRoute('blok3b.nonindustri'));
    }

    /** @test */
    public function blok4_redirects_to_blok3b_nonindustri_when_that_block_not_complete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Perdagangan',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '47100',
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok4'));

        $response->assertRedirect($this->blokRoute('blok3b.nonindustri'));
    }

    /** @test */
    public function blok6_is_accessible_after_full_non_industri_tahunan_sequence(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'                => 'PT Perdagangan',
            'kondisi_perusahaan'             => 'masih_aktif',
            'jaringan_unit_kegiatan'         => 'tunggal',
            'kbli_utama'                     => '47100',
            'blok3b_nonindustri_completed'   => true,
            'blok4_completed'                => true,
            'blok5_completed'                => true,
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertStatus(200);
    }

    // ─────────────────────────────────────────────────────────
    //  8. KBLI boundary tests (industri = 10–33, non-industri = everything else)
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function kbli_prefix_10_is_classified_as_industri(): void
    {
        $row = new SurveyResponse(['kbli_utama' => '10110']);
        $this->assertTrue($row->isKbliIndustri());
    }

    /** @test */
    public function kbli_prefix_33_is_classified_as_industri(): void
    {
        $row = new SurveyResponse(['kbli_utama' => '33150']);
        $this->assertTrue($row->isKbliIndustri());
    }

    /** @test */
    public function kbli_prefix_09_is_classified_as_non_industri(): void
    {
        $row = new SurveyResponse(['kbli_utama' => '09100']);
        $this->assertFalse($row->isKbliIndustri());
    }

    /** @test */
    public function kbli_prefix_34_is_classified_as_non_industri(): void
    {
        $row = new SurveyResponse(['kbli_utama' => '34000']);
        $this->assertFalse($row->isKbliIndustri());
    }

    /** @test */
    public function empty_kbli_is_classified_as_non_industri(): void
    {
        $row = new SurveyResponse(['kbli_utama' => null]);
        $this->assertFalse($row->isKbliIndustri());
    }

    // ─────────────────────────────────────────────────────────
    //  9. isBlok1Complete / isBlok2Complete unit-style tests
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function is_blok1_complete_returns_false_without_nama_perusahaan(): void
    {
        $row = new SurveyResponse();
        $this->assertFalse($row->isBlok1Complete());
    }

    /** @test */
    public function is_blok1_complete_returns_true_with_nama_perusahaan(): void
    {
        $row = new SurveyResponse(['nama_perusahaan' => 'PT ABC']);
        $this->assertTrue($row->isBlok1Complete());
    }

    /** @test */
    public function is_blok2_complete_returns_false_without_kondisi_perusahaan(): void
    {
        $row = new SurveyResponse(['nama_perusahaan' => 'PT ABC']);
        $this->assertFalse($row->isBlok2Complete());
    }

    /** @test */
    public function is_blok2_complete_returns_true_for_non_active_with_only_kondisi(): void
    {
        $row = new SurveyResponse(['kondisi_perusahaan' => 'tutup']);
        $this->assertTrue($row->isBlok2Complete());
    }

    /** @test */
    public function is_blok2_complete_returns_false_for_masih_aktif_without_jaringan(): void
    {
        $row = new SurveyResponse([
            'kondisi_perusahaan' => 'masih_aktif',
            'kbli_utama'         => '13100',
        ]);
        $this->assertFalse($row->isBlok2Complete());
    }

    /** @test */
    public function is_blok2_complete_returns_false_for_masih_aktif_without_kbli(): void
    {
        $row = new SurveyResponse([
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
        ]);
        $this->assertFalse($row->isBlok2Complete());
    }

    /** @test */
    public function is_blok2_complete_returns_true_when_all_required_fields_set(): void
    {
        $row = new SurveyResponse([
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '13100',
        ]);
        $this->assertTrue($row->isBlok2Complete());
    }

    // ─────────────────────────────────────────────────────────
    //  10. isBlok3aComplete tests
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function is_blok3a_complete_returns_true_with_product_data(): void
    {
        $row = new SurveyResponse([
            'blok3a_products' => [['nama_produk' => 'Kain Tenun', 'nilai' => 1000]],
        ]);
        $this->assertTrue($row->isBlok3aComplete());
    }

    /** @test */
    public function is_blok3a_complete_returns_false_with_empty_products(): void
    {
        $row = new SurveyResponse(['blok3a_products' => []]);
        $this->assertFalse($row->isBlok3aComplete());
    }

    /** @test */
    public function is_blok3a_complete_returns_false_without_products_even_if_blok3b_flag_set(): void
    {
        // No backward compat: blok3b flag alone is not enough.
        $row = new SurveyResponse(['blok3b_industri_completed' => true]);
        $this->assertFalse($row->isBlok3aComplete());
    }

    /** @test */
    public function is_blok3a_complete_returns_false_when_no_flags_set(): void
    {
        $row = new SurveyResponse();
        $this->assertFalse($row->isBlok3aComplete());
    }

    // ─────────────────────────────────────────────────────────
    //  11. Data persistence tests
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function blok3a_save_persists_product_data_to_database(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'        => 'PT Tekstil',
            'kondisi_perusahaan'     => 'masih_aktif',
            'jaringan_unit_kegiatan' => 'tunggal',
            'kbli_utama'             => '13100',
        ]);

        $this->actingAs($user)->postJson(
            "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok3a/save-all",
            [
                'blok3a_products' => [['nama_produk' => 'Kain Tenun', 'nilai' => 1000]],
                'is_completed'    => true,
            ]
        )->assertStatus(200);

        $row = \App\Models\SurveyResponse::where('user_id', $user->id)->first();
        $this->assertTrue($row->isBlok3aComplete());
    }

    // ─────────────────────────────────────────────────────────
    //  12. Backward-compat: existing completed surveys are not blocked
    // ─────────────────────────────────────────────────────────

    /** @test */
    public function already_completed_survey_redirects_to_results_not_sequential_guard(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'    => 'PT Selesai',
            'kondisi_perusahaan' => 'tutup',
            'is_completed'       => true,
        ]);

        // Completed surveys should redirect to results, not loop through guards
        $response = $this->actingAs($user)->get($this->blokUrl('blok2'));

        $response->assertRedirect(route('dashboard.surveys.sibstr.results'));
    }

    /** @test */
    public function legacy_row_without_blok3a_products_is_redirected_to_blok3a(): void
    {
        // Without actual product data, blok3a gate should redirect regardless of blok3b flag.
        $user = $this->makeUser();
        $this->seedResponse($user, [
            'nama_perusahaan'           => 'PT Legacy',
            'kondisi_perusahaan'        => 'masih_aktif',
            'jaringan_unit_kegiatan'    => 'tunggal',
            'kbli_utama'                => '13100',
            'blok3b_industri_completed' => true,
            'blok3a2_completed'         => true,
            'blok4_completed'           => true,
            'blok5_completed'           => true,
        ]);

        $response = $this->actingAs($user)->get($this->blokUrl('blok6'));

        $response->assertRedirect($this->blokRoute('blok3a'));
    }

    // ─────────────────────────────────────────────────────────
    //  13. finishSurvey – comprehensive sequential validation
    // ─────────────────────────────────────────────────────────

    private function finishUrl(): string
    {
        return "/survei/sibstr/" . self::YEAR . "/" . self::PERIOD . "/blok6/finish";
    }

    private function blok1Fields(): array
    {
        return [
            'nama_perusahaan'        => 'PT Test',
            'alamat_pabrik'          => 'Jl. Test No. 1',
            'kabupaten_kota'         => 'Kota Test',
            'telepon_fax'            => '021-5555555',
            'penghubung'             => 'Test User',
            'email'                  => 'test@test.com',
            'nib'                    => '1234567890',
            'jenis_kawasan'          => 'luar_kawasan',
            'nama_kawasan'           => 'Kawasan Test',
            'nama_pengelola_kawasan' => 'PT Pengelola Test',
            'legalisasi_nama'        => 'Dr. Test',
            'legalisasi_jabatan'     => 'Direktur',
        ];
    }

    private function blok2ActiveTahunanFields(string $kbli = '13100'): array
    {
        return [
            'kondisi_perusahaan'                 => 'masih_aktif',
            'jaringan_unit_kegiatan'             => 'tunggal',
            'kbli_utama'                         => $kbli,
            'kegiatan_utama_perusahaan'          => 'Kegiatan Utama Test',
            'produk_utama_perusahaan'            => 'Produk Test',
            'jumlah_bulan_aktif_2025'            => 12,
            'rata_hari_kerja_bulanan_2025'       => 25,
            'jumlah_seluruh_pekerja'             => 100,
            'tenaga_kerja_laki_laki'             => 60,
            'tenaga_kerja_perempuan'             => 40,
            'pekerja_bukan_outsourcing_produksi' => 70,
            'pekerja_bukan_outsourcing_lainnya'  => 10,
            'pekerja_outsourcing_produksi'       => 15,
            'pekerja_outsourcing_lainnya'        => 5,
            'tenaga_kerja_asing'                 => 0,
            'memproduksi_barang_sendiri'         => 'ya',
            'menyediakan_layanan_makan_minum'    => 'tidak',
            'penjualan_barang_pihak_lain'        => 'tidak',
            'aktivitas_jasa'                     => 'tidak',
            'penggunaan_internet'                => 'ya',
            'produksi_ramah_lingkungan'          => 'tidak',
            'penggunaan_input_ramah_lingkungan'  => 'tidak',
        ];
    }

    private function blok3aCompleteFields(): array
    {
        return [
            'blok3a_products'           => [['nama_produk' => 'Kain Tenun', 'nilai' => 1000000]],
            'blok3a_pendapatan_lainnya' => [
                'q302a' => '0', 'q302b' => '0', 'q302c' => '0',
                'q302d' => '0', 'q302e' => '0', 'q302f' => '0',
            ],
            'blok3a_q305a_maklun_nilai' => 0,
            'blok3a_q305b_maklun_pct'  => 0,
            'blok3a_q305_online'       => 0,
        ];
    }

    /** @test */
    public function finish_survey_rejects_when_blok1_is_incomplete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, []);

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok1');
    }

    /** @test */
    public function finish_survey_rejects_when_blok2_is_incomplete(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, $this->blok1Fields());

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok2');
    }

    /** @test */
    public function finish_survey_industri_redirects_to_blok3a_before_blok3c(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('13100'), // Industri KBLI
            [
                // blok3a intentionally NOT filled; later flags set to prove ordering
                'blok3b_industri_completed' => true,
                'blok3a2_completed'         => true,
                'blok4_completed'           => true,
                'blok5_completed'           => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok3a');
    }

    /** @test */
    public function finish_survey_industri_redirects_to_blok3b_industri_when_blok3a_done(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('13100'),
            $this->blok3aCompleteFields(),
            [
                // blok3b.industri NOT complete; 3c and later flags set
                'blok3b_industri_completed' => false,
                'blok3a2_completed'         => true,
                'blok4_completed'           => true,
                'blok5_completed'           => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok3b.industri');
    }

    /** @test */
    public function finish_survey_nonindustri_skips_blok3a_and_blok3c_redirects_to_blok3b_nonindustri(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('46100'), // Non-Industri KBLI
            [
                // blok3b.nonindustri NOT complete; blok3a/3c flags irrelevant for this path
                'blok3b_nonindustri_completed' => false,
                'blok4_completed'              => true,
                'blok5_completed'              => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok3b.nonindustri');
    }

    /** @test */
    public function finish_survey_non_active_company_succeeds_with_only_blok1_and_blok2(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            ['kondisi_perusahaan' => 'tutup']
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }

    /** @test */
    public function finish_survey_industri_rejects_when_blok3b_flag_set_but_data_is_empty(): void
    {
        // Regression: saveAllBlok3bIndustri sets blok3b_industri_completed=true even
        // for empty form submissions (all fields nullable). The finish validation must
        // detect this via isBlok3bIndustriComplete() and redirect back to blok3b.industri.
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('13100'), // Industri KBLI
            $this->blok3aCompleteFields(),
            [
                // Flag set by empty-form submission; no actual user-entered data
                'blok3b_industri_completed' => true,
                'blok3b_industri_data'      => [
                    'q309_awal'  => 0.0, // computed — always written
                    'q309_akhir' => 0.0, // computed — always written
                    // all user-entered fields (q310, q311, q306_awal, etc.) absent / null
                ],
                'blok3a2_completed' => true,
                'blok4_completed'   => true,
                'blok5_completed'   => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok3b.industri');
    }

    /** @test */
    public function finish_survey_rejects_when_blok4_flag_set_but_textareas_empty(): void
    {
        // Regression: user cleared Blok IV textareas after a prior valid save.
        // blok4_completed remains true but the data is gone → must redirect to blok4.
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('46100'), // Non-Industri for shorter path
            [
                'blok3b_nonindustri_completed' => true,
                'blok4_completed'              => true,
                'blok4_data'                   => [
                    'triwulan1' => '',   // cleared
                    'triwulan2' => '',
                    'triwulan3' => null, // or null — both must fail
                    'triwulan4' => '',
                ],
                'blok5_completed'              => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(422)
                 ->assertJsonPath('incomplete_block', 'blok4');
    }

    /** @test */
    public function finish_survey_tahunan_sets_finish_survey_status(): void
    {
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('46100'), // Non-Industri for shorter path
            [
                'blok3b_nonindustri_completed' => true,
                'blok4_completed'              => true,
                'blok4_data'                   => [
                    'triwulan1' => 'Fenomena TW1',
                    'triwulan2' => 'Fenomena TW2',
                    'triwulan3' => 'Fenomena TW3',
                    'triwulan4' => 'Fenomena TW4',
                ],
                'blok5_completed'              => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(200)
                 ->assertJsonPath('success', true)
                 ->assertJsonPath('is_tahunan', true);

        $this->assertTrue(SurveyResponse::isTahunanFullyCompletedForUser($user->id));
    }

    /** @test */
    public function finish_survey_tahunan_industri_succeeds_when_blok3b_has_all_tahunan_fields(): void
    {
        // Ensures that a genuinely complete tahunan Blok 3B (all tahunan-specific
        // required fields non-null) is NOT rejected by isBlok3bIndustriComplete(),
        // which would otherwise block the survey from ever finishing for Industri tahunan.
        $user = $this->makeUser();
        $this->seedResponse($user, array_merge(
            $this->blok1Fields(),
            $this->blok2ActiveTahunanFields('13100'), // Industri KBLI
            $this->blok3aCompleteFields(),
            [
                'blok3b_industri_completed' => true,
                'blok3b_industri_data' => [
                    // Tahunan-specific inventory (q306_year_* etc.)
                    'q306_year_awal'   => 1000000,
                    'q306_year_akhir'  => 1200000,
                    // Capital goods — tahunan required
                    'q310_beli_modal'  => 500000,
                    'q311_jual_modal'  => 200000,
                    'q312_taksir_modal'=> 3000000,
                    // Labor costs — tahunan required
                    'q313_a1'          => 800000,
                    'q313_b1'          => 300000,
                    'q314_a1'          => 100000,
                    'q314_b1'          => 50000,
                    // Computed fields (always present)
                    'q310b_awal'       => 1000000,
                    'q310b_akhir'      => 1200000,
                ],
                'blok3a2_completed' => true,
                'blok4_completed'   => true,
                'blok4_data'        => [
                    'triwulan1' => 'Fenomena TW1',
                    'triwulan2' => 'Fenomena TW2',
                    'triwulan3' => 'Fenomena TW3',
                    'triwulan4' => 'Fenomena TW4',
                ],
                'blok5_completed'   => true,
            ]
        ));

        $response = $this->actingAs($user)->postJson($this->finishUrl());

        $response->assertStatus(200)
                 ->assertJsonPath('success', true);
    }
}
