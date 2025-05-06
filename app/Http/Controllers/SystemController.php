<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function index()
    {
        // Systems
        $systems = [
            [
                'id' => 1,
                'slug' => 'monalisa',
                'name' => 'MONALISA',
                'description' => 'Monitoring dan Evaluasi Statistik Sektoral',
                'detail' => 'Sistem untuk monitoring dan evaluasi statistik sektoral, memungkinkan dinas dan instansi untuk mengunggah dokumen, mengevaluasi indikator berdasarkan standar EPSS, dan memantau kemajuan secara real-time.',
                'icon' => 'bar-chart-3',
                'url' => 'https://monalisa.bpsbatam.com',
                'category' => 'monitoring'
            ],
            [
                'id' => 2,
                'slug' => 'romantik',
                'name' => 'Romantik',
                'description' => 'Sistem Romantik',
                'detail' => 'Sistem Romantik adalah platform untuk pengelolaan dan analisis data statistik yang memungkinkan pengguna untuk mengakses dan mengolah data dengan lebih efisien.',
                'icon' => 'line-chart',
                'url' => 'https://romantik.bpsbatam.com',
                'category' => 'other'
            ],
            [
                'id' => 3,
                'slug' => 'simbatik',
                'name' => 'Simbatik',
                'description' => 'Sistem Simbatik',
                'detail' => 'Sistem Simbatik adalah platform untuk pengelolaan dan visualisasi data statistik yang memungkinkan pengguna untuk membuat visualisasi data yang interaktif dan informatif.',
                'icon' => 'bar-chart-3',
                'url' => 'https://simbatik.bpsbatam.com',
                'category' => 'other'
            ],
            [
                'id' => 4,
                'slug' => 'simdasi',
                'name' => 'SIMDASI',
                'description' => 'Sistem Informasi Manajemen Data Statistik',
                'detail' => 'SIMDASI adalah sistem informasi manajemen data statistik yang memungkinkan pengguna untuk mengelola, menganalisis, dan memvisualisasikan data statistik dengan mudah dan efisien.',
                'icon' => 'database',
                'url' => 'https://simdasi.bpsbatam.com',
                'category' => 'data'
            ],
            [
                'id' => 5,
                'slug' => 'sidasi',
                'name' => 'SIDASI',
                'description' => 'Sistem Diseminasi Data Statistik',
                'detail' => 'SIDASI adalah sistem diseminasi data statistik yang memungkinkan pengguna untuk mengakses dan mengunduh data statistik dengan mudah dan cepat.',
                'icon' => 'share',
                'url' => 'https://sidasi.bpsbatam.com',
                'category' => 'data'
            ],
            [
                'id' => 6,
                'slug' => 'simonitoring',
                'name' => 'SIMonitoring',
                'description' => 'Sistem Informasi Monitoring',
                'detail' => 'SIMonitoring adalah sistem informasi monitoring yang memungkinkan pengguna untuk memantau kemajuan dan kinerja program dan kegiatan statistik.',
                'icon' => 'activity',
                'url' => 'https://simonitoring.bpsbatam.com',
                'category' => 'monitoring'
            ]
        ];

        return view('systems.index', compact('systems'));
    }

    public function show($system)
    {
        // System details
        $systemDetails = [
            'monalisa' => [
                'id' => 1,
                'slug' => 'monalisa',
                'name' => 'MONALISA',
                'full_name' => 'Monitoring dan Evaluasi Statistik Sektoral',
                'description' => 'Sistem untuk monitoring dan evaluasi statistik sektoral, memungkinkan dinas dan instansi untuk mengunggah dokumen, mengevaluasi indikator berdasarkan standar EPSS, dan memantau kemajuan secara real-time.',
                'icon' => 'bar-chart-3',
                'url' => 'https://monalisa.bpsbatam.com',
                'category' => 'monitoring',
                'features' => [
                    [
                        'name' => 'Unggah Dokumen',
                        'description' => 'Unggah dokumen statistik sektoral untuk evaluasi',
                        'icon' => 'upload'
                    ],
                    [
                        'name' => 'Evaluasi Indikator',
                        'description' => 'Evaluasi indikator berdasarkan standar EPSS',
                        'icon' => 'bar-chart-3'
                    ],
                    [
                        'name' => 'Laporan Real-time',
                        'description' => 'Pantau kemajuan implementasi statistik sektoral',
                        'icon' => 'file-text'
                    ],
                    [
                        'name' => 'Kolaborasi',
                        'description' => 'Berkolaborasi dengan BPS Kota Batam',
                        'icon' => 'users'
                    ]
                ],
                'stats' => [
                    [
                        'name' => 'Total OPD',
                        'value' => '24',
                        'description' => 'Organisasi Perangkat Daerah'
                    ],
                    [
                        'name' => 'Dokumen Terunggah',
                        'value' => '156',
                        'description' => '+12 dari bulan lalu'
                    ],
                    [
                        'name' => 'Indikator Terevaluasi',
                        'value' => '432',
                        'description' => '+28 dari bulan lalu'
                    ],
                    [
                        'name' => 'Skor Rata-rata EPSS',
                        'value' => '78.5',
                        'description' => '+2.3 dari bulan lalu'
                    ]
                ],
                'progress' => [
                    [
                        'name' => 'Dinas Kesehatan',
                        'value' => 85
                    ],
                    [
                        'name' => 'Dinas Pendidikan',
                        'value' => 78
                    ],
                    [
                        'name' => 'Dinas Perindustrian dan Perdagangan',
                        'value' => 92
                    ],
                    [
                        'name' => 'Dinas Tenaga Kerja',
                        'value' => 65
                    ],
                    [
                        'name' => 'Dinas Lingkungan Hidup',
                        'value' => 73
                    ]
                ],
                'documents' => [
                    [
                        'name' => 'Laporan Kesehatan Masyarakat 2023',
                        'organization' => 'Dinas Kesehatan'
                    ],
                    [
                        'name' => 'Data Pendidikan Kota Batam 2023',
                        'organization' => 'Dinas Pendidikan'
                    ],
                    [
                        'name' => 'Statistik Industri Kota Batam 2023',
                        'organization' => 'Dinas Perindustrian dan Perdagangan'
                    ],
                    [
                        'name' => 'Laporan Ketenagakerjaan 2023',
                        'organization' => 'Dinas Tenaga Kerja'
                    ],
                    [
                        'name' => 'Data Lingkungan Hidup 2023',
                        'organization' => 'Dinas Lingkungan Hidup'
                    ]
                ],
                'evaluation' => [
                    [
                        'name' => 'Metadata',
                        'value' => 82,
                        'description' => 'Kelengkapan metadata statistik sektoral: 82%'
                    ],
                    [
                        'name' => 'Kualitas Data',
                        'value' => 75,
                        'description' => 'Kualitas data statistik sektoral: 75%'
                    ],
                    [
                        'name' => 'Aksesibilitas',
                        'value' => 68,
                        'description' => 'Aksesibilitas data statistik sektoral: 68%'
                    ],
                    [
                        'name' => 'Ketepatan Waktu',
                        'value' => 90,
                        'description' => 'Ketepatan waktu publikasi data: 90%'
                    ],
                    [
                        'name' => 'Kepatuhan Standar',
                        'value' => 78,
                        'description' => 'Kepatuhan terhadap standar statistik: 78%'
                    ]
                ]
            ],
            'romantik' => [
                'id' => 2,
                'slug' => 'romantik',
                'name' => 'Romantik',
                'full_name' => 'Sistem Romantik',
                'description' => 'Sistem Romantik adalah platform untuk pengelolaan dan analisis data statistik yang memungkinkan pengguna untuk mengakses dan mengolah data dengan lebih efisien.',
                'icon' => 'line-chart',
                'url' => 'https://romantik.bpsbatam.com',
                'category' => 'other',
                'features' => [
                    [
                        'name' => 'Analisis Data',
                        'description' => 'Analisis data statistik dengan berbagai metode',
                        'icon' => 'bar-chart-3'
                    ],
                    [
                        'name' => 'Visualisasi Data',
                        'description' => 'Visualisasi data dengan berbagai jenis grafik',
                        'icon' => 'pie-chart'
                    ],
                    [
                        'name' => 'Ekspor Data',
                        'description' => 'Ekspor data dalam berbagai format',
                        'icon' => 'download'
                    ],
                    [
                        'name' => 'Integrasi Data',
                        'description' => 'Integrasi data dari berbagai sumber',
                        'icon' => 'link'
                    ]
                ]
            ]
        ];

        if (!isset($systemDetails[$system])) {
            abort(404);
        }

        $systemDetail = $systemDetails[$system];

        return view('systems.show', compact('systemDetail'));
    }
}
