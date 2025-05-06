<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Featured news items
        $featuredNews = [
            [
                'id' => 1,
                'title' => 'Inflasi Kota Batam April 2023',
                'date' => '2 Mei 2023',
                'excerpt' => 'Pada bulan April 2023, Kota Batam mengalami inflasi sebesar 0,15 persen dengan Indeks Harga Konsumen (IHK) sebesar 112,30.'
            ],
            [
                'id' => 2,
                'title' => 'Ekspor-Impor Kota Batam Maret 2023',
                'date' => '15 April 2023',
                'excerpt' => 'Nilai ekspor Kota Batam pada Maret 2023 mencapai US$123,45 juta, naik 5,67 persen dibandingkan Februari 2023.'
            ],
            [
                'id' => 3,
                'title' => 'Pertumbuhan Ekonomi Kota Batam Q1 2023',
                'date' => '5 April 2023',
                'excerpt' => 'Perekonomian Kota Batam pada Triwulan I-2023 tumbuh sebesar 4,56 persen (y-on-y) dibandingkan Triwulan I-2022.'
            ]
        ];

        // Featured videos
        $featuredVideos = [
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
            ]
        ];

        return view('home', compact('featuredNews', 'featuredVideos'));
    }

    public function about()
    {
        return view('about');
    }
}
