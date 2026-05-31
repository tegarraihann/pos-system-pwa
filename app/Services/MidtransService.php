<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
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
        $fallbackName = $order->guest_name
            ?: $customer?->name
            ?: $order->creator?->name
            ?: 'Walk-in Customer';
        $fallbackEmail = $customer?->email
            ?: $order->creator?->email
            ?: $this->buildGuestFallbackEmail($order);
        $fallbackPhone = $order->guest_phone ?: $customer?->phone;

        $payload = [
            'transaction_details' => [
                'order_id' => $orderId ?: $order->order_number,
                'gross_amount' => $grossAmount,
            ],
            'item_details' => $items,
            'customer_details' => [
                'first_name' => $fallbackName,
                'email' => $fallbackEmail,
                'phone' => $fallbackPhone,
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

    public function syncPaymentStatus(Order $order, ?Payment $payment, array $payload): Payment
    {
        $paymentStatus = $this->resolvePaymentStatus($payload);
        $paymentType = (string) ($payload['payment_type'] ?? '');
        $transactionId = (string) ($payload['transaction_id'] ?? '');
        $gatewayRef = (string) ($payload['order_id'] ?? $transactionId);
        $grossAmount = (float) ($payload['gross_amount'] ?? 0);

        if (! $payment) {
            $payment = new Payment([
                'order_id' => $order->id,
                'method' => $paymentType !== '' ? $paymentType : 'midtrans_snap',
                'amount' => $grossAmount > 0 ? $grossAmount : (float) $order->grand_total,
                'status' => $paymentStatus,
                'gateway_provider' => 'midtrans',
            ]);
        }

        $payment->fill([
            'method' => $paymentType !== '' ? $paymentType : $payment->method,
            'status' => $paymentStatus,
            'gateway_provider' => 'midtrans',
            'gateway_ref' => $payment->gateway_ref ?: $gatewayRef,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        if ((float) $payment->amount <= 0) {
            $payment->amount = $grossAmount > 0 ? $grossAmount : (float) $order->grand_total;
        }

        $payment->save();

        $orderUpdate = [
            'payment_method' => Order::PAYMENT_MIDTRANS,
            'sync_status' => Order::SYNC_STATUS_SYNCED,
            'synced_at' => now(),
            'sync_error' => null,
        ];

        $shouldServeOrder = false;

        if ($order->isPublicQr()) {
            $orderUpdate['status'] = match ($paymentStatus) {
                'paid' => Order::STATUS_PAID,
                'pending' => Order::STATUS_PENDING_PAYMENT,
                'expired' => Order::STATUS_EXPIRED,
                'failed' => Order::STATUS_FAILED,
                'canceled' => Order::STATUS_CANCELED,
                default => $order->status,
            };
        } elseif ($paymentStatus === 'paid' && $order->status === Order::STATUS_DRAFT) {
            $shouldServeOrder = true;
        }

        $order->update($orderUpdate);

        if ($shouldServeOrder) {
            app(OrderAccountingService::class)->markAsServed($order);
        }

        return $payment;
    }

    public function resolvePaymentStatus(array $payload): string
    {
        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');

        return match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'canceled',
            default => 'pending',
        };
    }

    public function getSnapScriptUrl(): string
    {
        return config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    }

    protected function buildGuestFallbackEmail(Order $order): string
    {
        $namePart = Str::slug($order->guest_name ?: 'guest');

        return $namePart . '+' . Str::lower($order->order_number ?: Str::random(8)) . '@public-order.local';
    }

    protected function configure(): void
    {
        Config::$serverKey = (string) config('midtrans.server_key');
        Config::$isProduction = (bool) config('midtrans.is_production');
        Config::$isSanitized = (bool) config('midtrans.sanitize');
        Config::$is3ds = (bool) config('midtrans.is_3ds');
    }
}
