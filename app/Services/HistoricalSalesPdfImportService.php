<?php

namespace App\Services;

use App\Models\HistoricalOrderImport;
use App\Models\HistoricalOrderImportItem;
use App\Models\MenuVariant;
use App\Support\AhwaWarkopProductNameNormalizer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Smalot\PdfParser\Parser;

class HistoricalSalesPdfImportService
{
    /**
     * @return array{processed: int, matched: int, partial: int, ambiguous: int, unmatched: int}
     */
    public function importFromPdf(string $path): array
    {
        if (! is_file($path)) {
            throw new RuntimeException("File PDF tidak ditemukan: {$path}");
        }

        $parser = new Parser();
        $text = $parser->parseFile($path)->getText();
        $entries = $this->extractEntries($text);
        $variantLookup = $this->variantLookup();
        $sourceFile = str_replace('\\', '/', $path);

        $summary = [
            'processed' => 0,
            HistoricalOrderImport::STATUS_MATCHED => 0,
            HistoricalOrderImport::STATUS_PARTIAL => 0,
            HistoricalOrderImport::STATUS_AMBIGUOUS => 0,
            HistoricalOrderImport::STATUS_UNMATCHED => 0,
        ];

        DB::transaction(function () use ($entries, $variantLookup, $sourceFile, &$summary): void {
            foreach ($entries as $entry) {
                $mapped = $this->mapEntry($entry, $variantLookup);

                $import = HistoricalOrderImport::query()->updateOrCreate(
                    ['source_order_number' => $entry['source_order_number']],
                    [
                        'source_file' => $sourceFile,
                        'outlet_name' => $entry['outlet_name'],
                        'ordered_at' => $entry['ordered_at'],
                        'paid_at' => $entry['paid_at'],
                        'payment_method_raw' => $entry['payment_method_raw'],
                        'payment_method_mapped' => $entry['payment_method_mapped'],
                        'payment_channel_raw' => $entry['payment_channel_raw'],
                        'operator_raw' => $entry['operator_raw'],
                        'raw_products' => $entry['raw_products'],
                        'normalized_products' => $mapped['normalized_products'],
                        'unpaid_amount' => $entry['unpaid_amount'],
                        'total_amount' => $entry['total_amount'],
                        'base_mapped_total' => $mapped['base_mapped_total'],
                        'price_gap' => $mapped['price_gap'],
                        'mapping_status' => $mapped['mapping_status'],
                        'notes' => $mapped['notes'],
                        'imported_at' => now(),
                    ],
                );

                $import->items()->delete();

                foreach ($mapped['items'] as $item) {
                    HistoricalOrderImportItem::query()->create([
                        'historical_order_import_id' => $import->id,
                        'menu_variant_id' => $item['menu_variant_id'],
                        'raw_item_name' => $item['raw_item_name'],
                        'normalized_item_name' => $item['normalized_item_name'],
                        'listed_qty' => $item['listed_qty'],
                        'inferred_qty' => $item['inferred_qty'],
                        'unit_price' => $item['unit_price'],
                        'line_total_inferred' => $item['line_total_inferred'],
                        'mapping_status' => $item['mapping_status'],
                        'notes' => $item['notes'],
                    ]);
                }

                $summary['processed']++;
                $summary[$mapped['mapping_status']]++;
            }
        });

        return $summary;
    }

    /**
     * @return array<int, array{
     *     source_order_number: string,
     *     ordered_at: Carbon|null,
     *     paid_at: Carbon|null,
     *     outlet_name: string,
     *     raw_products: string,
     *     payment_method_raw: string,
     *     payment_method_mapped: string,
     *     payment_channel_raw: string|null,
     *     operator_raw: string|null,
     *     unpaid_amount: float,
     *     total_amount: float
     * }>
     */
    protected function extractEntries(string $text): array
    {
        preg_match_all('/CS\/\d{2}\/\d{6}\/\d{4}/', $text, $matches, PREG_OFFSET_CAPTURE);

        $entries = [];
        $offsets = $matches[0] ?? [];

        foreach ($offsets as $index => [, $start]) {
            $end = $offsets[$index + 1][1] ?? strlen($text);
            $chunk = substr($text, $start, $end - $start);
            $entry = $this->parseChunk($chunk);

            if ($entry !== null) {
                $entries[] = $entry;
            }
        }

        return $entries;
    }

