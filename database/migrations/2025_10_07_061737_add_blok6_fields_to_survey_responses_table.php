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
            // Add Blok 6 fields for SIBSTR survey
            $table->json('blok6_data')->nullable()->after('blok3a_totals');
            $table->boolean('blok6_completed')->default(false)->after('blok6_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['blok6_data', 'blok6_completed']);
        });
    }
};