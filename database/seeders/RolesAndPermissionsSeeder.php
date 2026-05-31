<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $guard = config('auth.defaults.guard', 'web');

        // Cleanup legacy KDS role/permission if they still exist.
        Permission::query()
            ->where('guard_name', $guard)
            ->where('name', 'View:KitchenDisplay')
            ->delete();

        Role::query()
            ->where('guard_name', $guard)
            ->where('name', 'kitchen')
            ->delete();

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

        collect([
            'View:PosCashier',
            'ViewSalesReport:Report',
            'ViewPaymentReport:Report',
            'ViewCashierReport:Report',
            'ViewAttendanceReport:Report',
            'ViewStockReport:Report',
            'ViewManagementReport:Report',
            'ViewCogsReport:Report',
            'ViewExpenseReport:Report',
            'ViewProfitLossReport:Report',
            'View:HistoricalOrderImport',
            'ViewAny:HistoricalOrderImport',
            'Create:ChartOfAccount',
            'Update:ChartOfAccount',
            'View:ChartOfAccount',
            'ViewAny:ChartOfAccount',
            'Create:OperatingExpense',
            'Update:OperatingExpense',
            'View:OperatingExpense',
            'ViewAny:OperatingExpense',
            'CheckIn:Attendance',
            'CheckOut:Attendance',
            'Open:CashierSession',
            'Close:CashierSession',
            'ViewOwn:CashierSession',
            'CountOwn:CashierSession',
            'View:CashierSession',
            'ViewAny:CashierSession',
            'ViewAny:OrderingQr',
            'View:OrderingQr',
            'Create:OrderingQr',
            'Update:OrderingQr',
            'Delete:OrderingQr',
            'Toggle:OrderingQr',
            'ViewAny:PublicOrder',
            'View:PublicOrder',
            'Process:PublicOrder',
            'Cancel:PublicOrder',
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
            'Open:CashierSession',
            'Close:CashierSession',
            'ViewOwn:CashierSession',
            'CountOwn:CashierSession',
            'ViewAny:PublicOrder',
            'View:PublicOrder',
            'Process:PublicOrder',
        ])->map(fn (string $name) => $permissionByName->get($name))
            ->filter()
            ->values();

        if ($kasirPermissions->isNotEmpty()) {
            $kasirRole->syncPermissions($kasirPermissions);
        }

        if (Schema::hasTable('chart_of_accounts')) {
            collect([
                ['code' => '1100', 'name' => 'Kas', 'category' => ChartOfAccount::CATEGORY_ASSET, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '1200', 'name' => 'Persediaan Bahan Baku', 'category' => ChartOfAccount::CATEGORY_ASSET, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '4100', 'name' => 'Penjualan Bersih', 'category' => ChartOfAccount::CATEGORY_REVENUE, 'normal_balance' => ChartOfAccount::BALANCE_CREDIT],
                ['code' => '5100', 'name' => 'Harga Pokok Penjualan', 'category' => ChartOfAccount::CATEGORY_COGS, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '6100', 'name' => 'Beban Gaji', 'category' => ChartOfAccount::CATEGORY_EXPENSE, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '6200', 'name' => 'Beban Sewa', 'category' => ChartOfAccount::CATEGORY_EXPENSE, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '6300', 'name' => 'Beban Utilitas', 'category' => ChartOfAccount::CATEGORY_EXPENSE, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
                ['code' => '6900', 'name' => 'Beban Operasional Lainnya', 'category' => ChartOfAccount::CATEGORY_EXPENSE, 'normal_balance' => ChartOfAccount::BALANCE_DEBIT],
            ])->each(function (array $account): void {
                ChartOfAccount::query()->updateOrCreate(
                    ['code' => $account['code']],
                    [
                        ...$account,
                        'is_active' => true,
                        'is_system' => true,
                    ],
                );
            });
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
