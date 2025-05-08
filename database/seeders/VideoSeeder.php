<?php

namespace Database\Seeders;

use App\Models\Video;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing videos data
        DB::table('videos')->truncate();

        // Videos
        $videos = [
            [
                'title' => 'Penyampaian Berita Resmi Statistik Inflasi Bulan April 2025',
                'date' => '2025-05-02',
                'url' => 'https://www.youtube.com/watch?v=cTjssxScosc'
            ],
            [
                'title' => 'Penyampaian BRS Pariwisata, Ekspor, Impor dan Transportasi, Mei 2025',
                'date' => '2025-05-05',
                'url' => 'https://www.youtube.com/watch?v=MNP-nRpTX1U'
            ]
        ];

        // Insert videos
        foreach ($videos as $video) {
            Video::create($video);
        }
    }
}
