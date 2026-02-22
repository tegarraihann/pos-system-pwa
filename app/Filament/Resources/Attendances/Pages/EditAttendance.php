<?php

namespace App\Filament\Resources\Attendances\Pages;

use App\Filament\Resources\Attendances\AttendanceResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Enums\Width;

class EditAttendance extends EditRecord
{
    protected static string $resource = AttendanceResource::class;

    protected Width|string|null $maxContentWidth = Width::FiveExtraLarge;
}

