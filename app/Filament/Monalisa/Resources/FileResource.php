<?php

namespace App\Filament\Monalisa\Resources;

use App\Filament\Monalisa\Resources\FileResource\Pages;
use App\Models\Monalisa\File;
use App\Models\Monalisa\Domain;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Storage;

class FileResource extends Resource
{
    protected static ?string $model = File::class;

    protected static ?string $navigationIcon = 'heroicon-o-document';

    protected static ?string $navigationLabel = 'Dokumen';

    protected static ?string $navigationGroup = 'Dokumen';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('domain_id')
                    ->label('Domain')
                    ->options(Domain::all()->pluck('indikator', 'id'))
                    ->searchable()
                    ->required(),
                Forms\Components\FileUpload::make('file_path')
                    ->label('File')
                    ->directory('files')
                    ->preserveFilenames()
                    ->required(),
                Forms\Components\TextInput::make('original_name')
                    ->label('Nama Asli File')
                    ->maxLength(255),
                Forms\Components\Toggle::make('hasil')
                    ->label('Hasil'),
                Forms\Components\Textarea::make('reasons')
                    ->label('Alasan')
                    ->maxLength(65535)
                    ->columnSpanFull(),
                Forms\Components\Select::make('context')
                    ->label('Konteks')
                    ->options([
                        'pembinaan' => 'Pembinaan',
                        'penilaian' => 'Penilaian',
                        'lainnya' => 'Lainnya',
                    ])
                    ->default('pembinaan')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain.indikator')
                    ->label('Indikator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('original_name')
                    ->label('Nama File')
                    ->searchable(),
                Tables\Columns\IconColumn::make('hasil')
                    ->label('Hasil')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('context')
                    ->label('Konteks')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pembinaan' => 'success',
                        'penilaian' => 'warning',
                        'lainnya' => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Tanggal Upload')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('context')
                    ->label('Konteks')
                    ->options([
                        'pembinaan' => 'Pembinaan',
                        'penilaian' => 'Penilaian',
                        'lainnya' => 'Lainnya',
                    ]),
                Tables\Filters\TernaryFilter::make('hasil')
                    ->label('Hasil'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('download')
                    ->label('Download')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->url(fn (File $record) => Storage::url($record->file_path))
                    ->openUrlInNewTab(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFiles::route('/'),
            'create' => Pages\CreateFile::route('/create'),
            'edit' => Pages\EditFile::route('/{record}/edit'),
        ];
    }
}
