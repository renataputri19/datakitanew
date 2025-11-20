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
        Schema::create('monalisa_domains', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('domain_number'); // 1-5
            $table->decimal('weight', 5, 2); // Domain weight percentage (e.g., 28.00)
            $table->integer('order')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monalisa_domains');
    }
};
