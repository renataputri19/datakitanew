<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Role flag for BPS Provinsi: a regular user who may also open the four
 * /bps/statistik dashboards.
 *
 * Deliberately separate from `is_bps` (BPS Batam). A Provinsi account has
 * `is_bps` = false, so every existing `is_bps` gate — the rest of /bps,
 * MONALISA BPS, Buku Tamu settings, /develop — treats it as a regular user.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_bps_provinsi')->default(false)->after('is_bps');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_bps_provinsi');
        });
    }
};
