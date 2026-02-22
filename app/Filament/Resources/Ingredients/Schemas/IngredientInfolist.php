<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Infolists\Components\TextEntry;

class IngredientInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Bahan Baku')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('code')
                            ->label('Kode Bahan'),
                        TextEntry::make('name')
                            ->label('Nama Bahan'),
                        TextEntry::make('category.name')
                            ->label('Kategori'),
                        TextEntry::make('supplier.name')
                            ->label('Supplier')
                            ->placeholder('-'),
                        TextEntry::make('unit')
                            ->label('Satuan'),
                        TextEntry::make('purchase_price')
                            ->label('Harga Beli')
                            ->placeholder('-'),
                        TextEntry::make('reminder_stock')
                            ->label('Reminder Stok Minimum')
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
