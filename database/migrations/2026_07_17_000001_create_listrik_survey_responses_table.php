<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('listrik_survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->integer('tahun')->default(2026);
            $table->string('survey_section')->default('blok1');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('last_saved_at')->nullable();

            // ── Blok I : Identitas & Lokasi (mirrors Survei UB Blok I-A) ──
            $table->string('provinsi', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kelurahan_desa', 100)->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->string('nama_komersial')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('nomor_hp', 30)->nullable();
            $table->string('email_perusahaan')->nullable();
            $table->string('jenis_pembangkit', 30)->nullable();
            $table->decimal('daya_terpasang_kw', 18, 2)->nullable();
            $table->string('nama_pengusaha')->nullable();
            $table->tinyInteger('jenis_kelamin')->nullable();
            $table->integer('umur')->nullable();
            $table->string('nik', 20)->nullable();
            $table->boolean('blok1_completed')->default(false);

            // ── Blok II : Produksi & nilai produksi listrik per bulan ──
            // { "2025_1": { "rt": {"kwh": 0, "rp": 0}, "ind": {...}, ... }, ... }
            $table->json('data_listrik')->nullable();
            $table->boolean('blok2_completed')->default(false);

            // ── Blok III : Catatan + submit ──
            $table->text('catatan')->nullable();
            $table->boolean('blok3_completed')->default(false);

            $table->timestamps();

            $table->unique(['user_id', 'tahun']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('listrik_survey_responses');
    }
};
