<?php

namespace App\Filament\Resources\Suppliers\Pages;

use App\Filament\Resources\Suppliers\SupplierResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewSupplier extends ViewRecord
{
    protected static string $resource = SupplierResource::class;
    protected static ?string $title = 'Detail Supplier';
    protected static ?string $breadcrumb = 'Detail';

    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}
