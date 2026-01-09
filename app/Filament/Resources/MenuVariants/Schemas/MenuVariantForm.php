<?php

namespace App\Filament\Resources\MenuVariants\Schemas;

use App\Models\MenuVariant;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class MenuVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Varian')
                    ->columns(2)
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->relationship('menu', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('kd_varian')
                            ->label('Kode Varian')
                            ->required()
                            ->maxLength(50)
                            ->unique(MenuVariant::class, 'kd_varian', ignoreRecord: true),
                        TextInput::make('size_varian')
                            ->label('Size Varian')
                            ->maxLength(50),
                        TextInput::make('temperature')
                            ->label('Temperature')
                            ->maxLength(50),
                        TextInput::make('sugar_level')
                            ->label('Sugar Level')
                            ->maxLength(50),
                        TextInput::make('ice_level')
                            ->label('Ice Level')
                            ->maxLength(50),
                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('stock')
                            ->label('Stok')
                            ->numeric()
                            ->minValue(0),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),
                    ]),
            ]);
    }
}
