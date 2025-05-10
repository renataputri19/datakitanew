<?php

namespace App\Http\Controllers;

use App\Models\System;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    /**
     * Display a listing of the systems.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        // Get all active systems ordered by order
        $systems = System::active()->ordered()->get();

        return view('systems.index', compact('systems'));
    }

    /**
     * Display the specified system.
     *
     * @param  string  $slug
     * @return \Illuminate\View\View
     */
    public function show($slug)
    {
        // Find the system by slug
        $system = System::where('slug', $slug)->firstOrFail();

        // Additional data for specific systems
        $systemDetail = [
            'id' => $system->id,
            'slug' => $system->slug,
            'name' => $system->name,
            'full_name' => $system->name,
            'description' => $system->description,
            'detail' => $system->detail,
            'icon' => $system->icon,
            'url' => $system->url,
            'category' => $system->category
        ];

        // Add features based on system slug
        if ($system->slug === 'monalisa') {
            $systemDetail['features'] = [
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
            ];

            $systemDetail['stats'] = [
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
            ];

            $systemDetail['progress'] = [
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
            ];

            $systemDetail['evaluation'] = [
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
            ];
        } elseif ($system->slug === 'romantik') {
            $systemDetail['features'] = [
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
            ];
        } else {
            // Default features for other systems
            $systemDetail['features'] = [
                [
                    'name' => 'Fitur 1',
                    'description' => 'Deskripsi fitur 1',
                    'icon' => 'bar-chart-3'
                ],
                [
                    'name' => 'Fitur 2',
                    'description' => 'Deskripsi fitur 2',
                    'icon' => 'pie-chart'
                ]
            ];
        }

        return view('systems.show', compact('systemDetail'));
    }
}
