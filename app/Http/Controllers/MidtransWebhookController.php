<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $payload = $request->all();
        $orderId = (string) ($payload['order_id'] ?? '');
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = (string) ($payload['signature_key'] ?? '');

        if ($orderId === '' || $statusCode === '' || $grossAmount === '' || $signatureKey === '') {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        $expectedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . config('midtrans.server_key'));

        if (! hash_equals($expectedSignature, $signatureKey)) {
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        $transactionStatus = (string) ($payload['transaction_status'] ?? '');
        $fraudStatus = (string) ($payload['fraud_status'] ?? '');
        $transactionId = (string) ($payload['transaction_id'] ?? '');
        $paymentType = (string) ($payload['payment_type'] ?? '');

        $payment = Payment::query()
            ->where('gateway_provider', 'midtrans')
            ->where('gateway_ref', $orderId)
            ->orderByDesc('id')
            ->first();

        if (! $payment && $transactionId !== '') {
            $payment = Payment::query()
                ->where('gateway_provider', 'midtrans')
                ->where('gateway_ref', $transactionId)
                ->orderByDesc('id')
                ->first();
        }

        $order = $payment?->order;

        if (! $order) {
            $orderNumber = $orderId;

            if (preg_match('/-\d+$/', $orderId)) {
                $parts = explode('-', $orderId);
                array_pop($parts);
                $orderNumber = implode('-', $parts);
            }

            $order = Order::query()->where('order_number', $orderNumber)->first();
        }

        if (! $order) {
            return response()->json(['message' => 'Order not found'], 404);
        }

        $paymentStatus = match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'canceled',
            default => 'pending',
        };

        if (! $payment) {
            $payment = new Payment([
                'order_id' => $order->id,
                'method' => $paymentType ?: 'midtrans_snap',
                'amount' => (float) $grossAmount,
                'status' => $paymentStatus,
                'gateway_provider' => 'midtrans',
            ]);
        }

        $payment->fill([
            'method' => $paymentType ?: $payment->method,
            'status' => $paymentStatus,
            'gateway_provider' => 'midtrans',
            'gateway_ref' => $payment->gateway_ref ?: $orderId,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        if ((float) $payment->amount <= 0) {
            $payment->amount = (float) $grossAmount;
        }

        $payment->save();

        $orderUpdate = [
            'payment_method' => Order::PAYMENT_MIDTRANS,
            'sync_status' => Order::SYNC_STATUS_SYNCED,
            'synced_at' => now(),
            'sync_error' => null,
        ];

        if ($paymentStatus === 'paid' && $order->status === Order::STATUS_DRAFT) {
            $orderUpdate['status'] = Order::STATUS_QUEUED;
        }

        $order->update($orderUpdate);

        return response()->json(['message' => 'OK']);
    }
}
