<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;
use Midtrans\Config;
use Midtrans\Snap;
use Midtrans\Transaction;

class MidtransService
{
    /**
     * @return array{token: string, redirect_url: string}
     */
    public function createSnapTransaction(Order $order, ?string $orderId = null): array
    {
        $this->configure();

        $items = $order->items()
            ->get()
            ->map(function ($item): array {
                $name = $item->item_name_snapshot ?: 'Item';

                return [
                    'id' => (string) $item->id,
                    'price' => (int) round((float) $item->price),
                    'quantity' => (int) max((int) $item->qty, 1),
                    'name' => Str::limit($name, 50, ''),
                ];
            })
            ->values()
            ->all();

        $grossAmount = array_reduce($items, function (int $total, array $item): int {
            return $total + ($item['price'] * $item['quantity']);
        }, 0);

        if ($grossAmount <= 0) {
            $grossAmount = (int) round((float) $order->grand_total);
        }

        $customer = $order->customer;
        $fallbackName = $order->creator?->name ?? 'Walk-in Customer';
        $fallbackEmail = $order->creator?->email ?? 'kasir@example.com';

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId ?: $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $customer?->name ?? $fallbackName,
                'email' => $customer?->email ?? $fallbackEmail,
                'phone' => $customer?->phone ?? null,
            ],
        ];

        $transaction = Snap::createTransaction($payload);

        return [
            'token' => $transaction->token ?? ($transaction['token'] ?? ''),
            'redirect_url' => $transaction->redirect_url ?? ($transaction['redirect_url'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getTransactionStatus(string $orderId): array
    {
        $this->configure();

        $response = Transaction::status($orderId);

        if (is_array($response)) {
            return $response;
        }

        return (array) $response;
    }

    protected function configure(): void
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.sanitize');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }
}
