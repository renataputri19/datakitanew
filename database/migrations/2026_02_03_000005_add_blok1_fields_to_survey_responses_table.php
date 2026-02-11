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
            // Blok I additions based on form inputs
            // 107 (Homepage/Website)
            $table->string('homepage')->nullable()->after('email');

            // 108 (Tahun mulai beroperasi secara komersial)
            $table->integer('tahun_mulai_beroperasi')->nullable()->after('homepage');

            // 112 (Nama Perusahaan Pengelola Kawasan)
            $table->string('nama_pengelola_kawasan')->nullable()->after('nama_kawasan');

            // 113 (Jenis Kelamin penanggung jawab) - radio options
            $table->enum('legalisasi_jenis_kelamin', ['laki_laki', 'perempuan'])->nullable()->after('legalisasi_jabatan');

            // 115 (NIK penanggung jawab) - 16 digits
            $table->string('legalisasi_nik', 16)->nullable()->after('legalisasi_jenis_kelamin');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'homepage',
                'tahun_mulai_beroperasi',
                'nama_pengelola_kawasan',
                'legalisasi_jenis_kelamin',
                'legalisasi_nik',
            ]);
        });
    }
};