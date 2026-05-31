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
                    <label class="report-label">Status Sesi</label>
                    <select wire:model.live="sessionStatus" class="report-select">
                        <option value="all">Semua</option>
                        <option value="{{ \App\Models\CashierSession::STATUS_OPEN }}">Open</option>
                        <option value="{{ \App\Models\CashierSession::STATUS_CLOSED }}">Closed</option>
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

        @php($sessionsTable = $this->getSessionTable())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Daftar Sesi Kasir',
            'columns' => $sessionsTable['columns'],
            'rows' => $sessionsTable['rows'],
        ])
    </div>
</x-filament-panels::page>
