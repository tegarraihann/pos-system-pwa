<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditIngredientCategory extends EditRecord
{
    protected static string $resource = IngredientCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                ->after(function (): void {
                    Notification::make()
                        ->title('Kategori bahan baku berhasil dihapus.')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function afterSave(): void
    {
        Notification::make()
            ->title('Kategori bahan baku berhasil diperbarui.')
            ->success()
            ->send();
    }

    protected function getRedirectUrl(): string
    {
        return IngredientCategoryResource::getUrl('view', ['record' => $this->record]);
    }
}
