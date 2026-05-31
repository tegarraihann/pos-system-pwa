<?php

namespace App\Filament\Pages\Reports;

use App\Models\CashierSession;
use BackedEnum;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class CashierSessionReport extends BaseReportPage
{
    public static function getReportKey(): string
    {
        return 'cashier-sessions';
    }

    protected static ?string $title = 'Laporan Sesi Kasir';

    protected static ?string $navigationLabel = 'Laporan Sesi Kasir';

    protected static ?int $navigationSort = 3;

    protected static ?string $slug = 'reports/cashier-sessions';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static ?string $reportPermission = 'ViewCashierReport:Report';

    protected string $view = 'filament.pages.reports.cashier-session-report';

    public string $sessionStatus = 'all';

    /**
     * @return array<int, array{label: string, value: string, hint: string|null}>
     */
    public function getSummaryCards(): array
    {
        $sessionsQuery = $this->baseSessionsQuery();

        $sessionCount = (clone $sessionsQuery)->count();
        $openCount = (clone $sessionsQuery)->where('status', CashierSession::STATUS_OPEN)->count();
        $openingCash = (float) (clone $sessionsQuery)->sum('opening_cash');
        $differenceAmount = (float) (clone $sessionsQuery)->sum('difference_amount');

        return [
            [
                'label' => 'Jumlah Sesi',
                'value' => $this->formatNumber($sessionCount),
                'hint' => 'Total sesi kasir pada periode filter.',
            ],
            [
                'label' => 'Sesi Masih Terbuka',
                'value' => $this->formatNumber($openCount),
                'hint' => 'Membantu memantau kasir yang belum tutup sesi.',
            ],
            [
                'label' => 'Akumulasi Modal Awal',
                'value' => $this->formatMoney($openingCash),
                'hint' => 'Total modal awal seluruh sesi dalam periode ini.',
            ],
            [
                'label' => 'Total Selisih Kas',
                'value' => $this->formatMoney($differenceAmount),
                'hint' => 'Nilai negatif artinya kas kurang, positif artinya lebih.',
            ],
        ];
    }

    /**
     * @return array{columns: array<int, string>, rows: array<int, array<int, string>>}
     */
    public function getSessionTable(): array
    {
        $rows = $this->baseSessionsQuery()
            ->with('user')
            ->latest('opened_at')
            ->limit(25)
            ->get();

        return [
            'columns' => ['Kasir', 'Status', 'Buka', 'Tutup', 'Modal', 'Expected', 'Actual', 'Selisih'],
            'rows' => $rows->map(fn (CashierSession $session): array => [
                (string) ($session->user?->name ?? '-'),
                $this->translateSessionStatus((string) $session->status),
                optional($session->opened_at)->translatedFormat('d M Y H:i') ?? '-',
                optional($session->closed_at)->translatedFormat('d M Y H:i') ?? '-',
                $this->formatMoney($session->opening_cash),
                $this->formatMoney($session->expected_cash),
                $this->formatMoney($session->actual_cash),
                $this->formatMoney($session->difference_amount),
            ])->all(),
        ];
    }

    protected function afterResetFilters(): void
    {
        $this->sessionStatus = 'all';
    }

    protected function getPdfData(): array
    {
        return [
            ...parent::getPdfData(),
            'summaryCards' => $this->getSummaryCards(),
            'sessionsTable' => $this->getSessionTable(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [
            'Status Sesi' => $this->sessionStatus === 'all'
                ? 'Semua'
                : $this->translateSessionStatus($this->sessionStatus),
        ];
    }

    protected function getPdfOrientation(): string
    {
        return 'landscape';
    }

    protected function getPdfView(): string
    {
        return 'pdf.reports.cashier-sessions';
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [
            'sessionStatus' => $this->sessionStatus,
        ];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
        $this->sessionStatus = (string) ($filters['sessionStatus'] ?? 'all');
    }

    protected function baseSessionsQuery(): Builder
    {
        $query = CashierSession::query();

        $this->applyDateRange($query, 'opened_at');

        if ($this->sessionStatus !== 'all') {
            $query->where('status', $this->sessionStatus);
        }

        return $query;
    }

    protected function translateSessionStatus(string $status): string
    {
        return match ($status) {
            CashierSession::STATUS_OPEN => 'Open',
            CashierSession::STATUS_CLOSED => 'Closed',
            default => ucfirst($status),
        };
    }
}
