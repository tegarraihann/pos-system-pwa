<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\SalesReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_access_sales_report(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewSalesReport:Report', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);

        $this->assertTrue($user->can('ViewSalesReport:Report'));
        $this->assertTrue(SalesReport::canAccess());
    }

    public function test_user_without_permission_cannot_access_sales_report(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse(SalesReport::canAccess());
    }
}
