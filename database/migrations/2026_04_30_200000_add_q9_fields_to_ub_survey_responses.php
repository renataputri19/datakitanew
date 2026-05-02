<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ub_survey_responses', function (Blueprint $table) {
            // Q9b – type of activity
            $table->tinyInteger('produksi_di_lokasi')->nullable()->after('kegiatan_utama');
            $table->tinyInteger('layanan_makan_minum')->nullable()->after('produksi_di_lokasi');
            $table->tinyInteger('penjualan_barang')->nullable()->after('layanan_makan_minum');
            $table->tinyInteger('aktivitas_jasa_pertanian')->nullable()->after('penjualan_barang');
            // Q9c – location of business
            $table->tinyInteger('lokasi_usaha')->nullable()->after('aktivitas_jasa_pertanian');
            // Q9d – input used in production
            $table->text('input_produksi')->nullable()->after('lokasi_usaha');
            // Q9e – production process
            $table->text('proses_produksi')->nullable()->after('input_produksi');
            // Q9f – main product
            $table->text('produk_utama')->nullable()->after('proses_produksi');
            // Q9g – KBLI code (filled by BPS)
            $table->string('kode_kbli', 10)->nullable()->after('produk_utama');
            // Q9h – business category (filled by BPS)
            $table->string('kategori_lapangan_usaha', 3)->nullable()->after('kode_kbli');
            // Q9i – accommodation classification
            $table->tinyInteger('klasifikasi_akomodasi')->nullable()->after('kategori_lapangan_usaha');
        });
    }

    public function down(): void
    {
        Schema::table('ub_survey_responses', function (Blueprint $table) {
            $table->dropColumn([
                'produksi_di_lokasi', 'layanan_makan_minum', 'penjualan_barang',
                'aktivitas_jasa_pertanian', 'lokasi_usaha', 'input_produksi',
                'proses_produksi', 'produk_utama', 'kode_kbli',
                'kategori_lapangan_usaha', 'klasifikasi_akomodasi',
            ]);
        });
    }
};
