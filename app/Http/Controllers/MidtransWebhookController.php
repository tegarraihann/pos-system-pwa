<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MidtransService;
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

        $transactionId = (string) ($payload['transaction_id'] ?? '');

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

        app(MidtransService::class)->syncPaymentStatus($order, $payment, $payload);

        return response()->json(['message' => 'OK']);
    }
}
