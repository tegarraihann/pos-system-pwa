<?php

namespace App\Filament\Tables\Concerns;

use Filament\Tables\Enums\PaginationMode;
use Filament\Tables\Table;

trait HasNumericPagination
{
    protected static function applyNumericPagination(
        Table $table,
        array $pageOptions = [10, 25, 50],
        int $defaultPage = 10,
    ): Table {
        return $table
            ->paginationMode(PaginationMode::Default)
            ->paginationPageOptions($pageOptions)
            ->defaultPaginationPageOption($defaultPage);
    }
}
