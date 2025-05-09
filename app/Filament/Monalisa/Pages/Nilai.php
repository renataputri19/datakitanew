<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Domain;


class Nilai extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.nilai';

    protected static ?string $navigationGroup = 'Dashboard'; // Group under "Dashboard"
    protected static ?string $navigationLabel = 'Nilai EPSS'; // Set the navigation label
    protected static ?int $navigationSort = 1;
    
    // Correctly override the getTitle method
    public function getTitle(): string
    {
        return 'Dashboard Nilai EPSS';
    }


    public array $domains = [];
    public int $totalAspeks = 0;

    public function mount()
    {
        $domains = Domain::all()
            ->groupBy('domain')
            ->map(function ($groupedIndicators, $domain) {
                return [
                    'domain' => $domain,
                    'indicators' => $groupedIndicators->map(function ($indicator) {
                        return [
                            'name' => $indicator->indikator,
                            'value' => $indicator->disetujui == 1 && $indicator->tingkat_tpb !== null
                                ? $indicator->tingkat_tpb
                                : 1,
                        ];
                    })->toArray(),
                    // Count the unique aspects for the domain
                    'aspek_count' => $groupedIndicators->unique('aspek')->count(),
                ];
            })
            ->values()
            ->toArray();
    
        $this->domains = $domains;
    
        // Calculate the total number of aspects across all domains
        $this->totalAspeks = collect($domains)->sum('aspek_count');
    }
    



    public function getViewData(): array
    {
        return [
            'scores' => $this->calculateScores(),
        ];
    }


    protected $weights = [
        'Prinsip Satu Data Indonesia' => [
            'weight' => 28,
            'aspeks' => [
                'Standar Data Statistik' => 25,
                'Metadata Statistik' => 25,
                'Interoperabilitas Data' => 25,
                'Kode Referensi dan/atau Data Induk' => 25,
            ],
        ],
        'Kualitas Data' => [
            'weight' => 24,
            'aspeks' => [
                'Relevansi' => 21,
                'Akurasi' => 16,
                'Aktualitas dan Ketepatan Waktu' => 21,
                'Aksesibilitas' => 21,
                'Keterbandingan dan Konsistensi' => 21,
            ],
        ],
        'Proses Bisnis Statistik' => [
            'weight' => 19,
            'aspeks' => [
                'Perencanaan Data' => 32,
                'Pengumpulan Data' => 26,
                'Pemeriksaan Data' => 21,
                'Penyebarluasan Data' => 21,
            ],
        ],
        'Kelembagaan' => [
            'weight' => 17,
            'aspeks' => [
                'Profesionalitas' => 35,
                'SDM yang Memadai dan Kapabel' => 30,
                'Pengorganisasian Statistik' => 35,
            ],
        ],
        'Statistik Nasional' => [
            'weight' => 12,
            'aspeks' => [
                'Pemanfaatan Data Statistik' => 34,
                'Pengelolaan Kegiatan Statistik' => 33,
                'Penguatan SSN Berkelanjutan' => 33,
            ],
        ],
    ];
    





    public function calculateScores()
    {
        $domains = Domain::all()
            ->groupBy('domain')
            ->map(function ($groupedIndicators, $domain) {
                $domainWeight = $this->weights[$domain]['weight'] ?? 0;
    
                // Calculate aspek scores
                $aspekScores = collect($this->weights[$domain]['aspeks'] ?? [])
                    ->mapWithKeys(function ($aspekWeight, $aspek) use ($groupedIndicators) {
                        // Get all indicators for the current aspek
                        $aspekIndicators = $groupedIndicators->where('aspek', $aspek);
    
                        // Calculate the average score for the aspek based on its indicators
                        $aspekScore = $aspekIndicators->map(function ($indicator) {
                            return $indicator->disetujui == 1 && $indicator->tingkat_tpb !== null
                                ? $indicator->tingkat_tpb
                                : 1;
                        })->avg();
    
                        // Adjust the aspek score by its weight
                        $weightedAspekScore = round(($aspekScore * $aspekWeight) / 100, 2);
    
                        return [$aspek => $weightedAspekScore];
                    });
    
                // Calculate total domain score by summing all aspek scores and applying the domain weight
                $domainScore = round($aspekScores->sum() * ($domainWeight / 100), 2);
    
                return [
                    'domain' => $domain,
                    'aspeks' => $aspekScores,
                    'score' => $domainScore,
                ];
            });
    
        // Add total score of all domains
        $totalScore = $domains->sum('score');
    
        return [
            'domains' => $domains->values(),
            'totalScore' => round($totalScore, 2), // Rounded to two decimal places
        ];
    }
    
    
    
    

    

}
