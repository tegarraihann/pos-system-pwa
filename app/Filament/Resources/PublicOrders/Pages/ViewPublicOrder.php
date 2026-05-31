<?php

namespace App\Filament\Resources\PublicOrders\Pages;

use App\Filament\Resources\PublicOrders\PublicOrderResource;
use App\Models\Order;
use App\Services\OrderAccountingService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Support\Enums\Width;

class ViewPublicOrder extends ViewRecord
{
    protected static string $resource = PublicOrderResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('startProcessing')
                ->label('Mulai Proses')
                ->color('info')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PAID)
                ->authorize(fn (): bool => auth()->user()?->can('Process:PublicOrder') ?? false)
                ->action(fn (): bool => $this->record->update(['status' => Order::STATUS_PROCESSING])),
            Action::make('markServed')
                ->label('Tandai Selesai')
                ->color('success')
                ->visible(fn (): bool => $this->record->status === Order::STATUS_PROCESSING)
                ->authorize(fn (): bool => auth()->user()?->can('Process:PublicOrder') ?? false)
                ->action(function (): bool {
                    app(OrderAccountingService::class)->markAsServed($this->record);

                    return true;
                }),
            Action::make('cancelOrder')
                ->label('Batalkan')
                ->color('danger')
                ->visible(fn (): bool => ! in_array($this->record->status, [
                    Order::STATUS_SERVED,
                    Order::STATUS_CANCELED,
                    Order::STATUS_EXPIRED,
                ], true))
                ->authorize(fn (): bool => auth()->user()?->can('Cancel:PublicOrder') ?? false)
                ->form([
                    Textarea::make('cancel_reason')
                        ->label('Alasan Pembatalan')
                        ->required()
                        ->rows(3),
                ])
                ->action(function (array $data): bool {
                    return $this->record->update([
                        'status' => Order::STATUS_CANCELED,
                        'cancel_reason' => $data['cancel_reason'],
                        'canceled_at' => now(),
                    ]);
                }),
        ];
    }
}
