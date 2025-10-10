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
        Schema::create('survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->string('survey_type')->default('sibstr'); // For future survey types
            $table->string('survey_section')->default('blok1'); // blok1, blok2, etc.
            
            // Header Information
            $table->string('kip')->nullable();
            $table->string('idsbr')->nullable();
            
            // Section I - KETERANGAN UMUM (Questions 101-109)
            $table->text('nama_perusahaan')->nullable(); // 101
            $table->text('alamat_pabrik')->nullable(); // 102
            $table->string('kabupaten_kota')->nullable(); // 103
            $table->string('telepon_fax')->nullable(); // 104
            $table->string('penghubung')->nullable(); // 105
            $table->string('email')->nullable(); // 106
            $table->string('nib')->nullable(); // 107
            $table->enum('jenis_kawasan', ['ekonomi_khusus', 'industri', 'luar_kawasan'])->nullable(); // 108
            $table->string('nama_kawasan')->nullable(); // 109
            
            // LEGALISASI PERUSAHAAN (Questions 110-111)
            $table->string('legalisasi_nama')->nullable(); // 110
            $table->string('legalisasi_jabatan')->nullable(); // 111
            
            // BPS Provinsi (Questions 112-116)
            $table->string('bps_provinsi_penghubung')->nullable(); // 112
            $table->string('bps_provinsi_telepon')->nullable(); // 113
            $table->string('bps_provinsi_fax')->nullable(); // 114
            $table->string('bps_provinsi_email')->nullable(); // 115
            $table->text('bps_provinsi_alamat')->nullable(); // 116
            
            // Auto-save tracking
            $table->timestamp('last_saved_at')->nullable();
            $table->boolean('is_completed')->default(false);
            
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['user_id', 'survey_type', 'survey_section']);
            $table->index('last_saved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('survey_responses');
    }
};