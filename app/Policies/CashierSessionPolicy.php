<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\CashierSession;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as AuthUser;

class CashierSessionPolicy
{
    use HandlesAuthorization;

    public function viewAny(AuthUser $authUser): bool
    {
        return $authUser->can('ViewAny:CashierSession');
    }

    public function view(AuthUser $authUser, CashierSession $cashierSession): bool
    {
        return $authUser->can('View:CashierSession');
    }
}
