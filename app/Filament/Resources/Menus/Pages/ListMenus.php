<?php

namespace App\Filament\Resources\Menus\Pages;

use App\Filament\Resources\Menus\MenuResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListMenus extends ListRecords
{
    protected static string $resource = MenuResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Menu';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Menu';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Menu'),
        ];
    }
}
