<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use App\Models\Order;
use App\Models\OrderingQr;
use App\Models\Payment;
use App\Services\MidtransService;
use App\Services\PublicOrderingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PublicOrderingController extends Controller
{
    public function show(OrderingQr $orderingQr): View
    {
        $this->ensureQrIsAccessible($orderingQr);

        $menus = Menu::query()
            ->where('is_active', true)
            ->whereHas('variants', fn ($query) => $query->where('is_active', true))
            ->with([
                'variants' => fn ($query) => $query
                    ->where('is_active', true)
                    ->orderBy('price')
                    ->orderBy('id'),
            ])
            ->orderByRaw('case when category is null or category = "" then 1 else 0 end')
            ->orderBy('category')
            ->orderBy('name')
            ->get();

        return view('public.ordering.show', [
            'menuGroups' => $menus->groupBy(fn ($menu) => $menu->category ?: 'Lainnya'),
            'menus' => $menus,
            'orderingQr' => $orderingQr->load('stockLocation'),
        ]);
    }

    public function store(
        Request $request,
        OrderingQr $orderingQr,
        PublicOrderingService $publicOrderingService,
        MidtransService $midtransService
    ): RedirectResponse {
        $this->ensureQrIsAccessible($orderingQr);

        $validated = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'guest_phone' => ['nullable', 'string', 'max:50'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'quantities' => ['required', 'array'],
            'quantities.*' => ['nullable', 'integer', 'min:0', 'max:99'],
        ], [
            'guest_name.required' => 'Nama pemesan wajib diisi.',
        ]);

        if (config('midtrans.server_key') === '' || config('midtrans.client_key') === '') {
            throw ValidationException::withMessages([
                'payment' => 'Konfigurasi Midtrans belum lengkap. Silakan hubungi admin outlet.',
            ]);
        }

        $order = null;
        $payment = null;

        try {
            [$order, $payment] = $publicOrderingService->createPendingPaymentOrder($orderingQr, $validated);
            $snap = $midtransService->createSnapTransaction($order, $payment->gateway_ref);

            if ($snap['token'] === '' || $snap['redirect_url'] === '') {
                throw new \RuntimeException('Snap token gagal dibuat.');
            }

            $payment->update([
                'gateway_token' => $snap['token'],
                'gateway_redirect_url' => $snap['redirect_url'],
            ]);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (\Throwable $exception) {
            report($exception);

            if ($payment instanceof Payment) {
                $payment->update([
                    'status' => 'failed',
                ]);
            }

            if ($order instanceof Order) {
                $order->update([
                    'status' => Order::STATUS_FAILED,
                    'sync_status' => Order::SYNC_STATUS_FAILED,
                    'sync_error' => 'Gagal memulai pembayaran Midtrans.',
                ]);
            }

            throw ValidationException::withMessages([
                'payment' => 'Pembayaran tidak dapat dimulai. Silakan coba lagi beberapa saat lagi.',
            ]);
        }

        return redirect()->route('public-ordering.payment', [
            'orderingQr' => $orderingQr,
            'orderNumber' => $order->order_number,
            'autopay' => 1,
        ]);
    }

    public function payment(
        Request $request,
        OrderingQr $orderingQr,
        string $orderNumber,
        MidtransService $midtransService
    ): View {
        $order = $this->resolvePublicOrder($orderingQr, $orderNumber);
        $payment = $order->payments()
            ->latest('id')
            ->first();
        $refreshError = null;

        if (
            $request->boolean('refresh')
            && $payment
            && $payment->gateway_provider === 'midtrans'
            && filled($payment->gateway_ref)
            && in_array($payment->status, ['pending', 'failed'], true)
        ) {
            try {
                $payload = $midtransService->getTransactionStatus($payment->gateway_ref);
                $payment = $midtransService->syncPaymentStatus($order, $payment, $payload)->fresh();
                $order = $order->fresh(['items.menuVariant.menu', 'payments', 'orderingQr', 'stockLocation']);
            } catch (\Throwable $exception) {
                report($exception);
                $refreshError = 'Status pembayaran belum bisa diperbarui. Coba lagi beberapa saat lagi.';
            }
        }

        if (
            $payment
            && $payment->gateway_provider === 'midtrans'
            && $payment->status === 'pending'
            && (blank($payment->gateway_token) || blank($payment->gateway_redirect_url))
        ) {
            $snap = $midtransService->createSnapTransaction($order, $payment->gateway_ref);

            if ($snap['token'] !== '' && $snap['redirect_url'] !== '') {
                $payment->update([
                    'gateway_token' => $snap['token'],
                    'gateway_redirect_url' => $snap['redirect_url'],
                ]);
                $payment = $payment->fresh();
            }
        }

        return view('public.ordering.payment', [
            'clientKey' => (string) config('midtrans.client_key'),
            'order' => $order,
            'orderingQr' => $orderingQr->loadMissing('stockLocation'),
            'payment' => $payment,
            'refreshError' => $refreshError,
            'snapScriptUrl' => $midtransService->getSnapScriptUrl(),
        ]);
    }

    protected function resolvePublicOrder(OrderingQr $orderingQr, string $orderNumber): Order
    {
        return Order::query()
            ->with(['items.menuVariant.menu', 'payments', 'orderingQr', 'stockLocation'])
            ->where('order_source', Order::SOURCE_PUBLIC_QR)
            ->where('ordering_qr_id', $orderingQr->id)
            ->where('order_number', $orderNumber)
            ->firstOrFail();
    }

    protected function ensureQrIsAccessible(OrderingQr $orderingQr): void
    {
        abort_unless(
            $orderingQr->is_active
            && $orderingQr->stockLocation
            && $orderingQr->stockLocation->is_active,
            404
        );
    }
}
