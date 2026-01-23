<?php

namespace App\Filament\Resources\StockLocations\Schemas;

use App\Models\StockLocation;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockLocationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lokasi Stok')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Lokasi')
                            ->required()
                            ->maxLength(50)
                            ->unique(StockLocation::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Lokasi')
                            ->required()
                            ->maxLength(255),
                        TextInput::make('type')
                            ->label('Tipe Lokasi')
                            ->maxLength(50)
                            ->placeholder('Gudang/Dapur/Outlet'),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
