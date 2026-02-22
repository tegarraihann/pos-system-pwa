<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use Filament\Facades\Filament;
use Filament\Resources\Resource;

abstract class BaseResource extends Resource
{
    public static function canAccess(): bool
    {
        $user = Filament::auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can(static::getNavigationPermissionName());
    }

    public static function shouldRegisterNavigation(): bool
    {
        return parent::shouldRegisterNavigation() && static::canAccess();
    }

    protected static function getNavigationPermissionName(): string
    {
        return 'ViewAny:' . class_basename(static::getModel());
    }
}
