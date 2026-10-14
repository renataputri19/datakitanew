<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Which survey data a blast drew its list from (SibstrReminder sources, e.g.
 * ["2026-1", "2026-2", "mulai"]). Null on blasts sent before sources existed.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_reminder_blasts', function (Blueprint $table) {
            $table->json('sources')->nullable()->after('target_statuses');
        });
    }

    public function down(): void
    {
        Schema::table('survey_reminder_blasts', function (Blueprint $table) {
            $table->dropColumn('sources');
        });
    }
};
