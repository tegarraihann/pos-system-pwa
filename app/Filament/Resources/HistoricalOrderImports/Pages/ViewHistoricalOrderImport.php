<?php

namespace App\Filament\Resources\HistoricalOrderImports\Pages;

use App\Filament\Resources\HistoricalOrderImports\HistoricalOrderImportResource;
use App\Models\HistoricalOrderImport;
use App\Services\HistoricalOrderMigrationService;
use App\Services\HistoricalOrderImportReviewService;
use Filament\Actions\Action;
use Filament\Resources\Pages\ViewRecord;
use Filament\Forms\Components\Textarea;
use Filament\Support\Enums\Width;

class ViewHistoricalOrderImport extends ViewRecord
{
    protected static string $resource = HistoricalOrderImportResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markReady')
                ->label('Tandai Siap Migrasi')
                ->color('success')
                ->icon('heroicon-o-check-badge')
                ->visible(fn (): bool => $this->record->mapping_status === HistoricalOrderImport::STATUS_MATCHED && ! $this->record->ready_for_migration)
                ->form([
                    Textarea::make('review_notes')
                        ->label('Catatan Review')
                        ->rows(3),
                ])
                ->action(function (array $data): void {
                    app(HistoricalOrderImportReviewService::class)->markReady(
                        $this->record,
                        $data['review_notes'] ?? null,
                        auth()->id(),
                    );

                    $this->record->refresh();
                }),
            Action::make('markNeedsReview')
                ->label('Kembalikan ke Review')
                ->color('warning')
                ->icon('heroicon-o-arrow-uturn-left')
                ->visible(fn (): bool => $this->record->ready_for_migration)
                ->form([
                    Textarea::make('review_notes')
                        ->label('Catatan Review')
                        ->rows(3)
                        ->required(),
                ])
                ->action(function (array $data): void {
                    app(HistoricalOrderImportReviewService::class)->markNeedsReview(
                        $this->record,
                        $data['review_notes'],
                        auth()->id(),
                    );

                    $this->record->refresh();
                }),
            Action::make('migrateRecord')
                ->label('Migrasikan ke Order Final')
                ->color('success')
                ->icon('heroicon-o-arrow-right-circle')
                ->visible(fn (): bool => $this->record->ready_for_migration && blank($this->record->migrated_order_id))
                ->requiresConfirmation()
                ->action(function (): void {
                    app(HistoricalOrderMigrationService::class)->migrate($this->record, auth()->id());
                    $this->record->refresh();
                }),
        ];
    }
}
