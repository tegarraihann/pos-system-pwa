<?php

declare(strict_types=1);

namespace App\Policies;

use Illuminate\Foundation\Auth\User as AuthUser;
use App\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:Order') || $authUser->can('ViewAny:PublicOrder');
    }

    public function view(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('View:Order')
            || ($order->order_source === Order::SOURCE_PUBLIC_QR && $authUser->can('View:PublicOrder'));
    }

    public function create(AuthUser $authUser): bool
    {
        return $authUser->can('Create:Order');
    }

    public function update(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('Update:Order')
            || ($order->order_source === Order::SOURCE_PUBLIC_QR && $authUser->can('Process:PublicOrder'));
    }

    public function delete(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('Delete:Order');
    }

    public function restore(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('Restore:Order');
    }

    public function forceDelete(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('ForceDelete:Order');
    }

    public function forceDeleteAny(AuthUser $authUser): bool
    {
        return $authUser->can('ForceDeleteAny:Order');
    }

    public function restoreAny(AuthUser $authUser): bool
    {
        return $authUser->can('RestoreAny:Order');
    }

    public function replicate(AuthUser $authUser, Order $order): bool
    {
        return $authUser->can('Replicate:Order');
    }

    public function reorder(AuthUser $authUser): bool
    {
        return $authUser->can('Reorder:Order');
    }

}
