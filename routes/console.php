<?php

use App\Models\Order;
use App\Models\HistoricalOrderImport;
use App\Services\HistoricalSalesPdfImportService;
use App\Services\HistoricalOrderMigrationService;
use App\Services\OrderAccountingService;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('finance:backfill-order-costs', function (): void {
    $service = app(OrderAccountingService::class);
    $processed = 0;

    Order::query()
        ->where('status', Order::STATUS_SERVED)
        ->whereNull('cost_accounted_at')
        ->orderBy('id')
        ->chunkById(100, function ($orders) use ($service, &$processed): void {
            foreach ($orders as $order) {
                $service->markAsServed($order);
                $processed++;
                $this->line("Diproses: {$order->order_number}");
            }
        });

    $this->info("Selesai. Total order yang dibackfill: {$processed}");
})->purpose('Hitung ulang HPP dan snapshot laba kotor untuk order selesai yang belum terakuntansi');

Artisan::command('historical:import-ahwa-sales {file=docs/detil_penjualan_2026_05_22_15_08_28.pdf}', function (string $file): void {
    $service = app(HistoricalSalesPdfImportService::class);
    $summary = $service->importFromPdf(base_path($file));

    $this->info('Import histori AHWA Warkop selesai.');
    $this->line("Total transaksi diproses: {$summary['processed']}");
    $this->line("Matched: {$summary['matched']}");
    $this->line("Partial: {$summary['partial']}");
    $this->line("Ambiguous: {$summary['ambiguous']}");
    $this->line("Unmatched: {$summary['unmatched']}");
})->purpose('Import staging histori penjualan AHWA Warkop dari PDF detail penjualan April 2026');

Artisan::command('historical:migrate-ready-orders', function (): void {
    $service = app(HistoricalOrderMigrationService::class);
    $processed = 0;

    HistoricalOrderImport::query()
        ->where('ready_for_migration', true)
        ->whereNull('migrated_order_id')
        ->orderBy('ordered_at')
        ->chunkById(100, function ($imports) use ($service, &$processed): void {
            foreach ($imports as $import) {
                $service->migrate($import);
                $processed++;
                $this->line("Dimigrasikan: {$import->source_order_number}");
            }
        });

    $this->info("Selesai. Total histori yang dimigrasikan: {$processed}");
})->purpose('Migrasikan histori staging yang sudah siap ke order final tanpa konsumsi stok historis');
