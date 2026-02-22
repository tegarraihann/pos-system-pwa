<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateSupplier extends CreateRecord
{
    protected static string $resource = SupplierResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
