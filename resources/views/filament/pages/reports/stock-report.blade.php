<x-filament-panels::page>
    @include('filament.pages.reports.partials.styles')

    <div class="report-page">
        <div class="report-panel">
            <div class="report-filter-grid">
                <div class="report-field">
                    <label class="report-label">Jenis Stok</label>
                    <select wire:model.live="stockType" class="report-select">
                        <option value="all">Semua</option>
                        <option value="menu">Varian Menu</option>
                        <option value="ingredient">Bahan Baku</option>
                    </select>
                </div>
            </div>
            <div class="report-toolbar">
                <p class="report-meta">Laporan stok menampilkan posisi stok terkini, bukan historis tanggal tertentu.</p>
                <x-filament::button color="gray" wire:click="resetFilters">
                        Reset Filter
                </x-filament::button>
            </div>
        </div>

        @include('filament.pages.reports.partials.stat-cards', ['stats' => $this->getSummaryCards()])

        @php($menuTable = $this->getMenuVariantTable())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Varian Menu Stok Kritis',
            'columns' => $menuTable['columns'],
            'rows' => $menuTable['rows'],
        ])

        @php($ingredientTable = $this->getIngredientTable())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Bahan Baku Stok Kritis',
            'columns' => $ingredientTable['columns'],
            'rows' => $ingredientTable['rows'],
        ])
    </div>
</x-filament-panels::page>
