<?php

namespace App\Filament\Resources\Ingredients\Pages;

use App\Filament\Resources\Ingredients\IngredientResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateIngredient extends CreateRecord
{
    protected static string $resource = IngredientResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
