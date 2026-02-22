<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use DomainException;
use Illuminate\Support\Carbon;

class AttendanceService
{
    public function getActiveAttendance(User $user): ?Attendance
    {
        return Attendance::query()
            ->where('user_id', $user->id)
            ->where('status', Attendance::STATUS_CHECKED_IN)
            ->whereNull('check_out_at')
            ->latest('check_in_at')
            ->first();
    }

    public function checkIn(User $user, string $deviceId, float $latitude, float $longitude): Attendance
    {
        if ($deviceId === '') {
            throw new DomainException('Perangkat tidak valid. Refresh halaman lalu coba lagi.');
        }

        $this->validateGeofence($latitude, $longitude);

        $activeAttendance = $this->getActiveAttendance($user);

        if ($activeAttendance && $activeAttendance->device_id !== null && $activeAttendance->device_id !== $deviceId) {
            throw new DomainException('Anda sudah check-in di perangkat lain. Gunakan perangkat yang sama untuk check-out.');
        }

        if ($activeAttendance) {
            throw new DomainException('Anda sudah check-in. Silakan check-out dulu sebelum check-in ulang.');
        }

        return Attendance::query()->create([
            'user_id' => $user->id,
            'shift_date' => now()->toDateString(),
            'device_id' => $deviceId,
            'status' => Attendance::STATUS_CHECKED_IN,
            'check_in_at' => now(),
            'check_in_lat' => $latitude,
            'check_in_lng' => $longitude,
        ]);
    }

    public function checkOut(User $user, string $deviceId, float $latitude, float $longitude): Attendance
    {
        if ($deviceId === '') {
            throw new DomainException('Perangkat tidak valid. Refresh halaman lalu coba lagi.');
        }

        $activeAttendance = $this->getActiveAttendance($user);

        if (! $activeAttendance) {
            throw new DomainException('Anda belum check-in hari ini.');
        }

        if ($this->isDeviceLockEnabled() && $activeAttendance->device_id !== null && $activeAttendance->device_id !== $deviceId) {
            throw new DomainException('Check-out harus dari perangkat yang sama dengan saat check-in.');
        }

        $this->validateGeofence($latitude, $longitude);

        $checkOutAt = now();
        $workedMinutes = max(
            Carbon::parse($activeAttendance->check_in_at)->diffInMinutes($checkOutAt),
            0
        );

        $activeAttendance->update([
            'status' => Attendance::STATUS_CHECKED_OUT,
            'check_out_at' => $checkOutAt,
            'check_out_lat' => $latitude,
            'check_out_lng' => $longitude,
            'work_minutes' => $workedMinutes,
        ]);

        return $activeAttendance->fresh();
    }

    /**
     * @return array{is_enabled: bool, distance_meters: float, radius_meters: int}
     */
    public function evaluateGeofence(float $latitude, float $longitude): array
    {
        $radiusMeters = $this->radiusMeters();
        $officeLatitude = $this->officeLatitude();
        $officeLongitude = $this->officeLongitude();

        if (! $this->isGeofenceEnabled() || $officeLatitude === null || $officeLongitude === null) {
            return [
                'is_enabled' => false,
                'distance_meters' => 0.0,
                'radius_meters' => $radiusMeters,
            ];
        }

        $distanceMeters = $this->haversineDistanceMeters(
            $latitude,
            $longitude,
            $officeLatitude,
            $officeLongitude
        );

        return [
            'is_enabled' => true,
            'distance_meters' => $distanceMeters,
            'radius_meters' => $radiusMeters,
        ];
    }

    protected function validateGeofence(float $latitude, float $longitude): void
    {
        $evaluation = $this->evaluateGeofence($latitude, $longitude);

        if (! $evaluation['is_enabled']) {
            return;
        }

        if ($evaluation['distance_meters'] <= $evaluation['radius_meters']) {
            return;
        }

        throw new DomainException('Lokasi di luar area absensi. Pastikan Anda berada di area outlet.');
    }

    protected function isDeviceLockEnabled(): bool
    {
        return (bool) config('attendance.device_lock_enabled', true);
    }

    protected function isGeofenceEnabled(): bool
    {
        return (bool) data_get(config('attendance.geofence'), 'enabled', true);
    }

    protected function officeLatitude(): ?float
    {
        $value = data_get(config('attendance.geofence'), 'latitude');

        return is_numeric($value) ? (float) $value : null;
    }

    protected function officeLongitude(): ?float
    {
        $value = data_get(config('attendance.geofence'), 'longitude');

        return is_numeric($value) ? (float) $value : null;
    }

    protected function radiusMeters(): int
    {
        return max((int) data_get(config('attendance.geofence'), 'radius_meters', 200), 1);
    }

    protected function haversineDistanceMeters(
        float $fromLatitude,
        float $fromLongitude,
        float $toLatitude,
        float $toLongitude
    ): float {
        $earthRadius = 6371000;

        $deltaLatitude = deg2rad($toLatitude - $fromLatitude);
        $deltaLongitude = deg2rad($toLongitude - $fromLongitude);
        $fromLatitude = deg2rad($fromLatitude);
        $toLatitude = deg2rad($toLatitude);

        $a = sin($deltaLatitude / 2) ** 2
            + cos($fromLatitude) * cos($toLatitude) * sin($deltaLongitude / 2) ** 2;

        return 2 * $earthRadius * asin(min(1, sqrt($a)));
    }
}

