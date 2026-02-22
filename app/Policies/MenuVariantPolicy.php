<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\MenuVariant;
use Illuminate\Auth\Access\HandlesAuthorization;

class MenuVariantPolicy
{
    use HandlesAuthorization;
    
    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:MenuVariant');
    }

    public function view(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('View:MenuVariant');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:MenuVariant');
    }

    public function update(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('Update:MenuVariant');
    }

    public function delete(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('Delete:MenuVariant');
    }

    public function restore(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('Restore:MenuVariant');
    }

    public function forceDelete(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('ForceDelete:MenuVariant');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:MenuVariant');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:MenuVariant');
    }

    public function replicate(AuthUser $authUser, MenuVariant $menuVariant): bool
    {
        return $authUser->can('Replicate:MenuVariant');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:MenuVariant');
    }

}