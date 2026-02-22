<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditCustomer extends EditRecord
{
    protected static string $resource = CustomerResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
