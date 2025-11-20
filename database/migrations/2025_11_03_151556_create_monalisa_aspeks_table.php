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
        Schema::create('monalisa_aspeks', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('domain_id');
            $table->foreign('domain_id')->references('id')->on('monalisa_domains')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('aspek_number'); // Aspek number within domain
            $table->decimal('weight', 5, 2); // Aspek weight percentage within domain
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index('domain_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_aspeks');
    }
};
