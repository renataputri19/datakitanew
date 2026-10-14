<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Survei Perusahaan Konstruksi. One row per user per period, where a period is
 * (tahun, triwulan) exactly as in survey_responses: triwulan = 0 is the annual
 * SKTH questionnaire, 1–4 are the quarterly SKTR ones.
 *
 * Tahunan and triwulanan are NOT the same instrument with a period switch —
 * only Blok I overlaps, SKTR has an "updating status" preamble SKTH lacks, and
 * SKTH has six blocks SKTR does not. They therefore get separate pages and
 * separate columns; the shared (tahun, triwulan) key exists so the landing
 * page, archive and BPS listing can treat both as one survey.
 *
 * Columns below cover the annual form (SKTH-2025) only. The quarterly columns
 * are added by a later migration when that flow is built.
 *
 * Grids are JSON, matching UB and Listrik: the BPS statistik pages aggregate
 * them in PHP rather than in SQL, so a child table would buy nothing and cost
 * a join on every read.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('konstruksi_survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id');
            $table->smallInteger('tahun')->unsigned()->default(2025);
            $table->tinyInteger('triwulan')->unsigned()->default(0); // 0 = tahunan
            $table->string('survey_section')->default('blok1');
            $table->boolean('is_completed')->default(false);
            $table->timestamp('last_saved_at')->nullable();

            // ── Blok I : Keterangan umum perusahaan ──
            $table->string('kip', 30)->nullable();
            $table->string('provinsi', 100)->nullable();
            $table->string('kabupaten_kota', 100)->nullable();
            $table->string('kecamatan', 100)->nullable();
            $table->string('kelurahan_desa', 100)->nullable();
            $table->string('nus', 20)->nullable();
            $table->string('nama_perusahaan')->nullable();
            $table->string('punya_nib', 10)->nullable();   // ya | tidak
            $table->string('nib', 30)->nullable();
            $table->string('nama_pengusaha')->nullable();
            $table->string('hp_pengusaha', 30)->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('rt', 5)->nullable();
            $table->string('rw', 5)->nullable();
            $table->string('kode_pos', 10)->nullable();
            $table->string('nomor_telepon', 30)->nullable();
            $table->string('website')->nullable();
            $table->string('email_perusahaan')->nullable();
            $table->text('alamat_operasional')->nullable();
            $table->string('rt_operasional', 5)->nullable();
            $table->string('rw_operasional', 5)->nullable();
            $table->string('kode_pos_operasional', 10)->nullable();
            $table->string('badan_hukum', 5)->nullable();  // 1,2,3,4,5,9
            $table->string('badan_hukum_lainnya')->nullable();
            $table->string('status_penanaman_modal', 5)->nullable(); // 1,2,9
            $table->string('kualifikasi', 5)->nullable();  // 1,2,3,4,9
            $table->boolean('blok1_completed')->default(false);

            // ── Blok II : Tenaga kerja & balas jasa ──
            // {"smp":{"l":..,"p":..},"sma":{...},"pt":{...}}
            $table->json('blok2_pekerja')->nullable();
            // {"gaji":..,"lembur":..,"transport_makan":..,"hadiah_bonus":..,
            //  "dana_pensiun":..,"asuransi":..}  — nilai dalam 000 Rp
            $table->json('blok2_balas_jasa')->nullable();
            $table->boolean('blok2_completed')->default(false);

            // ── Blok III : Kepemilikan modal (persen, jumlah harus 100) ──
            $table->json('blok3_modal')->nullable();
            $table->string('modal_asing_negara', 100)->nullable();
            $table->boolean('blok3_completed')->default(false);

            // ── Blok IV : Pengeluaran operasional (000 Rp) ──
            $table->json('blok4_pengeluaran')->nullable();
            $table->boolean('blok4_completed')->default(false);

            // ── Blok V : Penambahan & pengurangan barang modal (000 Rp) ──
            // {"tanah":{"tambah":..,"kurang":..}, …}
            $table->json('blok5_barang_modal')->nullable();
            $table->string('barang_modal_lainnya')->nullable();
            $table->boolean('blok5_completed')->default(false);

            // ── Blok VI : Pendapatan & pengeluaran pekerjaan konstruksi ──
            $table->json('data_proyek')->nullable();       // list of project rows
            $table->string('pct_bahan', 20)->nullable();   // rincian 2
            $table->string('pct_upah', 20)->nullable();    // rincian 3
            $table->json('blok6_sumber_modal')->nullable();// rincian 4, total 100%
            $table->boolean('blok6_completed')->default(false);

            // ── Blok VII : Struktur bahan/material proyek utama ──
            $table->json('blok7_bahan')->nullable();       // [{"nama":..,"nilai":..}, …]
            $table->boolean('blok7_completed')->default(false);

            // ── Blok VIII : Pendapatan lain (000 Rp) ──
            $table->json('blok8_pendapatan_lain')->nullable();
            $table->string('pendapatan_lain_lainnya')->nullable();
            $table->boolean('blok8_completed')->default(false);

            // ── Blok IX : Ringkasan — derived from II/IV/VI/VII/VIII, not stored ──
            $table->boolean('blok9_completed')->default(false);

            // ── Blok X : Metode pendataan, catatan, pemberi jawaban ──
            $table->string('metode_pendataan', 5)->nullable(); // 1 = mandiri, 2 = wawancara
            $table->text('catatan')->nullable();
            $table->string('pj_nama')->nullable();
            $table->string('pj_hp', 30)->nullable();
            $table->string('pj_jabatan')->nullable();
            $table->string('pj_email')->nullable();
            $table->boolean('blok10_completed')->default(false);

            $table->timestamps();
            $table->softDeletes();

            $table->index('user_id');
            // deleted_at is part of the key so a soft-deleted submission stops
            // occupying the slot and the user can re-fill — see the 2026_07_19
            // soft-delete migration for why this matters.
            $table->unique(
                ['user_id', 'tahun', 'triwulan', 'deleted_at'],
                'konstruksi_survey_responses_user_period_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('konstruksi_survey_responses');
    }
};
