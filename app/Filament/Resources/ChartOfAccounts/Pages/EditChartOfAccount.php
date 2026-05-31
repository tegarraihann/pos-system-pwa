<?php

namespace App\Filament\Resources\ChartOfAccounts\Pages;

use App\Filament\Resources\ChartOfAccounts\ChartOfAccountResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditChartOfAccount extends EditRecord
{
    protected static string $resource = ChartOfAccountResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
