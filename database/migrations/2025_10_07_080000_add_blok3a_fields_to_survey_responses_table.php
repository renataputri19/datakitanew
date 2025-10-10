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
            // Blok IIIA fields - KONDISI PEREKONOMIAN (PELAKU USAHA)
            // Store as JSON to handle dynamic product entries
            $table->json('blok3a_products')->nullable()->after('blok6_completed');
            $table->json('blok3a_lainnya')->nullable()->after('blok3a_products');
            $table->json('blok3a_totals')->nullable()->after('blok3a_lainnya');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'blok3a_products',
                'blok3a_lainnya', 
                'blok3a_totals'
            ]);
        });
    }
};
