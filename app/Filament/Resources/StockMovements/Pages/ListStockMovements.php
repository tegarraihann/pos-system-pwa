<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Pages\StockReminders;
use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Filament\Widgets\StockMovementStats;
use App\Services\StockReminderService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Collection;

class ListStockMovements extends ListRecords
{
    protected static string $resource = StockMovementResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Manajemen Stok';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Manajemen Stok';
    }

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
                ->url(fn (): string => StockReminders::getUrl([
                    'status' => $this->preferredReminderStatus($snapshot),
                ])),
            CreateAction::make()
                ->label('Tambah Pergerakan Stok'),
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
        $previewLimit = (int) config('stock.reminder_popup_preview_limit', 5);
        $snapshot = app(StockReminderService::class)->getSnapshot();

        if ($snapshot['impacted_count'] <= 0) {
            session()->forget([
                $this->stockReminderPopupLastSeenSessionKey(),
                $this->stockReminderPopupSnapshotHashSessionKey(),
            ]);

            return;
        }

        $snapshotHash = $this->stockReminderSnapshotHash($snapshot);

        if ($this->shouldSkipStockReminderPopup($snapshotHash, $cooldownMinutes)) {
            return;
        }

        $previewItems = $snapshot['items']->take($previewLimit)->values();
        $preferredStatus = $this->preferredReminderStatus($snapshot);
        $destinationUrl = StockReminders::getUrl(['status' => $preferredStatus]);
        $itemsPreview = $this->formatStockReminderPreviewItems($previewItems);

        $extraCount = max($snapshot['impacted_count'] - $previewItems->count(), 0);
        $extraText = $extraCount > 0 ? "\n+{$extraCount} item lainnya." : '';

        $notification = Notification::make()
            ->title('Reminder Stok')
            ->body("Terdapat {$snapshot['impacted_count']} item yang perlu perhatian.\n\n{$itemsPreview}{$extraText}")
            ->view('filament.notifications.stock-reminder-notification')
            ->safeViews('filament.notifications.stock-reminder-notification')
            ->viewData([
                'destinationUrl' => $destinationUrl,
                'summary' => "Terdapat {$snapshot['impacted_count']} item yang perlu perhatian.",
                'itemsPreview' => $this->formatStockReminderPreviewLines($previewItems),
                'extraCount' => $extraCount,
            ])
            ->actions([
                Action::make('viewStockReminders')
                    ->label('Lihat Detail')
                    ->button()
                    ->url($destinationUrl),
            ])
            ->persistent();

        if ($snapshot['out_count'] > 0) {
            $notification->danger();
        } else {
            $notification->warning();
        }

        $notification->send();

        session([
            $this->stockReminderPopupLastSeenSessionKey() => now()->toDateTimeString(),
            $this->stockReminderPopupSnapshotHashSessionKey() => $snapshotHash,
        ]);
    }

    /**
     * @param  array{out_count:int, low_count:int}  $snapshot
     */
    protected function preferredReminderStatus(array $snapshot): string
    {
        if (($snapshot['out_count'] ?? 0) > 0) {
            return 'out';
        }

        if (($snapshot['low_count'] ?? 0) > 0) {
            return 'low';
        }

        return 'all';
    }

    /**
     * @param  Collection<int, array{name:string, status:string, stock:float, reminder_stock:float}>  $items
     */
    protected function formatStockReminderPreviewItems(Collection $items): string
    {
        return $this->formatStockReminderPreviewLines($items)->implode("\n");
    }

    /**
     * @param  Collection<int, array{name:string, status:string, stock:float, reminder_stock:float}>  $items
     * @return Collection<int, string>
     */
    protected function formatStockReminderPreviewLines(Collection $items): Collection
    {
        return $items->map(static function (array $item): string {
            $status = $item['status'] === 'out' ? 'HABIS' : 'MENIPIS';

            return "{$item['name']} ({$status}) - stok {$item['stock']} / reminder {$item['reminder_stock']}";
        });
    }

    /**
     * @param  array{
     *   impacted_count:int,
     *   out_count:int,
     *   low_count:int,
     *   items:Collection<int, array{item_type:string, item_id:int, status:string, stock:float, reminder_stock:float}>
     * }  $snapshot
     */
    protected function stockReminderSnapshotHash(array $snapshot): string
    {
        return md5(json_encode([
            'impacted_count' => $snapshot['impacted_count'],
            'out_count' => $snapshot['out_count'],
            'low_count' => $snapshot['low_count'],
            'items' => $snapshot['items']
                ->map(static fn (array $item): array => [
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'status' => $item['status'],
                    'stock' => round((float) $item['stock'], 3),
                    'reminder_stock' => round((float) $item['reminder_stock'], 3),
                ])
                ->values()
                ->all(),
        ], JSON_THROW_ON_ERROR));
    }

    protected function shouldSkipStockReminderPopup(string $snapshotHash, int $cooldownMinutes): bool
    {
        $lastSeenAt = session($this->stockReminderPopupLastSeenSessionKey());
        $lastSnapshotHash = session($this->stockReminderPopupSnapshotHashSessionKey());

        if (blank($lastSeenAt) || blank($lastSnapshotHash)) {
            return false;
        }

        if ($lastSnapshotHash !== $snapshotHash) {
            return false;
        }

        return now()->diffInMinutes(\Illuminate\Support\Carbon::parse($lastSeenAt)) < $cooldownMinutes;
    }

    protected function stockReminderPopupLastSeenSessionKey(): string
    {
        return 'stock_reminder_popup_last_seen_at';
    }

    protected function stockReminderPopupSnapshotHashSessionKey(): string
    {
        return 'stock_reminder_popup_last_snapshot_hash';
    }
}
