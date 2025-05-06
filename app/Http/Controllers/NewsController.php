<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NewsController extends Controller
{
    public function index()
    {
        // News items
        $newsItems = [
            [
                'id' => 1,
                'title' => 'Rilis BRS Inflasi April 2023',
                'date' => '2 Mei 2023',
                'category' => 'BRS',
                'excerpt' => 'Pada bulan April 2023, Kota Batam mengalami inflasi sebesar 0,15 persen dengan Indeks Harga Konsumen (IHK) sebesar 112,30.',
                'thumbnail' => 'news/inflasi-april-2023.jpg',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=example1'
            ],
            [
                'id' => 2,
                'title' => 'Rilis BRS Ekspor-Impor Maret 2023',
                'date' => '15 April 2023',
                'category' => 'BRS',
                'excerpt' => 'Nilai ekspor Kota Batam pada Maret 2023 mencapai US$123,45 juta, naik 5,67 persen dibandingkan Februari 2023.',
                'thumbnail' => 'news/ekspor-impor-maret-2023.jpg',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=example2'
            ],
            [
                'id' => 3,
                'title' => 'Rilis BRS Pertumbuhan Ekonomi Q1 2023',
                'date' => '5 April 2023',
                'category' => 'BRS',
                'excerpt' => 'Perekonomian Kota Batam pada Triwulan I-2023 tumbuh sebesar 4,56 persen (y-on-y) dibandingkan Triwulan I-2022.',
                'thumbnail' => 'news/pertumbuhan-ekonomi-q1-2023.jpg',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=example3'
            ],
            [
                'id' => 4,
                'title' => 'Webinar Statistik Sektoral Kota Batam',
                'date' => '20 Maret 2023',
                'category' => 'Event',
                'excerpt' => 'BPS Kota Batam menyelenggarakan webinar tentang statistik sektoral untuk meningkatkan pemahaman dan kapasitas OPD dalam pengelolaan data statistik.',
                'thumbnail' => 'news/webinar-statistik-sektoral.jpg',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=example4'
            ],
            [
                'id' => 5,
                'title' => 'Sosialisasi Sensus Penduduk Online 2023',
                'date' => '10 Maret 2023',
                'category' => 'Event',
                'excerpt' => 'BPS Kota Batam melakukan sosialisasi sensus penduduk online 2023 kepada masyarakat Kota Batam.',
                'thumbnail' => 'news/sosialisasi-sensus-penduduk.jpg',
                'has_video' => true,
                'video_url' => 'https://www.youtube.com/watch?v=example5'
            ],
            [
                'id' => 6,
                'title' => 'Rilis Publikasi Kota Batam Dalam Angka 2023',
                'date' => '1 Maret 2023',
                'category' => 'Publikasi',
                'excerpt' => 'BPS Kota Batam merilis publikasi Kota Batam Dalam Angka 2023 yang berisi data statistik komprehensif tentang Kota Batam.',
                'thumbnail' => 'news/batam-dalam-angka-2023.jpg',
                'has_video' => false,
                'video_url' => null
            ]
        ];

        // Videos
        $videos = [
            [
                'id' => 1,
                'title' => 'Rilis BRS Inflasi April 2023',
                'date' => '2 Mei 2023',
                'thumbnail' => 'videos/inflasi-april-2023.jpg',
                'url' => 'https://www.youtube.com/watch?v=example1'
            ],
            [
                'id' => 2,
                'title' => 'Rilis BRS Ekspor-Impor Maret 2023',
                'date' => '15 April 2023',
                'thumbnail' => 'videos/ekspor-impor-maret-2023.jpg',
                'url' => 'https://www.youtube.com/watch?v=example2'
            ],
            [
                'id' => 3,
                'title' => 'Rilis BRS Pertumbuhan Ekonomi Q1 2023',
                'date' => '5 April 2023',
                'thumbnail' => 'videos/pertumbuhan-ekonomi-q1-2023.jpg',
                'url' => 'https://www.youtube.com/watch?v=example3'
            ],
            [
                'id' => 4,
                'title' => 'Webinar Statistik Sektoral Kota Batam',
                'date' => '20 Maret 2023',
                'thumbnail' => 'videos/webinar-statistik-sektoral.jpg',
                'url' => 'https://www.youtube.com/watch?v=example4'
            ],
            [
                'id' => 5,
                'title' => 'Sosialisasi Sensus Penduduk Online 2023',
                'date' => '10 Maret 2023',
                'thumbnail' => 'videos/sosialisasi-sensus-penduduk.jpg',
                'url' => 'https://www.youtube.com/watch?v=example5'
            ]
        ];

        return view('news.index', compact('newsItems', 'videos'));
    }

    public function show($id)
    {
        // Example news item
        $newsItem = [
            'id' => $id,
            'title' => 'Rilis BRS Inflasi April 2023',
            'date' => '2 Mei 2023',
            'category' => 'BRS',
            'content' => '<p>Pada bulan April 2023, Kota Batam mengalami inflasi sebesar 0,15 persen dengan Indeks Harga Konsumen (IHK) sebesar 112,30. Inflasi ini terutama disebabkan oleh kenaikan harga pada kelompok makanan, minuman, dan tembakau sebesar 0,25 persen.</p>
                        <p>Berdasarkan data yang dirilis oleh Badan Pusat Statistik (BPS) Kota Batam, inflasi tahunan (year-on-year) Kota Batam pada April 2023 mencapai 2,35 persen, lebih rendah dibandingkan inflasi tahunan pada bulan Maret 2023 yang sebesar 2,42 persen.</p>
                        <p>Kepala BPS Kota Batam, dalam keterangan persnya, menyampaikan bahwa inflasi pada bulan April 2023 terutama disebabkan oleh kenaikan harga pada kelompok makanan, minuman, dan tembakau sebesar 0,25 persen, kelompok transportasi sebesar 0,18 persen, dan kelompok perawatan pribadi dan jasa lainnya sebesar 0,12 persen.</p>
                        <p>Sementara itu, kelompok pengeluaran yang mengalami deflasi adalah kelompok pakaian dan alas kaki sebesar 0,05 persen dan kelompok perlengkapan, peralatan, dan pemeliharaan rutin rumah tangga sebesar 0,03 persen.</p>
                        <p>Inflasi di Kota Batam pada bulan April 2023 lebih rendah dibandingkan inflasi nasional yang mencapai 0,33 persen. Hal ini menunjukkan bahwa pengendalian inflasi di Kota Batam berjalan dengan baik.</p>',
            'thumbnail' => 'news/inflasi-april-2023.jpg',
            'has_video' => true,
            'video_url' => 'https://www.youtube.com/watch?v=example1',
            'related_news' => [
                [
                    'id' => 2,
                    'title' => 'Rilis BRS Ekspor-Impor Maret 2023',
                    'date' => '15 April 2023'
                ],
                [
                    'id' => 3,
                    'title' => 'Rilis BRS Pertumbuhan Ekonomi Q1 2023',
                    'date' => '5 April 2023'
                ],
                [
                    'id' => 6,
                    'title' => 'Rilis Publikasi Kota Batam Dalam Angka 2023',
                    'date' => '1 Maret 2023'
                ]
            ]
        ];

        return view('news.show', compact('newsItem'));
    }
}
