<?php

namespace App\Filament\Pages\Reports;

use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Pages\Page;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;
use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Http\Response;
use UnitEnum;

abstract class BaseReportPage extends Page
{
    protected static string|UnitEnum|null $navigationGroup = 'Reports';

    protected static ?string $reportPermission = null;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public function mount(): void
    {
        if (! $this->usesDateFilters()) {
            return;
        }

        $this->dateFrom ??= $this->defaultDateFrom();
        $this->dateTo ??= $this->defaultDateTo();
    }

    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(static::getReportPermissionName());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return parent::shouldRegisterNavigation() && static::canAccess();
    }

    public function resetFilters(): void
    {
        if ($this->usesDateFilters()) {
            $this->dateFrom = $this->defaultDateFrom();
            $this->dateTo = $this->defaultDateTo();
        }

        $this->afterResetFilters();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('downloadPdf')
                ->label('Unduh PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->color('gray')
                ->url(fn (): string => $this->getPdfDownloadUrl())
                ->openUrlInNewTab(),
        ];
    }

    public function downloadPdf(): Response
    {
        return app(\App\Services\ReportPdfService::class)->download(
            view: $this->getPdfView(),
            data: $this->getPdfData(),
            filename: $this->getPdfFilename(),
            paper: $this->getPdfPaperSize(),
            orientation: $this->getPdfOrientation(),
        );
    }

    public function usesDateFilters(): bool
    {
        return true;
    }

    public function getReadableDateRange(): string
    {
        if (! $this->usesDateFilters()) {
            return 'Data terbaru';
        }

        [$start, $end] = $this->resolveDateRange();

        return $start->translatedFormat('d M Y') . ' - ' . $end->translatedFormat('d M Y');
    }

    protected static function getReportPermissionName(): string
    {
        return static::$reportPermission ?? '';
    }

    protected function defaultDateFrom(): string
    {
        return now()->startOfMonth()->toDateString();
    }

    protected function defaultDateTo(): string
    {
        return now()->toDateString();
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    protected function resolveDateRange(): array
    {
        $start = Carbon::parse($this->dateFrom ?: $this->defaultDateFrom())->startOfDay();
        $end = Carbon::parse($this->dateTo ?: $this->defaultDateTo())->endOfDay();

        if ($start->greaterThan($end)) {
            [$start, $end] = [$end->copy()->startOfDay(), $start->copy()->endOfDay()];
        }

        return [$start, $end];
    }

    protected function applyDateRange(EloquentBuilder|QueryBuilder $query, string $column): void
    {
        if (! $this->usesDateFilters()) {
            return;
        }

        [$start, $end] = $this->resolveDateRange();

        $query->whereBetween($column, [$start, $end]);
    }

    protected function formatMoney(float|int|null $value): string
    {
        return 'Rp ' . number_format((float) $value, 0, ',', '.');
    }

    protected function formatNumber(float|int|null $value, int $decimals = 0): string
    {
        return number_format((float) $value, $decimals, ',', '.');
    }

    protected function formatHoursFromMinutes(int|float|null $minutes): string
    {
        $hours = ((float) $minutes) / 60;

        return $this->formatNumber($hours, 1) . ' jam';
    }

    protected function afterResetFilters(): void
    {
    }

    protected function getPdfData(): array
    {
        return [
            'title' => static::getTitle(),
            'generatedAt' => now(),
            'generatedBy' => Filament::auth()->user()?->name ?? '-',
            'periodLabel' => $this->usesDateFilters() ? $this->getReadableDateRange() : 'Posisi saat export',
            'filterSummary' => $this->getPdfFilterSummary(),
        ];
    }

    protected function getPdfFilterSummary(): array
    {
        return [];
    }

    public function applyReportFilters(array $filters): void
    {
        if ($this->usesDateFilters()) {
            $this->dateFrom = filled($filters['dateFrom'] ?? null)
                ? (string) $filters['dateFrom']
                : $this->defaultDateFrom();
            $this->dateTo = filled($filters['dateTo'] ?? null)
                ? (string) $filters['dateTo']
                : $this->defaultDateTo();
        }

        $this->applyCustomReportFilters($filters);
    }

    public function getPdfDownloadUrl(): string
    {
        return route('reports.download', [
            'report' => static::getReportKey(),
            ...$this->getPdfQueryParameters(),
        ]);
    }

    protected function getPdfFilename(): string
    {
        return str(static::getTitle())
            ->lower()
            ->replace(' ', '-')
            ->append('-' . now()->format('Y-m-d_H-i'))
            ->append('.pdf')
            ->toString();
    }

    protected function getPdfPaperSize(): string
    {
        return 'a4';
    }

    protected function getPdfOrientation(): string
    {
        return 'portrait';
    }

    protected function getPdfQueryParameters(): array
    {
        return array_filter([
            'dateFrom' => $this->dateFrom,
            'dateTo' => $this->dateTo,
            ...$this->getCustomPdfQueryParameters(),
        ], fn ($value): bool => $value !== null && $value !== '');
    }

    protected function getCustomPdfQueryParameters(): array
    {
        return [];
    }

    protected function applyCustomReportFilters(array $filters): void
    {
    }

    abstract public static function getReportKey(): string;

    abstract protected function getPdfView(): string;
}
