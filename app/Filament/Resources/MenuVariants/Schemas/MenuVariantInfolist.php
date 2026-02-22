<?php

namespace App\Filament\Resources\MenuVariants\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuVariantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('menu.name')
                            ->label('Menu')
                            ->placeholder('-'),
                        TextEntry::make('kd_varian')
                            ->label('Kode Varian'),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                    ]),

                Section::make('Karakteristik Varian')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('size_varian')
                            ->label('Size Varian')
                            ->placeholder('-'),
                        TextEntry::make('temperature')
                            ->label('Temperature')
                            ->placeholder('-'),
                        TextEntry::make('sugar_level')
                            ->label('Sugar Level')
                            ->placeholder('-'),
                        TextEntry::make('ice_level')
                            ->label('Ice Level')
                            ->placeholder('-'),
                    ]),

                Section::make('Harga & Stok')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('price')
                            ->label('Harga')
                            ->money('IDR', locale: 'id'),
                        TextEntry::make('stock')
                            ->label('Stok')
                            ->placeholder('-'),
                        TextEntry::make('reminder_stock')
                            ->label('Reminder Stok Minimum')
                            ->placeholder('-'),
                    ]),

                Section::make('Waktu')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Dibuat pada')
                            ->dateTime()
                            ->placeholder('-'),
                        TextEntry::make('updated_at')
                            ->label('Diperbarui pada')
                            ->dateTime()
                            ->placeholder('-'),
                    ]),
            ]);
    }
}
