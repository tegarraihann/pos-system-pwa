<?php

namespace App\Filament\Pages;

use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\StockLocation;
use App\Services\MidtransService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
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

        // Show notification
        Notification::make()
            ->title('Ditambahkan ke keranjang')
            ->body($label)
            ->success()
            ->duration(2000)
            ->send();
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

    public function updateQty(int $variantId, mixed $value): void
    {
        if (! isset($this->cart[$variantId])) {
            return;
        }

        $qty = (int) $value;

        if ($qty <= 0) {
            unset($this->cart[$variantId]);
            return;
        }

        $this->cart[$variantId]['qty'] = $qty;
    }

    public function removeFromCart(int $variantId): void
    {
        $label = $this->cart[$variantId]['label'] ?? 'Item';
        unset($this->cart[$variantId]);

        Notification::make()
            ->title('Dihapus dari keranjang')
            ->body($label)
            ->warning()
            ->duration(2000)
            ->send();
    }

    public function clearCart(): void
    {
        $this->cart = [];

        Notification::make()
            ->title('Keranjang dikosongkan')
            ->info()
            ->duration(2000)
            ->send();
    }

    public function checkout(): void
    {
        if (empty($this->cart)) {
            Notification::make()
                ->title('Keranjang kosong')
                ->body('Tambahkan item terlebih dahulu sebelum checkout.')
                ->danger()
                ->send();
            return;
        }

        if (config('midtrans.server_key') === '' || config('midtrans.client_key') === '') {
            Notification::make()
                ->title('Konfigurasi Midtrans belum lengkap')
                ->body('Pastikan MIDTRANS_SERVER_KEY dan MIDTRANS_CLIENT_KEY sudah diisi.')
                ->danger()
                ->send();
            return;
        }

        try {
            [$order, $payment] = DB::transaction(function (): array {
                $variantIds = array_keys($this->cart);

                $variants = MenuVariant::query()
                    ->with('menu')
                    ->whereIn('id', $variantIds)
                    ->get()
                    ->keyBy('id');

                if ($variants->isEmpty()) {
                    throw new \RuntimeException('Item menu tidak ditemukan.');
                }

                $order = Order::create([
                    'order_type' => Order::TYPE_DINE_IN,
                    'status' => Order::STATUS_DRAFT,
                    'customer_type' => Order::CUSTOMER_WALK_IN,
                    'stock_location_id' => StockLocation::query()->value('id'),
                    'created_by' => auth()->id(),
                ]);

                foreach ($this->cart as $item) {
                    $variant = $variants->get($item['id']);

                    if (! $variant) {
                        continue;
                    }

                    $order->items()->create([
                        'menu_variant_id' => $variant->id,
                        'price' => (float) $variant->price,
                        'qty' => (int) $item['qty'],
                        'discount_amount' => 0,
                    ]);
                }

                $order->refresh();

                $payment = $order->payments()->create([
                    'method' => 'midtrans_snap',
                    'amount' => (float) $order->grand_total,
                    'status' => 'pending',
                    'gateway_provider' => 'midtrans',
                ]);

                $payment->update([
                    'gateway_ref' => $payment->gateway_ref ?: $order->order_number,
                ]);

                return [$order, $payment];
            });

            $order->refresh();
            $snap = app(MidtransService::class)->createSnapTransaction($order);

            if ($snap['token'] === '') {
                throw new \RuntimeException('Snap token gagal dibuat.');
            }
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Gagal memulai pembayaran')
                ->body('Periksa konfigurasi Midtrans dan data order.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch('midtrans-snap', token: $snap['token']);
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
