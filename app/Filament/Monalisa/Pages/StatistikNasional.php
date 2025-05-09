<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class StatistikNasional extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Pembinaan Statistik Sektoral';
    protected static ?string $navigationLabel = 'Statistik Nasional';
    protected static ?int $navigationSort = 5;

    // Correctly override the getTitle method
    public function getTitle(): string
    {
        return 'Domain - Statistik Nasional';
    }

    protected static string $view = 'filament.pages.statistik-nasional';
}
