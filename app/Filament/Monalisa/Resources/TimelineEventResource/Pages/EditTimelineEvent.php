<?php

namespace App\Filament\Monalisa\Resources\TimelineEventResource\Pages;

use App\Filament\Monalisa\Resources\TimelineEventResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTimelineEvent extends EditRecord
{
    protected static string $resource = TimelineEventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
