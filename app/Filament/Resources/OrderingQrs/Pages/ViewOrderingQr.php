<?php

namespace App\Filament\Resources\OrderingQrs\Pages;

use App\Filament\Resources\OrderingQrs\OrderingQrResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewOrderingQr extends ViewRecord
{
    protected static string $resource = OrderingQrResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('openPublicUrl')
                ->label('Buka Halaman Publik')
                ->url(fn (): string => $this->record->publicUrl())
                ->openUrlInNewTab(),
            EditAction::make()
                ->label('Ubah'),
        ];
    }
}
