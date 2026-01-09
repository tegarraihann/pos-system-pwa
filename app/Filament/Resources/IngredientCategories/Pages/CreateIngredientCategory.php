<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateIngredientCategory extends CreateRecord
{
    protected static string $resource = IngredientCategoryResource::class;

    protected function afterCreate(): void
    {
        Notification::make()
            ->title('Kategori bahan baku berhasil dibuat.')
            ->success()
            ->send();
    }
}
