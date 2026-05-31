<?php

namespace App\Filament\Resources\OrderingQrs\Pages;

use App\Filament\Resources\OrderingQrs\OrderingQrResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditOrderingQr extends EditRecord
{
    protected static string $resource = OrderingQrResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make()
                ->label('Detail'),
            DeleteAction::make()
                ->label('Hapus'),
        ];
    }
}
