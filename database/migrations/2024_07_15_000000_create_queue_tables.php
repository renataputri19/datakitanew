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
        // Create queue_antrian table - stores queue numbers
        Schema::create('queue_antrian', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->date('tanggal');
            $table->string('no_antrian', 3);
            $table->enum('status', ['0', '1'])->default('0'); // 0: waiting, 1: called
            $table->dateTime('updated_date')->nullable();
        });

        // Create queue_panggilan table - stores queue calls
        Schema::create('queue_panggilan', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('antrian')->nullable();
            $table->string('loket')->nullable();
            $table->timestamps();
        });

        // Create queue_loket table - stores counter/desk information
        Schema::create('queue_loket', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('no_loket');
            $table->string('nama_loket');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_antrian');
        Schema::dropIfExists('queue_panggilan');
        Schema::dropIfExists('queue_loket');
    }
};
