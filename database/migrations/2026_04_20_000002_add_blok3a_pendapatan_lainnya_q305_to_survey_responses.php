<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // New Q302a-f: Pendapatan lainnya selama tahun 2025 (6 sub-fields, annual totals)
            $table->json('blok3a_pendapatan_lainnya')->nullable()->after('blok3a_totals');
            // Q305 moved from blok3b-industri to blok3a
            $table->decimal('blok3a_q305_online', 5, 2)->nullable()->after('blok3a_pendapatan_lainnya');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['blok3a_pendapatan_lainnya', 'blok3a_q305_online']);
        });
    }
};
