<?php

namespace App\Filament\Resources\StockMovements\Pages;

use App\Filament\Resources\StockMovements\StockMovementResource;
use App\Models\StockLocation;
use App\Models\StockMovement;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class CreateStockMovement extends CreateRecord
{
    protected static string $resource = StockMovementResource::class;
    protected Width | string | null $maxContentWidth = Width::FiveExtraLarge;

    public function getTitle(): string | Htmlable
    {
        return 'Tambah Pergerakan Stok';
    }

    public function getBreadcrumb(): string
    {
        return 'Tambah';
    }

    protected function getCreateFormAction(): Action
    {
        return parent::getCreateFormAction()
            ->label('Simpan');
    }

    protected function getCreateAnotherFormAction(): Action
    {
        return parent::getCreateAnotherFormAction()
            ->label('Simpan & tambah lagi');
    }

    protected function getCancelFormAction(): Action
    {
        return parent::getCancelFormAction()
            ->label('Batal');
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Pergerakan stok berhasil ditambahkan';
    }

    protected function mutateFormDataBeforeCreate(array $data): array
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

        $data['created_by'] = auth()->id();

        return $data;
    }
}
