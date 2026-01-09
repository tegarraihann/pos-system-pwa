<?php

namespace App\Filament\Resources\MenuVariants\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MenuVariantInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('menu.name')
                    ->label('Menu'),
                TextEntry::make('kd_varian')
                    ->label('Kode Varian'),
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
                TextEntry::make('price')
                    ->label('Harga'),
                TextEntry::make('stock')
                    ->label('Stok')
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
            ]);
    }
}
