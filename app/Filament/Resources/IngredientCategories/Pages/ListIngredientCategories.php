<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ListIngredientCategories extends ListRecords
{
    protected static string $resource = IngredientCategoryResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Kategori Bahan';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Kategori Bahan';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Kategori')
                ->modalHeading('Tambah Kategori Bahan')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->modalWidth(Width::ExtraLarge)
                ->successNotificationTitle('Kategori bahan baku berhasil dibuat.')
                ->createAnother(false),
        ];
    }

    public function getDefaultActionUrl(Action $action): ?string
    {
        if ($action->getName() === 'create') {
            return null;
        }

        return parent::getDefaultActionUrl($action);
    }
}
