<?php

namespace App\Filament\Resources\IngredientCategories\Pages;

use App\Filament\Resources\IngredientCategories\IngredientCategoryResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewIngredientCategory extends ViewRecord
{
    protected static string $resource = IngredientCategoryResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Detail Kategori Bahan';
    }

    public function getBreadcrumb(): string
    {
        return 'Detail';
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url(IngredientCategoryResource::getUrl('index'))
                ->color('gray'),
            EditAction::make()
                ->label('Ubah'),
        ];
    }
}
