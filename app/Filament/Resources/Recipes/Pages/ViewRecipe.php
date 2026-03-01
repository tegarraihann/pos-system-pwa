<?php

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ViewRecipe extends ViewRecord
{
    protected static string $resource = RecipeResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    public function getTitle(): string | Htmlable
    {
        return 'Detail Resep';
    }

    public function getBreadcrumb(): string
    {
        return 'Detail';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah'),
        ];
    }
}
