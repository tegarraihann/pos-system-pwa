<?php

namespace App\Filament\Resources\Menus\Schemas;

use Filament\Infolists\Components\ImageEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode Menu/SKU'),
                        TextEntry::make('name')
                            ->label('Nama Menu'),
                        TextEntry::make('category')
                            ->label('Kategori')
                            ->placeholder('-'),
                        TextEntry::make('unit')
                            ->label('Satuan')
                            ->placeholder('-'),
                    ]),

                Section::make('Status')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('is_active')
                            ->label('Status Menu')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'danger'),
                        TextEntry::make('is_stock_managed')
                            ->label('Kelola Stok')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Tidak Aktif')
                            ->color(fn (bool $state): string => $state ? 'warning' : 'gray'),
                    ]),

                Section::make('Media & Deskripsi')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        ImageEntry::make('image_path')
                            ->label('Foto')
                            ->disk('public')
                            ->placeholder('-')
                            ->columnSpanFull(),
                        TextEntry::make('description')
                            ->label('Deskripsi')
                            ->placeholder('-')
                            ->columnSpanFull(),
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
