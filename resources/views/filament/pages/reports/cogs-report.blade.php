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
            'title' => 'Ringkasan HPP Harian',
            'columns' => $dailyTable['columns'],
            'rows' => $dailyTable['rows'],
        ])

        @php($productTable = $this->getProductBreakdown())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'HPP dan Laba Kotor per Menu',
            'columns' => $productTable['columns'],
            'rows' => $productTable['rows'],
        ])
    </div>
</x-filament-panels::page>
