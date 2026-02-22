<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\CustomerResource;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
