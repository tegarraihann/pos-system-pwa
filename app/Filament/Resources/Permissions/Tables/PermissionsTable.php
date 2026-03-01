<?php

namespace App\Filament\Resources\Permissions\Tables;

use App\Filament\Tables\Concerns\HasNumericPagination;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PermissionsTable
{
    use HasNumericPagination;

    public static function configure(Table $table): Table
    {
        $table = self::applyNumericPagination($table, [5], 5);

        return $table
            ->searchPlaceholder('Cari izin akses...')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama Izin')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('guard_name')
                    ->label('Guard')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Dibuat pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diperbarui pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()
                    ->label('Detail'),
                EditAction::make()
                    ->label('Ubah')
                    ->modalHeading('Ubah Izin Akses')
                    ->modalSubmitActionLabel('Simpan perubahan')
                    ->modalCancelActionLabel('Batal')
                    ->modalWidth(Width::ExtraLarge),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Hapus terpilih'),
                ]),
            ]);
    }
}
