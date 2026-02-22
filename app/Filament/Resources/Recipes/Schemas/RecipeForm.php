<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Models\Recipe;
use Filament\Forms\Components\Repeater;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class RecipeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Resep')
                    ->description('Tentukan varian menu dan estimasi waktu pembuatan.')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        Select::make('menu_variant_id')
                            ->label('Varian Menu')
                            ->relationship(
                                name: 'menuVariant',
                                titleAttribute: 'kd_varian',
                                modifyQueryUsing: static fn ($query) => $query->with('menu'),
                            )
                            ->getOptionLabelFromRecordUsing(static function ($record): string {
                                $menuName = $record->menu?->name ?? '-';

                                return "{$menuName} - {$record->kd_varian}";
                            })
                            ->searchable()
                            ->preload()
                            ->required()
                            ->unique(Recipe::class, 'menu_variant_id', ignoreRecord: true)
                            ->helperText('Pilih varian menu yang akan memiliki resep ini.')
                            ->columnSpanFull(),
                        TextInput::make('prep_time_minutes')
                            ->label('Waktu Pembuatan (menit)')
                            ->numeric()
                            ->minValue(0)
                            ->placeholder('Contoh: 15')
                            ->helperText('Isi estimasi waktu proses dalam menit.'),
                    ]),
                Section::make('Bahan Resep')
                    ->description('Tambahkan bahan dan takaran sekaligus saat membuat resep baru.')
                    ->columnSpanFull()
                    ->visible(static fn (string $operation): bool => $operation === 'create')
                    ->schema([
                        Repeater::make('items')
                            ->label('Daftar Bahan')
                            ->relationship()
                            ->minItems(1)
                            ->defaultItems(1)
                            ->addActionLabel('Tambah Bahan')
                            ->columns(2)
                            ->schema([
                                Select::make('ingredient_id')
                                    ->label('Bahan')
                                    ->relationship('ingredient', 'name')
                                    ->searchable()
                                    ->preload()
                                    ->required()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                                TextInput::make('quantity')
                                    ->label('Takaran')
                                    ->numeric()
                                    ->minValue(0.001)
                                    ->step(0.001)
                                    ->required()
                                    ->placeholder('Contoh: 10'),
                            ]),
                    ]),
                Section::make('Catatan')
                    ->description('Tambahan informasi khusus untuk tim produksi.')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
