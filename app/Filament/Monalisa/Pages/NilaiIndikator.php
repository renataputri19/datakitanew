<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Domain;

class NilaiIndikator extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.nilai-indikator';

    protected static ?string $navigationGroup = 'Dashboard'; // Group under "Dashboard"
    protected static ?string $navigationLabel = 'Nilai Indikator'; // Set the navigation label
    protected static ?int $navigationSort = 2;

    // Correctly override the getTitle method
    public function getTitle(): string
    {
        return 'Dashboard Nilai Indikator';
    }


    public array $domains = [];
    public int $totalAspeks = 0;

    public function mount()
    {
        $this->domains = Domain::all()
            ->groupBy('domain')
            ->map(function ($groupedIndicators, $domain) {
                return [
                    'domain' => $domain,
                    'indicators' => $groupedIndicators->map(function ($indicator) {
                        return [
                            'name' => $indicator->indikator,
                            'value' => $indicator->disetujui == 1 && $indicator->tingkat_tpb !== null ? $indicator->tingkat_tpb : 1,
                        ];
                    })->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }
    
}
