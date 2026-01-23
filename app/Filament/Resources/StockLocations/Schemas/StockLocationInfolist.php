<?php

namespace App\Filament\Resources\StockLocations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StockLocationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Lokasi Stok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode Lokasi'),
                        TextEntry::make('name')
                            ->label('Nama Lokasi'),
                        TextEntry::make('type')
                            ->label('Tipe Lokasi')
                            ->placeholder('-'),
                        TextEntry::make('is_active')
                            ->label('Aktif')
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
                        TextEntry::make('created_at')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
