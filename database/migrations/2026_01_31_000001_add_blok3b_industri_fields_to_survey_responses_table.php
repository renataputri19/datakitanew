<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            // Store Blok 3B Industri data as JSON
            $table->json('blok3b_industri_data')->nullable();
            $table->boolean('blok3b_industri_completed')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn('blok3b_industri_data');
            $table->dropColumn('blok3b_industri_completed');
        });
    }
};