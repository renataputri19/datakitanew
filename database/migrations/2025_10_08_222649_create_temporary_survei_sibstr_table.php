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
        Schema::create('temporary_survei_sibstr', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama');
            $table->string('jabatan');
            $table->string('no_hp');
            $table->string('email');
            $table->string('perusahaan');
            $table->enum('jenis_perusahaan', ['industri', 'non-industri']);
            $table->json('file_paths')->nullable(); // Store array of uploaded file paths
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary_survei_sibstr');
    }
};
