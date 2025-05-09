<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\TimelineEvent;

class TimelineKegiatan extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.timeline-kegiatan';

    protected static ?string $navigationGroup = 'Dashboard'; // Group under "Dashboard"
    protected static ?string $navigationLabel = 'Timeline Kegiatan'; // Set the navigation label
    protected static ?int $navigationSort = 3;

    // Correctly override the getTitle method
    public function getTitle(): string
    {
        return 'Timeline Kegiatan EPSS 2025';
    }

    public $events;

    public function mount()
    {
        $this->events = \App\Models\TimelineEvent::all()
            ->groupBy('category')
            ->map(function ($tasks, $category) {
                return [
                    'category' => $category,
                    'tasks' => $tasks,
                ];
            })
            ->values()
            ->toArray();
    }
    
}
