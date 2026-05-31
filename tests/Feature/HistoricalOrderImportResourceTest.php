<?php

namespace Tests\Feature;

use App\Filament\Resources\HistoricalOrderImports\HistoricalOrderImportResource;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HistoricalOrderImportResourceTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_permission_can_access_historical_order_import_resource(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewAny:HistoricalOrderImport', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);

        $this->assertTrue(HistoricalOrderImportResource::canAccess());
    }

    public function test_user_without_permission_cannot_access_historical_order_import_resource(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->assertFalse(HistoricalOrderImportResource::canAccess());
    }
}
