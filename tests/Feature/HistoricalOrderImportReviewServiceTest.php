<?php

namespace Tests\Feature;

use App\Models\HistoricalOrderImport;
use App\Services\HistoricalOrderImportReviewService;
use Database\Seeders\AhwaWarkopMasterDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalOrderImportReviewServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_can_refresh_and_mark_a_staging_order_ready_for_migration(): void
    {
        $this->seed(AhwaWarkopMasterDataSeeder::class);
        $this->artisan('historical:import-ahwa-sales')->assertExitCode(0);

        $service = app(HistoricalOrderImportReviewService::class);

        $import = HistoricalOrderImport::query()
            ->where('source_order_number', 'CS/01/260401/0002')
            ->firstOrFail();

        $this->assertSame(HistoricalOrderImport::STATUS_AMBIGUOUS, $import->mapping_status);

        $item = $import->items()
            ->where('normalized_item_name', 'MOCHI VANILLA')
            ->firstOrFail();

        $item->update([
            'inferred_qty' => 2,
        ]);

        $import = $service->refreshImport($import->refresh());

        $this->assertSame(HistoricalOrderImport::STATUS_MATCHED, $import->mapping_status);
        $this->assertSame(0.0, (float) $import->price_gap);

        $import = $service->markReady($import, 'Qty mochi vanilla dikoreksi dari histori.');

        $this->assertTrue($import->ready_for_migration);
        $this->assertSame('Qty mochi vanilla dikoreksi dari histori.', $import->review_notes);
        $this->assertNotNull($import->reviewed_at);
    }
}
