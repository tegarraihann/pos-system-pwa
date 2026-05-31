<?php

namespace App\Providers;

use App\Models\Attendance;
use App\Models\CashierSession;
use App\Models\Order;
use App\Models\OrderingQr;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Policies\AttendancePolicy;
use App\Policies\CashierSessionPolicy;
use App\Policies\OrderPolicy;
use App\Policies\OrderingQrPolicy;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Attendance::class => AttendancePolicy::class,
        CashierSession::class => CashierSessionPolicy::class,
        Order::class => OrderPolicy::class,
        OrderingQr::class => OrderingQrPolicy::class,
        User::class => UserPolicy::class,
        Role::class => RolePolicy::class,
        Permission::class => PermissionPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