    /**
     * @return array<string, array{id: int, name: string, price: float}> 
     */
    protected function variantLookup(): array
    {
        return MenuVariant::query()
            ->with('menu:id,name')
            ->get()
            ->filter(fn (MenuVariant $variant): bool => filled($variant->menu?->name))
            ->mapWithKeys(function (MenuVariant $variant): array {
                $menuName = $variant->menu->name;

                return [
                    AhwaWarkopProductNameNormalizer::catalogKey($menuName) => [
                        'id' => $variant->id,
                        'name' => $menuName,
                        'price' => (float) $variant->price,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @param  array<string, array{id: int, name: string, price: float}>  $variantLookup
     * @return array{
     *     normalized_products: array<int, string>,
     *     items: array<int, array{
     *         menu_variant_id: int|null,
     *         raw_item_name: string,
     *         normalized_item_name: string,
     *         listed_qty: float,
     *         inferred_qty: float,
     *         unit_price: float|null,
     *         line_total_inferred: float|null,
     *         mapping_status: string,
     *         notes: string|null
     *     }>,
     *     base_mapped_total: float,
     *     price_gap: float,
     *     mapping_status: string,
     *     notes: string|null
     * }
     */
    protected function mapEntry(array $entry, array $variantLookup): array
    {
        $grouped = [];

        foreach ($this->splitProducts($entry['raw_products']) as $rawItemName) {
            $normalizedName = AhwaWarkopProductNameNormalizer::normalize($rawItemName);
            $catalogKey = AhwaWarkopProductNameNormalizer::catalogKey($normalizedName);
            $variant = $variantLookup[$catalogKey] ?? null;

            if (! isset($grouped[$catalogKey])) {
                $grouped[$catalogKey] = [
                    'menu_variant_id' => $variant['id'] ?? null,
                    'raw_item_name' => $rawItemName,
                    'normalized_item_name' => $normalizedName,
                    'listed_qty' => 0.0,
                    'inferred_qty' => 0.0,
                    'unit_price' => $variant['price'] ?? null,
                    'line_total_inferred' => null,
                    'mapping_status' => $variant ? HistoricalOrderImport::STATUS_MATCHED : HistoricalOrderImport::STATUS_UNMATCHED,
                    'notes' => null,
                ];
            }

            $grouped[$catalogKey]['listed_qty'] += 1.0;
        }

        $items = array_values($grouped);
        $totalCents = $this->moneyToCents($entry['total_amount']);
        $baseMappedCents = 0;
        $unmappedNames = [];

        foreach ($items as $index => $item) {
            if ($item['menu_variant_id'] === null || $item['unit_price'] === null) {
                $items[$index]['inferred_qty'] = $item['listed_qty'];
                $unmappedNames[] = $item['normalized_item_name'];

                continue;
            }

            $unitPriceCents = $this->moneyToCents($item['unit_price']);
            $listedQty = (int) round($item['listed_qty']);
            $inferredQty = $listedQty;
            $notes = null;

            if (count($items) === 1 && $unitPriceCents > 0 && $totalCents % $unitPriceCents === 0) {
                $ratio = (int) ($totalCents / $unitPriceCents);

                if ($ratio >= $listedQty) {
                    $inferredQty = $ratio;

                    if ($ratio !== $listedQty) {
                        $notes = 'Qty diinferensikan dari total transaksi tunggal.';
                    }
                }
            }

            $lineTotalCents = $unitPriceCents * $inferredQty;
            $items[$index]['inferred_qty'] = $inferredQty;
            $items[$index]['line_total_inferred'] = $this->centsToMoney($lineTotalCents);
            $items[$index]['notes'] = $notes;
            $baseMappedCents += $lineTotalCents;
        }

        $priceGapCents = $totalCents - $baseMappedCents;
        $normalizedProducts = array_values(array_map(
            fn (array $item): string => $item['normalized_item_name'],
            $items,
        ));

        $mappingStatus = HistoricalOrderImport::STATUS_MATCHED;
        $notes = [];

        if ($unmappedNames !== []) {
            $mappingStatus = count($unmappedNames) === count($items)
                ? HistoricalOrderImport::STATUS_UNMATCHED
                : HistoricalOrderImport::STATUS_PARTIAL;
            $notes[] = 'Ada item yang belum termapping: ' . implode(', ', $unmappedNames) . '.';
        } elseif ($priceGapCents !== 0) {
            $mappingStatus = HistoricalOrderImport::STATUS_AMBIGUOUS;
            $notes[] = 'Total transaksi tidak sama dengan total harga master; kemungkinan ada qty tambahan atau harga historis berbeda.';
        }

        return [
            'normalized_products' => $normalizedProducts,
            'items' => $items,
            'base_mapped_total' => $this->centsToMoney($baseMappedCents),
            'price_gap' => $this->centsToMoney($priceGapCents),
            'mapping_status' => $mappingStatus,
            'notes' => $notes === [] ? null : implode(' ', $notes),
        ];
    }

    /**
     * @return array{
     *     source_order_number: string,
     *     ordered_at: Carbon|null,
     *     paid_at: Carbon|null,
     *     outlet_name: string,
     *     raw_products: string,
     *     payment_method_raw: string,
     *     payment_method_mapped: string,
     *     payment_channel_raw: string|null,
     *     operator_raw: string|null,
     *     unpaid_amount: float,
     *     total_amount: float
     * }|null
     */
    protected function parseChunk(string $chunk): ?array
    {
        $pattern = '/^(CS\/\d{2}\/\d{6}\/\d{4})\s+'
            . '(\d{2}-\d{2}-\s*\d{4}\s+\d{2}:\d{2}:\d{2})\s+'
            . '(\d{2}-\d{2}-\s*\d{4}\s+\d{2}:\d{2}:\d{2})\s+'
            . 'AHWA\s+Warkop\s*'
            . '(.*?)\s*LainnyaRp([\d,]+\.\d{2})\s*Rp([\d,]+\.\d{2})\s*'
            . '(Cash|Bank Transfer,\s*Qris)\s*(.*)$/s';

        if (! preg_match($pattern, trim($chunk), $matches)) {
            return null;
        }

        $tail = trim($matches[8]);
        $paymentChannelRaw = null;
        $operatorRaw = $this->collapseWhitespace($tail);

        if ($matches[7] !== 'Cash' && $tail !== '') {
            $tailLines = array_values(array_filter(array_map(
                fn (string $line): string => $this->collapseWhitespace($line),
                preg_split('/\R+/', $tail) ?: [],
            )));

            $paymentChannelRaw = $tailLines[0] ?? null;
            $operatorRaw = implode(' ', array_slice($tailLines, 1)) ?: null;
        }

        return [
            'source_order_number' => $matches[1],
            'ordered_at' => $this->parsePdfDate($matches[2]),
            'paid_at' => $this->parsePdfDate($matches[3]),
            'outlet_name' => 'AHWA Warkop',
            'raw_products' => $this->collapseWhitespace($matches[4]),
            'payment_method_raw' => $this->collapseWhitespace($matches[7]),
            'payment_method_mapped' => $this->mapPaymentMethod($matches[7]),
            'payment_channel_raw' => $paymentChannelRaw,
            'operator_raw' => $operatorRaw ?: null,
            'unpaid_amount' => $this->parseMoney($matches[5]),
            'total_amount' => $this->parseMoney($matches[6]),
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function splitProducts(string $rawProducts): array
    {
        $items = preg_split('/\s*,\s*/', $this->collapseWhitespace($rawProducts)) ?: [];

        return array_values(array_filter(array_map('trim', $items)));
    }

    protected function parsePdfDate(string $value): ?Carbon
    {
        $normalized = preg_replace('/\s+/', '', $value) ?? $value;

        return Carbon::createFromFormat('d-m-YH:i:s', $normalized, 'Asia/Jakarta') ?: null;
    }

    protected function mapPaymentMethod(string $rawMethod): string
    {
        $normalized = $this->collapseWhitespace($rawMethod);

        return $normalized === 'Cash' ? 'cash' : 'bank_transfer_qris';
    }

    protected function parseMoney(string $value): float
    {
        return (float) str_replace([',', 'Rp', ' '], '', $value);
    }

    protected function collapseWhitespace(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', $value) ?? $value);
    }

    protected function moneyToCents(float $amount): int
    {
        return (int) round($amount * 100);
    }

    protected function centsToMoney(int $cents): float
    {
        return round($cents / 100, 2);
    }
}
