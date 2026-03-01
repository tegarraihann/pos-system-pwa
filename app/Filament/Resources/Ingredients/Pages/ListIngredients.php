<?php

namespace App\Filament\Resources\Ingredients\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListIngredients extends ListRecords
{
    protected static string $resource = IngredientResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Bahan Baku';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Bahan Baku';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Bahan Baku'),
        ];
    }
}
