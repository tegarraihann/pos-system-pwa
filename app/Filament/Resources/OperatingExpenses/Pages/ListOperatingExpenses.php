<?php

namespace App\Filament\Resources\OperatingExpenses\Pages;

use App\Filament\Resources\OperatingExpenses\OperatingExpenseResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListOperatingExpenses extends ListRecords
{
    protected static string $resource = OperatingExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
