<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pesan Menu - {{ $orderingQr->name }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-stone-100 text-stone-900">
    <div class="mx-auto max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
        <div class="mb-6 overflow-hidden rounded-3xl bg-gradient-to-br from-amber-500 via-orange-500 to-rose-500 px-5 py-6 text-white shadow-lg sm:px-7">
            <p class="text-xs font-semibold uppercase tracking-[0.24em] text-white/80">Public QR Ordering</p>
            <div class="mt-3 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h1 class="text-2xl font-semibold sm:text-3xl">Pesan dari {{ $orderingQr->name }}</h1>
                    <p class="mt-2 text-sm text-white/85">
                        Meja {{ $orderingQr->table_number }} | {{ $orderingQr->stockLocation?->name ?? 'Lokasi tidak tersedia' }}
                    </p>
                </div>
                <div class="rounded-2xl border border-white/20 bg-white/10 px-4 py-3 text-sm backdrop-blur">
                    <div>Setelah memilih menu, customer akan lanjut ke pembayaran Midtrans.</div>
                    <div class="mt-1 text-white/80">Pesanan masuk ke outlet setelah pembayaran tervalidasi.</div>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-rose-900 shadow-sm">
                <div class="text-sm font-semibold uppercase tracking-[0.18em] text-rose-700">Periksa Input Pesanan</div>
                <ul class="mt-2 space-y-1 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('public-ordering.store', $orderingQr) }}" class="grid gap-6 lg:grid-cols-[1.6fr,0.8fr]">
            @csrf

            <section class="space-y-4">
                @forelse ($menuGroups as $category => $categoryMenus)
                    <div class="rounded-3xl bg-white p-4 shadow-sm ring-1 ring-black/5 sm:p-5">
                        <div class="mb-4 flex items-center justify-between gap-3 border-b border-stone-200 pb-3">
                            <div>
                                <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Kategori</div>
                                <h2 class="text-lg font-semibold text-stone-900">{{ $category }}</h2>
                            </div>
                            <div class="rounded-full bg-stone-100 px-3 py-1 text-xs font-medium text-stone-600">
                                {{ $categoryMenus->count() }} menu
                            </div>
                        </div>

                        <div class="space-y-4">
                            @foreach ($categoryMenus as $menu)
                                <article class="overflow-hidden rounded-2xl border border-stone-200 bg-stone-50/70">
                                    <div class="grid gap-4 p-4 sm:grid-cols-[140px,1fr] sm:p-5">
                                        <div class="overflow-hidden rounded-2xl bg-stone-200">
                                            @if ($menu->image_path)
                                                <img
                                                    src="{{ \Illuminate\Support\Facades\Storage::url($menu->image_path) }}"
                                                    alt="{{ $menu->name }}"
                                                    class="h-32 w-full object-cover sm:h-full"
                                                >
                                            @else
                                                <div class="flex h-32 items-center justify-center bg-gradient-to-br from-stone-300 to-stone-200 text-3xl font-semibold text-stone-600 sm:h-full">
                                                    {{ \Illuminate\Support\Str::substr($menu->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div>
                                            <div class="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                                                <div>
                                                    <h3 class="text-lg font-semibold text-stone-900">{{ $menu->name }}</h3>
                                                    @if ($menu->description)
                                                        <p class="mt-1 text-sm leading-6 text-stone-600">{{ $menu->description }}</p>
                                                    @endif
                                                </div>
                                                <div class="rounded-full bg-white px-3 py-1 text-xs font-medium text-stone-600 ring-1 ring-stone-200">
                                                    {{ $menu->variants->count() }} varian
                                                </div>
                                            </div>

                                            <div class="mt-4 space-y-3">
                                                @foreach ($menu->variants as $variant)
                                                    @php
                                                        $variantParts = array_filter([
                                                            $variant->kd_varian,
                                                            $variant->size_varian,
                                                            $variant->temperature,
                                                            $variant->sugar_level,
                                                            $variant->ice_level,
                                                        ]);
                                                        $currentQty = old('quantities.' . $variant->id, 0);
                                                    @endphp

                                                    <div class="rounded-2xl bg-white px-4 py-3 ring-1 ring-stone-200">
                                                        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                                            <div>
                                                                <div class="font-medium text-stone-900">
                                                                    {{ $menu->name }}
                                                                    @if ($variant->kd_varian)
                                                                        <span class="text-stone-500">| {{ $variant->kd_varian }}</span>
                                                                    @endif
                                                                </div>
                                                                @if (count($variantParts) > 0)
                                                                    <div class="mt-1 text-xs leading-5 text-stone-500">
                                                                        {{ implode(' | ', $variantParts) }}
                                                                    </div>
                                                                @endif
                                                                <div class="mt-2 text-sm font-semibold text-orange-600">
                                                                    Rp {{ number_format((float) $variant->price, 0, ',', '.') }}
                                                                </div>
                                                            </div>

                                                            <div class="flex items-center gap-2">
                                                                <button
                                                                    type="button"
                                                                    class="rounded-xl border border-stone-200 bg-stone-100 px-3 py-2 text-sm font-semibold text-stone-700 transition hover:bg-stone-200"
                                                                    data-qty-decrease
                                                                    data-target="qty-{{ $variant->id }}"
                                                                >
                                                                    -
                                                                </button>
                                                                <input
                                                                    id="qty-{{ $variant->id }}"
                                                                    type="number"
                                                                    min="0"
                                                                    max="99"
                                                                    step="1"
                                                                    name="quantities[{{ $variant->id }}]"
                                                                    value="{{ $currentQty }}"
                                                                    data-qty-input
                                                                    data-price="{{ (float) $variant->price }}"
                                                                    class="w-20 rounded-xl border border-stone-200 bg-white px-3 py-2 text-center text-sm font-semibold text-stone-900 outline-none ring-orange-500 transition focus:ring"
                                                                >
                                                                <button
                                                                    type="button"
                                                                    class="rounded-xl border border-orange-200 bg-orange-50 px-3 py-2 text-sm font-semibold text-orange-700 transition hover:bg-orange-100"
                                                                    data-qty-increase
                                                                    data-target="qty-{{ $variant->id }}"
                                                                >
                                                                    +
                                                                </button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="rounded-3xl bg-white px-6 py-10 text-center shadow-sm ring-1 ring-black/5">
                        <h2 class="text-lg font-semibold text-stone-900">Menu belum tersedia</h2>
                        <p class="mt-2 text-sm text-stone-600">Belum ada menu aktif yang bisa dipesan dari QR ini.</p>
                    </div>
                @endforelse
            </section>

            <aside class="space-y-4 lg:sticky lg:top-6 lg:self-start">
                <div class="rounded-3xl bg-white p-5 shadow-sm ring-1 ring-black/5">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-stone-400">Identitas Pemesan</div>
                    <div class="mt-4 space-y-4">
                        <div>
                            <label for="guest_name" class="mb-2 block text-sm font-medium text-stone-700">Nama</label>
                            <input
                                id="guest_name"
                                type="text"
                                name="guest_name"
                                value="{{ old('guest_name') }}"
                                maxlength="120"
                                required
                                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none ring-orange-500 transition focus:bg-white focus:ring"
                                placeholder="Contoh: Budi"
                            >
                        </div>
                        <div>
                            <label for="guest_phone" class="mb-2 block text-sm font-medium text-stone-700">Nomor HP</label>
                            <input
                                id="guest_phone"
                                type="text"
                                name="guest_phone"
                                value="{{ old('guest_phone') }}"
                                maxlength="50"
                                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none ring-orange-500 transition focus:bg-white focus:ring"
                                placeholder="Opsional"
                            >
                        </div>
                        <div>
                            <label for="notes" class="mb-2 block text-sm font-medium text-stone-700">Catatan</label>
                            <textarea
                                id="notes"
                                name="notes"
                                rows="4"
                                maxlength="1000"
                                class="w-full rounded-2xl border border-stone-200 bg-stone-50 px-4 py-3 text-sm outline-none ring-orange-500 transition focus:bg-white focus:ring"
                                placeholder="Catatan tambahan untuk pesanan"
                            >{{ old('notes') }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl bg-stone-900 p-5 text-white shadow-lg">
                    <div class="text-xs font-semibold uppercase tracking-[0.22em] text-white/60">Ringkasan Pesanan</div>
                    <div class="mt-4 grid grid-cols-2 gap-3">
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <div class="text-xs text-white/60">Total Item</div>
                            <div id="total-items" class="mt-1 text-2xl font-semibold">0</div>
                        </div>
                        <div class="rounded-2xl bg-white/10 px-4 py-3">
                            <div class="text-xs text-white/60">Estimasi Total</div>
                            <div id="estimated-total" class="mt-1 text-lg font-semibold">Rp 0</div>
                        </div>
                    </div>
                    <p class="mt-4 text-sm leading-6 text-white/75">
                        Sistem akan membuat order dan langsung mengarahkan customer ke pembayaran Midtrans.
                    </p>
                    <button
                        type="submit"
                        class="mt-5 w-full rounded-2xl bg-amber-400 px-4 py-3 text-sm font-semibold text-stone-900 transition hover:bg-amber-300"
                    >
                        Lanjut ke Pembayaran
                    </button>
                </div>
            </aside>
        </form>
    </div>

    <script>
        (() => {
            const inputs = Array.from(document.querySelectorAll('[data-qty-input]'));
            const totalItemsNode = document.getElementById('total-items');
            const estimatedTotalNode = document.getElementById('estimated-total');
            const money = new Intl.NumberFormat('id-ID');

            const updateSummary = () => {
                let totalItems = 0;
                let estimatedTotal = 0;

                for (const input of inputs) {
                    const qty = Math.max(parseInt(input.value || '0', 10) || 0, 0);
                    const price = parseFloat(input.dataset.price || '0');

                    totalItems += qty;
                    estimatedTotal += qty * price;
                }

                totalItemsNode.textContent = String(totalItems);
                estimatedTotalNode.textContent = `Rp ${money.format(estimatedTotal)}`;
            };

            document.querySelectorAll('[data-qty-decrease]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.target);

                    if (!input) {
                        return;
                    }

                    const nextValue = Math.max((parseInt(input.value || '0', 10) || 0) - 1, 0);
                    input.value = String(nextValue);
                    updateSummary();
                });
            });

            document.querySelectorAll('[data-qty-increase]').forEach((button) => {
                button.addEventListener('click', () => {
                    const input = document.getElementById(button.dataset.target);

                    if (!input) {
                        return;
                    }

                    const nextValue = Math.min((parseInt(input.value || '0', 10) || 0) + 1, 99);
                    input.value = String(nextValue);
                    updateSummary();
                });
            });

            for (const input of inputs) {
                input.addEventListener('input', updateSummary);
            }

            updateSummary();
        })();
    </script>
</body>
</html>
