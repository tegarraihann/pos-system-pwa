<?php

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;
use Filament\Tables\Enums\PaginationMode;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    public function getTitle(): string | Htmlable
    {
        return 'Izin Akses';
    }

    public function getBreadcrumb(): ?string
    {
        return 'Izin Akses';
    }

    public function getTableRecordsPerPage(): int | string | null
    {
        return 5;
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Tambah Izin Akses')
                ->modalHeading('Tambah Izin Akses')
                ->modalSubmitActionLabel('Simpan')
                ->modalCancelActionLabel('Batal')
                ->modalWidth(Width::ExtraLarge)
                ->successNotificationTitle('Izin akses berhasil ditambahkan')
                ->createAnother(false),
        ];
    }

    public function getDefaultActionUrl(Action $action): ?string
    {
        if (in_array($action->getName(), ['create', 'edit'], true)) {
            return null;
        }

        return parent::getDefaultActionUrl($action);
    }

    protected function paginateTableQuery(Builder $query): Paginator | CursorPaginator
    {
        $perPage = $this->getTableRecordsPerPage();

        $mode = $this->getTable()->getPaginationMode();

        if ($mode === PaginationMode::Simple) {
            return $query->simplePaginate(
                perPage: ($perPage === 'all') ? $query->toBase()->getCountForPagination() : $perPage,
                pageName: $this->getTablePaginationPageName(),
            );
        }

        if ($mode === PaginationMode::Cursor) {
            return $query->cursorPaginate(
                perPage: ($perPage === 'all') ? $query->toBase()->getCountForPagination() : $perPage,
                cursorName: $this->getTablePaginationPageName(),
            );
        }

        $total = $query->toBase()->getCountForPagination();

        /** @var LengthAwarePaginator $records */
        $records = $query->paginate(
            perPage: ($perPage === 'all') ? $total : $perPage,
            pageName: $this->getTablePaginationPageName(),
            total: $total,
        );

        return $records->onEachSide($records->lastPage());
    }
}
