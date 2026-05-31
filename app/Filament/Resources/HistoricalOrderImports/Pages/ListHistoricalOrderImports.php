<?php

namespace App\Filament\Resources\HistoricalOrderImports\Pages;

use App\Filament\Resources\HistoricalOrderImports\HistoricalOrderImportResource;
use App\Models\HistoricalOrderImport;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\Width;

class ListHistoricalOrderImports extends ListRecords
{
    protected static string $resource = HistoricalOrderImportResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;

    public function getTabs(): array
    {
        return [
            'perlu_review' => Tab::make('Perlu Review')
                ->badge($this->countByStatuses([
                    HistoricalOrderImport::STATUS_PARTIAL,
                    HistoricalOrderImport::STATUS_AMBIGUOUS,
                    HistoricalOrderImport::STATUS_UNMATCHED,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->whereIn('mapping_status', [
                    HistoricalOrderImport::STATUS_PARTIAL,
                    HistoricalOrderImport::STATUS_AMBIGUOUS,
                    HistoricalOrderImport::STATUS_UNMATCHED,
                ])),
            'matched' => Tab::make('Matched')
                ->badge($this->countByStatuses([
                    HistoricalOrderImport::STATUS_MATCHED,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('mapping_status', HistoricalOrderImport::STATUS_MATCHED)),
            'ambiguous' => Tab::make('Ambiguous')
                ->badge($this->countByStatuses([
                    HistoricalOrderImport::STATUS_AMBIGUOUS,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('mapping_status', HistoricalOrderImport::STATUS_AMBIGUOUS)),
            'partial' => Tab::make('Partial')
                ->badge($this->countByStatuses([
                    HistoricalOrderImport::STATUS_PARTIAL,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('mapping_status', HistoricalOrderImport::STATUS_PARTIAL)),
            'unmatched' => Tab::make('Unmatched')
                ->badge($this->countByStatuses([
                    HistoricalOrderImport::STATUS_UNMATCHED,
                ]))
                ->modifyQueryUsing(fn ($query) => $query->where('mapping_status', HistoricalOrderImport::STATUS_UNMATCHED)),
            'semua' => Tab::make('Semua')
                ->badge(HistoricalOrderImport::query()->count()),
        ];
    }

    public function getDefaultActiveTab(): string|int|null
    {
        return 'perlu_review';
    }

    protected function countByStatuses(array $statuses): int
    {
        return HistoricalOrderImport::query()
            ->whereIn('mapping_status', $statuses)
            ->count();
    }
}
