<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pembayaran Pesanan - {{ $order->order_number }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @if ($payment?->gateway_provider === 'midtrans' && filled($payment?->gateway_token) && $clientKey !== '')
        <script
            src="{{ $snapScriptUrl }}"
            data-client-key="{{ $clientKey }}"
        ></script>
    @endif
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <div class="mx-auto max-w-5xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-stone-900 via-stone-800 to-stone-700 px-5 py-6 text-white shadow-lg sm:px-7">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/70">Payment Status</p>
            <div class="mt-3 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold sm:text-3xl">Order {{ $order->order_number }}</h1>
                    <p class="mt-2 text-sm text-white/75">
                        Meja {{ $order->table_number ?: $orderingQr->table_number }} | {{ $orderingQr->name }}
                    </p>
                </div>
                <div class="rounded-2xl border border-white/10 bg-white/10 px-4 py-3 text-sm backdrop-blur">
                    <div>Status order: {{ \App\Models\Order::statusOptions()[$order->status] ?? $order->status }}</div>
                    <div class="mt-1 text-white/70">Status payment: {{ strtoupper((string) ($payment?->status ?? '-')) }}</div>
                </div>
            </div>
        </div>

        @if ($refreshError)
            <div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 px-5 py-4 text-amber-900 shadow-sm">
                {{ $refreshError }}
            </div>
        @endif

        <div class="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
            <section class="space-y-4">
                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Ringkasan Pesanan</div>
                    <div class="mt-4 space-y-3">
                        @foreach ($order->items as $item)
                            <div class="flex items-start justify-between gap-4 rounded-2xl bg-stone-50 px-4 py-3">
                                <div>
                                    <div class="font-medium text-stone-900">{{ $item->item_name_snapshot }}</div>
                                    <div class="mt-1 text-sm text-stone-500">{{ rtrim(rtrim(number_format((float) $item->qty, 3, '.', ''), '0'), '.') }} x Rp {{ number_format((float) $item->price, 0, ',', '.') }}</div>
                                </div>
                                <div class="text-sm font-semibold text-stone-900">
                                    Rp {{ number_format((float) $item->total, 0, ',', '.') }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Detail Pemesan</div>
                    <dl class="mt-4 space-y-3 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-500">Nama</dt>
                            <dd class="font-medium text-stone-900">{{ $order->guest_name ?: '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-500">Nomor HP</dt>
                            <dd class="font-medium text-stone-900">{{ $order->guest_phone ?: '-' }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-stone-500">Catatan</dt>
                            <dd class="max-w-[60%] text-right font-medium text-stone-900">{{ $order->notes ?: '-' }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Status Saat Ini</div>
                    <div class="mt-4">
                        @if ($order->status === \App\Models\Order::STATUS_PENDING_PAYMENT)
                            <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-4 text-sm text-amber-900">
                                Pembayaran belum selesai. Lanjutkan pembayaran untuk mengirim pesanan ke outlet.
                            </div>
                        @elseif ($order->status === \App\Models\Order::STATUS_PAID)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                                Pembayaran berhasil. Pesanan sudah diteruskan ke outlet dan menunggu diproses.
                            </div>
                        @elseif ($order->status === \App\Models\Order::STATUS_PROCESSING)
                            <div class="rounded-2xl border border-sky-200 bg-sky-50 px-4 py-4 text-sm text-sky-900">
                                Pesanan sedang diproses oleh outlet.
                            </div>
                        @elseif ($order->status === \App\Models\Order::STATUS_SERVED)
                            <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-4 text-sm text-emerald-900">
                                Pesanan selesai dan sudah disajikan.
                            </div>
                        @elseif ($order->status === \App\Models\Order::STATUS_EXPIRED)
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-900">
                                Waktu pembayaran sudah kadaluarsa. Silakan buat pesanan baru dari katalog.
                            </div>
                        @elseif ($order->status === \App\Models\Order::STATUS_FAILED)
                            <div class="rounded-2xl border border-rose-200 bg-rose-50 px-4 py-4 text-sm text-rose-900">
                                Pembayaran gagal diproses. Coba cek status atau ulangi pemesanan.
                            </div>
                        @else
                            <div class="rounded-2xl border border-stone-200 bg-stone-50 px-4 py-4 text-sm text-stone-800">
                                Status pesanan: {{ \App\Models\Order::statusOptions()[$order->status] ?? $order->status }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 border-t border-stone-200 pt-4">
                        <div class="flex items-center justify-between text-sm text-stone-500">
                            <span>Total bayar</span>
                            <span class="font-semibold text-stone-900">Rp {{ number_format((float) $order->grand_total, 0, ',', '.') }}</span>
                        </div>
                        <div class="mt-2 flex items-center justify-between text-sm text-stone-500">
                            <span>Sudah dibayar</span>
                            <span class="font-semibold text-stone-900">Rp {{ number_format((float) $order->paid_total, 0, ',', '.') }}</span>
                        </div>
                    </div>
                    <div class="mt-5 space-y-3">
                        @if ($order->status === \App\Models\Order::STATUS_PENDING_PAYMENT && filled($payment?->gateway_token))
                            <button
                                type="button"
                                id="pay-now-button"
                                class="w-full rounded-2xl bg-amber-400 px-4 py-3 text-sm font-semibold text-stone-900 transition hover:bg-amber-300"
                            >
                                Bayar Sekarang
                            </button>
                        @endif

                        @if ($payment?->gateway_provider === 'midtrans' && filled($payment?->gateway_ref))
                            <a
                                href="{{ route('public-ordering.payment', ['orderingQr' => $orderingQr, 'orderNumber' => $order->order_number, 'refresh' => 1]) }}"
                                class="block w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                            >
                                Cek Status Pembayaran
                            </a>
                        @endif

                        <a
                            href="{{ route('public-ordering.show', $orderingQr) }}"
                            class="block w-full rounded-2xl border border-stone-200 bg-white px-4 py-3 text-center text-sm font-semibold text-stone-700 transition hover:bg-stone-50"
                        >
                            Kembali ke Katalog
                        </a>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    @if ($payment?->gateway_provider === 'midtrans' && filled($payment?->gateway_token) && $clientKey !== '')
        @php
            $refreshPaymentUrl = route('public-ordering.payment', [
                'orderingQr' => $orderingQr,
                'orderNumber' => $order->order_number,
                'refresh' => 1,
            ]);
        @endphp
        <script>
            (() => {
                const payButton = document.getElementById('pay-now-button');
                const snapToken = @json($payment->gateway_token);
                const refreshUrl = @json($refreshPaymentUrl);
                const shouldAutopay = @json(request()->boolean('autopay'));

                if (!window.snap || !snapToken || !payButton) {
                    return;
                }

                const openSnap = () => {
                    window.snap.pay(snapToken, {
                        onSuccess: () => window.location.href = refreshUrl,
                        onPending: () => window.location.href = refreshUrl,
                        onError: () => window.location.href = refreshUrl,
                        onClose: () => window.location.href = refreshUrl,
                    });
                };

                payButton.addEventListener('click', openSnap);

                if (shouldAutopay) {
                    setTimeout(openSnap, 250);
                }
            })();
        </script>
    @endif
</body>
</html>
