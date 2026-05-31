<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Services\CashierSessionService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashierSessionServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_opens_syncs_and_closes_a_cashier_session(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'shift_date' => now()->toDateString(),
            'status' => Attendance::STATUS_CHECKED_IN,
            'check_in_at' => now(),
        ]);

        $service = app(CashierSessionService::class);

        $session = $service->openSession($user, $attendance, 'device-1', 50000, 'Modal awal pagi');

        $this->assertSame(50000.0, (float) $session->opening_cash);
        $this->assertSame(50000.0, (float) $session->expected_cash);

        $order = Order::query()->create([
            'status' => Order::STATUS_SERVED,
            'customer_type' => Order::CUSTOMER_WALK_IN,
            'payment_method' => Order::PAYMENT_CASH,
            'cashier_session_id' => $session->id,
            'created_by' => $user->id,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'cashier_session_id' => $session->id,
            'method' => Order::PAYMENT_CASH,
            'amount' => 100000,
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $session = $service->syncExpectedCash($session);

        $this->assertSame(150000.0, (float) $session->expected_cash);

        $closedSession = $service->closeSession($user, 145000, 'Ada selisih kecil');

        $this->assertSame(145000.0, (float) $closedSession->actual_cash);
        $this->assertSame(-5000.0, (float) $closedSession->difference_amount);
        $this->assertSame('closed', $closedSession->status);
        $this->assertNotNull($closedSession->closed_at);
    }

    public function test_it_rejects_opening_a_second_active_session(): void
    {
        $user = User::factory()->create();
        $attendance = Attendance::query()->create([
            'user_id' => $user->id,
            'shift_date' => now()->toDateString(),
            'status' => Attendance::STATUS_CHECKED_IN,
            'check_in_at' => now(),
        ]);

        $service = app(CashierSessionService::class);
        $service->openSession($user, $attendance, 'device-1', 10000);

        $this->expectException(DomainException::class);
        $service->openSession($user, $attendance, 'device-1', 20000);
    }
}
