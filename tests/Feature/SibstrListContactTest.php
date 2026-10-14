<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The BPS SIBSTR list shows each company's Blok I contact (105 Penghubung,
 * 104 Telepon/Fax, 106 Email) and lets BPS search by it.
 */
class SibstrListContactTest extends TestCase
{
    use RefreshDatabase;

    private function bpsUser(): User
    {
        $user = User::factory()->create();
        $user->setRole(User::ROLE_ADMIN); // Admin BPS — setRole() does not persist
        $user->save();

        return $user->fresh();
    }

    private function sibstr(string $name, array $contact): SurveyResponse
    {
        return SurveyResponse::create(array_merge([
            'user_id'         => User::factory()->create()->id,
            'survey_type'     => 'sibstr',
            'survey_section'  => 'blok1',
            'tahun'           => 2026,
            'triwulan'        => 0,
            'nama_perusahaan' => $name,
        ], $contact));
    }

    public function test_list_shows_blok1_contact(): void
    {
        $this->sibstr('PT Kontak Lengkap', [
            'penghubung'  => 'Budi Santoso',
            'telepon_fax' => '0812-7000-1234',
            'email'       => 'budi@kontak.example',
        ]);

        $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.index', ['year' => 2026, 'type' => 'tahunan']))
            ->assertOk()
            ->assertSee('Kontak Penghubung')
            ->assertSee('Budi Santoso')
            ->assertSee('0812-7000-1234')
            ->assertSee('mailto:budi@kontak.example', false);
    }

    public function test_search_matches_penghubung(): void
    {
        $this->sibstr('PT Dicari', ['penghubung' => 'Siti Rahmawati']);
        $this->sibstr('PT Lain', ['penghubung' => 'Andi Wijaya']);

        $this->actingAs($this->bpsUser())
            ->get(route('bps.sibstr.index', ['year' => 2026, 'type' => 'tahunan', 'search' => 'Rahmawati']))
            ->assertOk()
            ->assertSee('PT Dicari')
            ->assertDontSee('PT Lain');
    }
}
