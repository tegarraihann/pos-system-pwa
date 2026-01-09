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

        $role = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => $guard,
        ]);

        $permissions = Permission::where('guard_name', $guard)->get();
        if ($permissions->isNotEmpty()) {
            $role->syncPermissions($permissions);
        }

        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('password'),
            ]
        );

        if (method_exists($user, 'assignRole')) {
            $user->assignRole($role);
        }
    }
}
