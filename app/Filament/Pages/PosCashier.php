<?php

namespace App\Filament\Pages;

use App\Models\MenuVariant;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class PosCashier extends Page
{
    protected static ?string $title = 'POS Kasir';
    protected static ?string $navigationLabel = 'POS Kasir';
    protected static string|UnitEnum|null $navigationGroup = 'POS management';
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;
    protected static ?string $slug = 'pos-cashier';

    protected string $view = 'filament.pages.pos-cashier';

    public string $search = '';
    public string $barcode = '';

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $cart = [];

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->hasRole(['kasir', 'admin', 'super_admin']);
    }

    public function getMenuVariantsProperty()
    {
        return MenuVariant::query()
            ->with('menu')
            ->where('is_active', true)
            ->when($this->search !== '', function ($query): void {
                $search = '%' . $this->search . '%';

                $query->where('kd_varian', 'like', $search)
                    ->orWhereHas('menu', function ($menuQuery) use ($search): void {
                        $menuQuery->where('name', 'like', $search)
                            ->orWhere('code', 'like', $search);
                    });
            })
            ->orderBy('menu_id')
            ->orderBy('kd_varian')
            ->limit(60)
            ->get();
    }

    public function addToCart(int $variantId): void
    {
        $variant = MenuVariant::query()->with('menu')->find($variantId);

        if (! $variant) {
            return;
        }

        $menuName = $variant->menu?->name ?? '-';
        $label = $menuName . ' - ' . $variant->kd_varian;

        if (! isset($this->cart[$variantId])) {
            $this->cart[$variantId] = [
                'id' => $variantId,
                'label' => $label,
                'price' => (float) $variant->price,
                'qty' => 0,
            ];
        }

        $this->cart[$variantId]['qty']++;
    }

    public function incrementQty(int $variantId): void
    {
        if (! isset($this->cart[$variantId])) {
            return;
        }

        $this->cart[$variantId]['qty']++;
    }

    public function decrementQty(int $variantId): void
    {
        if (! isset($this->cart[$variantId])) {
            return;
        }

        $this->cart[$variantId]['qty']--;

        if ($this->cart[$variantId]['qty'] <= 0) {
            unset($this->cart[$variantId]);
        }
    }

    public function removeFromCart(int $variantId): void
    {
        unset($this->cart[$variantId]);
    }

    public function clearCart(): void
    {
        $this->cart = [];
    }

    public function getCartItemsProperty(): array
    {
        return array_values($this->cart);
    }

    public function getCartSubtotalProperty(): float
    {
        return array_reduce($this->cart, function (float $total, array $item): float {
            return $total + ($item['price'] * $item['qty']);
        }, 0.0);
    }
}
