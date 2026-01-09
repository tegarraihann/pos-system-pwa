<?php

namespace App\Filament\Resources\Recipes\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RecipeInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('menuVariant.menu.name')
                    ->label('Menu'),
                TextEntry::make('menuVariant.kd_varian')
                    ->label('Kode Varian'),
                TextEntry::make('prep_time_minutes')
                    ->label('Waktu Pembuatan (menit)')
                    ->placeholder('-'),
                TextEntry::make('notes')
                    ->label('Catatan')
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
