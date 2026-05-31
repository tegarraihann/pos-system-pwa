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
                    <label class="report-label">Status Absensi</label>
                    <select wire:model.live="attendanceStatus" class="report-select">
                        <option value="all">Semua</option>
                        <option value="{{ \App\Models\Attendance::STATUS_CHECKED_IN }}">Checked In</option>
                        <option value="{{ \App\Models\Attendance::STATUS_CHECKED_OUT }}">Checked Out</option>
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

        @php($attendanceTable = $this->getAttendanceTable())
        @include('filament.pages.reports.partials.table-card', [
            'title' => 'Daftar Absensi',
            'columns' => $attendanceTable['columns'],
            'rows' => $attendanceTable['rows'],
        ])
    </div>
</x-filament-panels::page>
