<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            if (!Schema::hasColumn('survey_responses', 'blok6_data')) {
                $table->json('blok6_data')->nullable()->after('kbli_utama');
            }
            if (!Schema::hasColumn('survey_responses', 'blok6_completed')) {
                $table->boolean('blok6_completed')->default(false)->after('blok6_data');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            if (Schema::hasColumn('survey_responses', 'blok6_data')) {
                $table->dropColumn('blok6_data');
            }
            if (Schema::hasColumn('survey_responses', 'blok6_completed')) {
                $table->dropColumn('blok6_completed');
            }
        });
    }
};
