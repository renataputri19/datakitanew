<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ub_survey_responses', function (Blueprint $table) {
            $table->timestamp('last_saved_at')->nullable()->after('is_completed');
        });
    }

    public function down(): void
    {
        Schema::table('ub_survey_responses', function (Blueprint $table) {
            $table->dropColumn('last_saved_at');
        });
    }
};
