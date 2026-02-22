<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        $adminRole = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $superAdminRole = Role::firstOrCreate([
            'name' => 'super_admin',
            'guard_name' => $guard,
        ]);

        $kasirRole = Role::firstOrCreate([
            'name' => 'kasir',
            'guard_name' => $guard,
        ]);

        $kitchenRole = Role::firstOrCreate([
            'name' => 'kitchen',
            'guard_name' => $guard,
        ]);

        collect([
            'View:PosCashier',
            'View:KitchenDisplay',
            'CheckIn:Attendance',
            'CheckOut:Attendance',
            'Create:Attendance',
            'Delete:Attendance',
            'ForceDelete:Attendance',
            'ForceDeleteAny:Attendance',
            'Reorder:Attendance',
            'Replicate:Attendance',
            'Restore:Attendance',
            'RestoreAny:Attendance',
            'Update:Attendance',
            'View:Attendance',
            'ViewAny:Attendance',
        ])->each(function (string $permissionName) use ($guard): void {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        });

        $permissions = Permission::where('guard_name', $guard)->get();
        if ($permissions->isNotEmpty()) {
            $adminRole->syncPermissions($permissions);
            $superAdminRole->syncPermissions($permissions);
        }

        $permissionByName = $permissions->keyBy('name');

        $kasirPermissions = collect([
            'View:PosCashier',
            'CheckIn:Attendance',
            'CheckOut:Attendance',
        ])->map(fn (string $name) => $permissionByName->get($name))
            ->filter()
            ->values();

        if ($kasirPermissions->isNotEmpty()) {
            $kasirRole->syncPermissions($kasirPermissions);
        }

        $kitchenPermissions = collect([
            'View:KitchenDisplay',
        ])->map(fn (string $name) => $permissionByName->get($name))
            ->filter()
            ->values();

        if ($kitchenPermissions->isNotEmpty()) {
            $kitchenRole->syncPermissions($kitchenPermissions);
        }

        $adminUser = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (method_exists($adminUser, 'assignRole')) {
            $adminUser->assignRole($adminRole);
        }

        $superAdminUser = User::firstOrCreate(
            ['email' => 'superadmin@example.com'],
            [
                'name' => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (method_exists($superAdminUser, 'assignRole')) {
            $superAdminUser->assignRole($superAdminRole);
        }
    }
}
