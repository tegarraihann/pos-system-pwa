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
                Section::make('Bahan Baku')
                    ->columns(2)
                    ->schema([
                        TextInput::make('code')
                            ->label('Kode Bahan')
                            ->required()
                            ->maxLength(50)
                            ->unique(Ingredient::class, 'code', ignoreRecord: true),
                        TextInput::make('name')
                            ->label('Nama Bahan')
                            ->required()
                            ->maxLength(255),
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
                        TextInput::make('unit')
                            ->label('Satuan')
                            ->required()
                            ->maxLength(50),
                        TextInput::make('purchase_price')
                            ->label('Harga Beli')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
