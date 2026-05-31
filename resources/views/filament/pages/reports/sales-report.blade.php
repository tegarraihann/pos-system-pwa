    <x-filament-panels::page>
    @include('filament.pages.reports.partials.styles')

    <div class="report-page">
        <div class="report-panel">
            <div class="report-filter-grid">
                <div class="report-field">
                    <label class="report-label">Tanggal Mulai</label>
                    <input type="date" wire:model.live="dateFrom" class="report-input" />
                </div>
                <div class="report-field">
                    <label class="report-label">Tanggal Akhir</label>
                    <input type="date" wire:model.live="dateTo" class="report-input" />
                </div>
                <div class="report-field">
                    <label class="report-label">Sumber Order</label>
                    <select wire:model.live="orderSource" class="report-select">
                        <option value="all">Semua</option>
                        @foreach (\App\Models\Order::sourceOptions() as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="report-field">
                    <label class="report-label">Metode Bayar</label>
                    <select wire:model.live="paymentMethod" class="report-select">
                        <option value="all">Semua</option>
                        <option value="{{ \App\Models\Order::PAYMENT_CASH }}">Cash</option>
                        <option value="{{ \App\Models\Order::PAYMENT_MIDTRANS }}">Midtrans</option>
                    </select>
                </div>
            </div>

            <div class="report-toolbar">
                <p class="report-meta">Periode aktif: {{ $this->getReadableDateRange() }}</p>
                <x-filament::button color="gray" wire:click="resetFilters">
                    Reset Filter
                </x-filament::button>
            </div>
        </div>

        @include('filament.pages.reports.partials.stat-cards', ['stats' => $this->getSummaryCards()])

        @php($dailyTable = $this->getDailyBreakdown())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Ringkasan Harian',
            'columns' => $dailyTable['columns'],
            'rows' => $dailyTable['rows'],
        ])

        @php($topItemsTable = $this->getTopItems())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Top Menu Terjual',
            'columns' => $topItemsTable['columns'],
            'rows' => $topItemsTable['rows'],
        ])
    </div>
</x-filament-panels::page>
