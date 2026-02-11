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
            // Add Blok 5 fields for SIBSTR survey
            $table->json('blok5_data')->nullable()->after('blok4_completed');
            $table->boolean('blok5_completed')->default(false)->after('blok5_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['blok5_data', 'blok5_completed']);
        });
    }
};