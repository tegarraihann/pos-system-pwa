<?php

namespace App\Services;

use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderingQr;
use App\Models\Payment;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PublicOrderingService
{
    /**
     * @return array{0:Order,1:Payment}
     */
    public function createPendingPaymentOrder(OrderingQr $orderingQr, array $payload): array
    {
        $quantities = collect($payload['quantities'] ?? [])
            ->mapWithKeys(fn (mixed $qty, mixed $variantId): array => [(int) $variantId => (int) $qty])
            ->filter(fn (int $qty): bool => $qty > 0);

        if ($quantities->isEmpty()) {
            throw ValidationException::withMessages([
                'quantities' => 'Pilih minimal satu menu untuk dipesan.',
            ]);
        }

        $variants = MenuVariant::query()
            ->with('menu')
            ->whereIn('id', $quantities->keys())
            ->where('is_active', true)
            ->get()
            ->keyBy('id');

        if ($variants->count() !== $quantities->count()) {
            throw ValidationException::withMessages([
                'quantities' => 'Sebagian menu tidak lagi tersedia. Muat ulang halaman lalu coba lagi.',
            ]);
        }

        $this->validateQuantities($quantities, $variants);

        return DB::transaction(function () use ($orderingQr, $payload, $quantities, $variants): array {
            $order = Order::query()->create([
                'ordered_at' => now(),
                'order_type' => Order::TYPE_DINE_IN,
                'status' => Order::STATUS_PENDING_PAYMENT,
                'customer_type' => Order::CUSTOMER_WALK_IN,
                'order_source' => Order::SOURCE_PUBLIC_QR,
                'ordering_qr_id' => $orderingQr->id,
                'payment_method' => Order::PAYMENT_MIDTRANS,
                'sync_status' => Order::SYNC_STATUS_SYNCED,
                'synced_at' => now(),
                'guest_name' => trim((string) $payload['guest_name']),
                'guest_phone' => blank($payload['guest_phone'] ?? null)
                    ? null
                    : trim((string) $payload['guest_phone']),
                'stock_location_id' => $orderingQr->stock_location_id,
                'table_number' => $orderingQr->table_number,
                'queue_number' => $this->nextQueueNumber($orderingQr->stock_location_id),
                'notes' => blank($payload['notes'] ?? null)
                    ? null
                    : trim((string) $payload['notes']),
            ]);

            $quantities->each(function (int $qty, int $variantId) use ($order, $variants): void {
                /** @var MenuVariant $variant */
                $variant = $variants->get($variantId);

                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'menu_variant_id' => $variant->id,
                    'price' => $variant->price,
                    'qty' => $qty,
                    'discount_amount' => 0,
                    'notes' => null,
                ]);
            });

            $order = $order->fresh(['items.menuVariant.menu', 'orderingQr', 'stockLocation']);

            $payment = $order->payments()->create([
                'method' => 'midtrans_snap',
                'amount' => (float) $order->grand_total,
                'status' => 'pending',
                'gateway_provider' => 'midtrans',
            ]);

            $payment->update([
                'gateway_ref' => $order->order_number . '-' . $payment->id,
            ]);

            return [$order->fresh(['items.menuVariant.menu', 'orderingQr', 'stockLocation']), $payment->fresh()];
        });
    }

    protected function validateQuantities(Collection $quantities, Collection $variants): void
    {
        foreach ($quantities as $variantId => $qty) {
            /** @var MenuVariant|null $variant */
            $variant = $variants->get($variantId);

            if (! $variant || ! $variant->menu || ! $variant->menu->is_active) {
                throw ValidationException::withMessages([
                    'quantities' => 'Sebagian menu tidak lagi aktif.',
                ]);
            }

            if ($variant->menu->is_stock_managed && $qty > (int) $variant->stock) {
                throw ValidationException::withMessages([
                    'quantities' => sprintf(
                        'Stok untuk %s tidak mencukupi.',
                        $variant->menu->name
                    ),
                ]);
            }
        }
    }

    protected function nextQueueNumber(int $stockLocationId): int
    {
        $nextValue = Order::query()
            ->where('stock_location_id', $stockLocationId)
            ->whereDate('ordered_at', now()->toDateString())
            ->max('queue_number');

        return ((int) $nextValue) + 1;
    }
}
