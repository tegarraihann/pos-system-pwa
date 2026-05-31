<?php

namespace App\Filament\Resources\OrderingQrs\Pages;

use App\Filament\Resources\OrderingQrs\OrderingQrResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListOrderingQrs extends ListRecords
{
    protected static string $resource = OrderingQrResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Buat QR Pemesanan'),
        ];
    }
}
