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
            // Blok II fields
            $table->string('kondisi_perusahaan')->nullable()->after('bps_provinsi_alamat');
            $table->string('jaringan_unit_kegiatan')->nullable()->after('kondisi_perusahaan');
            $table->integer('rata_rata_tenaga_kerja')->nullable()->after('jaringan_unit_kegiatan');
            $table->longText('kegiatan_utama_perusahaan')->nullable()->after('rata_rata_tenaga_kerja');
            $table->string('kbli_utama')->nullable()->after('kegiatan_utama_perusahaan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'kondisi_perusahaan',
                'jaringan_unit_kegiatan',
                'rata_rata_tenaga_kerja',
                'kegiatan_utama_perusahaan',
                'kbli_utama'
            ]);
        });
    }
};
