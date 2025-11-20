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
        Schema::create('monalisa_indikators', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('aspek_id');
            $table->foreign('aspek_id')->references('id')->on('monalisa_aspeks')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('indikator_code'); // e.g., "1.1", "2.1", "2.2"
            $table->decimal('weight', 5, 2)->nullable(); // Indikator weight if applicable
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('aspek_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_indikators');
    }
};
