<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;

class ViewStockMovement extends ViewRecord
{
    protected static string $resource = StockMovementResource::class;
    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    public function getTitle(): string | Htmlable
    {
        return 'Detail Pergerakan Stok';
    }

    public function getBreadcrumb(): string
    {
        return 'Detail';
    }

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make()
                ->label('Ubah'),
        ];
    }
}
