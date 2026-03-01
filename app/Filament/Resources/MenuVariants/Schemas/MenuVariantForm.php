<?php

namespace App\Filament\Resources\MenuVariants\Schemas;

use App\Models\MenuVariant;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MenuVariantForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Utama')
                    ->description('Data utama untuk identitas varian menu.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('menu_id')
                            ->label('Menu')
                            ->relationship('menu', 'name')
                            ->placeholder('Pilih menu')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('kd_varian')
                            ->label('Kode Varian')
                            ->required()
                            ->maxLength(50)
                            ->unique(MenuVariant::class, 'kd_varian', ignoreRecord: true)
                            ->placeholder('Contoh: ICBL-LG'),
                        TextInput::make('size_varian')
                            ->label('Ukuran Varian')
                            ->maxLength(50)
                            ->placeholder('Contoh: Besar'),
                        TextInput::make('price')
                            ->label('Harga')
                            ->prefix('Rp')
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true)
                            ->inline(false),
                    ]),

                Section::make('Atribut Varian')
                    ->description('Atribut tambahan sesuai kebutuhan menu.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('temperature')
                            ->label('Suhu')
                            ->maxLength(50)
                            ->placeholder('Contoh: Panas / Dingin'),
                        TextInput::make('sugar_level')
                            ->label('Level Gula')
                            ->maxLength(50)
                            ->placeholder('Contoh: Normal / Sedikit'),
                        TextInput::make('ice_level')
                            ->label('Level Es')
                            ->maxLength(50)
                            ->placeholder('Contoh: Normal / Sedikit'),
                    ]),

                Section::make('Stok & Reminder')
                    ->description('Pengingat stok berdasarkan batas minimum yang Anda tetapkan.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        TextInput::make('stock')
                            ->label('Stok (Total)')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false)
                            ->helperText('Dihitung otomatis dari pergerakan stok.')
                            ->columnSpanFull(),
                        TextInput::make('reminder_stock')
                            ->label('Reminder Stok Minimum')
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->placeholder('Contoh: 5')
                            ->helperText('Kosongkan jika varian tidak perlu reminder.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
