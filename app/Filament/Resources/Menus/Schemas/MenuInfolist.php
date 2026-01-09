<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('Kode Menu/SKU'),
                TextEntry::make('name')
                    ->label('Nama Menu'),
                TextEntry::make('unit')
                    ->label('Satuan')
                    ->placeholder('-'),
                TextEntry::make('is_active')
                    ->label('Aktif')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
                TextEntry::make('is_stock_managed')
                    ->label('Kelola Stok')
                    ->formatStateUsing(fn (bool $state): string => $state ? 'Ya' : 'Tidak'),
                TextEntry::make('description')
                    ->label('Deskripsi')
                    ->placeholder('-'),
                ImageEntry::make('image_path')
                    ->label('Foto')
                    ->disk('public')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}
