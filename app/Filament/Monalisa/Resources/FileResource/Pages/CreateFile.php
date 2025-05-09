<?php

namespace App\Filament\Monalisa\Resources\FileResource\Pages;

use App\Filament\Monalisa\Resources\FileResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateFile extends CreateRecord
{
    protected static string $resource = FileResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set the user_id to the current user
        $data['user_id'] = auth()->id();
        
        // If original_name is not provided, use the uploaded file name
        if (empty($data['original_name']) && !empty($data['file_path'])) {
            $data['original_name'] = $data['file_path'];
        }
        
        return $data;
    }
}
