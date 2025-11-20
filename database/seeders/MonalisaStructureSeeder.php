<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MonalisaDomain;
use App\Models\MonalisaAspek;
use App\Models\MonalisaIndikator;

class MonalisaStructureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Domain 1: Prinsip Satu Data Indonesia (SDI) - 28%
        $domain1 = MonalisaDomain::create([
            'name' => 'Prinsip Satu Data Indonesia (SDI)',
            'description' => 'Domain yang mengatur standar data, metadata, interoperabilitas, dan kode referensi',
            'domain_number' => 1,
            'weight' => 28.00,
            'order' => 1,
        ]);

        // Domain 1 - Aspek 1: Standar Data Statistik (25%)
        $aspek1_1 = MonalisaAspek::create([
            'domain_id' => $domain1->id,
            'name' => 'Standar Data Statistik',
            'aspek_number' => 1,
            'weight' => 25.00,
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek1_1->id,
            'name' => 'Tingkat Kematangan Penerapan Standar Data Statistik (SDS)',
            'indikator_code' => '1.1',
            'order' => 1,
        ]);

        // Domain 1 - Aspek 2: Metadata Statistik (25%)
        $aspek1_2 = MonalisaAspek::create([
            'domain_id' => $domain1->id,
            'name' => 'Metadata Statistik',
            'aspek_number' => 2,
            'weight' => 25.00,
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek1_2->id,
            'name' => 'Tingkat Kematangan Penerapan Metadata Statistik',
            'indikator_code' => '2.1',
            'order' => 1,
        ]);

        // Domain 1 - Aspek 3: Interoperabilitas Data (25%)
        $aspek1_3 = MonalisaAspek::create([
            'domain_id' => $domain1->id,
            'name' => 'Interoperabilitas Data',
            'aspek_number' => 3,
            'weight' => 25.00,
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek1_3->id,
            'name' => 'Tingkat Kematangan Penerapan Interoperabilitas Data',
            'indikator_code' => '3.1',
            'order' => 1,
        ]);

        // Domain 1 - Aspek 4: Kode Referensi dan/atau Data Induk (25%)
        $aspek1_4 = MonalisaAspek::create([
            'domain_id' => $domain1->id,
            'name' => 'Kode Referensi dan/atau Data Induk',
            'aspek_number' => 4,
            'weight' => 25.00,
            'order' => 4,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek1_4->id,
            'name' => 'Tingkat Kematangan Penerapan Kode Referensi',
            'indikator_code' => '4.1',
            'order' => 1,
        ]);

        // Domain 2: Kualitas Data - 24%
        $domain2 = MonalisaDomain::create([
            'name' => 'Kualitas Data',
            'description' => 'Domain yang mengatur relevansi, akurasi, aktualitas, aksesibilitas, dan konsistensi data',
            'domain_number' => 2,
            'weight' => 24.00,
            'order' => 2,
        ]);

        // Domain 2 - Aspek 1: Relevansi (21%)
        $aspek2_1 = MonalisaAspek::create([
            'domain_id' => $domain2->id,
            'name' => 'Relevansi',
            'aspek_number' => 1,
            'weight' => 21.00,
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_1->id,
            'name' => 'Tingkat Kematangan Relevansi Data terhadap Pengguna',
            'indikator_code' => '1.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_1->id,
            'name' => 'Tingkat Kematangan Proses Identifikasi Kebutuhan Data',
            'indikator_code' => '1.2',
            'order' => 2,
        ]);

        // Domain 2 - Aspek 2: Akurasi (16%)
        $aspek2_2 = MonalisaAspek::create([
            'domain_id' => $domain2->id,
            'name' => 'Akurasi',
            'aspek_number' => 2,
            'weight' => 16.00,
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_2->id,
            'name' => 'Tingkat Kematangan Penilaian Akurasi Data',
            'indikator_code' => '2.1',
            'order' => 1,
        ]);

        // Domain 2 - Aspek 3: Aktualitas dan Ketepatan Waktu (21%)
        $aspek2_3 = MonalisaAspek::create([
            'domain_id' => $domain2->id,
            'name' => 'Aktualitas dan Ketepatan Waktu',
            'aspek_number' => 3,
            'weight' => 21.00,
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_3->id,
            'name' => 'Tingkat Kematangan Penjaminan Aktualitas Data',
            'indikator_code' => '3.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_3->id,
            'name' => 'Tingkat Kematangan Pemantauan Ketepatan Waktu Diseminasi',
            'indikator_code' => '3.2',
            'order' => 2,
        ]);

        // Domain 2 - Aspek 4: Aksesibilitas (21%)
        $aspek2_4 = MonalisaAspek::create([
            'domain_id' => $domain2->id,
            'name' => 'Aksesibilitas',
            'aspek_number' => 4,
            'weight' => 21.00,
            'order' => 4,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_4->id,
            'name' => 'Tingkat Kematangan Ketersediaan Data untuk Pengguna Data',
            'indikator_code' => '4.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_4->id,
            'name' => 'Tingkat Kematangan Akses Media Penyebarluasan Data',
            'indikator_code' => '4.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_4->id,
            'name' => 'Tingkat Kematangan Penyediaan Format Data',
            'indikator_code' => '4.3',
            'order' => 3,
        ]);

        // Domain 2 - Aspek 5: Keterbandingan dan Konsistensi (21%)
        $aspek2_5 = MonalisaAspek::create([
            'domain_id' => $domain2->id,
            'name' => 'Keterbandingan dan Konsistensi',
            'aspek_number' => 5,
            'weight' => 21.00,
            'order' => 5,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_5->id,
            'name' => 'Tingkat Kematangan Keterbandingan Data',
            'indikator_code' => '5.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek2_5->id,
            'name' => 'Tingkat Kematangan Konsistensi Statistik',
            'indikator_code' => '5.2',
            'order' => 2,
        ]);

        // Domain 3: Proses Bisnis Statistik - 19%
        $domain3 = MonalisaDomain::create([
            'name' => 'Proses Bisnis Statistik',
            'description' => 'Domain yang mengatur perencanaan, pengumpulan, pemeriksaan, dan penyebarluasan data',
            'domain_number' => 3,
            'weight' => 19.00,
            'order' => 3,
        ]);

        // Domain 3 - Aspek 1: Perencanaan Data (32%)
        $aspek3_1 = MonalisaAspek::create([
            'domain_id' => $domain3->id,
            'name' => 'Perencanaan Data',
            'aspek_number' => 1,
            'weight' => 32.00,
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_1->id,
            'name' => 'Tingkat Kematangan Pendefinisian Kebutuhan Statistik',
            'indikator_code' => '1.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_1->id,
            'name' => 'Tingkat Kematangan Desain Statistik',
            'indikator_code' => '1.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_1->id,
            'name' => 'Tingkat Kematangan Penyiapan Instrumen',
            'indikator_code' => '1.3',
            'order' => 3,
        ]);

        // Domain 3 - Aspek 2: Pengumpulan Data (26%)
        $aspek3_2 = MonalisaAspek::create([
            'domain_id' => $domain3->id,
            'name' => 'Pengumpulan Data',
            'aspek_number' => 2,
            'weight' => 26.00,
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_2->id,
            'name' => 'Tingkat Kematangan Proses Pengumpulan Data/Akuisisi Data',
            'indikator_code' => '2.1',
            'order' => 1,
        ]);

        // Domain 3 - Aspek 3: Pemeriksaan Data (21%)
        $aspek3_3 = MonalisaAspek::create([
            'domain_id' => $domain3->id,
            'name' => 'Pemeriksaan Data',
            'aspek_number' => 3,
            'weight' => 21.00,
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_3->id,
            'name' => 'Tingkat Kematangan Pengolahan Data',
            'indikator_code' => '3.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_3->id,
            'name' => 'Tingkat Kematangan Analisis Data',
            'indikator_code' => '3.2',
            'order' => 2,
        ]);

        // Domain 3 - Aspek 4: Penyebarluasan Data (21%)
        $aspek3_4 = MonalisaAspek::create([
            'domain_id' => $domain3->id,
            'name' => 'Penyebarluasan Data',
            'aspek_number' => 4,
            'weight' => 21.00,
            'order' => 4,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek3_4->id,
            'name' => 'Tingkat Kematangan Diseminasi Data',
            'indikator_code' => '4.1',
            'order' => 1,
        ]);

        // Domain 4: Kelembagaan - 17%
        $domain4 = MonalisaDomain::create([
            'name' => 'Kelembagaan',
            'description' => 'Domain yang mengatur profesionalitas, SDM, dan pengorganisasian statistik',
            'domain_number' => 4,
            'weight' => 17.00,
            'order' => 4,
        ]);

        // Domain 4 - Aspek 1: Profesionalitas (35%)
        $aspek4_1 = MonalisaAspek::create([
            'domain_id' => $domain4->id,
            'name' => 'Profesionalitas',
            'aspek_number' => 1,
            'weight' => 35.00,
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_1->id,
            'name' => 'Tingkat Kematangan Penjaminan Transparansi Informasi Statistik',
            'indikator_code' => '1.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_1->id,
            'name' => 'Tingkat Kematangan Penjaminan Netralitas dan Objektivitas terhadap Penggunaan Sumber Data dan Metodologi',
            'indikator_code' => '1.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_1->id,
            'name' => 'Tingkat Kematangan Penjaminan Kualitas Data',
            'indikator_code' => '1.3',
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_1->id,
            'name' => 'Tingkat Kematangan Penjaminan Konfidensialitas Data',
            'indikator_code' => '1.4',
            'order' => 4,
        ]);

        // Domain 4 - Aspek 2: SDM yang Memadai dan Kapabel (30%)
        $aspek4_2 = MonalisaAspek::create([
            'domain_id' => $domain4->id,
            'name' => 'SDM yang Memadai dan Kapabel',
            'aspek_number' => 2,
            'weight' => 30.00,
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_2->id,
            'name' => 'Tingkat Kematangan Pemenuhan Kompetensi SDM Bidang Statistik',
            'indikator_code' => '2.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_2->id,
            'name' => 'Tingkat Kematangan Pemenuhan Kompetensi SDM Bidang Manajemen Data',
            'indikator_code' => '2.2',
            'order' => 2,
        ]);

        // Domain 4 - Aspek 3: Pengorganisasian Statistik (35%)
        $aspek4_3 = MonalisaAspek::create([
            'domain_id' => $domain4->id,
            'name' => 'Pengorganisasian Statistik',
            'aspek_number' => 3,
            'weight' => 35.00,
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_3->id,
            'name' => 'Tingkat Kematangan Kolaborasi Penyelenggaraan Kegiatan Statistik',
            'indikator_code' => '3.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_3->id,
            'name' => 'Tingkat Kematangan Penyelenggaraan Forum Satu Data Indonesia',
            'indikator_code' => '3.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_3->id,
            'name' => 'Tingkat Kematangan Kolaborasi dengan Pembina Data Statistik',
            'indikator_code' => '3.3',
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek4_3->id,
            'name' => 'Tingkat Kematangan Pelaksanaan Tugas sebagai Walidata',
            'indikator_code' => '3.4',
            'order' => 4,
        ]);

        // Domain 5: Statistik Nasional - 12%
        $domain5 = MonalisaDomain::create([
            'name' => 'Statistik Nasional',
            'description' => 'Domain yang mengatur pemanfaatan data, pengelolaan kegiatan, dan penguatan SSN',
            'domain_number' => 5,
            'weight' => 12.00,
            'order' => 5,
        ]);

        // Domain 5 - Aspek 1: Pemanfaatan Data Statistik (34%)
        $aspek5_1 = MonalisaAspek::create([
            'domain_id' => $domain5->id,
            'name' => 'Pemanfaatan Data Statistik',
            'aspek_number' => 1,
            'weight' => 34.00,
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_1->id,
            'name' => 'Tingkat Kematangan Penggunaan Data Statistik Dasar untuk Perencanaan, Monitoring, dan Evaluasi, dan atau Penyusunan Kebijakan',
            'indikator_code' => '1.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_1->id,
            'name' => 'Tingkat Kematangan Penggunaan Data Statistik Sektoral untuk Perencanaan, Monitoring, dan Evaluasi, dan atau Penyusunan Kebijakan',
            'indikator_code' => '1.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_1->id,
            'name' => 'Tingkat Kematangan Sosialisasi dan Literasi Hasil Statistik',
            'indikator_code' => '1.3',
            'order' => 3,
        ]);

        // Domain 5 - Aspek 2: Pengelolaan Kegiatan Statistik (33%)
        $aspek5_2 = MonalisaAspek::create([
            'domain_id' => $domain5->id,
            'name' => 'Pengelolaan Kegiatan Statistik',
            'aspek_number' => 2,
            'weight' => 33.00,
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_2->id,
            'name' => 'Tingkat Kematangan Pelaksanaan Rekomendasi Kegiatan Statistik',
            'indikator_code' => '2.1',
            'order' => 1,
        ]);

        // Domain 5 - Aspek 3: Penguatan SSN Berkelanjutan (33%)
        $aspek5_3 = MonalisaAspek::create([
            'domain_id' => $domain5->id,
            'name' => 'Penguatan SSN Berkelanjutan',
            'aspek_number' => 3,
            'weight' => 33.00,
            'order' => 3,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_3->id,
            'name' => 'Tingkat Kematangan Perencanaan Pembangunan Statistik',
            'indikator_code' => '3.1',
            'order' => 1,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_3->id,
            'name' => 'Tingkat Kematangan Penyebarluasan Data',
            'indikator_code' => '3.2',
            'order' => 2,
        ]);

        MonalisaIndikator::create([
            'aspek_id' => $aspek5_3->id,
            'name' => 'Tingkat Kematangan Pemanfaatan Big Data',
            'indikator_code' => '3.3',
            'order' => 3,
        ]);
    }
}

