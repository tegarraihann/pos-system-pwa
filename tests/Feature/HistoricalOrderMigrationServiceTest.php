<?php

namespace Tests\Feature;

use App\Models\HistoricalOrderImport;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockMovement;
use App\Services\HistoricalOrderMigrationService;
use App\Services\HistoricalOrderImportReviewService;
use Database\Seeders\AhwaWarkopInitialRecipesSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalOrderMigrationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_migrates_a_ready_historical_order_into_final_orders_without_consuming_stock(): void
    {
        $this->seed(AhwaWarkopInitialRecipesSeeder::class);
        $this->artisan('historical:import-ahwa-sales')->assertExitCode(0);

        $reviewService = app(HistoricalOrderImportReviewService::class);
        $migrationService = app(HistoricalOrderMigrationService::class);

        $import = HistoricalOrderImport::query()
            ->where('source_order_number', 'CS/01/260403/0014')
            ->firstOrFail();

        $import = $reviewService->markReady($import, 'Cocok penuh dengan master menu.');
        $import = $migrationService->migrate($import);

        $this->assertNotNull($import->migrated_order_id);
        $this->assertNotNull($import->migrated_at);

        $order = Order::query()->findOrFail($import->migrated_order_id);

        $this->assertSame('CS/01/260403/0014', $order->order_number);
        $this->assertSame(Order::STATUS_SERVED, $order->status);
        $this->assertSame(Order::SOURCE_POS, $order->order_source);
        $this->assertSame('bank_transfer_qris', $order->payment_method);
        $this->assertSame(24000.0, (float) $order->paid_total);
        $this->assertCount(2, $order->items);
        $this->assertNotNull($order->cost_accounted_at);

        $payment = Payment::query()->where('order_id', $order->id)->firstOrFail();
        $this->assertSame('bank_transfer_qris', $payment->method);
        $this->assertSame('paid', $payment->status);
        $this->assertSame(24000.0, (float) $payment->amount);

        $this->assertDatabaseMissing('stock_movements', [
            'reference_no' => 'ORDER-CS/01/260403/0014',
            'type' => StockMovement::TYPE_OUT,
        ]);
    }
}
