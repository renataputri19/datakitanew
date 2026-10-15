<?php

namespace Tests\Feature;

use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Survey statistics count respondent accounts only. Entries typed in from
 * staff accounts (BPS, superadmin, Mitra, Kominfo) are tests or trial runs;
 * counted, they made the TW II "Selesai" one higher than the real companies.
 */
class StatistikRespondentsOnlyTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role): User
    {
        $user = User::factory()->create();
        $user->setRole($role);
        $user->save();

        return $user->fresh();
    }

    private function sibstr(User $user, string $nama): void
    {
        SurveyResponse::create([
            'user_id'         => $user->id,
            'survey_type'     => 'sibstr',
            'survey_section'  => 'blok1',
            'tahun'           => 2026,
            'triwulan'        => 2,
            'nama_perusahaan' => $nama,
            'is_completed'    => true,
        ]);
    }

    public function test_respondents_scope_matches_the_user_role(): void
    {
        $respondent = $this->user(User::ROLE_USER);
        foreach ([User::ROLE_SUPERADMIN, User::ROLE_ADMIN, User::ROLE_MITRA, User::ROLE_KOMINFO] as $role) {
            $this->user($role);
        }
        // The retired is_admin flag does not make an account staff.
        $legacy = User::factory()->create();
        $legacy->forceFill(['is_admin' => true])->save();

        $ids = User::respondents()->pluck('id')->sort()->values()->all();
        $this->assertSame(collect([$respondent->id, $legacy->id])->sort()->values()->all(), $ids);
        $this->assertSame(User::ROLE_USER, $legacy->fresh()->role);
    }

    public function test_statistics_leave_out_staff_entries(): void
    {
        $this->sibstr($this->user(User::ROLE_USER), 'PT Responden Asli');
        $this->sibstr($this->user(User::ROLE_SUPERADMIN), 'Perusahaan A');
        $this->sibstr($this->user(User::ROLE_ADMIN), 'PT Uji BPS');
        $this->sibstr($this->user(User::ROLE_MITRA), 'PT Uji Mitra');
        $bps = $this->user(User::ROLE_ADMIN);

        $rows = $this->actingAs($bps)->get(route('bps.statistik.index', ['tahun' => 2026]))
            ->assertOk()
            ->viewData('payload')['rows'];
        $this->assertSame(['PT Responden Asli'], collect($rows)->pluck('perusahaan')->all());

        $dashboard = $this->actingAs($bps)->get(route('bps.dashboard'))->assertOk();
        $this->assertSame(1, $dashboard->viewData('sibstrCount'));
        $this->assertSame(1, $dashboard->viewData('sibstrCompletedCount'));

        foreach (['bps.statistik.listrik', 'bps.statistik.ub', 'bps.statistik.konstruksi'] as $route) {
            $this->actingAs($bps)->get(route($route))->assertOk();
        }
    }
}
