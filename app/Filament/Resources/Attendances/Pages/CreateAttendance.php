<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Enums\Width;

class CreateAttendance extends CreateRecord
{
    protected static string $resource = AttendanceResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;
}

