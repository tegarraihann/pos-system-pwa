<?php

namespace App\Filament\Resources\PublicOrders\Pages;

use App\Filament\Resources\PublicOrders\PublicOrderResource;
use App\Models\Order;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListPublicOrders extends ListRecords
{
    protected static string $resource = PublicOrderResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    public function getTabs(): array
    {
        return [
            'aktif' => Tab::make('Aktif')
                ->badge($this->countOrdersByStatuses([
                    Order::STATUS_PAID,
                    Order::STATUS_PROCESSING,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    Order::STATUS_PAID,
                    Order::STATUS_PROCESSING,
                ])),
            'sudah_dibayar' => Tab::make('Sudah Dibayar')
                ->badge($this->countOrdersByStatuses([
                    Order::STATUS_PAID,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('status', Order::STATUS_PAID)),
            'diproses' => Tab::make('Diproses')
                ->badge($this->countOrdersByStatuses([
                    Order::STATUS_PROCESSING,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('status', Order::STATUS_PROCESSING)),
            'selesai' => Tab::make('Selesai')
                ->badge($this->countOrdersByStatuses([
                    Order::STATUS_SERVED,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('status', Order::STATUS_SERVED)),
            'masalah' => Tab::make('Masalah')
                ->badge($this->countOrdersByStatuses([
                    Order::STATUS_EXPIRED,
                    Order::STATUS_FAILED,
                    Order::STATUS_CANCELED,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->whereIn('status', [
                    Order::STATUS_EXPIRED,
                    Order::STATUS_FAILED,
                    Order::STATUS_CANCELED,
                ])),
            'semua' => Tab::make('Semua')
                ->badge(Order::query()
                    ->where('order_source', Order::SOURCE_PUBLIC_QR)
                    ->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'aktif';
    }

    protected function countOrdersByStatuses(array $statuses): int
    {
        return Order::query()
            ->where('order_source', Order::SOURCE_PUBLIC_QR)
            ->whereIn('status', $statuses)
            ->count();
    }
}
