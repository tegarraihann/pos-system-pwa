<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Filament\Widgets\StockMovementStats;
use App\Services\StockReminderService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    public function mount(): void
    {
        parent::mount();

        $this->sendStockReminderPopup();
    }

    protected function getHeaderActions(): array
    {
        $snapshot = app(StockReminderService::class)->getSnapshot(
            limit: (int) config('stock.reminder_popup_preview_limit', 5),
        );

        $impactedCount = $snapshot['impacted_count'];
        $badgeColor = $snapshot['out_count'] > 0
            ? 'danger'
            : ($snapshot['low_count'] > 0 ? 'warning' : 'success');

        return [
            Action::make('stockReminderBadge')
                ->label('Reminder Stok')
                ->badge(number_format($impactedCount))
                ->color($badgeColor)
                ->icon('heroicon-o-bell-alert')
                ->disabled(),
            CreateAction::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            StockMovementStats::class,
        ];
    }

    protected function sendStockReminderPopup(): void
    {
        $cooldownMinutes = (int) config('stock.reminder_popup_cooldown_minutes', 10);
        $sessionKey = 'stock_reminder_popup_last_seen_at';
        $lastSeenAt = session($sessionKey);

        if ($lastSeenAt && now()->diffInMinutes(\Illuminate\Support\Carbon::parse($lastSeenAt)) < $cooldownMinutes) {
            return;
        }

        $snapshot = app(StockReminderService::class)->getSnapshot(
            limit: (int) config('stock.reminder_popup_preview_limit', 5),
        );

        if ($snapshot['impacted_count'] <= 0) {
            return;
        }

        $itemsPreview = $snapshot['items']
            ->map(static function (array $item): string {
                $status = $item['status'] === 'out' ? 'HABIS' : 'MENIPIS';

                return "{$item['name']} ({$status}) - stok {$item['stock']} / reminder {$item['reminder_stock']}";
            })
            ->implode("\n");

        $extraCount = max($snapshot['impacted_count'] - $snapshot['items']->count(), 0);
        $extraText = $extraCount > 0 ? "\n+{$extraCount} item lainnya." : '';

        $notification = Notification::make()
            ->title('Reminder Stok')
            ->body("Terdapat {$snapshot['impacted_count']} item yang perlu perhatian.\n\n{$itemsPreview}{$extraText}")
            ->persistent();

        if ($snapshot['out_count'] > 0) {
            $notification->danger();
        } else {
            $notification->warning();
        }

        $notification->send();

        session([$sessionKey => now()->toDateTimeString()]);
    }
}
