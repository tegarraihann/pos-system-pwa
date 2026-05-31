<?php

namespace App\Filament\Resources\ChartOfAccounts\Pages;

use App\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateChartOfAccount extends CreateRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
