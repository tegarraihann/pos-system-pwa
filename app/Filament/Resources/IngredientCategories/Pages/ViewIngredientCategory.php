<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewIngredientCategory extends ViewRecord
{
    protected static string $resource = IngredientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url(IngredientCategoryResource::getUrl('index'))
                ->color('gray'),
            EditAction::make(),
        ];
    }
}
