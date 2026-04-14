<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->json('blok3a2_materials')->nullable()->after('blok3a_totals');
            $table->boolean('blok3a2_completed')->default(false)->after('blok3a2_materials');
        });
    }

    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn(['blok3a2_materials', 'blok3a2_completed']);
        });
    }
};
