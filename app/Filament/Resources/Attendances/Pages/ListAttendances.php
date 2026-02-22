<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Enums\Width;

class ListAttendances extends ListRecords
{
    protected static string $resource = AttendanceResource::class;

    protected Width|string|null $maxContentWidth = Width::SevenExtraLarge;
}

