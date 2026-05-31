<?php

namespace Tests\Feature;

use App\Models\Menu;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MenuCodeGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_generates_menu_code_automatically_when_code_is_empty(): void
    {
        $firstMenu = Menu::query()->create([
            'name' => 'Americano',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $secondMenu = Menu::query()->create([
            'name' => 'Latte',
            'is_active' => true,
            'is_stock_managed' => false,
        ]);

        $this->assertSame('MENU-0001', $firstMenu->code);
        $this->assertSame('MENU-0002', $secondMenu->code);
    }
}
