<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->smallInteger('tahun')->unsigned()->default(2025)->after('survey_section');
            $table->tinyInteger('triwulan')->unsigned()->default(0)->after('tahun');
            // 0 = annual / legacy (full-year); 1–4 = quarterly
        });

        // Tag every existing row as 2025 annual (triwulan = 0)
        DB::table('survey_responses')
            ->where('survey_type', 'sibstr')
            ->update(['tahun' => 2025, 'triwulan' => 0]);

        // Drop the old (user_id, survey_type) unique constraint
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropUnique('survey_responses_user_type_unique');
        });

        // Add the new (user_id, survey_type, tahun, triwulan) unique constraint
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'survey_type', 'tahun', 'triwulan'],
                'survey_responses_user_type_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropUnique('survey_responses_user_type_period_unique');
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->unique(
                ['user_id', 'survey_type'],
                'survey_responses_user_type_unique'
            );
        });

        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['tahun', 'triwulan']);
        });
    }
};
