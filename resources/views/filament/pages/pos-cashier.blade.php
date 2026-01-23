<x-filament-panels::page>
    <style>
        .pos-shell {
            display: grid;
            gap: 24px;
            grid-template-columns: 1fr;
        }
        @media (min-width: 1024px) {
            .pos-shell {
                grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
            }
        }
        .pos-hero {
            border-radius: 18px;
            padding: 18px;
            background: radial-gradient(circle at top left, rgba(255, 196, 0, 0.15), transparent 55%),
                linear-gradient(135deg, rgba(17, 24, 39, 0.95), rgba(15, 23, 42, 0.65));
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
        .pos-hero-title {
            font-size: 22px;
            font-weight: 600;
            color: #f8fafc;
        }
        .pos-hero-subtitle {
            font-size: 13px;
            color: #cbd5f5;
        }
        .pos-input-row {
            display: grid;
            gap: 12px;
            grid-template-columns: 1fr;
            margin-top: 14px;
        }
        @media (min-width: 768px) {
            .pos-input-row {
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }
        }
        .pos-grid {
            display: grid;
            gap: 16px;
            margin-top: 18px;
            grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
        }
        .pos-card {
            border-radius: 16px;
            background: #0f172a;
            border: 1px solid rgba(255, 255, 255, 0.06);
            overflow: hidden;
            transition: transform 0.2s ease, border-color 0.2s ease;
        }
        .pos-card:hover {
            transform: translateY(-2px);
            border-color: rgba(245, 158, 11, 0.6);
        }
        .pos-card-img {
            aspect-ratio: 1 / 1;
            background: rgba(15, 23, 42, 0.85);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #94a3b8;
            font-size: 12px;
        }
        .pos-card-img img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        .pos-card-body {
            padding: 14px;
            display: grid;
            gap: 10px;
        }
        .pos-card-title {
            font-size: 14px;
            font-weight: 600;
            color: #f8fafc;
        }
        .pos-card-meta {
            font-size: 12px;
            color: #94a3b8;
        }
        .pos-card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pos-price {
            font-size: 14px;
            font-weight: 600;
            color: #fbbf24;
        }
        .pos-cart {
            border-radius: 18px;
            padding: 18px;
            background: #0b1220;
            border: 1px solid rgba(255, 255, 255, 0.08);
            position: sticky;
            top: 90px;
        }
        .pos-cart-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .pos-cart-title {
            font-size: 18px;
            font-weight: 600;
            color: #f8fafc;
        }
        .pos-cart-count {
            font-size: 12px;
            padding: 2px 8px;
            border-radius: 999px;
            background: rgba(148, 163, 184, 0.16);
            color: #e2e8f0;
        }
        .pos-cart-item {
            border-radius: 12px;
            padding: 12px;
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid rgba(255, 255, 255, 0.06);
            display: grid;
            gap: 8px;
        }
        .pos-cart-empty {
            border-radius: 12px;
            padding: 16px;
            text-align: center;
            border: 1px dashed rgba(148, 163, 184, 0.4);
            color: #94a3b8;
        }
        .pos-cart-summary {
            margin-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            padding-top: 12px;
            display: grid;
            gap: 8px;
            font-size: 13px;
            color: #cbd5f5;
        }
        .pos-cart-summary strong {
            color: #f8fafc;
        }
        .pos-action-row {
            margin-top: 16px;
            display: grid;
            gap: 10px;
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    </style>

    <div class="pos-shell">
        <div>
            <div class="pos-hero">
                <div class="pos-hero-title">POS Kasir</div>
                <div class="pos-hero-subtitle">Pilih menu, lalu tambah ke cart.</div>

                <div class="pos-input-row">
                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model.live="search"
                            placeholder="Cari menu atau kode..."
                        />
                    </x-filament::input.wrapper>

                    <x-filament::input.wrapper>
                        <x-filament::input
                            wire:model.live="barcode"
                            placeholder="Scan barcode..."
                        />
                    </x-filament::input.wrapper>
                </div>
            </div>

            <div class="pos-grid">
                @forelse ($this->menuVariants as $variant)
                    <div class="pos-card">
                        <div class="pos-card-img">
                            @if ($variant->menu?->image_path)
                                <img
                                    src="{{ asset('storage/' . ltrim($variant->menu->image_path, '/')) }}"
                                    alt="{{ $variant->menu?->name ?? 'Menu' }}"
                                    loading="lazy"
                                />
                            @else
                                No Image
                            @endif
                        </div>
                        <div class="pos-card-body">
                            <div>
                                <div class="pos-card-title">
                                    {{ $variant->menu?->name ?? 'Menu' }}
                                </div>
                                <div class="pos-card-meta">
                                    {{ $variant->kd_varian }} • {{ $variant->menu?->unit ?? 'Satuan' }}
                                </div>
                            </div>
                            <div class="pos-card-footer">
                                <div class="pos-price">
                                    Rp {{ number_format((float) $variant->price, 0, ',', '.') }}
                                </div>
                                <x-filament::button
                                    size="xs"
                                    color="primary"
                                    wire:click="addToCart({{ $variant->id }})"
                                >
                                    Tambah
                                </x-filament::button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="pos-cart-empty">
                        Menu belum tersedia.
                    </div>
                @endforelse
            </div>
        </div>

        <div>
            <div class="pos-cart">
                <div class="pos-cart-header">
                    <div class="pos-cart-title">Cart</div>
                    <div class="pos-cart-count">{{ count($this->cartItems) }} item</div>
                </div>

                <div class="mt-4 space-y-3">
                    @forelse ($this->cartItems as $item)
                        <div class="pos-cart-item">
                            <div class="text-sm font-semibold text-gray-100">
                                {{ $item['label'] }}
                            </div>
                            <div class="flex items-center justify-between text-xs text-gray-400">
                                <span>
                                    Rp {{ number_format((float) $item['price'], 0, ',', '.') }}
                                </span>
                                <span>
                                    Qty: {{ $item['qty'] }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <div class="inline-flex items-center gap-2">
                                    <x-filament::button
                                        size="xs"
                                        color="gray"
                                        wire:click="decrementQty({{ $item['id'] }})"
                                    >
                                        -
                                    </x-filament::button>
                                    <x-filament::button
                                        size="xs"
                                        color="gray"
                                        wire:click="incrementQty({{ $item['id'] }})"
                                    >
                                        +
                                    </x-filament::button>
                                </div>
                                <x-filament::button
                                    size="xs"
                                    color="danger"
                                    wire:click="removeFromCart({{ $item['id'] }})"
                                >
                                    Hapus
                                </x-filament::button>
                            </div>
                        </div>
                    @empty
                        <div class="pos-cart-empty">
                            Cart kosong.
                        </div>
                    @endforelse
                </div>

                <div class="pos-cart-summary">
                    <div class="flex items-center justify-between">
                        <span>Subtotal</span>
                        <strong>Rp {{ number_format((float) $this->cartSubtotal, 0, ',', '.') }}</strong>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span>Diskon</span>
                        <span>Rp 0</span>
                    </div>
                    <div class="flex items-center justify-between text-xs">
                        <span>Pajak</span>
                        <span>Rp 0</span>
                    </div>
                </div>

                <div class="pos-action-row">
                    <x-filament::button color="gray" wire:click="clearCart">
                        Clear
                    </x-filament::button>
                    <x-filament::button color="primary" :disabled="count($this->cartItems) === 0">
                        Bayar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>
</x-filament-panels::page>
