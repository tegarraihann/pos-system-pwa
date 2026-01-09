<?php

namespace App\Filament\Resources\IngredientCategories\Schemas;

use Filament\Schemas\Schema;
use App\Models\IngredientCategory;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class IngredientCategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Kategori Bahan Baku')
                    ->columnSpanFull()
                    ->schema([
                        TextInput::make('name')
                            ->label('Nama Kategori')
                            ->required()
                            ->maxLength(255)
                            ->unique(IngredientCategory::class, 'name', ignoreRecord: true),
                    ]),
            ]);
    }
}
