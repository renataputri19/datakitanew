<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Gives every staff account (BPS Batam, BPS Provinsi, Super Admin) a new
 * random password, logs them out everywhere, and writes the new passwords to
 * a CSV for the operator to hand out privately.
 *
 * The CSV is written before anything changes, and removed again if the
 * database update fails, so the file and the database never disagree.
 *
 *   php artisan users:rotate-staff-passwords --dry-run   # who would change
 *   php artisan users:rotate-staff-passwords --print     # do it, show passwords
 */
class RotateStaffPasswords extends Command
{
    protected $signature = 'users:rotate-staff-passwords
        {--only=* : Hanya akun dengan email ini (boleh diulang)}
        {--except=* : Lewati akun dengan email ini (boleh diulang)}
        {--dry-run : Tampilkan akun yang akan diubah tanpa mengubah apa pun}
        {--print : Tampilkan juga password baru di layar}
        {--output= : Path file CSV (default: storage/app/password-rotation/staff-passwords-<waktu>.csv)}
        {--force : Lewati konfirmasi (untuk terminal non-interaktif)}';

    protected $description = 'Ganti password semua akun BPS dan Super Admin dengan password acak, logout semua sesinya, dan tulis password baru ke CSV';

    /** No 0/O or 1/l/I: these passwords get read off a list and typed by hand. */
    private const LETTERS = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ';
    private const DIGITS  = '23456789';
    private const LENGTH  = 14;

    public function handle(): int
    {
        $only   = $this->emails('only');
        $except = $this->emails('except');
        $users  = $this->targets($only, $except);

        // An --only email that is not a staff account is skipped, never rotated.
        $unmatched = array_diff($only, $users->pluck('email')->map(fn ($e) => mb_strtolower($e))->all());
        foreach ($unmatched as $email) {
            $this->warn("Dilewati: {$email} bukan akun BPS/Super Admin (atau tidak ditemukan).");
        }

        if ($users->isEmpty()) {
            $this->error('Tidak ada akun BPS/Super Admin yang cocok — tidak ada yang diubah.');

            return self::FAILURE;
        }

        $this->table(
            ['#', 'Nama', 'Email', 'Role'],
            $users->values()->map(fn (User $u, int $i) => [$i + 1, $u->name, $u->email, $u->roleLabel()])->all()
        );

        if ($this->option('dry-run')) {
            $this->info("Dry run: {$users->count()} akun di atas akan diganti passwordnya. Tidak ada yang diubah.");

            return self::SUCCESS;
        }

        if (! $this->option('force')
            && ! $this->confirm("Ganti password {$users->count()} akun di atas? Semua sesi login mereka akan diakhiri.")) {
            $this->warn('Dibatalkan — tidak ada yang diubah.');

            return self::FAILURE;
        }

        $passwords = $users->mapWithKeys(fn (User $u) => [$u->id => $this->generatePassword()]);

        // Written first: if this fails, nothing has been changed yet.
        $path = $this->writeCsv($users, $passwords);

        try {
            $sessionsCleared = DB::transaction(function () use ($users, $passwords) {
                foreach ($users as $user) {
                    $user->forceFill([
                        'password'       => Hash::make($passwords[$user->id]),
                        'remember_token' => Str::random(60), // invalidates "ingat saya" cookies
                    ])->save();
                }

                return $this->clearSessions($users->pluck('id')->all());
            });
        } catch (\Throwable $e) {
            @unlink($path);
            $this->error('Gagal menyimpan password baru. Tidak ada password yang berubah; file CSV sudah dihapus.');

            throw $e;
        }

        $this->newLine();
        $this->info("Password {$users->count()} akun berhasil diganti.");

        if ($sessionsCleared === null) {
            $this->warn('SESSION_DRIVER=' . config('session.driver') . ': sesi yang sedang aktif tidak bisa diakhiri otomatis '
                . 'dan baru berakhir sendiri setelah ' . config('session.lifetime') . ' menit tidak aktif.');
        } else {
            $this->line("Sesi login aktif yang diakhiri: {$sessionsCleared}.");
        }

        if ($this->option('print')) {
            $this->table(
                ['Nama', 'Email', 'Password Baru'],
                $users->map(fn (User $u) => [$u->name, $u->email, $passwords[$u->id]])->all()
            );
        }

        $this->line("File CSV: <comment>{$path}</comment>");
        $this->warn('Bagikan password ke masing-masing orang secara pribadi, lalu HAPUS file CSV ini.');

        return self::SUCCESS;
    }

    /**
     * @return Collection<int, User>
     */
    private function targets(array $only, array $except): Collection
    {
        $query = User::query()
            ->where(fn ($q) => $q->where('is_bps', true)->orWhere('is_bps_provinsi', true)->orWhere('is_superadmin', true))
            ->orderByDesc('is_superadmin')
            ->orderBy('name');

        // users.email uses a case-insensitive collation, so lower-cased input matches.
        if ($only !== []) {
            $query->whereIn('email', $only);
        }
        if ($except !== []) {
            $query->whereNotIn('email', $except);
        }

        return $query->get();
    }

    /** @return array<int, string> lower-cased, trimmed, de-duplicated */
    private function emails(string $option): array
    {
        return collect($this->option($option))
            ->map(fn ($e) => mb_strtolower(trim((string) $e)))
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    /**
     * 14 characters from a 55-symbol alphabet (~80 bits), always with at least
     * one letter and one digit so it also passes the app's own password rule.
     */
    private function generatePassword(): string
    {
        $alphabet = self::LETTERS . self::DIGITS;
        $max = strlen($alphabet) - 1;

        do {
            $password = '';
            for ($i = 0; $i < self::LENGTH; $i++) {
                $password .= $alphabet[random_int(0, $max)];
            }
        } while (! preg_match('/[A-Za-z]/', $password) || ! preg_match('/[0-9]/', $password));

        return $password;
    }

    /**
     * @param  Collection<int, User>  $users
     * @param  Collection<string, string>  $passwords  keyed by user id
     */
    private function writeCsv(Collection $users, Collection $passwords): string
    {
        $path = $this->option('output')
            ?: storage_path('app/password-rotation/staff-passwords-' . now('Asia/Jakarta')->format('Ymd_His') . '.csv');

        $dir = dirname($path);
        if (! is_dir($dir) && ! mkdir($dir, 0700, true) && ! is_dir($dir)) {
            throw new RuntimeException("Tidak dapat membuat folder {$dir}");
        }

        // 'x' refuses to overwrite an existing file.
        $handle = @fopen($path, 'x');
        if ($handle === false) {
            throw new RuntimeException("Tidak dapat membuat file {$path} (sudah ada, atau folder tidak bisa ditulisi). Tidak ada yang diubah.");
        }

        fwrite($handle, "\xEF\xBB\xBF"); // BOM so Excel reads the names as UTF-8
        fputcsv($handle, ['Nama', 'Email', 'Role', 'Password Baru']);
        foreach ($users as $user) {
            fputcsv($handle, [$user->name, $user->email, $user->roleLabel(), $passwords[$user->id]]);
        }
        fclose($handle);
        @chmod($path, 0600);

        return $path;
    }

    /**
     * End every logged-in session of these users. Only possible when sessions
     * live in the database (prod); returns null for any other driver.
     */
    private function clearSessions(array $userIds): ?int
    {
        if (config('session.driver') !== 'database') {
            return null;
        }

        return DB::connection(config('session.connection'))
            ->table(config('session.table', 'sessions'))
            ->whereIn('user_id', $userIds)
            ->delete();
    }
}
