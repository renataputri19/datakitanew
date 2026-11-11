<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * users:rotate-staff-passwords — new random password for every BPS (Batam and
 * Provinsi) and Super Admin account, sessions ended, CSV for distribution.
 */
class RotateStaffPasswordsTest extends TestCase
{
    use RefreshDatabase;

    private string $csv;

    protected function setUp(): void
    {
        parent::setUp();
        $this->csv = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'rotate-test-' . uniqid() . '.csv';
    }

    protected function tearDown(): void
    {
        @unlink($this->csv);
        parent::tearDown();
    }

    private function user(string $role, string $email): User
    {
        $user = User::factory()->create(['email' => $email, 'password' => Hash::make('LamaSekali123')]);
        $user->setRole($role);
        $user->save();

        return $user->fresh();
    }

    /** @return array<string, string> email => new password, from the CSV */
    private function csvPasswords(): array
    {
        $lines = file($this->csv, FILE_IGNORE_NEW_LINES);
        $lines[0] = preg_replace('/^\xEF\xBB\xBF/', '', $lines[0]);
        $rows = array_map('str_getcsv', $lines);
        $this->assertSame(['Nama', 'Email', 'Role', 'Password Baru'], array_shift($rows));

        return collect($rows)->mapWithKeys(fn ($r) => [$r[1] => $r[3]])->all();
    }

    private function staffAndRespondent(): array
    {
        return [
            'superadmin' => $this->user(User::ROLE_SUPERADMIN, 'super@bps.go.id'),
            'batam'      => $this->user(User::ROLE_ADMIN, 'batam@bps.go.id'),
            'prov'       => $this->user(User::ROLE_BPS_PROVINSI, 'prov@gmail.com'),
            'mitra'      => $this->user(User::ROLE_MITRA, 'mitra@gmail.com'),
            'respondent' => $this->user(User::ROLE_USER, 'pt@contoh.co.id'),
        ];
    }

    public function test_dry_run_changes_nothing(): void
    {
        $users = $this->staffAndRespondent();

        $this->artisan('users:rotate-staff-passwords', ['--dry-run' => true, '--output' => $this->csv])
            ->expectsOutputToContain('3 akun')
            ->assertSuccessful();

        $this->assertFileDoesNotExist($this->csv);
        foreach ($users as $user) {
            $this->assertTrue(Hash::check('LamaSekali123', $user->fresh()->password));
        }
    }

    public function test_rotates_every_staff_account_and_nobody_else(): void
    {
        $users = $this->staffAndRespondent();
        $oldTokens = collect($users)->map(fn (User $u) => $u->remember_token);

        $this->artisan('users:rotate-staff-passwords', ['--force' => true, '--output' => $this->csv])
            ->assertSuccessful();

        $passwords = $this->csvPasswords();
        $this->assertEqualsCanonicalizing(['super@bps.go.id', 'batam@bps.go.id', 'prov@gmail.com'], array_keys($passwords));

        foreach (['superadmin', 'batam', 'prov'] as $key) {
            $fresh = $users[$key]->fresh();
            $new = $passwords[$fresh->email];

            $this->assertMatchesRegularExpression('/^(?=.*[a-zA-Z])(?=.*[0-9])[A-Za-z0-9]{14}$/', $new);
            $this->assertTrue(Hash::check($new, $fresh->password), "{$key} cannot log in with the CSV password");
            $this->assertFalse(Hash::check('LamaSekali123', $fresh->password), "{$key} old password still works");
            $this->assertNotSame($oldTokens[$key], $fresh->remember_token);
        }

        foreach (['mitra', 'respondent'] as $key) {
            $this->assertTrue(Hash::check('LamaSekali123', $users[$key]->fresh()->password), "{$key} was rotated");
        }

        $this->assertCount(3, array_unique($passwords));
    }

    public function test_ends_staff_sessions_when_sessions_live_in_the_database(): void
    {
        config(['session.driver' => 'database']);
        $users = $this->staffAndRespondent();

        foreach ($users as $key => $user) {
            DB::table('sessions')->insert([
                'id' => "sess-{$key}", 'user_id' => $user->id, 'ip_address' => '127.0.0.1',
                'user_agent' => 'test', 'payload' => '', 'last_activity' => time(),
            ]);
        }

        $this->artisan('users:rotate-staff-passwords', ['--force' => true, '--output' => $this->csv])
            ->expectsOutputToContain('Sesi login aktif yang diakhiri: 3')
            ->assertSuccessful();

        $this->assertEqualsCanonicalizing(['sess-mitra', 'sess-respondent'], DB::table('sessions')->pluck('id')->all());
    }

    public function test_only_and_except_narrow_the_run_and_never_reach_non_staff(): void
    {
        $users = $this->staffAndRespondent();

        $this->artisan('users:rotate-staff-passwords', [
            '--force'  => true,
            '--output' => $this->csv,
            '--only'   => ['BATAM@bps.go.id', 'prov@gmail.com', 'pt@contoh.co.id'],
            '--except' => ['prov@gmail.com'],
        ])
            ->expectsOutputToContain('Dilewati: pt@contoh.co.id')
            ->assertSuccessful();

        $this->assertSame(['batam@bps.go.id'], array_keys($this->csvPasswords()));
        $this->assertTrue(Hash::check('LamaSekali123', $users['prov']->fresh()->password));
        $this->assertTrue(Hash::check('LamaSekali123', $users['respondent']->fresh()->password));
    }

    public function test_refuses_to_overwrite_an_existing_file_and_changes_nothing(): void
    {
        $users = $this->staffAndRespondent();
        file_put_contents($this->csv, 'jangan ditimpa');

        try {
            $this->artisan('users:rotate-staff-passwords', ['--force' => true, '--output' => $this->csv])->run();
            $this->fail('Expected the command to refuse an existing output file.');
        } catch (\RuntimeException $e) {
            $this->assertStringContainsString('Tidak ada yang diubah', $e->getMessage());
        }

        $this->assertSame('jangan ditimpa', file_get_contents($this->csv));
        $this->assertTrue(Hash::check('LamaSekali123', $users['batam']->fresh()->password));
    }

    public function test_without_force_a_declined_confirmation_changes_nothing(): void
    {
        $users = $this->staffAndRespondent();

        $this->artisan('users:rotate-staff-passwords', ['--output' => $this->csv])
            ->expectsConfirmation('Ganti password 3 akun di atas? Semua sesi login mereka akan diakhiri.', 'no')
            ->assertFailed();

        $this->assertFileDoesNotExist($this->csv);
        $this->assertTrue(Hash::check('LamaSekali123', $users['superadmin']->fresh()->password));
    }
}
