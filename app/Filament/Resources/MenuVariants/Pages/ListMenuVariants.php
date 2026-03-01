<?php

namespace App\Filament\Resources\MenuVariants\Pages;

use App\Filament\Resources\MenuVariants\MenuVariantResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListMenuVariants extends ListRecords
{
    protected static string $resource = MenuVariantResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Varian Menu';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Varian Menu';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Varian Menu'),
        ];
    }
}
