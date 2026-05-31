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
                    <label class="report-label">Akun Beban</label>
                    <select wire:model.live="accountId" class="report-select">
                        <option value="all">Semua</option>
                        @foreach (\App\Models\ChartOfAccount::query()->where('category', \App\Models\ChartOfAccount::CATEGORY_EXPENSE)->where('is_active', true)->orderBy('code')->get() as $account)
                            <option value="{{ $account->id }}">{{ $account->code }} - {{ $account->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="report-field">
                    <label class="report-label">Metode Pembayaran</label>
                    <select wire:model.live="paymentMethod" class="report-select">
                        <option value="all">Semua</option>
                        @foreach (\App\Models\OperatingExpense::paymentMethodOptions() as $value => $label)
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

        @php($accountSummary = $this->getAccountSummary())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Ringkasan per Akun Beban',
            'columns' => $accountSummary['columns'],
            'rows' => $accountSummary['rows'],
        ])

        @php($detailsTable = $this->getExpenseDetails())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Detail Beban Operasional',
            'columns' => $detailsTable['columns'],
            'rows' => $detailsTable['rows'],
        ])
    </div>
</x-filament-panels::page>
