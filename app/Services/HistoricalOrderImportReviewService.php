<?php

namespace App\Services;

use App\Models\HistoricalOrderImport;
use App\Models\HistoricalOrderImportItem;
use Illuminate\Support\Facades\DB;

class HistoricalOrderImportReviewService
{
    public function refreshImport(HistoricalOrderImport $import): HistoricalOrderImport
    {
        return DB::transaction(function () use ($import): HistoricalOrderImport {
            $import->loadMissing('items');

            $items = $import->items;
            $mappedItems = $items->filter(fn (HistoricalOrderImportItem $item): bool => filled($item->menu_variant_id) && $item->unit_price !== null);
            $unmappedItems = $items->filter(fn (HistoricalOrderImportItem $item): bool => blank($item->menu_variant_id) || $item->unit_price === null);

            $baseMappedTotal = $mappedItems->sum(function (HistoricalOrderImportItem $item): float {
                $lineTotal = ((float) $item->unit_price) * ((float) $item->inferred_qty);

                $item->updateQuietly([
                    'line_total_inferred' => $lineTotal,
                    'mapping_status' => HistoricalOrderImport::STATUS_MATCHED,
                ]);

                return $lineTotal;
            });

            foreach ($unmappedItems as $item) {
                $item->updateQuietly([
                    'line_total_inferred' => null,
                    'mapping_status' => HistoricalOrderImport::STATUS_UNMATCHED,
                ]);
            }

            $mappingStatus = HistoricalOrderImport::STATUS_MATCHED;
            $notes = [];
            $priceGap = (float) $import->total_amount - $baseMappedTotal;

            if ($items->isEmpty() || $unmappedItems->count() === $items->count()) {
                $mappingStatus = HistoricalOrderImport::STATUS_UNMATCHED;
                $notes[] = 'Semua item masih belum termapping.';
            } elseif ($unmappedItems->isNotEmpty()) {
                $mappingStatus = HistoricalOrderImport::STATUS_PARTIAL;
                $notes[] = 'Masih ada item yang belum termapping.';
            } elseif (round($priceGap, 2) !== 0.0) {
                $mappingStatus = HistoricalOrderImport::STATUS_AMBIGUOUS;
                $notes[] = 'Masih ada selisih total terhadap harga master.';
            }

            $import->updateQuietly([
                'base_mapped_total' => $baseMappedTotal,
                'price_gap' => $priceGap,
                'mapping_status' => $mappingStatus,
                'notes' => $notes === [] ? null : implode(' ', $notes),
            ]);

            if ($mappingStatus !== HistoricalOrderImport::STATUS_MATCHED && $import->ready_for_migration) {
                $import->updateQuietly([
                    'ready_for_migration' => false,
                ]);
            }

            return $import->refresh();
        });
    }

    public function markReady(HistoricalOrderImport $import, ?string $reviewNotes, ?int $reviewedBy = null): HistoricalOrderImport
    {
        $import = $this->refreshImport($import);

        if ($import->mapping_status !== HistoricalOrderImport::STATUS_MATCHED) {
            return $import;
        }

        $import->update([
            'ready_for_migration' => true,
            'review_notes' => $reviewNotes,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);

        return $import->refresh();
    }

    public function markNeedsReview(HistoricalOrderImport $import, ?string $reviewNotes, ?int $reviewedBy = null): HistoricalOrderImport
    {
        $import->update([
            'ready_for_migration' => false,
            'review_notes' => $reviewNotes,
            'reviewed_by' => $reviewedBy,
            'reviewed_at' => now(),
        ]);

        return $import->refresh();
    }
}
