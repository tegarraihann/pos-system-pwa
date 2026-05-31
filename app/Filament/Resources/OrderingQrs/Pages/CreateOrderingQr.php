<?php

namespace App\Filament\Resources\OrderingQrs\Pages;

use App\Filament\Resources\OrderingQrs\OrderingQrResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateOrderingQr extends CreateRecord
{
    protected static string $resource = OrderingQrResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;
}
