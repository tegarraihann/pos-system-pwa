<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use UnitEnum;

class KitchenDisplay extends Page
{
    protected static ?string $title = 'Kitchen Display';
    protected static ?string $navigationLabel = 'Kitchen Display';
    protected static string|UnitEnum|null $navigationGroup = 'POS management';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedFire;
    protected static ?string $slug = 'kitchen-display';

    protected string $view = 'filament.pages.kitchen-display';

    public string $station = 'all';

    protected $listeners = ['kds-refresh' => '$refresh'];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('View:KitchenDisplay');
    }

    /**
     * @return array<string, Collection<int, Order>>
     */
    public function getOrdersByStatusProperty(): array
    {
        $statuses = [
            Order::STATUS_QUEUED,
            Order::STATUS_RECEIVED,
            Order::STATUS_PREPARING,
            Order::STATUS_READY,
        ];

        $orders = Order::query()
            ->with(['items.menuVariant.menu', 'items.modifiers'])
            ->whereIn('status', $statuses)
            ->orderByDesc('ordered_at')
            ->orderByDesc('created_at')
            ->get();

        return $orders->groupBy('status')->map(function (Collection $group): Collection {
            return $group->filter(fn (Order $order): bool => $this->orderMatchesStation($order));
        })->all();
    }

    public function getAveragePrepSecondsProperty(): int
    {
        return (int) Order::query()
            ->whereNotNull('received_at')
            ->whereNotNull('ready_at')
            ->whereIn('status', [Order::STATUS_READY, Order::STATUS_SERVED])
            ->orderByDesc('ready_at')
            ->limit(30)
            ->get()
            ->map(fn (Order $order): int => $order->received_at->diffInSeconds($order->ready_at))
            ->avg() ?: 0;
    }

    public function formatDuration(int $seconds): string
    {
        $minutes = intdiv($seconds, 60);
        $remain = $seconds % 60;

        return sprintf('%02d:%02d', $minutes, $remain);
    }

    public function orderMatchesStation(Order $order): bool
    {
        if ($this->station === 'all') {
            return true;
        }

        foreach ($order->items as $item) {
            $category = $item->menuVariant?->menu?->category;
            if ($this->categoryToStation($category) === $this->station) {
                return true;
            }
        }

        return false;
    }

    public function itemMatchesStation(OrderItem $item): bool
    {
        if ($this->station === 'all') {
            return true;
        }

        $category = $item->menuVariant?->menu?->category;

        return $this->categoryToStation($category) === $this->station;
    }

    public function categoryToStation(?string $category): string
    {
        if (! $category) {
            return 'food';
        }

        $value = Str::lower($category);
        $keywords = ['minuman', 'beverage', 'drink', 'kopi', 'coffee', 'tea', 'milk', 'juice'];

        foreach ($keywords as $keyword) {
            if (Str::contains($value, $keyword)) {
                return 'beverage';
            }
        }

        return 'food';
    }

    public function markReceived(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        if ($order->status === Order::STATUS_QUEUED) {
            $order->update([
                'status' => Order::STATUS_RECEIVED,
                'received_at' => now(),
            ]);
        }
    }

    public function markPreparing(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        if (in_array($order->status, [Order::STATUS_RECEIVED, Order::STATUS_PREPARING], true)) {
            $order->update(['status' => Order::STATUS_PREPARING]);
        }
    }

    public function markReady(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        if (in_array($order->status, [Order::STATUS_PREPARING, Order::STATUS_RECEIVED], true)) {
            $order->update([
                'status' => Order::STATUS_READY,
                'ready_at' => now(),
            ]);
        }
    }

    public function markServed(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        if ($order->status === Order::STATUS_READY) {
            $order->update(['status' => Order::STATUS_SERVED]);
        }
    }

    public function togglePriority(int $orderId): void
    {
        $order = Order::query()->find($orderId);

        if (! $order) {
            return;
        }

        $order->update(['is_priority' => ! $order->is_priority]);

        Notification::make()
            ->title($order->is_priority ? 'Order diprioritaskan' : 'Prioritas dihapus')
            ->success()
            ->send();
    }
}
