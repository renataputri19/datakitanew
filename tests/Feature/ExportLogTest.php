<?php

namespace Tests\Feature;

use App\Models\ExportLog;
use App\Models\SurveyResponse;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Every survey Excel export from the BPS Data Survei pages leaves an audit
 * row, and only superadmins can read the trail.
 */
class ExportLogTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->setRole($role);
        $user->save();

        return $user->fresh();
    }

    public function test_each_survey_export_is_logged_with_who_what_and_how_many_rows(): void
    {
        $bps = $this->user(User::ROLE_ADMIN, ['name' => 'Petugas Batam', 'email' => 'petugas@bps.go.id']);

        foreach (['sibstr', 'ub', 'listrik', 'konstruksi'] as $survey) {
            $this->actingAs($bps)->get(route("bps.{$survey}.export"))->assertOk();
        }

        $this->assertSame(4, ExportLog::count());
        $this->assertEqualsCanonicalizing(['sibstr', 'ub', 'listrik', 'konstruksi'], ExportLog::pluck('survey')->all());

        $log = ExportLog::where('survey', 'ub')->firstOrFail();
        $this->assertSame($bps->id, $log->user_id);
        $this->assertSame('Petugas Batam', $log->user_name);
        $this->assertSame('petugas@bps.go.id', $log->user_email);
        $this->assertSame(User::ROLE_ADMIN, $log->user_role);
        $this->assertSame('xlsx', $log->format);
        $this->assertStringStartsWith('Data_Survei_UB_', $log->filename);
        $this->assertSame(0, $log->row_count);
        $this->assertNull($log->filters);
    }

    public function test_log_records_row_count_filters_format_and_cloudflare_ip(): void
    {
        $bps = $this->user(User::ROLE_ADMIN);
        $respondent = $this->user(User::ROLE_USER);

        foreach (['PT Satu', 'PT Dua'] as $name) {
            SurveyResponse::create([
                'user_id' => $respondent->id, 'survey_type' => 'sibstr', 'survey_section' => 'blok1',
                'tahun' => 2026, 'triwulan' => $name === 'PT Satu' ? 1 : 2, 'nama_perusahaan' => $name,
                'is_completed' => true,
            ]);
        }

        $this->actingAs($bps)
            ->withHeaders(['CF-Connecting-IP' => '203.0.113.9'])
            ->get(route('bps.sibstr.export', ['year' => 2026, 'type' => 'triwulanan', 'status' => '', 'format' => 'csv']))
            ->assertOk();

        $log = ExportLog::sole();
        $this->assertSame(2, $log->row_count);
        $this->assertSame('csv', $log->format);
        $this->assertSame(['year' => '2026', 'type' => 'triwulanan'], $log->filters); // empty values and `format` dropped
        $this->assertSame('203.0.113.9', $log->ip_address);
    }

    public function test_blocked_exports_leave_no_log(): void
    {
        $this->actingAs($this->user(User::ROLE_BPS_PROVINSI))->get(route('bps.sibstr.export'))->assertRedirect();
        $this->actingAs($this->user(User::ROLE_USER))->get(route('bps.ub.export'))->assertRedirect();

        $this->assertSame(0, ExportLog::count());
    }

    public function test_log_survives_the_account_being_deleted(): void
    {
        $bps = $this->user(User::ROLE_ADMIN, ['email' => 'pergi@bps.go.id']);
        $this->actingAs($bps)->get(route('bps.listrik.export'))->assertOk();

        $bps->delete();

        $log = ExportLog::sole();
        $this->assertNull($log->user_id);
        $this->assertSame('pergi@bps.go.id', $log->user_email);
    }

    public function test_only_superadmin_can_read_the_log_and_it_filters_by_survey(): void
    {
        $bps = $this->user(User::ROLE_ADMIN, ['email' => 'pengunduh@bps.go.id']);
        $this->actingAs($bps)->get(route('bps.sibstr.export'))->assertOk();
        $this->actingAs($bps)->get(route('bps.ub.export'))->assertOk();

        $this->actingAs($bps)->get(route('superadmin.export-logs.index'))->assertRedirect();

        $superadmin = $this->user(User::ROLE_SUPERADMIN);

        $this->actingAs($superadmin)->get(route('superadmin.export-logs.index'))
            ->assertOk()
            ->assertSee('pengunduh@bps.go.id')
            ->assertSee('Data_SIBSTR_')
            ->assertSee('Data_Survei_UB_');

        $this->actingAs($superadmin)->get(route('superadmin.export-logs.index', ['survey' => 'ub']))
            ->assertOk()
            ->assertSee('Data_Survei_UB_')
            ->assertDontSee('Data_SIBSTR_');

        $this->actingAs($superadmin)->get(route('superadmin.dashboard'))
            ->assertOk()
            ->assertSee('Unduhan Excel Terbaru')
            ->assertSee('pengunduh@bps.go.id');
    }
}
