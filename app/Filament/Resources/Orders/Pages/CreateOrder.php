<?php

namespace App\Filament\Resources\Orders\Pages;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use App\Services\OrderAccountingService;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();

        return $data;
    }

    protected function handleRecordCreation(array $data): Order
    {
        $shouldServe = ($data['status'] ?? null) === Order::STATUS_SERVED;

        if ($shouldServe) {
            $data['status'] = Order::STATUS_DRAFT;
        }

        /** @var Order $order */
        $order = static::getModel()::query()->create($data);

        if ($shouldServe) {
            return app(OrderAccountingService::class)->markAsServed($order);
        }

        return $order;
    }
}
