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
        .pos-qty-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }
        .dark .pos-qty-btn {
            color: var(--gray-400);
        }
        .dark .pos-qty-btn:hover {
            background: rgba(55, 65, 81, 0.8);
        }
        .pos-stock-meta {
            margin-top: 0.25rem;
            display: flex;
            align-items: center;
            gap: 0.4rem;
            flex-wrap: wrap;
        }
        .pos-stock-count {
            font-size: 0.7rem;
            color: var(--gray-500);
        }
        .pos-stock-badge {
            display: inline-flex;
            align-items: center;
            border-radius: 9999px;
            padding: 0.125rem 0.5rem;
            font-size: 0.65rem;
            font-weight: 600;
            line-height: 1.2;
        }
        .pos-stock-badge.stock-ok {
            background: rgba(22, 163, 74, 0.15);
            color: #166534;
        }
        .pos-stock-badge.stock-low {
            background: rgba(245, 158, 11, 0.2);
            color: #92400e;
        }
        .pos-stock-badge.stock-out {
            background: rgba(220, 38, 38, 0.16);
            color: #991b1b;
        }
        .pos-stock-badge.stock-untracked {
            background: rgba(107, 114, 128, 0.16);
            color: #374151;
        }
        .dark .pos-stock-count {
            color: #9ca3af;
        }
        .dark .pos-stock-badge.stock-ok {
            background: rgba(34, 197, 94, 0.22);
            color: #86efac;
        }
        .dark .pos-stock-badge.stock-low {
            background: rgba(245, 158, 11, 0.22);
            color: #fcd34d;
        }
        .dark .pos-stock-badge.stock-out {
            background: rgba(239, 68, 68, 0.24);
            color: #fca5a5;
        }
        .dark .pos-stock-badge.stock-untracked {
            background: rgba(107, 114, 128, 0.28);
            color: #d1d5db;
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
        .pos-payment-modal {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #111827;
        }
        .pos-payment-modal-header {
            border-bottom: 1px solid #e5e7eb;
        }
        .pos-payment-modal-footer {
            border-top: 1px solid #e5e7eb;
        }
        .pos-payment-title {
            color: #111827;
        }
        .pos-payment-muted {
            color: #4b5563;
        }
        .pos-payment-option {
            color: #111827;
            border: 1px solid #d1d5db;
            background: #ffffff;
        }
        .pos-payment-option.active {
            border-color: var(--primary-500);
            background: rgba(59, 130, 246, 0.14);
        }
        .pos-payment-option[disabled] {
            opacity: .55;
            cursor: not-allowed;
        }
        .pos-payment-cash-box {
            border: 1px solid #d1d5db;
            background: #f9fafb;
        }
        .pos-payment-input {
            color: #111827;
            border: 1px solid #d1d5db;
            background: #ffffff;
        }
        .pos-attendance-panel {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            padding: 1rem 1.125rem;
            margin-bottom: 1rem;
        }
        .dark .pos-attendance-panel {
            background: rgba(17, 24, 39, 0.85);
            border-color: rgba(55, 65, 81, 0.5);
        }
        .pos-attendance-status {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            font-size: .82rem;
            font-weight: 600;
            border-radius: 9999px;
            padding: .3rem .65rem;
        }
        .pos-attendance-status .dot {
            width: .55rem;
            height: .55rem;
            border-radius: 9999px;
        }
        .pos-attendance-status.checked-in {
            background: rgba(22, 163, 74, 0.15);
            color: #166534;
        }
        .pos-attendance-status.checked-in .dot {
            background: #16a34a;
        }
        .pos-attendance-status.not-checked {
            background: rgba(217, 119, 6, 0.18);
            color: #92400e;
        }
        .pos-attendance-status.not-checked .dot {
            background: #d97706;
        }
        .dark .pos-attendance-status.checked-in {
            background: rgba(34, 197, 94, 0.24);
            color: #bbf7d0;
        }
        .dark .pos-attendance-status.not-checked {
            background: rgba(245, 158, 11, 0.24);
            color: #fcd34d;
        }
        .pos-attendance-actions {
            display: flex;
            align-items: center;
            gap: .5rem;
            flex-wrap: wrap;
            margin-top: .75rem;
        }
        .pos-attendance-btn {
            border: 1px solid #d1d5db;
            background: #ffffff;
            color: #111827;
            border-radius: .625rem;
            padding: .48rem .85rem;
            font-size: .82rem;
            font-weight: 600;
            transition: all .15s ease;
        }
        .pos-attendance-btn:hover {
            border-color: #9ca3af;
            background: #f9fafb;
        }
        .pos-attendance-btn[disabled] {
            opacity: .55;
            cursor: not-allowed;
        }
        .dark .pos-attendance-btn {
            border-color: #374151;
            background: #111827;
            color: #e5e7eb;
        }
        .pos-printer-badge {
            position: fixed;
            top: 5.75rem;
            right: 1.25rem;
            width: 2.75rem;
            height: 2.75rem;
            border-radius: 9999px;
            border: 2px solid rgba(255, 255, 255, 0.9);
            box-shadow: 0 10px 20px -12px rgba(0, 0, 0, 0.45);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 50;
            transition: transform .15s ease, box-shadow .15s ease;
        }
        .pos-printer-badge:hover {
            transform: translateY(-1px);
            box-shadow: 0 14px 26px -14px rgba(0, 0, 0, 0.55);
        }
        .pos-printer-badge.status-checking {
            background: #6b7280;
        }
        .pos-printer-badge.status-idle {
            background: #6b7280;
        }
        .pos-printer-badge.status-offline {
            background: #dc2626;
        }
        .pos-printer-badge.status-service-only {
            background: #d97706;
        }
        .pos-printer-badge.status-ready {
            background: #16a34a;
        }
        .pos-printer-badge.status-error {
            background: #b91c1c;
        }
        .pos-printer-modal-backdrop {
            position: fixed;
            inset: 0;
            z-index: 9900;
            background: rgba(0, 0, 0, 0.6);
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .pos-printer-modal {
            width: min(36rem, 100%);
            border-radius: 1rem;
            background: #ffffff;
            border: 1px solid #e5e7eb;
            overflow: hidden;
            color: #111827;
        }
        .pos-printer-modal-header {
            padding: 1rem 1.25rem;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        .pos-printer-modal-body {
            padding: 1rem 1.25rem;
        }
        .pos-printer-meta {
            font-size: 0.875rem;
            color: #4b5563;
        }
        .pos-printer-list {
            margin-top: 0.75rem;
            max-height: 16rem;
            overflow: auto;
            border: 1px solid #e5e7eb;
            border-radius: 0.75rem;
            padding: 0.5rem;
            background: #f9fafb;
        }
        .pos-printer-item {
            width: 100%;
            border: 1px solid #d1d5db;
            background: #ffffff;
            border-radius: 0.625rem;
            padding: 0.625rem 0.75rem;
            text-align: left;
            font-size: 0.875rem;
            color: #111827;
            margin-bottom: 0.5rem;
            cursor: pointer;
            transition: border-color .15s ease, background .15s ease;
        }
        .pos-printer-item:last-child {
            margin-bottom: 0;
        }
        .pos-printer-item:hover {
            border-color: var(--primary-500);
            background: rgba(59, 130, 246, 0.06);
        }
        .pos-printer-item.active {
            border-color: var(--primary-500);
            background: rgba(59, 130, 246, 0.12);
        }
        .pos-printer-modal-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid #e5e7eb;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }
        .pos-print-notice-container {
            position: fixed;
            top: 1rem;
            right: 1rem;
            z-index: 10050;
            display: flex;
            flex-direction: column;
            gap: 0.5rem;
        }
        .pos-print-notice {
            min-width: 14rem;
            max-width: 22rem;
            border-radius: 0.625rem;
            padding: 0.625rem 0.875rem;
            color: #ffffff;
            font-size: 0.85rem;
            font-weight: 600;
            box-shadow: 0 12px 24px -14px rgba(0, 0, 0, 0.45);
            opacity: 1;
            transform: translateY(0);
            transition: opacity .2s ease, transform .2s ease;
        }
        .pos-print-notice.success {
            background: #15803d;
        }
        .pos-print-notice.error {
            background: #b91c1c;
        }
        .pos-print-notice.info {
            background: #1d4ed8;
        }
    </style>

    <button
        id="printerStatusBadge"
        type="button"
        class="pos-printer-badge status-idle"
        title="Belum cek koneksi printer"
        aria-label="Status koneksi printer"
    >
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 1.15rem; height: 1.15rem; color: #ffffff;">
            <path fill-rule="evenodd" d="M3 6.75A2.25 2.25 0 0 1 5.25 4.5h13.5A2.25 2.25 0 0 1 21 6.75v7.5A2.25 2.25 0 0 1 18.75 16.5h-1.5v1.875A1.125 1.125 0 0 1 16.125 19.5H7.875a1.125 1.125 0 0 1-1.125-1.125V16.5h-1.5A2.25 2.25 0 0 1 3 14.25v-7.5ZM7.5 16.5v.75h9v-.75h-9Zm9.75-2.25h1.5a.75.75 0 0 0 .75-.75v-6a.75.75 0 0 0-.75-.75H5.25a.75.75 0 0 0-.75.75v6c0 .414.336.75.75.75h1.5v-1.125A1.125 1.125 0 0 1 7.875 12h8.25a1.125 1.125 0 0 1 1.125 1.125v1.125Z" clip-rule="evenodd" />
        </svg>
    </button>

    <div id="printerModalBackdrop" class="pos-printer-modal-backdrop">
        <div class="pos-printer-modal">
            <div class="pos-printer-modal-header">
                <div>
                    <h3 style="font-size: 1.05rem; font-weight: 700; color: #111827;">Koneksi Printer</h3>
                    <p id="printerModalSummary" class="pos-printer-meta">Memeriksa koneksi printer...</p>
                </div>
                <button id="closePrinterModalButton" type="button" style="color: #6b7280;" title="Tutup">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" style="width: 1.25rem; height: 1.25rem;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="pos-printer-modal-body">
                <div class="pos-printer-meta">
                    Printer terpilih: <strong id="selectedPrinterName">-</strong>
                </div>
                <div class="pos-printer-list" id="printerListContainer">
                    <div class="pos-printer-meta">Belum ada daftar printer.</div>
                </div>
            </div>
            <div class="pos-printer-modal-footer">
                <x-filament::button color="gray" id="refreshPrinterButton" type="button">
                    Refresh
                </x-filament::button>
                <x-filament::button color="gray" id="disconnectPrinterButton" type="button">
                    Unpair
                </x-filament::button>
                <x-filament::button color="primary" id="testPrinterButton" type="button">
                    Test Print
                </x-filament::button>
                <x-filament::button color="primary" id="closePrinterModalFooterButton" type="button">
                    Tutup
                </x-filament::button>
            </div>
        </div>
    </div>

    @php
        $cartItems = $this->cartItems;
        $cartItemCount = count($cartItems);
    @endphp

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

            @php
                $workedMinutes = (int) ($attendanceWorkedMinutesToday ?? 0);
                $workedHoursLabel = sprintf('%02d:%02d', intdiv($workedMinutes, 60), $workedMinutes % 60);
            @endphp
            <div class="pos-attendance-panel">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: .75rem; flex-wrap: wrap;">
                    <div style="display: flex; align-items: center; gap: .55rem; flex-wrap: wrap;">
                        <span class="pos-attendance-status {{ $isCheckedIn ? 'checked-in' : 'not-checked' }}">
                            <span class="dot"></span>
                            {{ $isCheckedIn ? 'Sudah Check-in' : 'Belum Check-in' }}
                        </span>
                        @if ($attendanceCheckedInAt)
                            <span style="font-size: .78rem; color: var(--gray-500);">
                                Check-in: {{ $attendanceCheckedInAt }}
                            </span>
                        @endif
                    </div>
                    <span style="font-size: .78rem; color: var(--gray-500);">
                        Jam kerja hari ini: <strong style="color: var(--gray-700);">{{ $workedHoursLabel }}</strong>
                    </span>
                </div>
                <div style="margin-top: .45rem; font-size: .75rem; color: var(--gray-500);">
                    Absensi membutuhkan izin lokasi GPS browser.
                </div>
                <div class="pos-attendance-actions">
                    <button
                        type="button"
                        id="attendanceCheckInButton"
                        data-attendance-action="check_in"
                        class="pos-attendance-btn"
                        @disabled($isCheckedIn)
                    >
                        Check-in
                    </button>
                    <button
                        type="button"
                        id="attendanceCheckOutButton"
                        data-attendance-action="check_out"
                        class="pos-attendance-btn"
                        @disabled(! $isCheckedIn)
                    >
                        Check-out
                    </button>
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
                                {{ $variant->kd_varian }} - {{ $variant->menu?->unit ?? 'Pcs' }}
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
                            {{ $cartItemCount }} item
                        </span>
                    </div>
                </div>

                {{-- Cart Items --}}
                <div style="padding: 1rem; max-height: 24rem; overflow-y: auto;">
                    @forelse ($cartItems as $item)
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
                                    <p style="font-size: 0.72rem; color: var(--gray-500); margin-top: 0.125rem;">
                                        {{ $item['variant_code'] ?? '-' }} - {{ $item['unit'] ?? 'Pcs' }}
                                    </p>
                                    <div class="pos-stock-meta">
                                        @if ($item['is_stock_tracked'] ?? false)
                                            <span class="pos-stock-count">
                                                Sisa: {{ number_format((float) ($item['stock_left_display'] ?? 0), 0, ',', '.') }}
                                            </span>
                                        @else
                                            <span class="pos-stock-count">Stok tidak dilacak</span>
                                        @endif
                                        <span class="pos-stock-badge stock-{{ $item['stock_status'] ?? 'untracked' }}">
                                            {{ $item['stock_status_label'] ?? 'Tidak dilacak' }}
                                        </span>
                                    </div>
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
                                    <button
                                        wire:click="incrementQty({{ $item['id'] }})"
                                        class="pos-qty-btn"
                                        @disabled(! ($item['can_increment'] ?? true))
                                        title="{{ ($item['can_increment'] ?? true) ? 'Tambah qty' : 'Qty sudah mencapai batas stok' }}"
                                    >
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
                        :disabled="$cartItemCount === 0"
                    >
                        Bersihkan
                    </x-filament::button>

                    <x-filament::button
                        color="primary"
                        wire:click="openPaymentModal"
                        :disabled="$cartItemCount === 0"
                    >
                        Bayar
                    </x-filament::button>
                </div>
            </div>
        </div>
    </div>

    @if ($showPaymentModal)
        <div
            style="position: fixed; inset: 0; z-index: 9999; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1rem;"
            wire:click.self="closePaymentModal"
        >
            <div style="width: min(32rem, 100%); border-radius: 1rem; overflow: hidden;" class="pos-payment-modal">
                <div style="padding: 1rem 1.25rem;" class="pos-payment-modal-header">
                    <h3 style="font-size: 1.125rem; font-weight: 700;" class="pos-payment-title">Pilih Metode Pembayaran</h3>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;" class="pos-payment-muted">
                        Total transaksi: <strong>Rp {{ number_format((float) $this->cartSubtotal, 0, ',', '.') }}</strong>
                    </p>
                    @if ($isOffline)
                        <p style="margin-top: 0.5rem; font-size: 0.75rem; color: #f59e0b;">
                            Mode offline aktif. Midtrans dinonaktifkan, hanya pembayaran tunai tersedia.
                        </p>
                    @endif
                </div>

                <div style="padding: 1rem 1.25rem;">
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                        <button
                            type="button"
                            wire:click="$set('selectedPaymentMethod', 'cash')"
                            style="padding: 0.75rem; border-radius: 0.75rem; text-align: left;"
                            class="pos-payment-option {{ $selectedPaymentMethod === 'cash' ? 'active' : '' }}"
                        >
                            <div style="font-weight: 600; color: #111827;">Tunai</div>
                            <div style="font-size: 0.75rem;" class="pos-payment-muted">Bayar langsung di kasir</div>
                        </button>

                        <button
                            type="button"
                            wire:click="$set('selectedPaymentMethod', 'midtrans')"
                            @disabled($isOffline)
                            style="padding: 0.75rem; border-radius: 0.75rem; text-align: left;"
                            class="pos-payment-option {{ $selectedPaymentMethod === 'midtrans' ? 'active' : '' }}"
                        >
                            <div style="font-weight: 600; color: #111827;">Midtrans</div>
                            <div style="font-size: 0.75rem;" class="pos-payment-muted">Pembayaran non tunai</div>
                        </button>
                    </div>

                    @if ($selectedPaymentMethod === 'cash')
                        <div style="margin-top: 1rem; padding: 0.875rem; border-radius: 0.75rem;" class="pos-payment-cash-box">
                            <label style="display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: #111827;">
                                Nominal Dibayar (Tunai)
                            </label>
                            <input
                                type="number"
                                min="0"
                                step="1"
                                wire:model.live="cashPaidAmount"
                                style="width: 100%; padding: 0.625rem 0.75rem; border-radius: 0.5rem;"
                                class="pos-payment-input"
                                placeholder="Kosongkan = pas sesuai total"
                            />
                            <div style="margin-top: 0.5rem; display: flex; justify-content: space-between; font-size: 0.85rem;">
                                <span class="pos-payment-muted">Kembalian</span>
                                <strong style="color: #111827;">Rp {{ number_format((float) $this->cashChange, 0, ',', '.') }}</strong>
                            </div>
                        </div>
                    @endif
                </div>

                <div style="padding: 1rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;" class="pos-payment-modal-footer">
                    <x-filament::button color="gray" wire:click="closePaymentModal">
                        Batal
                    </x-filament::button>
                    <x-filament::button
                        color="primary"
                        wire:click="processPayment"
                        :disabled="! $this->canConfirmPayment"
                    >
                        Konfirmasi Pembayaran
                    </x-filament::button>
                </div>
            </div>
        </div>
    @endif

    @if ($showPendingModal)
        <div
            style="position: fixed; inset: 0; z-index: 9998; background: rgba(0,0,0,0.55); display: flex; align-items: center; justify-content: center; padding: 1rem;"
            wire:click.self="closePendingModal"
        >
            <div style="width: min(34rem, 100%); border-radius: 1rem; overflow: hidden;" class="pos-payment-modal">
                <div style="padding: 1rem 1.25rem;" class="pos-payment-modal-header">
                    <h3 style="font-size: 1.125rem; font-weight: 700;" class="pos-payment-title">Menunggu Pembayaran Midtrans</h3>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;" class="pos-payment-muted">
                        Transaksi belum settlement. Silakan selesaikan pembayaran pelanggan.
                    </p>
                </div>
                <div style="padding: 1rem 1.25rem; display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem 1rem;">
                    <div>
                        <div class="pos-payment-muted" style="font-size: 0.8rem;">Metode</div>
                        <div style="font-weight: 600; color: #111827;">{{ strtoupper((string) ($pendingPaymentData['method'] ?? '-')) }}</div>
                    </div>
                    <div>
                        <div class="pos-payment-muted" style="font-size: 0.8rem;">Status</div>
                        <div style="font-weight: 600; color: #111827;">{{ strtoupper((string) ($pendingPaymentData['status'] ?? 'PENDING')) }}</div>
                    </div>
                    <div>
                        <div class="pos-payment-muted" style="font-size: 0.8rem;">Nominal</div>
                        <div style="font-weight: 600; color: #111827;">Rp {{ number_format((float) ($pendingPaymentData['amount'] ?? 0), 0, ',', '.') }}</div>
                    </div>
                    <div>
                        <div class="pos-payment-muted" style="font-size: 0.8rem;">VA</div>
                        <div style="font-weight: 600; color: #111827;">{{ (string) ($pendingPaymentData['va_number'] ?? '-') }}</div>
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <div class="pos-payment-muted" style="font-size: 0.8rem;">Kadaluarsa</div>
                        <div style="font-weight: 600; color: #111827;">{{ (string) ($pendingPaymentData['expiry_time'] ?? '-') }}</div>
                    </div>
                </div>
                <div style="padding: 1rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;" class="pos-payment-modal-footer">
                    <x-filament::button color="gray" wire:click="closePendingModal">
                        Tutup
                    </x-filament::button>
                    <button
                        type="button"
                        wire:click="checkMidtransStatus('{{ (string) ($pendingPaymentData['gateway_ref'] ?? '') }}')"
                        @disabled(blank($pendingPaymentData['gateway_ref'] ?? null))
                        style="display: inline-flex; align-items: center; justify-content: center; gap: .375rem; padding: .625rem 1rem; border-radius: .625rem; background: var(--primary-600); color: #fff; font-weight: 600;"
                    >
                        Cek Status Lagi
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($showReceiptModal)
        <div
            style="position: fixed; inset: 0; z-index: 9997; background: rgba(0,0,0,0.6); display: flex; align-items: center; justify-content: center; padding: 1rem;"
            wire:click.self="closeReceiptModal"
        >
            <div style="width: min(36rem, 100%); border-radius: 1rem; overflow: hidden;" class="pos-payment-modal">
                <div style="padding: 1rem 1.25rem;" class="pos-payment-modal-header">
                    <h3 style="font-size: 1.125rem; font-weight: 700;" class="pos-payment-title">Pembayaran Berhasil</h3>
                    <p style="font-size: 0.875rem; margin-top: 0.25rem;" class="pos-payment-muted">
                        Preview struk transaksi sebelum dicetak.
                    </p>
                </div>
                <div style="padding: 1rem 1.25rem; max-height: 60vh; overflow: auto;">
                    <div style="padding: 0.875rem; border: 1px dashed #d1d5db; border-radius: 0.75rem; background: #ffffff;">
                        <div style="text-align: center; margin-bottom: 0.625rem;">
                            <div style="font-weight: 700; color: #111827;">POS SYSTEM</div>
                            <div class="pos-payment-muted" style="font-size: 0.8rem;">Struk Pembayaran</div>
                        </div>
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.3rem; font-size: 0.82rem; color: #111827;">
                            <span>No. Order</span><span>{{ (string) ($receiptData['order_number'] ?? '-') }}</span>
                            <span>Waktu</span><span>{{ (string) ($receiptData['ordered_at'] ?? '-') }}</span>
                            <span>Kasir</span><span>{{ (string) ($receiptData['cashier_name'] ?? '-') }}</span>
                            <span>Metode</span><span>{{ strtoupper((string) ($receiptData['payment_method'] ?? '-')) }}</span>
                        </div>
                        <hr style="margin: 0.625rem 0; border-color: #e5e7eb;">
                        <div style="font-size: 0.84rem; color: #111827;">
                            @foreach (($receiptData['items'] ?? []) as $item)
                                <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.35rem; margin-bottom: 0.3rem;">
                                    <span>{{ (string) ($item['name'] ?? '-') }} ({{ (int) ($item['qty'] ?? 0) }} x {{ number_format((float) ($item['price'] ?? 0), 0, ',', '.') }})</span>
                                    <span>{{ number_format((float) ($item['total'] ?? 0), 0, ',', '.') }}</span>
                                </div>
                            @endforeach
                        </div>
                        <hr style="margin: 0.625rem 0; border-color: #e5e7eb;">
                        <div style="display: grid; grid-template-columns: 1fr auto; gap: 0.25rem; font-size: 0.82rem; color: #111827;">
                            <span>Subtotal</span><span>Rp {{ number_format((float) ($receiptData['subtotal'] ?? 0), 0, ',', '.') }}</span>
                            <span>Diskon</span><span>Rp {{ number_format((float) ($receiptData['discount_total'] ?? 0), 0, ',', '.') }}</span>
                            <span>Pajak</span><span>Rp {{ number_format((float) ($receiptData['tax_total'] ?? 0), 0, ',', '.') }}</span>
                            <span>Service</span><span>Rp {{ number_format((float) ($receiptData['service_total'] ?? 0), 0, ',', '.') }}</span>
                            <span style="font-weight: 700;">Total</span><span style="font-weight: 700;">Rp {{ number_format((float) ($receiptData['grand_total'] ?? 0), 0, ',', '.') }}</span>
                            <span>Dibayar</span><span>Rp {{ number_format((float) ($receiptData['paid_amount'] ?? 0), 0, ',', '.') }}</span>
                            <span>Kembalian</span><span>Rp {{ number_format((float) ($receiptData['change'] ?? 0), 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
                <div style="padding: 1rem 1.25rem; display: flex; justify-content: flex-end; gap: 0.5rem;" class="pos-payment-modal-footer">
                    <x-filament::button color="gray" wire:click="closeReceiptModal">
                        Tutup
                    </x-filament::button>
                    <button
                        type="button"
                        data-action="print-receipt"
                        data-receipt='@json($receiptData)'
                        style="display: inline-flex; align-items: center; justify-content: center; gap: .375rem; padding: .625rem 1rem; border-radius: .625rem; background: var(--primary-600); color: #fff; font-weight: 600;"
                    >
                        Cetak Struk
                    </button>
                </div>
            </div>
        </div>
    @endif
</x-filament-panels::page>

@push('scripts')
    @php
        $snapSrc = config('midtrans.is_production')
            ? 'https://app.midtrans.com/snap/snap.js'
            : 'https://app.sandbox.midtrans.com/snap/snap.js';
    @endphp
    <script src="https://cdn.jsdelivr.net/npm/qz-tray@2.2.4/qz-tray.min.js"></script>
    <script src="{{ $snapSrc }}" data-client-key="{{ config('midtrans.client_key') }}"></script>
    <script>
        document.addEventListener('livewire:init', () => {
            const updateNetworkState = () => {
                const isOffline = !window.navigator.onLine;
                Livewire.dispatch('setOfflineStatus', { isOffline });
            };

            const attendanceDeviceKey = 'pos:attendance_device_id';

            const printerKey = 'pos:selected_printer';
            let qzSecurityConfigured = false;
            const printerBadge = document.getElementById('printerStatusBadge');
            const printerModalBackdrop = document.getElementById('printerModalBackdrop');
            const closePrinterModalButton = document.getElementById('closePrinterModalButton');
            const closePrinterModalFooterButton = document.getElementById('closePrinterModalFooterButton');
            const printerModalSummary = document.getElementById('printerModalSummary');
            const selectedPrinterName = document.getElementById('selectedPrinterName');
            const printerListContainer = document.getElementById('printerListContainer');
            const refreshPrinterButton = document.getElementById('refreshPrinterButton');
            const disconnectPrinterButton = document.getElementById('disconnectPrinterButton');
            const testPrinterButton = document.getElementById('testPrinterButton');

            const printerState = {
                status: 'idle',
                summary: 'Belum cek koneksi printer. Klik Refresh untuk memulai.',
                printers: [],
                selected: localStorage.getItem(printerKey) || '',
                isPrintingTest: false,
            };

            const printerStatusConfig = {
                idle: { className: 'status-idle', title: 'Belum cek koneksi printer' },
                checking: { className: 'status-checking', title: 'Memeriksa koneksi printer...' },
                offline: { className: 'status-offline', title: 'QZ Tray tidak terdeteksi / belum aktif' },
                serviceOnly: { className: 'status-service-only', title: 'Service aktif, pilih printer dulu' },
                ready: { className: 'status-ready', title: 'Printer siap digunakan' },
                error: { className: 'status-error', title: 'Terjadi kendala pada koneksi printer' },
            };

            const ensurePrintNoticeContainer = () => {
                let container = document.getElementById('posPrintNoticeContainer');
                if (container) return container;

                container = document.createElement('div');
                container.id = 'posPrintNoticeContainer';
                container.className = 'pos-print-notice-container';
                document.body.appendChild(container);

                return container;
            };

            const showPrintNotice = (message, type = 'info') => {
                const container = ensurePrintNoticeContainer();
                const notice = document.createElement('div');
                notice.className = `pos-print-notice ${type}`;
                notice.textContent = message;
                container.appendChild(notice);

                window.setTimeout(() => {
                    notice.style.opacity = '0';
                    notice.style.transform = 'translateY(-4px)';
                    window.setTimeout(() => notice.remove(), 220);
                }, 2800);
            };

            const generateDeviceId = () => {
                if (typeof window.crypto !== 'undefined' && typeof window.crypto.randomUUID === 'function') {
                    return window.crypto.randomUUID();
                }

                return `${Date.now()}-${Math.random().toString(16).slice(2)}`;
            };

            const getAttendanceDeviceId = () => {
                let existingDeviceId = localStorage.getItem(attendanceDeviceKey);

                if (!existingDeviceId) {
                    existingDeviceId = generateDeviceId();
                    localStorage.setItem(attendanceDeviceKey, existingDeviceId);
                }

                return existingDeviceId;
            };

            const dispatchAttendanceContext = (latitude = null, longitude = null) => {
                Livewire.dispatch('attendance-context-updated', {
                    deviceId: getAttendanceDeviceId(),
                    latitude,
                    longitude,
                });
            };

            const requestAttendanceAction = (action) => {
                const deviceId = getAttendanceDeviceId();

                if (!navigator.geolocation) {
                    showPrintNotice('Browser tidak mendukung GPS.', 'error');
                    Livewire.dispatch('attendance-action', {
                        action,
                        deviceId,
                    });

                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    (position) => {
                        const latitude = Number(position.coords.latitude);
                        const longitude = Number(position.coords.longitude);

                        dispatchAttendanceContext(latitude, longitude);
                        Livewire.dispatch('attendance-action', {
                            action,
                            deviceId,
                            latitude,
                            longitude,
                        });
                    },
                    () => {
                        showPrintNotice('Izin lokasi ditolak. Aktifkan GPS browser.', 'error');
                        Livewire.dispatch('attendance-action', {
                            action,
                            deviceId,
                        });
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 30000,
                    }
                );
            };

            const setReceiptButtonLoading = (button, isLoading) => {
                if (!button) return;

                if (isLoading) {
                    button.dataset.originalText = button.textContent?.trim() || 'Cetak Struk';
                    button.disabled = true;
                    button.textContent = 'Mencetak...';
                    button.style.opacity = '.75';
                    button.style.cursor = 'not-allowed';
                    return;
                }

                button.disabled = false;
                button.textContent = button.dataset.originalText || 'Cetak Struk';
                button.style.opacity = '1';
                button.style.cursor = 'pointer';
            };

            const applyPrinterBadge = (status) => {
                if (!printerBadge) return;

                const statusKey = printerStatusConfig[status] ? status : 'error';
                printerState.status = statusKey;

                printerBadge.classList.remove('status-idle', 'status-checking', 'status-offline', 'status-service-only', 'status-ready', 'status-error');
                printerBadge.classList.add(printerStatusConfig[statusKey].className);
                printerBadge.title = printerStatusConfig[statusKey].title;
            };

            const renderPrinterState = () => {
                if (!printerModalSummary || !selectedPrinterName || !printerListContainer) return;

                printerModalSummary.textContent = printerState.summary;
                selectedPrinterName.textContent = printerState.selected || '-';

                 if (testPrinterButton) {
                    const canTest = !!printerState.selected && printerState.printers.includes(printerState.selected) && !printerState.isPrintingTest;
                    testPrinterButton.disabled = !canTest;
                    testPrinterButton.textContent = printerState.isPrintingTest ? 'Mencetak...' : 'Test Print';
                 }

                if (!printerState.printers.length) {
                    printerListContainer.innerHTML = '<div class="pos-printer-meta">Tidak ada printer yang terdeteksi.</div>';
                    return;
                }

                printerListContainer.innerHTML = printerState.printers
                    .map((printerName) => {
                        const activeClass = printerName === printerState.selected ? 'active' : '';
                        const encodedPrinterName = encodeURIComponent(printerName);

                        return `
                            <button type="button" class="pos-printer-item ${activeClass}" data-printer-name="${encodedPrinterName}">
                                ${printerName}
                            </button>
                        `;
                    })
                    .join('');

                printerListContainer.querySelectorAll('[data-printer-name]').forEach((button) => {
                    button.addEventListener('click', () => {
                        const printerName = decodeURIComponent(button.getAttribute('data-printer-name') || '');
                        if (!printerName) return;

                        printerState.selected = printerName;
                        localStorage.setItem(printerKey, printerName);
                        printerState.summary = `Printer "${printerName}" berhasil dipilih.`;
                        applyPrinterBadge('ready');
                        renderPrinterState();
                    });
                });
            };

            const openPrinterModal = () => {
                if (!printerModalBackdrop) return;

                printerModalBackdrop.style.display = 'flex';
                renderPrinterState();
            };

            const closePrinterModal = () => {
                if (!printerModalBackdrop) return;
                printerModalBackdrop.style.display = 'none';
            };

            const connectQz = async () => {
                if (!window.qz) {
                    throw new Error('QZ Tray tidak tersedia.');
                }

                if (window.qz.security && !qzSecurityConfigured) {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

                    window.qz.security.setSignatureAlgorithm('SHA512');
                    window.qz.security.setCertificatePromise((resolve, reject) => {
                        fetch('/api/qz-tray/certificate', {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                            },
                        })
                            .then(async (response) => {
                                const payload = await response.json().catch(() => ({}));
                                if (!response.ok || !payload?.data) {
                                    throw new Error(payload?.message || 'QZ certificate tidak tersedia.');
                                }

                                resolve(payload.data);
                            })
                            .catch((error) => reject(error));
                    });

                    window.qz.security.setSignaturePromise((toSign) => (resolve, reject) => {
                        fetch('/api/qz-tray/sign', {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': csrfToken,
                            },
                            body: JSON.stringify({ request: toSign }),
                        })
                            .then(async (response) => {
                                const payload = await response.json().catch(() => ({}));
                                if (!response.ok || !payload?.data) {
                                    throw new Error(payload?.message || 'QZ signature gagal dibuat.');
                                }

                                resolve(payload.data);
                            })
                            .catch((error) => reject(error));
                    });

                    qzSecurityConfigured = true;
                }

                if (window.qz.websocket.isActive()) {
                    return true;
                }

                await window.qz.websocket.connect({ retries: 1, delay: 0.75 });
                return true;
            };

            const normalizePrinters = (rawPrinters) => {
                if (!rawPrinters) return [];
                if (Array.isArray(rawPrinters)) return rawPrinters.filter(Boolean);
                if (typeof rawPrinters === 'string') return [rawPrinters];
                return [];
            };

            const isLikelyReceiptPrinter = (printerName) => {
                if (!printerName) return false;

                return /(thermal|receipt|esc\/pos|58mm|80mm|epson\s*tm|pos[-\s]?printer|xprinter|sunmi|bixolon)/i.test(printerName);
            };

            const escapeHtml = (value) => String(value ?? '')
                .replaceAll('&', '&amp;')
                .replaceAll('<', '&lt;')
                .replaceAll('>', '&gt;')
                .replaceAll('"', '&quot;')
                .replaceAll("'", '&#039;');

            const refreshPrinterStatus = async () => {
                applyPrinterBadge('checking');

                try {
                    await connectQz();

                    const printerResult = await window.qz.printers.find();
                    printerState.printers = normalizePrinters(printerResult);

                    if (!printerState.printers.length) {
                        printerState.selected = '';
                        localStorage.removeItem(printerKey);
                        printerState.summary = 'Service printer aktif, tetapi belum ada printer yang terdeteksi.';
                        applyPrinterBadge('serviceOnly');
                        renderPrinterState();
                        return;
                    }

                    if (printerState.selected && !printerState.printers.includes(printerState.selected)) {
                        printerState.selected = '';
                        localStorage.removeItem(printerKey);
                    }

                    if (!printerState.selected) {
                        printerState.summary = 'Service aktif. Pilih printer untuk pairing.';
                        applyPrinterBadge('serviceOnly');
                    } else {
                        printerState.summary = `Printer "${printerState.selected}" siap digunakan.`;
                        applyPrinterBadge('ready');
                    }
                } catch (error) {
                    printerState.printers = [];
                    const errorMessage = error instanceof Error ? error.message : 'Terjadi kendala saat cek printer.';
                    printerState.summary = errorMessage || 'QZ Tray belum aktif. Jalankan QZ Tray terlebih dahulu.';
                    applyPrinterBadge('offline');
                }

                renderPrinterState();
            };

            const runTestPrint = async () => {
                if (printerState.isPrintingTest) {
                    return;
                }

                if (!printerState.selected) {
                    printerState.summary = 'Pilih printer terlebih dahulu sebelum test print.';
                    applyPrinterBadge('serviceOnly');
                    renderPrinterState();
                    return;
                }

                printerState.isPrintingTest = true;
                printerState.summary = `Mengirim test print ke "${printerState.selected}"...`;
                renderPrinterState();

                try {
                    await connectQz();

                    const printers = normalizePrinters(await window.qz.printers.find());
                    printerState.printers = printers;

                    if (!printers.includes(printerState.selected)) {
                        printerState.selected = '';
                        localStorage.removeItem(printerKey);
                        printerState.summary = 'Printer tidak ditemukan. Silakan pairing ulang.';
                        applyPrinterBadge('serviceOnly');
                        return;
                    }

                    const config = window.qz.configs.create(printerState.selected, {
                        encoding: 'UTF-8',
                    });

                    const now = new Date().toLocaleString('id-ID');
                    const useRawMode = isLikelyReceiptPrinter(printerState.selected);
                    const printData = useRawMode
                        ? [
                            '\n',
                            'POS SYSTEM\n',
                            '==============================\n',
                            'TEST PRINT\n',
                            `Waktu   : ${now}\n`,
                            `Printer : ${printerState.selected}\n`,
                            'Status  : OK\n',
                            '==============================\n',
                            '\n\n\n',
                        ]
                        : [{
                            type: 'html',
                            format: 'plain',
                            data: `
                                <div style="font-family: Arial, sans-serif; padding: 12px;">
                                    <h3 style="margin: 0 0 8px;">POS SYSTEM</h3>
                                    <div style="margin-bottom: 4px;">TEST PRINT</div>
                                    <div style="margin-bottom: 4px;">Waktu: ${escapeHtml(now)}</div>
                                    <div style="margin-bottom: 4px;">Printer: ${escapeHtml(printerState.selected)}</div>
                                    <div>Status: OK</div>
                                </div>
                            `,
                        }];

                    await window.qz.print(config, printData);

                    const modeLabel = useRawMode ? 'RAW' : 'HTML';
                    printerState.summary = `Test print (${modeLabel}) berhasil dikirim ke "${printerState.selected}".`;
                    applyPrinterBadge('ready');
                } catch (error) {
                    const errorMessage = error instanceof Error ? error.message : 'Gagal test print.';
                    printerState.summary = errorMessage || 'Gagal test print. Cek QZ Tray dan koneksi printer.';
                    applyPrinterBadge('error');
                } finally {
                    printerState.isPrintingTest = false;
                    renderPrinterState();
                }
            };

            const runReceiptPrint = async (receipt, triggerButton = null) => {
                setReceiptButtonLoading(triggerButton, true);

                if (!receipt || typeof receipt !== 'object') {
                    printerState.summary = 'Data struk tidak valid.';
                    applyPrinterBadge('error');
                    renderPrinterState();
                    showPrintNotice('Data struk tidak valid.', 'error');
                    setReceiptButtonLoading(triggerButton, false);
                    return;
                }

                if (!printerState.selected) {
                    printerState.summary = 'Pairing printer terlebih dahulu sebelum cetak struk.';
                    applyPrinterBadge('serviceOnly');
                    renderPrinterState();
                    showPrintNotice('Pilih printer terlebih dahulu.', 'error');
                    setReceiptButtonLoading(triggerButton, false);
                    return;
                }

                try {
                    await connectQz();

                    const printers = normalizePrinters(await window.qz.printers.find());
                    printerState.printers = printers;

                    if (!printers.includes(printerState.selected)) {
                        printerState.selected = '';
                        localStorage.removeItem(printerKey);
                        printerState.summary = 'Printer tidak ditemukan. Silakan pairing ulang.';
                        applyPrinterBadge('serviceOnly');
                        renderPrinterState();
                        return;
                    }

                    const config = window.qz.configs.create(printerState.selected, {
                        encoding: 'UTF-8',
                    });

                    const number = (value) => Number(value || 0);
                    const formatRupiah = (value) => number(value).toLocaleString('id-ID');
                    const receiptItems = Array.isArray(receipt.items) ? receipt.items : [];
                    const useRawMode = isLikelyReceiptPrinter(printerState.selected);

                    const lines = [
                        '\n',
                        'POS SYSTEM\n',
                        '================================\n',
                        `Order   : ${receipt.order_number ?? '-'}\n`,
                        `Waktu   : ${receipt.ordered_at ?? '-'}\n`,
                        `Kasir   : ${receipt.cashier_name ?? '-'}\n`,
                        `Metode  : ${(receipt.payment_method ?? '-').toUpperCase()}\n`,
                        '--------------------------------\n',
                    ];

                    receiptItems.forEach((item) => {
                        const itemName = String(item.name ?? 'Item').slice(0, 30);
                        const qty = number(item.qty);
                        const price = number(item.price);
                        const total = number(item.total);

                        lines.push(`${itemName}\n`);
                        lines.push(`  ${qty} x ${formatRupiah(price)}  = ${formatRupiah(total)}\n`);
                    });

                    lines.push('--------------------------------\n');
                    lines.push(`Subtotal : ${formatRupiah(receipt.subtotal)}\n`);
                    lines.push(`Diskon   : ${formatRupiah(receipt.discount_total)}\n`);
                    lines.push(`Pajak    : ${formatRupiah(receipt.tax_total)}\n`);
                    lines.push(`Service  : ${formatRupiah(receipt.service_total)}\n`);
                    lines.push(`TOTAL    : ${formatRupiah(receipt.grand_total)}\n`);
                    lines.push(`Bayar    : ${formatRupiah(receipt.paid_amount)}\n`);
                    lines.push(`Kembali  : ${formatRupiah(receipt.change)}\n`);
                    lines.push('================================\n');
                    lines.push('Terima kasih\n');
                    lines.push('\n\n\n');

                    if (useRawMode) {
                        await window.qz.print(config, lines);
                    } else {
                        const rowsHtml = receiptItems.map((item) => `
                            <tr>
                                <td style="padding: 2px 0;">${escapeHtml(item.name)} (${number(item.qty)} x ${formatRupiah(item.price)})</td>
                                <td style="padding: 2px 0; text-align:right;">${formatRupiah(item.total)}</td>
                            </tr>
                        `).join('');

                        const receiptHtml = `
                            <div style="font-family: Arial, sans-serif; font-size: 12px; padding: 12px;">
                                <h3 style="margin: 0 0 8px;">POS SYSTEM</h3>
                                <div style="margin-bottom: 2px;">Order: ${escapeHtml(receipt.order_number ?? '-')}</div>
                                <div style="margin-bottom: 2px;">Waktu: ${escapeHtml(receipt.ordered_at ?? '-')}</div>
                                <div style="margin-bottom: 2px;">Kasir: ${escapeHtml(receipt.cashier_name ?? '-')}</div>
                                <div style="margin-bottom: 8px;">Metode: ${escapeHtml((receipt.payment_method ?? '-').toUpperCase())}</div>
                                <table style="width:100%; border-collapse: collapse; margin-bottom: 8px;">
                                    <tbody>${rowsHtml}</tbody>
                                </table>
                                <hr>
                                <table style="width:100%; border-collapse: collapse;">
                                    <tr><td>Subtotal</td><td style="text-align:right;">${formatRupiah(receipt.subtotal)}</td></tr>
                                    <tr><td>Diskon</td><td style="text-align:right;">${formatRupiah(receipt.discount_total)}</td></tr>
                                    <tr><td>Pajak</td><td style="text-align:right;">${formatRupiah(receipt.tax_total)}</td></tr>
                                    <tr><td>Service</td><td style="text-align:right;">${formatRupiah(receipt.service_total)}</td></tr>
                                    <tr><td><strong>Total</strong></td><td style="text-align:right;"><strong>${formatRupiah(receipt.grand_total)}</strong></td></tr>
                                    <tr><td>Dibayar</td><td style="text-align:right;">${formatRupiah(receipt.paid_amount)}</td></tr>
                                    <tr><td>Kembalian</td><td style="text-align:right;">${formatRupiah(receipt.change)}</td></tr>
                                </table>
                                <div style="margin-top: 10px;">Terima kasih</div>
                            </div>
                        `;

                        await window.qz.print(config, [{
                            type: 'html',
                            format: 'plain',
                            data: receiptHtml,
                        }]);
                    }

                    printerState.summary = `Struk berhasil dikirim ke "${printerState.selected}".`;
                    applyPrinterBadge('ready');
                    showPrintNotice('Struk berhasil dicetak.', 'success');
                } catch (error) {
                    const errorMessage = error instanceof Error ? error.message : 'Gagal cetak struk.';
                    printerState.summary = errorMessage || 'Gagal cetak struk. Cek QZ Tray dan koneksi printer.';
                    applyPrinterBadge('error');
                    showPrintNotice(printerState.summary, 'error');
                }

                renderPrinterState();
                setReceiptButtonLoading(triggerButton, false);
            };

            updateNetworkState();
            dispatchAttendanceContext();
            window.addEventListener('online', updateNetworkState);
            window.addEventListener('offline', updateNetworkState);
            applyPrinterBadge('idle');
            renderPrinterState();

            printerBadge?.addEventListener('click', openPrinterModal);
            closePrinterModalButton?.addEventListener('click', closePrinterModal);
            closePrinterModalFooterButton?.addEventListener('click', closePrinterModal);
            printerModalBackdrop?.addEventListener('click', (event) => {
                if (event.target === printerModalBackdrop) {
                    closePrinterModal();
                }
            });
            refreshPrinterButton?.addEventListener('click', refreshPrinterStatus);
            disconnectPrinterButton?.addEventListener('click', () => {
                printerState.selected = '';
                localStorage.removeItem(printerKey);
                printerState.summary = 'Pairing printer dilepas. Pilih printer lagi untuk pairing.';
                applyPrinterBadge(printerState.printers.length ? 'serviceOnly' : 'offline');
                renderPrinterState();
            });
            testPrinterButton?.addEventListener('click', runTestPrint);
            document.addEventListener('click', (event) => {
                const element = event.target instanceof Element
                    ? event.target
                    : null;

                if (!element) {
                    return;
                }

                const attendanceButton = element.closest('[data-attendance-action]');

                if (attendanceButton) {
                    if (attendanceButton.hasAttribute('disabled')) {
                        return;
                    }

                    const action = attendanceButton.getAttribute('data-attendance-action');

                    if (action) {
                        requestAttendanceAction(action);
                    }

                    return;
                }

                const target = element.closest('[data-action="print-receipt"]');

                if (!target) {
                    return;
                }

                const rawReceipt = target.getAttribute('data-receipt');

                if (!rawReceipt) {
                    return;
                }

                try {
                    runReceiptPrint(JSON.parse(rawReceipt), target);
                } catch (error) {
                    printerState.summary = 'Format data struk tidak valid.';
                    applyPrinterBadge('error');
                    renderPrinterState();
                    showPrintNotice('Format data struk tidak valid.', 'error');
                }
            });

            Livewire.on('midtrans-snap', ({ token, gatewayRef }) => {
                if (!window.snap) {
                    console.error('Midtrans snap.js tidak termuat.');
                    return;
                }

                const resolveGatewayRef = (result) => {
                    if (result && typeof result.order_id === 'string' && result.order_id.length > 0) {
                        return result.order_id;
                    }

                    if (typeof gatewayRef === 'string' && gatewayRef.length > 0) {
                        return gatewayRef;
                    }

                    return '';
                };

                window.snap.pay(token, {
                    onSuccess: function (result) {
                        const ref = resolveGatewayRef(result);
                        if (ref !== '') {
                            Livewire.dispatch('midtrans-check-status', { gatewayRef: ref });
                        }
                    },
                    onPending: function (result) {
                        const ref = resolveGatewayRef(result);
                        if (ref !== '') {
                            Livewire.dispatch('midtrans-pending-client', {
                                gatewayRef: ref,
                                payload: result ?? {},
                            });
                        }
                    },
                    onError: function (result) {
                        console.error('Pembayaran gagal.');
                        const ref = resolveGatewayRef(result);
                        if (ref !== '') {
                            Livewire.dispatch('midtrans-check-status', { gatewayRef: ref });
                        }
                    },
                    onClose: function () {
                        Livewire.dispatch('midtrans-close-client');
                    }
                });
            });
        });
    </script>
@endpush
