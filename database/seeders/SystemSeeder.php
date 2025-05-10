<?php

namespace Database\Seeders;

use App\Models\System;
use Illuminate\Database\Seeder;

class SystemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing systems
        System::truncate();

        // Create systems
        $systems = [
            [
                'name' => 'MONALISA',
                'slug' => 'monalisa',
                'description' => 'Monitoring dan Evaluasi Statistik Sektoral',
                'detail' => 'Sistem untuk monitoring dan evaluasi statistik sektoral, memungkinkan dinas dan instansi untuk mengunggah dokumen, mengevaluasi indikator berdasarkan standar EPSS, dan memantau kemajuan secara real-time.',
                'icon' => 'bar-chart-3',
                'url' => 'https://monalisa.bpsbatam.com/',
                'category' => 'monitoring',
            ],
            [
                'name' => 'Romantik',
                'slug' => 'romantik',
                'description' => 'Sistem Romantik',
                'detail' => 'Sistem Romantik digunakan untuk pelaporan dan rekomendasi acara statistik. Ini memastikan bahwa semua kegiatan statistik diatur dan dilaporkan secara tepat.',
                'icon' => 'line-chart',
                'url' => 'https://romantik.web.bps.go.id/',
                'category' => 'other',
            ],
            [
                'name' => 'Simbatik',
                'slug' => 'simbatik',
                'description' => 'Sistem Simbatik',
                'detail' => 'Sistem Simbatik adalah platform untuk mengunggah dokumen yang berkaitan dengan indikator EPSS. Ini memudahkan proses dokumentasi dan verifikasi.',
                'icon' => 'bar-chart-3',
                'url' => 'https://epss.web.bps.go.id/home',
                'category' => 'other',
            ],
            [
                'name' => 'INDAH',
                'slug' => 'indah',
                'description' => 'Indonesia Data Hub (INDAH)',
                'detail' => 'Indonesia Data Hub merupakan one stop collaboration platform yang bertujuan untuk meningkatkan literasi data dan value of statistics serta mendukung interoperabilitas data dan kolaborasi eksplorasi terhadap data.',
                'icon' => 'database',
                'url' => 'https://indah.bps.go.id/',
                'category' => 'data',
            ],
        ];

        // Insert systems
        foreach ($systems as $system) {
            System::create($system);
        }
    }
}
