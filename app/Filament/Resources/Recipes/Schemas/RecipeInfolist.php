<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class RecipeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('menuVariant.menu.name')
                            ->label('Menu')
                            ->placeholder('-'),
                        TextEntry::make('menuVariant.kd_varian')
                            ->label('Kode Varian')
                            ->placeholder('-'),
                    ]),
                Section::make('Proses')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('prep_time_minutes')
                            ->label('Waktu Pembuatan (menit)')
                            ->formatStateUsing(fn (?int $state): string => filled($state) ? "{$state} menit" : '-')
                            ->placeholder('-'),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-'),
                    ]),
                Section::make('Waktu Data')
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
