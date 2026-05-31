<?php

namespace App\Filament\Resources\OrderingQrs\Schemas;

use App\Models\OrderingQr;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class OrderingQrInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('QR Pemesanan')
                    ->columnSpanFull()
                    ->columns(2)
                    ->schema([
                        TextEntry::make('name')
                            ->label('Nama QR'),
                        TextEntry::make('table_number')
                            ->label('Nomor Meja'),
                        TextEntry::make('stockLocation.name')
                            ->label('Lokasi Stok'),
                        TextEntry::make('is_active')
                            ->label('Status')
                            ->badge()
                            ->formatStateUsing(fn (bool $state): string => $state ? 'Aktif' : 'Nonaktif')
                            ->color(fn (bool $state): string => $state ? 'success' : 'gray'),
                        TextEntry::make('slug')
                            ->label('Slug URL'),
                        TextEntry::make('public_url')
                            ->label('URL Publik')
                            ->state(fn (OrderingQr $record): string => sprintf(
                                '<a href="%1$s" target="_blank" rel="noopener noreferrer" class="text-primary-600 underline">%1$s</a>',
                                e($record->publicUrl())
                            ))
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('qr_preview')
                            ->label('Preview QR')
                            ->state(fn (OrderingQr $record): string => sprintf(
                                '<img src="%s" alt="QR %s" class="h-64 w-64 rounded-2xl border border-gray-200 bg-white p-4" />',
                                e($record->qrImageUri()),
                                e($record->name)
                            ))
                            ->html()
                            ->columnSpanFull(),
                        TextEntry::make('notes')
                            ->label('Catatan')
                            ->placeholder('-')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
