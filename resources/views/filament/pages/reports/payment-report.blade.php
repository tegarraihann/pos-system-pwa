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
                    <label class="report-label">Status Payment</label>
                    <select wire:model.live="paymentStatus" class="report-select">
                        <option value="all">Semua</option>
                        <option value="paid">Paid</option>
                        <option value="pending">Pending</option>
                        <option value="expired">Kadaluarsa</option>
                        <option value="failed">Gagal</option>
                        <option value="canceled">Dibatalkan</option>
                    </select>
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
                    <label class="report-label">Metode</label>
                    <select wire:model.live="paymentMethod" class="report-select">
                        <option value="all">Semua</option>
                        <option value="cash">Cash</option>
                        <option value="midtrans">Midtrans</option>
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

        @php($methodSummary = $this->getMethodSummary())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Ringkasan per Metode',
            'columns' => $methodSummary['columns'],
            'rows' => $methodSummary['rows'],
        ])

        @php($recentPayments = $this->getRecentPayments())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Pembayaran Terbaru',
            'columns' => $recentPayments['columns'],
            'rows' => $recentPayments['rows'],
        ])
    </div>
</x-filament-panels::page>
