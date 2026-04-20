<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add annual_survey_status to survey_responses.
     *
     * This column tracks whether the 2025 Tahunan survey has been formally
     * completed through the Block 6 finishSurvey endpoint (FINISH_SURVEY status).
     * It is intentionally separate from is_completed so that legacy rows that
     * were marked completed by the old mechanism remain locked until the user
     * explicitly re-submits Block 6 through the new finish flow.
     *
     * Expected values:
     *   null            – not yet formally finished
     *   'FINISH_SURVEY' – completed via SurveyController::finishSurvey()
     */
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->string('annual_survey_status')->nullable()->default(null)->after('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn('annual_survey_status');
        });
    }
};
