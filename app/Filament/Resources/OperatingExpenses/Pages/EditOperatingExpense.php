<?php

namespace App\Filament\Resources\OperatingExpenses\Pages;

use App\Filament\Resources\OperatingExpenses\OperatingExpenseResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditOperatingExpense extends EditRecord
{
    protected static string $resource = OperatingExpenseResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;
}
