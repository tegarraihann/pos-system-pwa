<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderAccountingService;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditOrder extends EditRecord
{
    protected static string $resource = OrderResource::class;
    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function handleRecordUpdate($record, array $data): Order
    {
        $wasServed = $record->status === Order::STATUS_SERVED;

        $record->update($data);

        if (! $wasServed && ($data['status'] ?? null) === Order::STATUS_SERVED) {
            return app(OrderAccountingService::class)->markAsServed($record);
        }

        return $record;
    }
}
