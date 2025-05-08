<?php

namespace Database\Seeders;

use App\Models\News;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class NewsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing news data
        DB::table('news')->truncate();

        // News items
        $newsItems = [
            [
                'title' => 'Perkembangan Indeks Harga Konsumen Kota Batam, April 2025',
                'date' => '2025-05-02',
                'category' => 'BRS',
                'excerpt' => 'IHK Kota Batam pada April 2025 tercatat 109,21 atau mengalami inflasi Year on Year (y-o-y) sebesar 2,81 persen ....',
                'source_url' => 'https://batamkota.bps.go.id/id/pressrelease/2025/05/02/701/pada-april-2025--kota-batam-mengalami-inflasi-year-on-year--y-on-y--sebesar-2-81-persen-dengan-indeks-harga-konsumen--ihk--sebesar-109-21-.html',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=cTjssxScosc'
            ],
            [
                'title' => 'Perkembangan Ekspor dan Impor Kota Batam, Maret 2025',
                'date' => '2025-05-05',
                'category' => 'BRS',
                'excerpt' => 'Ekspor Maret 2025 sebesar US$ 1.496,46 juta atau naik 3,19 persen dibandingkan ekspor Februari 2025. ...',
                'source_url' => 'https://batamkota.bps.go.id/id/pressrelease/2025/05/05/713/ekspor-maret-2025--naik-3-19-persen-dibandingkan-bulan-lalu-namun-impor-maret-2025--turun-1-09-persen-.html',
                'has_video' => false,
                'video_url' => null
            ],
            [
                'title' => 'Perkembangan Transportasi Kota Batam, Maret 2025',
                'date' => '2025-05-05',
                'category' => 'BRS',
                'excerpt' => 'Jumlah Penumpang angkutan udara domestik pada Maret 2025 sebanyak 293.416 orang sedangkan jumlah penumpang angkutan laut domestik pada Maret 2025 sebanyak 342.982 orang ...',
                'source_url' => 'https://batamkota.bps.go.id/id/pressrelease/2025/05/05/678/maret-2025--jumlah-penumpang-angkutan-udara-domestik-naik-sebesar-6-50-persen-.html',
                'has_video' => false,
                'video_url' => null
            ],
            [
                'title' => 'Perkembangan Wisman Mancanegara Kota Batam, Maret 2025',
                'date' => '2025-05-05',
                'category' => 'BRS',
                'excerpt' => 'Pada Maret 2025 jumlah wisman mancanegara ke KotaBatam sebanyak 100.279 kunjungan, dan TPK hotel bintang di Kota Batam sebesar 46,25 persen. ...',
                'source_url' => 'https://batamkota.bps.go.id/id/pressrelease/2025/05/05/654/pada-maret-2025-jumlah-wisman-mancanegara-ke-kotabatam-sebanyak-100-279-kunjungan--.html',
                'has_video' => false,
                'video_url' => null
            ]
        ];

        // Insert news items
        foreach ($newsItems as $item) {
            News::create($item);
        }
    }
}
