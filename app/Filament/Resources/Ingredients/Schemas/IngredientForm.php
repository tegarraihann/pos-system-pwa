<?php

namespace App\Filament\Resources\Ingredients\Schemas;

use App\Models\Ingredient;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class IngredientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Data inti bahan baku untuk kebutuhan resep dan stok.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Bahan')
                            ->required()
                            ->maxLength(50)
                            ->unique(Ingredient::class, 'code', ignoreRecord: true)
                            ->placeholder('Contoh: GULA-001'),
                        TextInput::make('name')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: Gula Pasir'),
                        Select::make('ingredient_category_id')
                            ->label('Kategori Bahan')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('supplier_id')
                            ->label('Supplier')
                            ->relationship('supplier', 'name')
                            ->searchable()
                            ->preload()
                            ->nullable(),
                    ]),
                Section::make('Satuan & Harga')
                    ->description('Informasi satuan dan harga beli bahan.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50)
                            ->placeholder('Contoh: Kg / Liter'),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Contoh: 25000'),
                    ]),
                Section::make('Reminder & Status')
                    ->description('Pengingat stok minimum dan status aktif bahan.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextInput::make('reminder_stock')
                            ->label('Reminder Stok Minimum')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->placeholder('Contoh: 10')
                            ->helperText('Kosongkan jika bahan ini tidak perlu reminder stok.')
                            ->columnSpan(1),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false)
                            ->columnSpan(1),
                    ]),
            ]);
    }
}
