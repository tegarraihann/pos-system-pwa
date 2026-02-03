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

        Role::firstOrCreate([
            'name' => 'kitchen',
            'guard_name' => $guard,
        ]);

        $permissions = Permission::where('guard_name', $guard)->get();
        if ($permissions->isNotEmpty()) {
            $adminRole->syncPermissions($permissions);
            $superAdminRole->syncPermissions($permissions);
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
