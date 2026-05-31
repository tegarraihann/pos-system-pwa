<?php

namespace Tests\Feature;

use App\Filament\Pages\Reports\ProfitLossReport;
use App\Filament\Pages\Reports\SalesReport;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportPdfExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_sales_report_can_be_downloaded_as_pdf(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewSalesReport:Report', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);

        /** @var SalesReport $page */
        $page = app(SalesReport::class);
        $page->mount();

        $response = $page->downloadPdf();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));
    }

    public function test_profit_loss_report_can_be_downloaded_as_pdf(): void
    {
        $user = User::factory()->create();
        $role = Role::query()->create(['name' => 'finance-admin', 'guard_name' => 'web']);
        $permission = Permission::query()->create(['name' => 'ViewProfitLossReport:Report', 'guard_name' => 'web']);
        $role->givePermissionTo($permission);
        $user->assignRole($role);

        $this->actingAs($user);

        /** @var ProfitLossReport $page */
        $page = app(ProfitLossReport::class);
        $page->mount();

        $response = $page->downloadPdf();

        $this->assertSame('application/pdf', $response->headers->get('content-type'));
        $this->assertStringContainsString('.pdf', (string) $response->headers->get('content-disposition'));
    }
}
