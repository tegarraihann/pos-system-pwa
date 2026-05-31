<?php

namespace App\Filament\Pages\Reports;

use App\Models\Attendance;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class AttendanceReport extends BaseReportPage
{
    public static function getReportKey(): string
    {
        return 'attendance';
    }

    protected static ?string $title = 'Laporan Absensi';

    protected static ?string $navigationLabel = 'Laporan Absensi';

    protected static ?int $navigationSort = 4;

    protected static ?string $slug = 'reports/attendance';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static ?string $reportPermission = 'ViewAttendanceReport:Report';

    protected string $view = 'filament.pages.reports.attendance-report';

    public string $attendanceStatus = 'all';

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function getSummaryCards(): array
    {
        $attendanceQuery = $this->baseAttendanceQuery();

        $total = (clone $attendanceQuery)->count();
        $checkedOut = (clone $attendanceQuery)->where('status', Attendance::STATUS_CHECKED_OUT)->count();
        $checkedIn = (clone $attendanceQuery)->where('status', Attendance::STATUS_CHECKED_IN)->count();
        $workMinutes = (int) (clone $attendanceQuery)->sum('work_minutes');

        return [
            [
                'label' => 'Total Record Absensi',
                'value' => $this->formatNumber($total),
                'hint' => 'Jumlah absensi yang tercatat pada periode filter.',
            ],
            [
                'label' => 'Sudah Check-out',
                'value' => $this->formatNumber($checkedOut),
                'hint' => 'Absensi yang sudah lengkap check-in dan check-out.',
            ],
            [
                'label' => 'Masih Check-in',
                'value' => $this->formatNumber($checkedIn),
                'hint' => 'Perlu dipantau karena shift belum ditutup.',
            ],
            [
                'label' => 'Akumulasi Jam Kerja',
                'value' => $this->formatHoursFromMinutes($workMinutes),
                'hint' => 'Total durasi kerja berdasarkan work_minutes.',
            ],
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getAttendanceTable(): array
    {
        $rows = $this->baseAttendanceQuery()
            ->with('user')
            ->latest('shift_date')
            ->latest('check_in_at')
            ->limit(25)
            ->get();

        return [
            'columns' => ['Kasir', 'Tanggal Shift', 'Status', 'Check-in', 'Check-out', 'Durasi'],
            'rows' => $rows->map(fn (Attendance $attendance): array => [
                (string) ($attendance->user?->name ?? '-'),
                optional($attendance->shift_date)->translatedFormat('d M Y') ?? '-',
                $this->translateAttendanceStatus((string) $attendance->status),
                optional($attendance->check_in_at)->translatedFormat('d M Y H:i') ?? '-',
                optional($attendance->check_out_at)->translatedFormat('d M Y H:i') ?? '-',
                $this->formatHoursFromMinutes($attendance->work_minutes),
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->attendanceStatus = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'attendanceTable' => $this->getAttendanceTable(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Status Absensi' => $this->attendanceStatus === 'all'
                ? 'Semua'
                : $this->translateAttendanceStatus($this->attendanceStatus),
        ];
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.attendance';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'attendanceStatus' => $this->attendanceStatus,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->attendanceStatus = (string) ($filters['attendanceStatus'] ?? 'all');
    }

    protected function baseAttendanceQuery(): Builder
    {
        $query = Attendance::query();

        $this->applyDateRange($query, 'shift_date');

        if ($this->attendanceStatus !== 'all') {
            $query->where('status', $this->attendanceStatus);
        }

        return $query;
    }

    protected function translateAttendanceStatus(string $status): string
    {
        return match ($status) {
            Attendance::STATUS_CHECKED_IN => 'Checked In',
            Attendance::STATUS_CHECKED_OUT => 'Checked Out',
            default => ucfirst($status),
        };
    }
}
