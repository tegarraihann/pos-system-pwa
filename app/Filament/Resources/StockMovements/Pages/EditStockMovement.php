<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Validation\ValidationException;

class EditStockMovement extends EditRecord
{
    protected static string $resource = StockMovementResource::class;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (! StockLocation::isMultiLocationEnabled()) {
            if (($data['type'] ?? null) === StockMovement::TYPE_TRANSFER) {
                throw ValidationException::withMessages([
                    'type' => 'Transfer dinonaktifkan saat mode lokasi tunggal aktif.',
                ]);
            }

            $defaultLocation = StockLocation::resolveDefaultLocation();

            if (! $defaultLocation) {
                throw ValidationException::withMessages([
                    'type' => 'Lokasi default stok belum tersedia. Tambahkan minimal 1 lokasi aktif atau aktifkan mode multi lokasi.',
                ]);
            }

            $data = match ($data['type'] ?? null) {
                StockMovement::TYPE_IN => [
                    ...$data,
                    'from_location_id' => null,
                    'to_location_id' => $defaultLocation->id,
                ],
                StockMovement::TYPE_OUT, StockMovement::TYPE_ADJUSTMENT => [
                    ...$data,
                    'from_location_id' => $defaultLocation->id,
                    'to_location_id' => null,
                ],
                default => $data,
            };
        }

        return $data;
    }
}
