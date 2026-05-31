<?php

namespace Tests\Feature;

use App\Models\Menu;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\OrderingQr;
use App\Models\Payment;
use App\Models\StockLocation;
use App\Services\MidtransService;
use App\Services\PublicOrderingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery\MockInterface;
use Tests\TestCase;

class PublicOrderingTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_order_page_only_shows_active_menu_variants(): void
    {
        $location = StockLocation::query()->create([
            'code' => 'OUTLET-1',
            'name' => 'Outlet 1',
            'is_active' => true,
        ]);

        $orderingQr = OrderingQr::query()->create([
            'name' => 'Meja A1',
            'table_number' => 'A1',
            'stock_location_id' => $location->id,
            'is_active' => true,
        ]);

        $activeMenu = Menu::query()->create([
            'name' => 'Es Kopi Susu',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        MenuVariant::query()->create([
            'menu_id' => $activeMenu->id,
            'kd_varian' => 'REG',
            'price' => 18000,
            'is_active' => true,
            'stock' => 10,
        ]);

        $inactiveMenu = Menu::query()->create([
            'name' => 'Menu Nonaktif',
            'is_active' => false,
            'is_stock_managed' => false,
        ]);

        MenuVariant::query()->create([
            'menu_id' => $inactiveMenu->id,
            'kd_varian' => 'OFF',
            'price' => 15000,
            'is_active' => true,
            'stock' => 10,
        ]);

        $inactiveVariantMenu = Menu::query()->create([
            'name' => 'Menu Habis',
            'is_active' => true,
            'is_stock_managed' => true,
        ]);

        MenuVariant::query()->create([
            'menu_id' => $inactiveVariantMenu->id,
            'kd_varian' => 'HABIS',
            'price' => 22000,
            'is_active' => false,
            'stock' => 0,
        ]);

        $response = $this->get(route('public-ordering.show', $orderingQr));

        $response->assertOk();
        $response->assertSee('Es Kopi Susu');
        $response->assertDontSee('Menu Nonaktif');
        $response->assertDontSee('Menu Habis');
    }

    public function test_it_creates_a_pending_payment_public_order_and_redirects_to_payment_page(): void
    {
        config()->set('midtrans.server_key', 'server-key-test');
        config()->set('midtrans.client_key', 'client-key-test');

        $this->mock(MidtransService::class, function (MockInterface $mock): void {
            $mock->shouldReceive('createSnapTransaction')
                ->once()
                ->andReturn([
                    'token' => 'snap-token-test',
                    'redirect_url' => 'https://midtrans.test/pay/abc',
                ]);
        });

        $location = StockLocation::query()->create([
            'code' => 'OUTLET-1',
            'name' => 'Outlet 1',
            'is_active' => true,
        ]);

        $orderingQr = OrderingQr::query()->create([
            'name' => 'Meja B2',
            'table_number' => 'B2',
            'stock_location_id' => $location->id,
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'name' => 'Cappuccino',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $variant = MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'kd_varian' => 'HOT',
            'price' => 25000,
            'is_active' => true,
            'stock' => 10,
        ]);

        $response = $this->post(route('public-ordering.store', $orderingQr), [
            'guest_name' => 'Budi',
            'guest_phone' => '08123456789',
            'notes' => 'Tanpa sedotan',
            'quantities' => [
                $variant->id => 2,
            ],
        ]);

        $order = Order::query()->firstOrFail();
        $payment = Payment::query()->firstOrFail();

        $response->assertRedirectContains(route('public-ordering.payment', [
            'orderingQr' => $orderingQr,
            'orderNumber' => $order->order_number,
        ]));
        $response->assertSessionHasNoErrors();

        $this->assertSame(Order::SOURCE_PUBLIC_QR, $order->order_source);
        $this->assertSame(Order::STATUS_PENDING_PAYMENT, $order->status);
        $this->assertSame('Budi', $order->guest_name);
        $this->assertSame('08123456789', $order->guest_phone);
        $this->assertSame('B2', $order->table_number);
        $this->assertSame($orderingQr->id, $order->ordering_qr_id);
        $this->assertSame($location->id, $order->stock_location_id);
        $this->assertSame(1, $order->queue_number);
        $this->assertSame(50000.0, (float) $order->fresh()->grand_total);
        $this->assertCount(1, $order->items);
        $this->assertSame(2.0, (float) $order->items->first()->qty);
        $this->assertSame($order->id, $payment->order_id);
        $this->assertSame('pending', $payment->status);
        $this->assertSame('midtrans', $payment->gateway_provider);
        $this->assertSame('snap-token-test', $payment->gateway_token);
        $this->assertSame('https://midtrans.test/pay/abc', $payment->gateway_redirect_url);
    }

    public function test_midtrans_webhook_marks_public_order_as_paid(): void
    {
        config()->set('midtrans.server_key', 'server-key-test');

        [$order, $payment] = $this->createPublicPendingPaymentOrder();

        $payload = [
            'order_id' => $payment->gateway_ref,
            'status_code' => '200',
            'gross_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'transaction_status' => 'settlement',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-paid-1',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('midtrans.server_key')
        );

        $this->postJson('/midtrans/notification', $payload)
            ->assertOk();

        $this->assertSame(Order::STATUS_PAID, $order->fresh()->status);
        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame((float) $order->grand_total, (float) $order->fresh()->paid_total);
    }

    public function test_midtrans_webhook_marks_public_order_as_expired(): void
    {
        config()->set('midtrans.server_key', 'server-key-test');

        [$order, $payment] = $this->createPublicPendingPaymentOrder();

        $payload = [
            'order_id' => $payment->gateway_ref,
            'status_code' => '200',
            'gross_amount' => number_format((float) $payment->amount, 2, '.', ''),
            'transaction_status' => 'expire',
            'payment_type' => 'qris',
            'transaction_id' => 'trx-expired-1',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'] . $payload['status_code'] . $payload['gross_amount'] . config('midtrans.server_key')
        );

        $this->postJson('/midtrans/notification', $payload)
            ->assertOk();

        $this->assertSame(Order::STATUS_EXPIRED, $order->fresh()->status);
        $this->assertSame('expired', $payment->fresh()->status);
    }

    public function test_it_rejects_public_order_when_stock_is_insufficient(): void
    {
        config()->set('midtrans.server_key', 'server-key-test');
        config()->set('midtrans.client_key', 'client-key-test');

        $location = StockLocation::query()->create([
            'code' => 'OUTLET-1',
            'name' => 'Outlet 1',
            'is_active' => true,
        ]);

        $orderingQr = OrderingQr::query()->create([
            'name' => 'Meja C3',
            'table_number' => 'C3',
            'stock_location_id' => $location->id,
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'name' => 'Matcha Latte',
            'is_active' => true,
            'is_stock_managed' => true,
        ]);

        $variant = MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'kd_varian' => 'ICE',
            'price' => 28000,
            'is_active' => true,
            'stock' => 1,
        ]);

        $response = $this->from(route('public-ordering.show', $orderingQr))
            ->post(route('public-ordering.store', $orderingQr), [
                'guest_name' => 'Sinta',
                'quantities' => [
                    $variant->id => 3,
                ],
            ]);

        $response->assertRedirect(route('public-ordering.show', $orderingQr));
        $response->assertSessionHasErrors('quantities');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_inactive_qr_cannot_be_accessed(): void
    {
        $location = StockLocation::query()->create([
            'code' => 'OUTLET-1',
            'name' => 'Outlet 1',
            'is_active' => true,
        ]);

        $orderingQr = OrderingQr::query()->create([
            'name' => 'Meja D4',
            'table_number' => 'D4',
            'stock_location_id' => $location->id,
            'is_active' => false,
        ]);

        $this->get(route('public-ordering.show', $orderingQr))
            ->assertNotFound();
    }

    /**
     * @return array{0:Order,1:Payment}
     */
    protected function createPublicPendingPaymentOrder(): array
    {
        $location = StockLocation::query()->create([
            'code' => 'OUTLET-2',
            'name' => 'Outlet 2',
            'is_active' => true,
        ]);

        $orderingQr = OrderingQr::query()->create([
            'name' => 'Meja E5',
            'table_number' => 'E5',
            'stock_location_id' => $location->id,
            'is_active' => true,
        ]);

        $menu = Menu::query()->create([
            'name' => 'Americano',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $variant = MenuVariant::query()->create([
            'menu_id' => $menu->id,
            'kd_varian' => 'HOT',
            'price' => 22000,
            'is_active' => true,
            'stock' => 10,
        ]);

        /** @var PublicOrderingService $service */
        $service = app(PublicOrderingService::class);
        [$order, $payment] = $service->createPendingPaymentOrder($orderingQr, [
            'guest_name' => 'Andi',
            'guest_phone' => '0811000000',
            'notes' => 'No sugar',
            'quantities' => [
                $variant->id => 1,
            ],
        ]);

        $payment->update([
            'gateway_ref' => $payment->gateway_ref ?: $order->order_number . '-' . $payment->id,
        ]);

        return [$order->fresh(), $payment->fresh()];
    }
}
