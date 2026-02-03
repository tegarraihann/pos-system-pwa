<x-filament-panels::page>
    <style>
        .kds-grid {
            display: grid;
            grid-template-columns: repeat(1, minmax(0, 1fr));
            gap: 1rem;
        }

        @media (min-width: 1024px) {
            .kds-grid {
                grid-template-columns: repeat(4, minmax(0, 1fr));
            }
        }

        .kds-column {
            background: rgba(15, 23, 42, 0.02);
            border: 1px solid var(--gray-200);
            border-radius: 1rem;
            padding: 0.75rem;
            min-height: 20rem;
        }

        .dark .kds-column {
            background: rgba(15, 23, 42, 0.4);
            border-color: rgba(55, 65, 81, 0.5);
        }

        .kds-card {
            background: var(--gray-50);
            border: 1px solid var(--gray-200);
            border-radius: 0.75rem;
            padding: 0.75rem;
            margin-bottom: 0.75rem;
        }

        .dark .kds-card {
            background: rgba(17, 24, 39, 0.9);
            border-color: rgba(55, 65, 81, 0.6);
        }

        .kds-card.priority {
            border-color: var(--danger-500);
            box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.15);
        }

        .kds-badge {
            font-size: 0.7rem;
            font-weight: 600;
            padding: 0.2rem 0.5rem;
            border-radius: 9999px;
            background: var(--gray-200);
            color: var(--gray-700);
        }

        .kds-actions {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 0.5rem;
            margin-top: 0.75rem;
        }
    </style>

    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 1.5rem;">
        <div>
            <h1 style="font-size: 1.5rem; font-weight: 700;">Kitchen Display</h1>
            <p style="font-size: 0.85rem; color: var(--gray-500);">Rata-rata waktu preparasi: {{ $this->formatDuration($this->averagePrepSeconds) }} (Received → Ready)</p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <x-filament::button id="kds-sound-toggle" color="gray">
                <span id="kds-sound-label">Aktifkan Suara</span>
            </x-filament::button>
            <x-filament::button color="{{ $station === 'all' ? 'primary' : 'gray' }}" wire:click="$set('station','all')">
                Semua
            </x-filament::button>
            <x-filament::button color="{{ $station === 'food' ? 'primary' : 'gray' }}" wire:click="$set('station','food')">
                Food
            </x-filament::button>
            <x-filament::button color="{{ $station === 'beverage' ? 'primary' : 'gray' }}" wire:click="$set('station','beverage')">
                Beverage
            </x-filament::button>
            <x-filament::button color="gray" wire:click="$refresh">
                Refresh
            </x-filament::button>
        </div>
    </div>

    <div class="kds-grid">
        @php
            $columns = [
                \App\Models\Order::STATUS_QUEUED => 'Queued',
                \App\Models\Order::STATUS_RECEIVED => 'Received',
                \App\Models\Order::STATUS_PREPARING => 'Preparing',
                \App\Models\Order::STATUS_READY => 'Ready',
            ];
        @endphp

        @foreach ($columns as $status => $label)
            @php
                $orders = $this->ordersByStatus[$status] ?? collect();
            @endphp
            <div class="kds-column">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                    <h2 style="font-size: 1rem; font-weight: 700;">{{ $label }}</h2>
                    <span class="kds-badge">{{ $orders->count() }}</span>
                </div>

                @forelse ($orders as $order)
                    <div class="kds-card {{ $order->is_priority ? 'priority' : '' }}">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.5rem;">
                            <div style="font-weight: 700;">#{{ $order->order_number }}</div>
                            <div style="font-size: 0.75rem; color: var(--gray-500);">
                                {{ ($order->received_at ?? $order->created_at)->diffForHumans() }}
                            </div>
                        </div>

                        <div style="font-size: 0.75rem; color: var(--gray-500); margin-bottom: 0.5rem;">
                            Lokasi: {{ $order->stockLocation?->name ?? '-' }} • Meja: {{ $order->table_number ?? '-' }}
                        </div>

                        <div style="margin-bottom: 0.5rem;">
                            @foreach ($order->items as $item)
                                @if ($this->itemMatchesStation($item))
                                    <div style="display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 0.25rem;">
                                        <div>
                                            <strong>{{ $item->item_name_snapshot }}</strong>
                                            @if ($item->notes)
                                                <div style="font-size: 0.75rem; color: var(--gray-500);">Catatan: {{ $item->notes }}</div>
                                            @endif
                                            @if ($item->modifiers->isNotEmpty())
                                                <div style="font-size: 0.75rem; color: var(--gray-500);">
                                                    Modifier: {{ $item->modifiers->pluck('name')->implode(', ') }}
                                                </div>
                                            @endif
                                        </div>
                                        <div style="font-weight: 700;">x{{ (int) $item->qty }}</div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        <div class="kds-actions">
                            <x-filament::button color="gray" wire:click="togglePriority({{ $order->id }})">
                                {{ $order->is_priority ? 'Unprioritize' : 'Priority' }}
                            </x-filament::button>

                            @if ($order->status === \App\Models\Order::STATUS_QUEUED)
                                <x-filament::button color="primary" wire:click="markReceived({{ $order->id }})">
                                    Received
                                </x-filament::button>
                            @elseif ($order->status === \App\Models\Order::STATUS_RECEIVED)
                                <x-filament::button color="primary" wire:click="markPreparing({{ $order->id }})">
                                    Preparing
                                </x-filament::button>
                            @elseif ($order->status === \App\Models\Order::STATUS_PREPARING)
                                <x-filament::button color="primary" wire:click="markReady({{ $order->id }})">
                                    Ready
                                </x-filament::button>
                            @elseif ($order->status === \App\Models\Order::STATUS_READY)
                                <x-filament::button color="primary" wire:click="markServed({{ $order->id }})">
                                    Served
                                </x-filament::button>
                            @endif
                        </div>
                    </div>
                @empty
                    <div style="font-size: 0.85rem; color: var(--gray-400);">Tidak ada order.</div>
                @endforelse
            </div>
        @endforeach
    </div>
