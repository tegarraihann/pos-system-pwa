<?php

namespace App\Filament\Resources\MenuVariants\Pages;

use App\Filament\Resources\MenuVariants\MenuVariantResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewMenuVariant extends ViewRecord
{
    protected static string $resource = MenuVariantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
