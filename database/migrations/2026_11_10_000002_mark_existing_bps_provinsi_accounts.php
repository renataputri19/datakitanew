<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * One-off: the BPS Provinsi accounts that already existed as BPS when the
 * role was introduced (2026-11-10). Later ones are assigned from
 * /superadmin/users.
 *
 * Moves them from BPS Batam (`is_bps`) to BPS Provinsi (`is_bps_provinsi`),
 * which only removes access. Only accounts that are currently BPS and not
 * Super Admin are touched, so it can never grant anything to anyone.
 */
return new class extends Migration
{
    private const EMAILS = ['resa@gmail.com'];

    public function up(): void
    {
        DB::table('users')
            ->whereIn('email', self::EMAILS)
            ->where('is_bps', true)
            ->where('is_superadmin', false)
            ->update(['is_bps' => false, 'is_bps_provinsi' => true, 'is_admin' => false]);
    }

    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', self::EMAILS)
            ->where('is_bps_provinsi', true)
            ->update(['is_bps' => true, 'is_bps_provinsi' => false]);
    }
};
