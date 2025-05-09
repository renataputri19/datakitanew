<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Monalisa\TimelineEvent;

class MonalisaTimelineEventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $events = [
            [
                'task' => 'Pembinaan dalam Persiapan EPSS',
                'start_date' => '2025-01-01',
                'end_date' => '2025-03-15',
                'category' => 'Persiapan Pelaksanaan Penilaian EPSS',
            ],
            [
                'task' => 'Romantik',
                'start_date' => '2025-01-01',
                'end_date' => '2025-03-15',
                'category' => 'Persiapan Pelaksanaan Penilaian EPSS',
            ],
            [
                'task' => 'Koordinasi Kesiapan TPI',
                'start_date' => '2025-01-01',
                'end_date' => '2025-03-15',
                'category' => 'Persiapan Pelaksanaan Penilaian EPSS',
            ],
            [
                'task' => 'Metadata',
                'start_date' => '2025-01-01',
                'end_date' => '2025-03-15',
                'category' => 'Persiapan Pelaksanaan Penilaian EPSS',
            ],
            [
                'task' => 'Penilaian Mandiri di OPD',
                'start_date' => '2025-04-01',
                'end_date' => '2025-05-15',
                'category' => 'Penilaian Mandiri',
            ],
            [
                'task' => 'TPB',
                'start_date' => '2025-06-16',
                'end_date' => '2025-06-30',
                'category' => 'Penilaian Dokumen',
            ],
            [
                'task' => 'TPK',
                'start_date' => '2025-06-16',
                'end_date' => '2025-06-30',
                'category' => 'Penilaian Dokumen',
            ],
            [
                'task' => 'Penyesuaian TPB',
                'start_date' => '2025-06-16',
                'end_date' => '2025-06-30',
                'category' => 'Penilaian Dokumen',
            ],
            [
                'task' => 'Penilaian Interviu',
                'start_date' => '2025-06-15',
                'end_date' => '2025-07-31',
                'category' => 'Interviu',
            ],
            [
                'task' => 'Penilaian Visitasi',
                'start_date' => '2025-07-15',
                'end_date' => '2025-08-1',
                'category' => 'Visitasi',
            ],
            [
                'task' => 'Harmonisasi Pleno Provinsi',
                'start_date' => '2025-08-01',
                'end_date' => '2025-08-31',
                'category' => 'Pleno',
            ],
        ];

        foreach ($events as $event) {
            TimelineEvent::create($event);
        }
    }
}
