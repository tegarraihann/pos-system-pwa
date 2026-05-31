<?php

namespace App\Filament\Resources\OperatingExpenses\Pages;

use App\Filament\Resources\OperatingExpenses\OperatingExpenseResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateOperatingExpense extends CreateRecord
{
    protected static string $resource = OperatingExpenseResource::class;

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }
}
