<?php

namespace App\Filament\Resources\Recipes\Schemas;

use App\Models\Recipe;
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
                Section::make('Resep')
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
                            ->unique(Recipe::class, 'menu_variant_id', ignoreRecord: true),
                        TextInput::make('prep_time_minutes')
                            ->label('Waktu Pembuatan (menit)')
                            ->numeric()
                            ->minValue(0),
                        Textarea::make('notes')
                            ->label('Catatan')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
