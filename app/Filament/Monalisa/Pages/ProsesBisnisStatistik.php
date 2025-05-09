<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class ProsesBisnisStatistik extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Pembinaan Statistik Sektoral';
    protected static ?string $navigationLabel = 'Proses Bisnis Statistik';
    protected static ?int $navigationSort = 3;

    // Correctly override the getTitle method
    public function getTitle(): string
    {
        return 'Domain - Proses Bisnis Statistik';
    }

    protected static string $view = 'filament.pages.proses-bisnis-statistik';
}