</x-filament-panels::page>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const soundKey = 'kds-sound-enabled';
            const soundButton = document.getElementById('kds-sound-toggle');
            const soundLabel = document.getElementById('kds-sound-label');
            let soundEnabled = localStorage.getItem(soundKey) === '1';
            let audioCtx = null;

            function updateSoundLabel() {
                if (!soundLabel) {
                    return;
                }

                soundLabel.textContent = soundEnabled ? 'Suara Aktif' : 'Aktifkan Suara';
            }

            function ensureAudioContext() {
                if (!audioCtx) {
                    audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                }

                if (audioCtx.state === 'suspended') {
                    audioCtx.resume();
                }
            }

            function kdsPlayBeep() {
                if (!soundEnabled) {
                    return;
                }

                ensureAudioContext();

                const oscillator = audioCtx.createOscillator();
                const gain = audioCtx.createGain();

                oscillator.type = 'sine';
                oscillator.frequency.setValueAtTime(880, audioCtx.currentTime);
                gain.gain.setValueAtTime(0.15, audioCtx.currentTime);

                oscillator.connect(gain);
                gain.connect(audioCtx.destination);
                oscillator.start();
                oscillator.stop(audioCtx.currentTime + 0.2);
            }

            function getOrderSpeech(payload) {
                const orderNumber = payload?.order_number || '';
                const items = Array.isArray(payload?.items) ? payload.items : [];
                const itemText = items
                    .filter(item => item?.name)
                    .map(item => `${item.name}${item.qty ? ' ' + item.qty + ' item' : ''}`)
                    .join(', ');

                if (itemText) {
                    return `Pesanan baru, ${itemText}.`;
                }

                return orderNumber ? `Pesanan baru nomor ${orderNumber}.` : 'Pesanan baru.';
            }

            function kdsSpeak(text) {
                if (!soundEnabled || !text) {
                    return;
                }

                if (!('speechSynthesis' in window)) {
                    kdsPlayBeep();
                    return;
                }

                const utterance = new SpeechSynthesisUtterance(text);
                utterance.lang = 'id-ID';

                const voices = window.speechSynthesis.getVoices();
                const indonesianVoice = voices.find((voice) => voice.lang?.toLowerCase().startsWith('id'));
                if (indonesianVoice) {
                    utterance.voice = indonesianVoice;
                }

                window.speechSynthesis.cancel();
                window.speechSynthesis.speak(utterance);
            }

            updateSoundLabel();

            if (soundButton) {
                soundButton.addEventListener('click', () => {
                    soundEnabled = !soundEnabled;
                    localStorage.setItem(soundKey, soundEnabled ? '1' : '0');
                    updateSoundLabel();
                    if (soundEnabled) {
                        ensureAudioContext();
                        kdsSpeak('Suara diaktifkan.');
                    }
                });
            }

            const wsHost = @js(config('broadcasting.connections.reverb.options.host')) || window.location.hostname;
            const wsPort = @js((int) config('broadcasting.connections.reverb.options.port', 8080));
            const useTls = @js(config('broadcasting.connections.reverb.options.scheme', 'https') === 'https');

            if (!window.Echo && window.EchoFactory) {
                window.Echo = new window.EchoFactory({
                    broadcaster: 'reverb',
                    key: @js(config('broadcasting.connections.reverb.key')),
                    wsHost,
                    wsPort,
                    wssPort: wsPort,
                    forceTLS: useTls,
                    enabledTransports: ['ws', 'wss'],
                });
            }

            if (!window.Echo || typeof window.Echo.channel !== 'function') {
                console.warn('Echo belum siap.');
                return;
            }

            window.Echo.channel('orders')
                .listen('.order.created', (payload) => {
                    if (payload?.status === 'queued' || payload?.status === 'received') {
                        kdsSpeak(getOrderSpeech(payload));
                        if (!('speechSynthesis' in window)) {
                            kdsPlayBeep();
                        }
                    }
                    window.Livewire?.dispatch('kds-refresh');
                })
                .listen('.order.updated', (payload) => {
                    if (payload?.status === 'queued' || payload?.status === 'received') {
                        kdsSpeak(getOrderSpeech(payload));
                        if (!('speechSynthesis' in window)) {
                            kdsPlayBeep();
                        }
                    }
                    window.Livewire?.dispatch('kds-refresh');
                });
        });
    </script>
@endpush
