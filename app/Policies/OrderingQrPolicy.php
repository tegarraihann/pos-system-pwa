<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\OrderingQr;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class OrderingQrPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:OrderingQr');
    }

    public function view(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return $authUser->can('View:OrderingQr');
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:OrderingQr');
    }

    public function update(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return $authUser->can('Update:OrderingQr');
    }

    public function delete(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return $authUser->can('Delete:OrderingQr');
    }

    public function restore(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return false;
    }

    public function forceDelete(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return false;
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return false;
    }

    public function replicate(AuthUser $authUser, OrderingQr $orderingQr): bool
    {
        return false;
    }

    public function reorder(AuthUser $authUser): bool
    {
        return false;
    }
}
