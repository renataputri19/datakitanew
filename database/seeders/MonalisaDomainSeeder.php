<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Monalisa\Domain;

class MonalisaDomainSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $domains = [

            // SDI
            [
                'domain' => 'Prinsip Satu Data Indonesia',
                'aspek' => 'Standar Data Statistik',
                'indikator' => '10101. Penerapan Standar Data Statistik (SDS)',
            ],
            [
                'domain' => 'Prinsip Satu Data Indonesia',
                'aspek' => 'Metadata Statistik',
                'indikator' => '10201 Penerapan Metadata Statistik',
            ],
            [
                'domain' => 'Prinsip Satu Data Indonesia',
                'aspek' => 'Interoperabilitas Data',
                'indikator' => '10301 Penerapan Interoperabilitas Data',
            ],
            [
                'domain' => 'Prinsip Satu Data Indonesia',
                'aspek' => 'Kode Referensi dan/atau Data Induk',
                'indikator' => '10401 Penerapan Kode Referensi',
            ],

            // KUALITAS DATA
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Relevansi',
                'indikator' => '20101 Relevansi Data terhadap Pengguna',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Relevansi',
                'indikator' => '20102 Proses Identifikasi Kebutuhan Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Akurasi',
                'indikator' => '20201 Penilaian Akurasi Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Aktualitas dan Ketepatan Waktu',
                'indikator' => '20301 Penjaminan Aktualitas Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Aktualitas dan Ketepatan Waktu',
                'indikator' => '20302 Pemantauan Ketepatan Waktu Diseminasi',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Aksesibilitas',
                'indikator' => '20401 Ketersediaan Data untuk Pengguna Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Aksesibilitas',
                'indikator' => '20402 Akses Media Penyebarluasan Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Aksesibilitas',
                'indikator' => '20403 Penyediaan Format Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Keterbandingan dan Konsistensi',
                'indikator' => '20501 Keterbandingan Data',
            ],
            [
                'domain' => 'Kualitas Data',
                'aspek' => 'Keterbandingan dan Konsistensi',
                'indikator' => '20502 Konsistensi Statistik',
            ],


            // PROSES BISNIS STATISTIK
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Perencanaan Data',
                'indikator' => '30101 Pendefinisian Kebutuhan Statistik',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Perencanaan Data',
                'indikator' => '30102 Desain Statistik',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Perencanaan Data',
                'indikator' => '30103 Penyiapan Instrumen',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Pengumpulan Data',
                'indikator' => '30201 Proses Pengumpulan Data/Akuisisi Data',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Pemeriksaan Data',
                'indikator' => '30301 Pengolahan Data',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Pemeriksaan Data',
                'indikator' => '30302 Analisis Data',
            ],
            [
                'domain' => 'Proses Bisnis Statistik',
                'aspek' => 'Penyebarluasan Data',
                'indikator' => '30401 Diseminasi Data',
            ],




            // KELEMBAGAAN
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Profesionalitas',
                'indikator' => '40101 Penjaminan Transparansi Informasi Statistik',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Profesionalitas',
                'indikator' => '40102 Penjaminan Netralitas dan Objektifitas terhadap Penggunaan Sumber Data dan Metodologi',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Profesionalitas',
                'indikator' => '40103 Penjaminan Kualitas Data',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Profesionalitas',
                'indikator' => '40104 Penjaminan Konfidensialitas Data',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'SDM yang Memadai dan Kapabel',
                'indikator' => '40201 Penerapan Kompetensi SDM Bidang Statistik',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'SDM yang Memadai dan Kapabel',
                'indikator' => '40202 Penerapan Kompetensi SDM Bidang Manajemen Data',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Pengorganisasian Statistik',
                'indikator' => '40301 Kolaborasi Penyelenggaraan Kegiatan Statistik',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Pengorganisasian Statistik',
                'indikator' => '40302 Penyelenggaraan Forum SDI',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Pengorganisasian Statistik',
                'indikator' => '40303 Kolaborasi dengan Pembina Data Statistik',
            ],
            [
                'domain' => 'Kelembagaan',
                'aspek' => 'Pengorganisasian Statistik',
                'indikator' => '40304 Pelaksanaan Tugas sebagai Walidata',
            ],
            
            

            // STATISTIK NASIONAL
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Pemanfaatan Data Statistik',
                'indikator' => '50101 Penggunaan Data Statistik Dasar untuk Perencanaan, 
Monitoring, Evaluasi, dan/atau Penyusunan Kebijakan',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Pemanfaatan Data Statistik',
                'indikator' => '50102 Penggunaan Data Statistik Sektoral untuk Perencanaan, 
Monitoring, Evaluasi, dan/atau Penyusunan Kebijakan',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Pemanfaatan Data Statistik',
                'indikator' => '50103 Sosialisasi dan Literasi Data Statistik',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Pengelolaan Kegiatan Statistik',
                'indikator' => '50201 Pelaksanaan Rekomendasi Kegiatan Statistik',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Penguatan SSN Berkelanjutan',
                'indikator' => '50301 Perencanaan Pembangunan Statistik',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Penguatan SSN Berkelanjutan',
                'indikator' => '50302 Penyebarluasan Data',
            ],
            [
                'domain' => 'Statistik Nasional',
                'aspek' => 'Penguatan SSN Berkelanjutan',
                'indikator' => '50303 Pemanfaatan Big Data',
            ],




        ];

        foreach ($domains as $domain) {
            Domain::create($domain);
        }
    }
}
