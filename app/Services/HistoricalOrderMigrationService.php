<?php

namespace App\Services;

use App\Models\HistoricalOrderImport;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockLocation;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class HistoricalOrderMigrationService
{
    public function migrate(HistoricalOrderImport $import, ?int $migratedBy = null): HistoricalOrderImport
    {
        $import->loadMissing('items.menuVariant.menu', 'migratedOrder');

        if ($import->migrated_order_id) {
            return $import->refresh();
        }

        if (! $import->ready_for_migration) {
            throw new RuntimeException('Transaksi staging belum ditandai siap migrasi.');
        }

        if ($import->mapping_status !== HistoricalOrderImport::STATUS_MATCHED) {
            throw new RuntimeException('Hanya transaksi staging dengan status matched yang boleh dimigrasikan.');
        }

        return DB::transaction(function () use ($import, $migratedBy): HistoricalOrderImport {
            $location = StockLocation::query()
                ->where('code', 'AHWA-WARKOP')
                ->first()
                ?? StockLocation::resolveDefaultLocation();

            $order = Order::query()->create([
                'order_number' => $import->source_order_number,
                'ordered_at' => $import->ordered_at,
                'order_type' => Order::TYPE_DINE_IN,
                'status' => Order::STATUS_PAID,
                'customer_type' => Order::CUSTOMER_WALK_IN,
                'order_source' => Order::SOURCE_POS,
                'payment_method' => $import->payment_method_mapped,
                'stock_location_id' => $location?->id,
                'notes' => $this->buildOrderNotes($import),
                'created_by' => $migratedBy,
            ]);

            foreach ($import->items as $item) {
                $variant = $item->menuVariant;

                if (! $variant) {
                    throw new RuntimeException('Masih ada item tanpa master menu saat migrasi final.');
                }

                $order->items()->create([
                    'menu_variant_id' => $variant->id,
                    'item_name_snapshot' => ($variant->menu?->name ?? $item->normalized_item_name) . ' - ' . $variant->kd_varian,
                    'price' => $item->unit_price,
                    'qty' => $item->inferred_qty,
                    'discount_amount' => 0,
                    'notes' => $item->notes,
                ]);
            }

            Payment::query()->create([
                'order_id' => $order->id,
                'method' => $import->payment_method_mapped,
                'amount' => $import->total_amount,
                'status' => 'paid',
                'paid_at' => $import->paid_at ?? $import->ordered_at,
            ]);

            $order->refreshPaidTotal();
            app(OrderAccountingService::class)->accountHistoricalOrder($order);

            $import->update([
                'migrated_order_id' => $order->id,
                'migrated_at' => now(),
                'migration_notes' => 'Dimigrasikan ke order final tanpa konsumsi stok historis.',
            ]);

            return $import->fresh(['migratedOrder']);
        });
    }

    protected function buildOrderNotes(HistoricalOrderImport $import): string
    {
        $notes = [
            'Import histori dari PDF AHWA Warkop April 2026.',
            'Nomor sumber: ' . $import->source_order_number . '.',
        ];

        if ($import->operator_raw) {
            $notes[] = 'Operator sumber: ' . $import->operator_raw . '.';
        }

        if ($import->review_notes) {
            $notes[] = 'Catatan review: ' . $import->review_notes;
        }

        return implode(' ', $notes);
    }
}
