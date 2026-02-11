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
            // Add Blok 4 fields for SIBSTR survey
            $table->json('blok4_data')->nullable()->after('blok3b_industri_completed');
            $table->boolean('blok4_completed')->default(false)->after('blok4_data');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['blok4_data', 'blok4_completed']);
        });
    }
};