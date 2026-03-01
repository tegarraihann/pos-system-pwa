<?php

namespace App\Filament\Resources\Permissions\Schemas;

use Filament\Schemas\Schema;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class PermissionForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Izin Akses')
                    ->columnSpanFull()
                    ->columns(1)
                    ->schema([
                        Hidden::make('guard_name')
                            ->default(config('auth.defaults.guard', 'web')),
                        TextInput::make('name')
                            ->label('Nama Izin')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('Contoh: View:Order')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
