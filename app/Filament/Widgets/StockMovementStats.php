<?php

namespace App\Filament\Widgets;

use App\Models\StockMovement;
use App\Services\StockReminderService;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StockMovementStats extends BaseWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $snapshot = app(StockReminderService::class)->getSnapshot();
        $totalActiveItems = $snapshot['total_items'];
        $lowStockCount = $snapshot['low_count'];
        $outOfStockCount = $snapshot['out_count'];

        $todayMovements = StockMovement::query()
            ->whereDate('movement_date', today())
            ->count();

        return [
            Stat::make('Item Terpantau', number_format($totalActiveItems))
                ->description('Item dengan reminder stok terisi')
                ->color('primary'),

            Stat::make('Stok Menipis', number_format($lowStockCount))
                ->description('Berdasarkan reminder stok manual')
                ->color($lowStockCount > 0 ? 'warning' : 'success'),

            Stat::make('Stok Habis', number_format($outOfStockCount))
                ->description('Item dengan stok 0')
                ->color($outOfStockCount > 0 ? 'danger' : 'success'),

            Stat::make('Pergerakan Hari Ini', number_format($todayMovements))
                ->description('Tanggal ' . now()->format('d M Y'))
                ->color('info'),
        ];
    }
}
