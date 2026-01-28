<x-filament-panels::page>
    {{-- Custom Styles for POS --}}
    <style>
        .pos-container {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 1024px) {
            .pos-container {
                grid-template-columns: 2fr 1fr;
            }
        }
        .pos-products-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
        }
        @media (min-width: 640px) {
            .pos-products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (min-width: 1024px) {
            .pos-products-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (min-width: 1280px) {
            .pos-products-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        .pos-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .dark .pos-card {
            background: rgba(17, 24, 39, 0.8);
            border-color: rgba(55, 65, 81, 0.5);
        }
        .pos-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 24px -8px rgba(0,0,0,0.15);
            border-color: var(--primary-500);
        }
        .pos-card-image {
            aspect-ratio: 1;
            background: var(--gray-100);
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }
        .dark .pos-card-image {
            background: rgba(31, 41, 55, 0.8);
        }
        .pos-card-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        .pos-card:hover .pos-card-image img {
            transform: scale(1.08);
        }
        .pos-cart-panel {
            position: sticky;
            top: 5rem;
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            overflow: hidden;
        }
        .dark .pos-cart-panel {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(55, 65, 81, 0.5);
        }
        .pos-qty-control {
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
            background: var(--gray-100);
            border-radius: 0.5rem;
            padding: 0.125rem;
        }
        .dark .pos-qty-control {
            background: rgba(31, 41, 55, 0.8);
        }
        .pos-qty-btn {
            width: 2rem;
            height: 2rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 0.375rem;
            color: var(--gray-600);
            transition: background 0.15s ease;
        }
        .pos-qty-btn:hover {
            background: var(--gray-200);
        }
        .dark .pos-qty-btn {
            color: var(--gray-400);
        }
        .dark .pos-qty-btn:hover {
            background: rgba(55, 65, 81, 0.8);
        }
        .pos-qty-input {
            width: 3rem;
            text-align: center;
            background: transparent;
            border: none;
            font-weight: 500;
            color: var(--gray-900);
        }
        .dark .pos-qty-input {
            color: var(--gray-100);
        }
        .pos-qty-input:focus {
            outline: none;
        }
    </style>

    <div class="pos-container">
        {{-- LEFT: Products --}}
        <div>
            {{-- Header --}}
            <div style="background: linear-gradient(135deg, var(--primary-600), var(--primary-800)); border-radius: 1rem; padding: 1.5rem; margin-bottom: 1.5rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: -2rem; right: -2rem; width: 8rem; height: 8rem; background: rgba(255,255,255,0.1); border-radius: 50%; filter: blur(40px);"></div>
                <div style="position: relative; z-index: 1;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.75rem; height: 1.75rem; color: rgba(255,255,255,0.9);">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                        </svg>
                        <h1 style="font-size: 1.5rem; font-weight: 700; color: white;">POS Kasir</h1>
                    </div>
                    <p style="font-size: 0.875rem; color: rgba(255,255,255,0.75);">Pilih menu untuk menambahkan ke keranjang</p>

                    {{-- Search Row --}}
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-top: 1.25rem;">
                        <div style="position: relative;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 1.25rem; height: 1.25rem; color: rgba(255,255,255,0.5);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                            </svg>
                            <input
                                type="text"
                                wire:model.live.debounce.300ms="search"
                                placeholder="Cari menu..."
                                style="width: 100%; padding: 0.625rem 0.75rem 0.625rem 2.5rem; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem; color: white; font-size: 0.875rem;"
                            />
                        </div>
                        <div style="position: relative;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); width: 1.25rem; height: 1.25rem; color: rgba(255,255,255,0.5);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 3.75 9.375v-4.5ZM3.75 14.625c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5a1.125 1.125 0 0 1-1.125-1.125v-4.5ZM13.5 4.875c0-.621.504-1.125 1.125-1.125h4.5c.621 0 1.125.504 1.125 1.125v4.5c0 .621-.504 1.125-1.125 1.125h-4.5A1.125 1.125 0 0 1 13.5 9.375v-4.5Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 6.75h.75v.75h-.75v-.75ZM6.75 16.5h.75v.75h-.75v-.75ZM16.5 6.75h.75v.75h-.75v-.75ZM13.5 13.5h.75v.75h-.75v-.75ZM13.5 19.5h.75v.75h-.75v-.75ZM19.5 13.5h.75v.75h-.75v-.75ZM19.5 19.5h.75v.75h-.75v-.75ZM16.5 16.5h.75v.75h-.75v-.75Z" />
                            </svg>
                            <input
                                type="text"
                                wire:model.live="barcode"
                                placeholder="Scan barcode..."
                                autofocus
                                style="width: 100%; padding: 0.625rem 0.75rem 0.625rem 2.5rem; background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.2); border-radius: 0.5rem; color: white; font-size: 0.875rem;"
                            />
                        </div>
                    </div>
                </div>
            </div>

            {{-- Product Grid --}}
            <div class="pos-products-grid">
                @forelse ($this->menuVariants as $variant)
                    <div
                        wire:key="variant-{{ $variant->id }}"
                        wire:click="addToCart({{ $variant->id }})"
                        class="pos-card"
                    >
                        <div class="pos-card-image">
                            @if ($variant->menu?->image_path)
                                <img
                                    src="{{ asset('storage/' . ltrim($variant->menu->image_path, '/')) }}"
                                    alt="{{ $variant->menu?->name ?? 'Menu' }}"
                                    loading="lazy"
                                />
                            @else
                                <div style="display: flex; flex-direction: column; align-items: center; color: var(--gray-400);">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 2.5rem; height: 2.5rem;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                                    </svg>
                                    <span style="font-size: 0.75rem; margin-top: 0.25rem;">No Image</span>
                                </div>
                            @endif
                        </div>
                        <div style="padding: 0.875rem;">
                            <h3 style="font-size: 0.875rem; font-weight: 600; color: var(--gray-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="dark:text-gray-100">
                                {{ $variant->menu?->name ?? 'Menu' }}
                            </h3>
                            <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.125rem;">
                                {{ $variant->kd_varian }} • {{ $variant->menu?->unit ?? 'Pcs' }}
                            </p>
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem;">
                                <span style="font-size: 0.875rem; font-weight: 700; color: var(--primary-600);">
                                    Rp {{ number_format((float) $variant->price, 0, ',', '.') }}
                                </span>
                                <span style="display: inline-flex; align-items: center; justify-content: center; width: 1.75rem; height: 1.75rem; background: var(--primary-500); color: white; border-radius: 9999px; font-size: 1rem; font-weight: bold;">
                                    +
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div style="grid-column: 1 / -1; padding: 4rem 2rem; text-align: center;">
                        <div style="width: 5rem; height: 5rem; margin: 0 auto 1rem; background: var(--gray-100); border-radius: 9999px; display: flex; align-items: center; justify-content: center;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 2.5rem; height: 2.5rem; color: var(--gray-400);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m20.25 7.5-.625 10.632a2.25 2.25 0 0 1-2.247 2.118H6.622a2.25 2.25 0 0 1-2.247-2.118L3.75 7.5m6 4.125 2.25 2.25m0 0 2.25 2.25M12 13.875l2.25-2.25M12 13.875l-2.25 2.25M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
                            </svg>
                        </div>
                        <h3 style="font-size: 1.125rem; font-weight: 600; color: var(--gray-700);">Menu Tidak Ditemukan</h3>
                        <p style="font-size: 0.875rem; color: var(--gray-500); margin-top: 0.5rem;">Coba ubah kata kunci pencarian.</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- RIGHT: Cart --}}
        <div>
            <div class="pos-cart-panel">
                {{-- Cart Header --}}
                <div style="padding: 1rem 1.25rem; background: linear-gradient(to right, var(--gray-100), var(--gray-50)); border-bottom: 1px solid var(--gray-200);" class="dark:bg-gray-800">
                    <div style="display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1.25rem; height: 1.25rem; color: var(--primary-500);">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007ZM8.625 10.5a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm7.5 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
                            </svg>
                            <h2 style="font-size: 1.125rem; font-weight: 700; color: var(--gray-900);" class="dark:text-white">Keranjang</h2>
                        </div>
                        <span style="padding: 0.25rem 0.75rem; background: var(--primary-100); color: var(--primary-800); font-size: 0.75rem; font-weight: 500; border-radius: 9999px;">
                            {{ count($this->cartItems) }} item
                        </span>
                    </div>
                </div>

                {{-- Cart Items --}}
                <div style="padding: 1rem; max-height: 24rem; overflow-y: auto;">
                    @forelse ($this->cartItems as $item)
                        <div
                            wire:key="cart-{{ $item['id'] }}"
                            style="padding: 0.875rem; background: var(--gray-100); border-radius: 0.75rem; margin-bottom: 0.75rem;"
                            class="dark:bg-gray-800"
                        >
                            <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.5rem;">
                                <div style="flex: 1; min-width: 0;">
                                    <h4 style="font-size: 0.875rem; font-weight: 600; color: var(--gray-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;" class="dark:text-gray-100">
                                        {{ $item['label'] }}
                                    </h4>
                                    <p style="font-size: 0.75rem; color: var(--gray-500); margin-top: 0.125rem;">
                                        @ Rp {{ number_format((float) $item['price'], 0, ',', '.') }}
                                    </p>
                                </div>
                                <button
                                    wire:click="removeFromCart({{ $item['id'] }})"
                                    style="padding: 0.375rem; border-radius: 0.5rem; color: var(--danger-500); transition: background 0.15s;"
                                    onmouseover="this.style.background='rgba(239,68,68,0.1)'"
                                    onmouseout="this.style.background='transparent'"
                                    title="Hapus"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 1rem; height: 1rem;">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" />
                                    </svg>
                                </button>
                            </div>

                            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 0.75rem;">
                                <div class="pos-qty-control">
                                    <button wire:click="decrementQty({{ $item['id'] }})" class="pos-qty-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                            <path fill-rule="evenodd" d="M4 10a.75.75 0 0 1 .75-.75h10.5a.75.75 0 0 1 0 1.5H4.75A.75.75 0 0 1 4 10Z" clip-rule="evenodd" />
                                        </svg>
                                    </button>
                                    <input
                                        type="number"
                                        min="1"
                                        value="{{ $item['qty'] }}"
                                        wire:change="updateQty({{ $item['id'] }}, $event.target.value)"
                                        class="pos-qty-input"
                                    />
                                    <button wire:click="incrementQty({{ $item['id'] }})" class="pos-qty-btn">
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" style="width: 1rem; height: 1rem;">
                                            <path d="M10.75 4.75a.75.75 0 0 0-1.5 0v4.5h-4.5a.75.75 0 0 0 0 1.5h4.5v4.5a.75.75 0 0 0 1.5 0v-4.5h4.5a.75.75 0 0 0 0-1.5h-4.5v-4.5Z" />
                                        </svg>
                                    </button>
                                </div>
                                <span style="font-size: 0.875rem; font-weight: 700; color: var(--primary-600);">
                                    Rp {{ number_format((float) ($item['price'] * $item['qty']), 0, ',', '.') }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="padding: 3rem 1rem; text-align: center;">
                            <div style="width: 4rem; height: 4rem; margin: 0 auto 0.75rem; background: var(--gray-100); border-radius: 9999px; display: flex; align-items: center; justify-content: center;">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" style="width: 2rem; height: 2rem; color: var(--gray-400);">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z" />
                                </svg>
                            </div>
                            <p style="font-size: 0.875rem; font-weight: 500; color: var(--gray-600);">Keranjang kosong</p>
                            <p style="font-size: 0.75rem; color: var(--gray-400); margin-top: 0.25rem;">Klik produk untuk menambahkan</p>
                        </div>
                    @endforelse
                </div>

                {{-- Summary --}}
                <div style="padding: 1rem 1.25rem; background: var(--gray-100); border-top: 1px solid var(--gray-200);" class="dark:bg-gray-800/50">
                    <div style="display: flex; justify-content: space-between; font-size: 0.875rem; margin-bottom: 0.5rem;">
                        <span style="color: var(--gray-600);">Subtotal</span>
                        <span style="font-weight: 600; color: var(--gray-900);" class="dark:text-white">Rp {{ number_format((float) $this->cartSubtotal, 0, ',', '.') }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem; margin-bottom: 0.25rem;">
                        <span style="color: var(--gray-500);">Diskon</span>
                        <span style="color: var(--gray-500);">Rp 0</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; font-size: 0.75rem;">
                        <span style="color: var(--gray-500);">Pajak</span>
                        <span style="color: var(--gray-500);">Rp 0</span>
                    </div>

                    <div style="margin-top: 0.75rem; padding-top: 0.75rem; border-top: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;">
                        <span style="font-size: 1rem; font-weight: 700; color: var(--gray-900);" class="dark:text-white">Total</span>
                        <span style="font-size: 1.25rem; font-weight: 700; color: var(--primary-600);">Rp {{ number_format((float) $this->cartSubtotal, 0, ',', '.') }}</span>
                    </div>
                </div>

                {{-- Actions --}}
                <div style="padding: 1rem 1.25rem; border-top: 1px solid var(--gray-200); display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <x-filament::button
                        color="gray"
                        wire:click="clearCart"
                        :disabled="count($this->cartItems) === 0"
                    >
                        Bersihkan
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="checkout"
                        :disabled="count($this->cartItems) === 0"
                    >
                        Bayar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>

@push('scripts')
    @php
        $snapSrc = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="{{ $snapSrc }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            Livewire.on('midtrans-snap', ({ token }) => {
                if (!window.snap) {
                    console.error('Midtrans snap.js tidak termuat.');
                    return;
                }

                window.snap.pay(token, {
                    onSuccess: function () {
                        console.info('Pembayaran berhasil.');
                    },
                    onPending: function () {
                        console.info('Pembayaran pending.');
                    },
                    onError: function () {
                        console.error('Pembayaran gagal.');
                    },
                    onClose: function () {
                        console.info('Popup ditutup.');
                    }
                });
            });
        });
    </script>
@endpush
