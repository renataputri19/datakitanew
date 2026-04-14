<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Drop old q501 columns if they exist (created before the rename)
            if (Schema::hasColumn('survey_responses', 'blok3a_q501a_maklun_nilai')) {
                $table->dropColumn('blok3a_q501a_maklun_nilai');
            }
            if (Schema::hasColumn('survey_responses', 'blok3a_q501b_maklun_pct')) {
                $table->dropColumn('blok3a_q501b_maklun_pct');
            }
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            // Q305a: Nilai pendapatan dari jasa industri (maklun) — annual, in Rupiah
            if (!Schema::hasColumn('survey_responses', 'blok3a_q305a_maklun_nilai')) {
                $table->decimal('blok3a_q305a_maklun_nilai', 20, 2)->nullable()->after('blok3a_q305_online');
            }
            // Q305b: Persentase nilai pendapatan dari jasa industri (maklun) luar negeri — 0–100%
            if (!Schema::hasColumn('survey_responses', 'blok3a_q305b_maklun_pct')) {
                $table->decimal('blok3a_q305b_maklun_pct', 5, 2)->nullable()->after('blok3a_q305a_maklun_nilai');
            }
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Drop whichever columns exist (q501 = original name, q305 = renamed)
            $toDrop = [];
            if (Schema::hasColumn('survey_responses', 'blok3a_q305a_maklun_nilai')) {
                $toDrop[] = 'blok3a_q305a_maklun_nilai';
            }
            if (Schema::hasColumn('survey_responses', 'blok3a_q305b_maklun_pct')) {
                $toDrop[] = 'blok3a_q305b_maklun_pct';
            }
            if (Schema::hasColumn('survey_responses', 'blok3a_q501a_maklun_nilai')) {
                $toDrop[] = 'blok3a_q501a_maklun_nilai';
            }
            if (Schema::hasColumn('survey_responses', 'blok3a_q501b_maklun_pct')) {
                $toDrop[] = 'blok3a_q501b_maklun_pct';
            }
            if (!empty($toDrop)) {
                $table->dropColumn($toDrop);
            }
        });
    }
};
