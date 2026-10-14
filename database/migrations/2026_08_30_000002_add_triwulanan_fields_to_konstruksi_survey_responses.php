<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Columns for the quarterly questionnaire (SKTR), added alongside the annual
 * (SKTH) ones already on konstruksi_survey_responses.
 *
 * A row is either annual (triwulan = 0) or quarterly (1–4), never both, so the
 * two instruments share this table and the columns each does not use stay null.
 * Blok I, the closing block (metode pendataan / catatan / pemberi jawaban) and
 * the blok1..blok3 completion flags are shared outright — SKTR's Blok I is a
 * subset of SKTH's, and KonstruksiBlokPath picks the right predicate per period.
 *
 * SKTR's own additions are the "Updating Status Perusahaan" preamble, which
 * SKTH has no equivalent of, and the two quarterly grids.
 *
 * Quarter-relative answers are stored under slot names (prev / cur / lalu /
 * ini / depan), NOT under quarter keys like "2026_1": the questionnaire's
 * columns roll every quarter, and slot names keep the stored shape stable while
 * KonstruksiSurveyResponse::quarterLabels() derives what each slot is called.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('konstruksi_survey_responses', function (Blueprint $table) {
            // ── Updating Status Perusahaan (SKTR preamble) ──
            $table->string('status_perusahaan', 5)->nullable()->after('survey_section');   // 1–6
            $table->string('kesediaan_responden', 5)->nullable()->after('status_perusahaan'); // 1–3
            $table->string('keberadaan_proyek', 5)->nullable()->after('kesediaan_responden'); // 1–2

            // Alamat baru, only when status_perusahaan = 6 (Pindah Kabupaten/Kota)
            $table->string('pindah_kabupaten_kota', 100)->nullable();
            $table->string('pindah_kecamatan', 100)->nullable();
            $table->string('pindah_kelurahan_desa', 100)->nullable();
            $table->text('pindah_alamat')->nullable();
            $table->string('pindah_rt', 5)->nullable();
            $table->string('pindah_rw', 5)->nullable();
            $table->string('pindah_kode_pos', 10)->nullable();

            $table->boolean('status_completed')->default(false);

            // ── Blok II (SKTR): two quarters only ──
            // {"prev": "12", "cur": "15"} — jumlah pekerja tetap & kontrak
            $table->json('tw_pekerja')->nullable();
            // {"prev": "1.500.000", "cur": "1.600.000"} — balas jasa, 000 Rp
            $table->json('tw_balas_jasa')->nullable();

            // ── Blok III (SKTR): the 16-column project grid ──
            $table->json('data_proyek_tw')->nullable();

            // Closing block lives on the Blok III sheet in SKTR; it gets its own
            // page in the app, so it gets its own flag.
            $table->boolean('penutup_completed')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('konstruksi_survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'status_perusahaan', 'kesediaan_responden', 'keberadaan_proyek',
                'pindah_kabupaten_kota', 'pindah_kecamatan', 'pindah_kelurahan_desa',
                'pindah_alamat', 'pindah_rt', 'pindah_rw', 'pindah_kode_pos',
                'status_completed', 'tw_pekerja', 'tw_balas_jasa', 'data_proyek_tw',
                'penutup_completed',
            ]);
        });
    }
};
