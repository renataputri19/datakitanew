<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ub_survey_responses', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('user_id')->constrained()->onDelete('cascade');
            $table->unsignedSmallInteger('tahun')->default(2026);
            $table->string('survey_section')->nullable();
            $table->boolean('is_completed')->default(false);

            // ── Blok 1-A: Identitas & Lokasi ─────────────────────────
            $table->string('provinsi')->nullable();
            $table->string('kabupaten_kota')->nullable();
            $table->string('kecamatan')->nullable();
            $table->string('kelurahan_desa')->nullable();
            // Q5
            $table->string('nama_perusahaan')->nullable();
            $table->string('nama_komersial')->nullable();
            $table->text('alamat_perusahaan')->nullable();
            $table->string('rt')->nullable();
            $table->string('rw')->nullable();
            $table->string('nomor_telepon')->nullable();
            $table->string('kode_pos')->nullable();
            $table->string('email_perusahaan')->nullable();
            $table->string('homepage')->nullable();
            $table->string('nomor_hp')->nullable();
            $table->tinyInteger('jenis_kawasan')->nullable();
            $table->string('nama_kawasan')->nullable();
            // Q6
            $table->tinyInteger('has_nib')->nullable();
            $table->string('nib')->nullable();
            $table->tinyInteger('alasan_tidak_nib')->nullable();
            // Q7
            $table->tinyInteger('status_badan_usaha')->nullable();
            $table->tinyInteger('is_koperasi_kdkmp')->nullable();
            $table->tinyInteger('jenis_koperasi')->nullable();
            $table->tinyInteger('has_laporan_keuangan')->nullable();
            // Q8
            $table->string('nama_pengusaha')->nullable();
            $table->tinyInteger('jenis_kelamin')->nullable();
            $table->unsignedTinyInteger('umur')->nullable();
            $table->string('nik')->nullable();
            $table->boolean('blok1a_completed')->default(false);

            // ── Blok 1-B: Kegiatan & Digital ─────────────────────────
            // Q9
            $table->text('kegiatan_utama')->nullable();
            // Q10
            $table->tinyInteger('jaringan_usaha')->nullable();
            $table->unsignedInteger('jumlah_cabang')->nullable();
            // Q11
            $table->string('kp_nama')->nullable();
            $table->text('kp_alamat')->nullable();
            $table->string('kp_email')->nullable();
            $table->string('kp_negara')->nullable();
            $table->string('kp_provinsi')->nullable();
            $table->string('kp_kabkota')->nullable();
            // Q12
            $table->tinyInteger('uses_internet')->nullable();
            $table->tinyInteger('internet_pesanan')->nullable();
            $table->tinyInteger('internet_produksi')->nullable();
            $table->tinyInteger('internet_distribusi')->nullable();
            $table->tinyInteger('internet_beli_bahan_baku')->nullable();
            $table->tinyInteger('internet_promosi')->nullable();
            $table->tinyInteger('internet_lainnya')->nullable();
            $table->tinyInteger('uses_teknologi_digital')->nullable();
            // Q13
            $table->tinyInteger('produk_ramah_lingkungan')->nullable();
            $table->tinyInteger('uses_input_lingkungan')->nullable();
            // Q14
            $table->tinyInteger('uses_karya_seni')->nullable();
            $table->boolean('blok1b_completed')->default(false);

            // ── Blok 1-C: Sertifikasi & Kemitraan ────────────────────
            // Q15
            $table->tinyInteger('sertifikat_halal')->nullable();
            $table->unsignedInteger('jumlah_produk_halal_bpjph')->nullable();
            $table->unsignedInteger('jumlah_produk_belum_halal_bpjph')->nullable();
            // Q16
            $table->tinyInteger('izin_edar')->nullable();
            $table->unsignedInteger('jumlah_produk_izin_edar_bpom')->nullable();
            $table->unsignedInteger('jumlah_produk_tanpa_izin_edar_bpom')->nullable();
            // Q17
            $table->tinyInteger('bermitra_kdkmp')->nullable();
            // Q18
            $table->tinyInteger('terlibat_mbg')->nullable();
            // Q19
            $table->tinyInteger('ekspor_impor_barang')->nullable();
            $table->tinyInteger('ekspor_impor_jasa')->nullable();
            $table->boolean('blok1c_completed')->default(false);

            // ── Blok 1-D: Tenaga Kerja & Keuangan ────────────────────
            // Q20
            $table->unsignedInteger('pekerja_laki')->nullable();
            $table->unsignedInteger('pekerja_perempuan')->nullable();
            $table->unsignedInteger('total_pekerja')->nullable();
            // Q21
            $table->unsignedSmallInteger('tahun_beroperasi')->nullable();
            // Q22
            $table->decimal('pengeluaran_upah_gaji', 20, 2)->nullable();
            $table->decimal('pengeluaran_biaya_produksi', 20, 2)->nullable();
            $table->decimal('pengeluaran_pembelian_barang', 20, 2)->nullable();
            $table->decimal('pengeluaran_operasional', 20, 2)->nullable();
            $table->decimal('pengeluaran_nonoperasional', 20, 2)->nullable();
            $table->decimal('total_pengeluaran', 20, 2)->nullable();
            // Q23
            $table->decimal('nilai_produksi_barang_jasa', 20, 2)->nullable();
            $table->decimal('pendapatan_lainnya', 20, 2)->nullable();
            $table->decimal('total_nilai_produksi', 20, 2)->nullable();
            $table->decimal('persen_pendapatan_online', 5, 2)->nullable();
            // Q24
            $table->decimal('nilai_aset_tanah_bangunan', 20, 2)->nullable();
            $table->decimal('nilai_aset_lainnya', 20, 2)->nullable();
            $table->decimal('nilai_total_aset', 20, 2)->nullable();
            $table->tinyInteger('range_total_aset')->nullable();
            $table->decimal('luas_tanah', 15, 2)->nullable();
            // Q25
            $table->decimal('modal_pribadi', 6, 2)->nullable();
            $table->decimal('modal_nonprofit', 6, 2)->nullable();
            $table->decimal('modal_korporasi_publik', 6, 2)->nullable();
            $table->decimal('modal_korporasi_nonpublik', 6, 2)->nullable();
            $table->decimal('modal_pemerintah', 6, 2)->nullable();
            $table->decimal('modal_asing', 6, 2)->nullable();
            $table->boolean('blok1d_completed')->default(false);

            // ── Blok 2: Catatan ───────────────────────────────────────
            $table->text('catatan')->nullable();
            $table->boolean('blok2_completed')->default(false);

            // ── Blok 3: Keterangan Pemberi Jawaban ───────────────────
            $table->string('ppl_nama')->nullable();
            $table->string('ppl_nip')->nullable();
            $table->string('ppl_telepon')->nullable();
            $table->string('ppl_email')->nullable();
            $table->date('ppl_tanggal')->nullable();
            $table->string('pml_nama')->nullable();
            $table->string('pml_nip')->nullable();
            $table->string('pml_telepon')->nullable();
            $table->string('pml_email')->nullable();
            $table->date('pml_tanggal')->nullable();
            $table->string('resp_nama')->nullable();
            $table->string('resp_nip')->nullable();
            $table->string('resp_telepon')->nullable();
            $table->string('resp_email')->nullable();
            $table->date('resp_tanggal')->nullable();
            $table->boolean('blok3_completed')->default(false);

            $table->timestamps();
            $table->unique(['user_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ub_survey_responses');
    }
};
