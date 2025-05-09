<?php

namespace App\Filament\Monalisa\Resources;

use App\Filament\Monalisa\Resources\DomainResource\Pages;
use App\Models\Monalisa\Domain;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DomainResource extends Resource
{
    protected static ?string $model = Domain::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Domain & Indikator';

    protected static ?string $navigationLabel = 'Domain & Indikator';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('domain')
                    ->required()
                    ->maxLength(255)
                    ->label('Domain'),
                Forms\Components\TextInput::make('aspek')
                    ->required()
                    ->maxLength(255)
                    ->label('Aspek'),
                Forms\Components\TextInput::make('indikator')
                    ->required()
                    ->maxLength(255)
                    ->label('Indikator'),
                Forms\Components\Select::make('tingkat')
                    ->options([
                        1 => 'Tingkat 1',
                        2 => 'Tingkat 2',
                        3 => 'Tingkat 3',
                        4 => 'Tingkat 4',
                        5 => 'Tingkat 5',
                    ])
                    ->label('Tingkat'),
                Forms\Components\Select::make('tingkat_tpb')
                    ->options([
                        1 => 'Tingkat 1',
                        2 => 'Tingkat 2',
                        3 => 'Tingkat 3',
                        4 => 'Tingkat 4',
                        5 => 'Tingkat 5',
                    ])
                    ->label('Tingkat TPB'),
                Forms\Components\Toggle::make('disetujui')
                    ->label('Disetujui'),
                Forms\Components\Textarea::make('reasons')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->label('Alasan'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('domain')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('aspek')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('indikator')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tingkat')
                    ->sortable(),
                Tables\Columns\IconColumn::make('disetujui')
                    ->boolean()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
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
            'index' => Pages\ListDomains::route('/'),
            'create' => Pages\CreateDomain::route('/create'),
            'edit' => Pages\EditDomain::route('/{record}/edit'),
        ];
    }
}
