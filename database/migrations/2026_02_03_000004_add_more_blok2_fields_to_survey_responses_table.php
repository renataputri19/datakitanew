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
            // Additional Blok II fields (R202–R211)
            $table->integer('jumlah_cabang_dan_unit_usaha')->nullable()->after('kbli_utama');
            $table->string('info_kantor_pusat_nama')->nullable()->after('jumlah_cabang_dan_unit_usaha');
            $table->text('info_kantor_pusat_alamat')->nullable()->after('info_kantor_pusat_nama');
            $table->string('info_kantor_pusat_email')->nullable()->after('info_kantor_pusat_alamat');
            $table->string('info_kantor_pusat_negara')->nullable()->after('info_kantor_pusat_email');
            $table->string('info_kantor_pusat_provinsi')->nullable()->after('info_kantor_pusat_negara');
            $table->string('info_kantor_pusat_kabkota')->nullable()->after('info_kantor_pusat_provinsi');

            $table->integer('jumlah_bulan_aktif_2025')->nullable()->after('info_kantor_pusat_kabkota');
            $table->integer('rata_hari_kerja_bulanan_2025')->nullable()->after('jumlah_bulan_aktif_2025');
            $table->integer('rata_jam_kerja_per_hari_2025')->nullable()->after('rata_hari_kerja_bulanan_2025');
            $table->integer('rata_shift_per_hari_2025')->nullable()->after('rata_jam_kerja_per_hari_2025');

            $table->integer('tenaga_kerja_laki_laki')->nullable()->after('rata_shift_per_hari_2025');
            $table->integer('tenaga_kerja_perempuan')->nullable()->after('tenaga_kerja_laki_laki');
            $table->integer('tenaga_kerja_produksi')->nullable()->after('tenaga_kerja_perempuan');
            $table->integer('tenaga_kerja_lainnya')->nullable()->after('tenaga_kerja_produksi');
            $table->integer('tenaga_kerja_asing')->nullable()->after('tenaga_kerja_lainnya');
            $table->integer('tenaga_kerja_outsourcing')->nullable()->after('tenaga_kerja_asing');

            $table->string('memproduksi_barang_sendiri')->nullable()->after('tenaga_kerja_outsourcing');
            $table->string('menyediakan_layanan_makan_minum')->nullable()->after('memproduksi_barang_sendiri');
            $table->string('penjualan_barang_pihak_lain')->nullable()->after('menyediakan_layanan_makan_minum');
            $table->string('aktivitas_jasa')->nullable()->after('penjualan_barang_pihak_lain');

            $table->string('penggunaan_internet')->nullable()->after('aktivitas_jasa');
            $table->string('internet_a1_menerima_pesanan')->nullable()->after('penggunaan_internet');
            $table->string('internet_a2_produksi')->nullable()->after('internet_a1_menerima_pesanan');
            $table->string('internet_a3_distribusi')->nullable()->after('internet_a2_produksi');
            $table->string('internet_a4_beli_bahan_baku')->nullable()->after('internet_a3_distribusi');
            $table->string('internet_a5_promosi')->nullable()->after('internet_a4_beli_bahan_baku');
            $table->string('internet_a6_lainnya')->nullable()->after('internet_a5_promosi');

            $table->string('pemanfaatan_teknologi_digital')->nullable()->after('internet_a6_lainnya');
            $table->string('produksi_ramah_lingkungan')->nullable()->after('pemanfaatan_teknologi_digital');
            $table->string('penggunaan_input_ramah_lingkungan')->nullable()->after('produksi_ramah_lingkungan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'jumlah_cabang_dan_unit_usaha',
                'info_kantor_pusat_nama',
                'info_kantor_pusat_alamat',
                'info_kantor_pusat_email',
                'info_kantor_pusat_negara',
                'info_kantor_pusat_provinsi',
                'info_kantor_pusat_kabkota',
                'jumlah_bulan_aktif_2025',
                'rata_hari_kerja_bulanan_2025',
                'rata_jam_kerja_per_hari_2025',
                'rata_shift_per_hari_2025',
                'tenaga_kerja_laki_laki',
                'tenaga_kerja_perempuan',
                'tenaga_kerja_produksi',
                'tenaga_kerja_lainnya',
                'tenaga_kerja_asing',
                'tenaga_kerja_outsourcing',
                'memproduksi_barang_sendiri',
                'menyediakan_layanan_makan_minum',
                'penjualan_barang_pihak_lain',
                'aktivitas_jasa',
                'penggunaan_internet',
                'internet_a1_menerima_pesanan',
                'internet_a2_produksi',
                'internet_a3_distribusi',
                'internet_a4_beli_bahan_baku',
                'internet_a5_promosi',
                'internet_a6_lainnya',
                'pemanfaatan_teknologi_digital',
                'produksi_ramah_lingkungan',
                'penggunaan_input_ramah_lingkungan',
            ]);
        });
    }
};