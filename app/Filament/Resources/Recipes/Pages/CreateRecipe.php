<?php

namespace App\Filament\Resources\Recipes\Pages;

use App\Filament\Resources\Recipes\RecipeResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateRecipe extends CreateRecord
{
    protected static string $resource = RecipeResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
