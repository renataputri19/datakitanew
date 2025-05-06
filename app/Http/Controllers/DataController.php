<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DataController extends Controller
{
    public function index()
    {
        $categories = [
            [
                'slug' => 'ekonomi',
                'name' => 'Ekonomi',
                'icon' => 'bar-chart-3',
                'description' => 'Inflasi, kemiskinan, dan indikator ekonomi lainnya',
                'detail' => 'Data ekonomi Kota Batam, termasuk inflasi, PDRB, kemiskinan, ketenagakerjaan, dan indikator ekonomi lainnya.'
            ],
            [
                'slug' => 'sosial',
                'name' => 'Sosial',
                'icon' => 'users',
                'description' => 'IPM, pendidikan, dan indikator sosial lainnya',
                'detail' => 'Data sosial Kota Batam, termasuk Indeks Pembangunan Manusia (IPM), pendidikan, kesehatan, dan indikator sosial lainnya.'
            ],
            [
                'slug' => 'demografi',
                'name' => 'Demografi',
                'icon' => 'users',
                'description' => 'Populasi, migrasi, dan data kependudukan lainnya',
                'detail' => 'Data demografi Kota Batam, termasuk jumlah penduduk, kepadatan penduduk, migrasi, dan data kependudukan lainnya.'
            ],
            [
                'slug' => 'lingkungan',
                'name' => 'Lingkungan',
                'icon' => 'pie-chart',
                'description' => 'Iklim, biodiversitas, dan data lingkungan lainnya',
                'detail' => 'Data lingkungan Kota Batam, termasuk iklim, biodiversitas, kualitas udara, dan indikator lingkungan lainnya.'
            ],
            [
                'slug' => 'publikasi',
                'name' => 'Publikasi',
                'icon' => 'book-open',
                'description' => 'Buku digital, laporan, dan publikasi lainnya',
                'detail' => 'Publikasi BPS Kota Batam, termasuk buku digital, laporan statistik, dan publikasi resmi lainnya.'
            ],
            [
                'slug' => 'tabel-dinamis',
                'name' => 'Tabel Dinamis',
                'icon' => 'database',
                'description' => 'Tabel statistik interaktif yang dapat disesuaikan',
                'detail' => 'Tabel statistik interaktif yang dapat disesuaikan sesuai kebutuhan pengguna untuk analisis data yang lebih mendalam.'
            ]
        ];

        $popularData = [
            [
                'id' => 1,
                'title' => 'Inflasi Kota Batam 2023',
                'category' => 'Ekonomi',
                'description' => 'Data inflasi Kota Batam tahun 2023 berdasarkan kelompok pengeluaran.'
            ],
            [
                'id' => 2,
                'title' => 'IPM Kota Batam 2022',
                'category' => 'Sosial',
                'description' => 'Indeks Pembangunan Manusia Kota Batam tahun 2022 dan komponennya.'
            ],
            [
                'id' => 3,
                'title' => 'Jumlah Penduduk Kota Batam 2022',
                'category' => 'Demografi',
                'description' => 'Data jumlah penduduk Kota Batam tahun 2022 berdasarkan kecamatan.'
            ]
        ];

        $recentData = [
            [
                'id' => 4,
                'title' => 'PDRB Kota Batam Q1 2023',
                'category' => 'Ekonomi',
                'description' => 'Produk Domestik Regional Bruto Kota Batam Triwulan I 2023.'
            ],
            [
                'id' => 5,
                'title' => 'Ekspor-Impor Kota Batam Maret 2023',
                'category' => 'Ekonomi',
                'description' => 'Data ekspor dan impor Kota Batam bulan Maret 2023.'
            ],
            [
                'id' => 6,
                'title' => 'Tingkat Pengangguran Terbuka 2023',
                'category' => 'Sosial',
                'description' => 'Tingkat Pengangguran Terbuka Kota Batam tahun 2023.'
            ]
        ];

        return view('data.index', compact('categories', 'popularData', 'recentData'));
    }

    public function category($category)
    {
        // Example data for the Ekonomi category
        $ekonomiData = [
            [
                'id' => 1,
                'title' => 'Inflasi Kota Batam 2023',
                'updated' => 'April 2023',
                'description' => 'Data inflasi Kota Batam tahun 2023 berdasarkan kelompok pengeluaran.'
            ],
            [
                'id' => 2,
                'title' => 'PDRB Kota Batam Q1 2023',
                'updated' => 'April 2023',
                'description' => 'Produk Domestik Regional Bruto Kota Batam Triwulan I 2023.'
            ],
            [
                'id' => 3,
                'title' => 'Kemiskinan Kota Batam 2022',
                'updated' => 'Desember 2022',
                'description' => 'Data kemiskinan Kota Batam tahun 2022, termasuk garis kemiskinan dan jumlah penduduk miskin.'
            ],
            [
                'id' => 4,
                'title' => 'Ketenagakerjaan Kota Batam 2022',
                'updated' => 'Desember 2022',
                'description' => 'Data ketenagakerjaan Kota Batam tahun 2022, termasuk tingkat pengangguran terbuka dan tingkat partisipasi angkatan kerja.'
            ],
            [
                'id' => 5,
                'title' => 'Ekspor-Impor Kota Batam 2023',
                'updated' => 'Maret 2023',
                'description' => 'Data ekspor dan impor Kota Batam tahun 2023, termasuk nilai dan volume ekspor-impor.'
            ],
            [
                'id' => 6,
                'title' => 'Indeks Harga Konsumen 2023',
                'updated' => 'April 2023',
                'description' => 'Indeks Harga Konsumen Kota Batam tahun 2023 berdasarkan kelompok pengeluaran.'
            ]
        ];

        $categoryData = [];
        $categoryName = '';
        $categoryDescription = '';

        if ($category === 'ekonomi') {
            $categoryData = $ekonomiData;
            $categoryName = 'Ekonomi';
            $categoryDescription = 'Data ekonomi Kota Batam, termasuk inflasi, PDRB, kemiskinan, ketenagakerjaan, dan indikator ekonomi lainnya';
        }

        return view('data.category', compact('categoryData', 'categoryName', 'categoryDescription', 'category'));
    }

    public function show($category, $id)
    {
        // Example data for a specific dataset
        $dataset = [
            'id' => $id,
            'title' => 'Inflasi Kota Batam 2023',
            'category' => 'Ekonomi',
            'updated' => 'April 2023',
            'description' => 'Data inflasi Kota Batam tahun 2023 berdasarkan kelompok pengeluaran.',
            'content' => 'Pada bulan April 2023, Kota Batam mengalami inflasi sebesar 0,15 persen dengan Indeks Harga Konsumen (IHK) sebesar 112,30. Inflasi ini terutama disebabkan oleh kenaikan harga pada kelompok makanan, minuman, dan tembakau sebesar 0,25 persen.',
            'charts' => [
                [
                    'type' => 'bar',
                    'title' => 'Inflasi Bulanan Kota Batam 2023',
                    'data' => [
                        'labels' => ['Jan', 'Feb', 'Mar', 'Apr'],
                        'values' => [0.32, 0.18, 0.22, 0.15]
                    ]
                ],
                [
                    'type' => 'line',
                    'title' => 'Tren Inflasi Tahunan Kota Batam 2019-2023',
                    'data' => [
                        'labels' => ['2019', '2020', '2021', '2022', '2023'],
                        'values' => [2.65, 1.45, 1.78, 2.12, 2.35]
                    ]
                ]
            ]
        ];

        return view('data.show', compact('dataset', 'category'));
    }
}
