<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateCustomer extends CreateRecord
{
    protected static string $resource = CustomerResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
