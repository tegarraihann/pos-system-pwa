<?php

namespace Tests\Feature;

use App\Models\HistoricalOrderImport;
use Database\Seeders\AhwaWarkopMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalSalesPdfImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_imports_april_2026_sales_pdf_into_staging_tables(): void
    {
        $this->seed(AhwaWarkopMasterDataSeeder::class);

        $this->artisan('historical:import-ahwa-sales')
            ->expectsOutput('Import histori AHWA Warkop selesai.')
            ->assertExitCode(0);

        $this->assertSame(512, HistoricalOrderImport::query()->count());

        $singleOrder = HistoricalOrderImport::query()
            ->where('source_order_number', 'CS/01/260401/0001')
            ->firstOrFail();

        $this->assertSame(HistoricalOrderImport::STATUS_MATCHED, $singleOrder->mapping_status);
        $this->assertSame('cash', $singleOrder->payment_method_mapped);
        $this->assertSame(10000.0, (float) $singleOrder->total_amount);

        $singleItem = $singleOrder->items()->firstOrFail();
        $this->assertSame('PRIMA 600 ML', $singleItem->normalized_item_name);
        $this->assertSame(1.0, (float) $singleItem->listed_qty);
        $this->assertSame(2.0, (float) $singleItem->inferred_qty);
        $this->assertSame(5000.0, (float) $singleItem->unit_price);

        $bankTransferOrder = HistoricalOrderImport::query()
            ->where('source_order_number', 'CS/01/260403/0014')
            ->firstOrFail();

        $this->assertSame(HistoricalOrderImport::STATUS_MATCHED, $bankTransferOrder->mapping_status);
        $this->assertSame('bank_transfer_qris', $bankTransferOrder->payment_method_mapped);
        $this->assertSame('BSI', $bankTransferOrder->payment_channel_raw);
        $this->assertCount(2, $bankTransferOrder->items);

        $ambiguousOrder = HistoricalOrderImport::query()
            ->where('source_order_number', 'CS/01/260401/0002')
            ->firstOrFail();

        $this->assertSame(HistoricalOrderImport::STATUS_AMBIGUOUS, $ambiguousOrder->mapping_status);
        $this->assertSame(5000.0, (float) $ambiguousOrder->price_gap);
    }
}
