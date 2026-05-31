<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\CashierSession;
use App\Models\Payment;
use App\Models\User;
use DomainException;

class CashierSessionService
{
    public function getActiveSession(User $user): ?CashierSession
    {
        return CashierSession::query()
            ->where('user_id', $user->id)
            ->where('status', CashierSession::STATUS_OPEN)
            ->whereNull('closed_at')
            ->latest('opened_at')
            ->first();
    }

    public function openSession(
        User $user,
        Attendance $attendance,
        string $deviceId,
        float $openingCash,
        ?string $openingNotes = null
    ): CashierSession {
        if ($openingCash < 0) {
            throw new DomainException('Modal awal tidak boleh bernilai negatif.');
        }

        $activeSession = $this->getActiveSession($user);

        if ($activeSession) {
            throw new DomainException('Masih ada sesi kas yang terbuka. Tutup sesi sebelumnya terlebih dahulu.');
        }

        return CashierSession::query()->create([
            'user_id' => $user->id,
            'attendance_id' => $attendance->id,
            'device_id' => trim($deviceId) !== '' ? trim($deviceId) : null,
            'status' => CashierSession::STATUS_OPEN,
            'opened_at' => now(),
            'opening_cash' => $openingCash,
            'expected_cash' => $openingCash,
            'opening_notes' => filled($openingNotes) ? trim((string) $openingNotes) : null,
        ]);
    }

    public function closeSession(User $user, float $actualCash, ?string $closingNotes = null): CashierSession
    {
        if ($actualCash < 0) {
            throw new DomainException('Uang aktual tidak boleh bernilai negatif.');
        }

        $session = $this->getActiveSession($user);

        if (! $session) {
            throw new DomainException('Tidak ada sesi kas aktif yang bisa ditutup.');
        }

        $session = $this->syncExpectedCash($session);
        $expectedCash = (float) $session->expected_cash;

        $session->update([
            'status' => CashierSession::STATUS_CLOSED,
            'closed_at' => now(),
            'actual_cash' => $actualCash,
            'difference_amount' => round($actualCash - $expectedCash, 2),
            'closing_notes' => filled($closingNotes) ? trim((string) $closingNotes) : null,
            'closed_by' => $user->id,
        ]);

        return $session->fresh(['user', 'attendance', 'closedBy']);
    }

    public function syncExpectedCash(CashierSession $session): CashierSession
    {
        $cashSalesTotal = Payment::query()
            ->where('cashier_session_id', $session->id)
            ->where('method', \App\Models\Order::PAYMENT_CASH)
            ->where('status', 'paid')
            ->sum('amount');

        $expectedCash = round((float) $session->opening_cash + (float) $cashSalesTotal, 2);

        $session->updateQuietly([
            'expected_cash' => $expectedCash,
        ]);

        return $session->fresh();
    }
}
