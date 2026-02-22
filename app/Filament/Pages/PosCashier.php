<?php

namespace App\Filament\Pages;

use App\Models\Attendance;
use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\Payment;
use App\Models\StockLocation;
use App\Services\AttendanceService;
use App\Services\MidtransService;
use BackedEnum;
use DomainException;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\On;
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
    public bool $showPaymentModal = false;
    public bool $showReceiptModal = false;
    public bool $showPendingModal = false;
    public string $selectedPaymentMethod = '';
    public ?string $cashPaidAmount = null;
    public bool $isOffline = false;
    public bool $isCheckedIn = false;
    public ?int $activeAttendanceId = null;
    public ?string $attendanceCheckedInAt = null;
    public ?int $attendanceWorkedMinutesToday = null;
    public ?string $attendanceDeviceId = null;
    public ?float $attendanceLatitude = null;
    public ?float $attendanceLongitude = null;
    public ?int $activeMidtransOrderId = null;
    public ?int $activeMidtransPaymentId = null;
    public ?string $activeMidtransGatewayRef = null;

    /**
     * @var array<string, mixed>
     */
    public array $receiptData = [];

    /**
     * @var array<string, mixed>
     */
    public array $pendingPaymentData = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $cart = [];

    public function mount(): void
    {
        $this->refreshAttendanceState();
    }

    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return $user->can('View:PosCashier');
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
        if (! $this->ensureCashierCheckedIn()) {
            return;
        }

        $variant = $this->findVariantForCart($variantId);

        if (! $variant) {
            return;
        }

        $existingQty = (int) ($this->cart[$variantId]['qty'] ?? 0);
        $desiredQty = $existingQty + 1;

        if (! $this->canSetQtyForVariant($variant, $desiredQty, true)) {
            return;
        }

        $menuName = $variant->menu?->name ?? '-';
        $label = $menuName . ' - ' . $variant->kd_varian;

        if (! isset($this->cart[$variantId])) {
            $this->cart[$variantId] = [
                'id' => $variantId,
                'label' => $label,
                'price' => (float) $variant->price,
                'variant_code' => (string) $variant->kd_varian,
                'unit' => (string) ($variant->menu?->unit ?? 'Pcs'),
                'is_stock_tracked' => (bool) ($variant->menu?->is_stock_managed ?? false),
                'stock_left' => $variant->menu?->is_stock_managed ? max((int) ($variant->stock ?? 0), 0) : null,
                'reminder_stock' => $variant->menu?->is_stock_managed && $variant->reminder_stock !== null
                    ? (float) $variant->reminder_stock
                    : null,
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

        $variant = $this->findVariantForCart($variantId);

        if (! $variant) {
            return;
        }

        $nextQty = (int) $this->cart[$variantId]['qty'] + 1;

        if (! $this->canSetQtyForVariant($variant, $nextQty, true)) {
            return;
        }

        $this->cart[$variantId]['qty'] = $nextQty;
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

        $variant = $this->findVariantForCart($variantId);

        if (! $variant) {
            unset($this->cart[$variantId]);

            Notification::make()
                ->title('Item tidak ditemukan')
                ->body('Varian menu sudah tidak tersedia.')
                ->warning()
                ->duration(2500)
                ->send();

            return;
        }

        $maxAllowedQty = $this->resolveMaxAllowedQty($variant);

        if ($maxAllowedQty !== null && $qty > $maxAllowedQty) {
            if ($maxAllowedQty <= 0) {
                unset($this->cart[$variantId]);

                Notification::make()
                    ->title('Stok habis')
                    ->body("{$variant->menu?->name} - {$variant->kd_varian} dihapus dari keranjang karena stok sudah habis.")
                    ->warning()
                    ->duration(2500)
                    ->send();

                return;
            }

            $this->cart[$variantId]['qty'] = $maxAllowedQty;

            Notification::make()
                ->title('Qty melebihi stok')
                ->body("Maksimal qty untuk {$this->cart[$variantId]['label']} adalah {$maxAllowedQty}.")
                ->warning()
                ->duration(2500)
                ->send();

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
        $this->closePaymentModal();

        Notification::make()
            ->title('Keranjang dikosongkan')
            ->info()
            ->duration(2000)
            ->send();
    }

    #[On('setOfflineStatus')]
    public function setOfflineStatus(bool $isOffline): void
    {
        $this->isOffline = $isOffline;

        if ($this->isOffline && $this->selectedPaymentMethod === Order::PAYMENT_MIDTRANS) {
            $this->selectedPaymentMethod = Order::PAYMENT_CASH;
        }
    }

    public function openPaymentModal(): void
    {
        if (! $this->ensureCashierCheckedIn()) {
            return;
        }

        if (! $this->ensureCartNotEmpty()) {
            return;
        }

        if (! $this->ensureCartStockAvailable()) {
            return;
        }

        $this->resetCheckoutFeedback();
        $this->showPaymentModal = true;
        $this->selectedPaymentMethod = $this->isOffline ? Order::PAYMENT_CASH : '';
        $this->cashPaidAmount = null;
    }

    public function closePaymentModal(): void
    {
        $this->showPaymentModal = false;
        $this->selectedPaymentMethod = '';
        $this->cashPaidAmount = null;
    }

    public function updatedSelectedPaymentMethod(string $method): void
    {
        if ($method === Order::PAYMENT_CASH && blank($this->cashPaidAmount)) {
            $this->cashPaidAmount = (string) round($this->cartSubtotal);
        }

        if ($method === Order::PAYMENT_MIDTRANS) {
            $this->cashPaidAmount = null;
        }
    }

    public function processPayment(): void
    {
        if (! $this->ensureCashierCheckedIn()) {
            return;
        }

        if (! $this->ensureCartNotEmpty()) {
            return;
        }

        if (! $this->ensureCartStockAvailable()) {
            return;
        }

        if ($this->selectedPaymentMethod === '') {
            Notification::make()
                ->title('Pilih metode pembayaran')
                ->body('Silakan pilih Tunai atau Midtrans.')
                ->warning()
                ->send();
            return;
        }

        if ($this->selectedPaymentMethod === Order::PAYMENT_CASH) {
            $this->processCashPayment();

            return;
        }

        if ($this->selectedPaymentMethod === Order::PAYMENT_MIDTRANS) {
            $this->processMidtransPayment();

            return;
        }

        Notification::make()
            ->title('Metode pembayaran tidak valid')
            ->danger()
            ->send();
    }

    public function checkout(): void
    {
        $this->openPaymentModal();
    }

    #[On('attendance-context-updated')]
    public function updateAttendanceContext(string $deviceId, ?float $latitude = null, ?float $longitude = null): void
    {
        $this->attendanceDeviceId = trim($deviceId);
        $this->attendanceLatitude = $latitude;
        $this->attendanceLongitude = $longitude;
    }

    #[On('attendance-action')]
    public function handleAttendanceAction(string $action, string $deviceId, ?float $latitude = null, ?float $longitude = null): void
    {
        $user = auth()->user();

        if (! $user) {
            Notification::make()
                ->title('Sesi login tidak valid')
                ->body('Silakan login ulang.')
                ->danger()
                ->send();

            return;
        }

        $normalizedAction = strtolower(trim($action));
        $normalizedDeviceId = trim($deviceId);
        $this->attendanceDeviceId = $normalizedDeviceId;
        $this->attendanceLatitude = $latitude;
        $this->attendanceLongitude = $longitude;

        if ($normalizedAction === 'check_in' && $user->cannot('CheckIn:Attendance')) {
            Notification::make()
                ->title('Akses absensi ditolak')
                ->body('Akun ini tidak memiliki izin check-in.')
                ->danger()
                ->send();

            return;
        }

        if ($normalizedAction === 'check_out' && $user->cannot('CheckOut:Attendance')) {
            Notification::make()
                ->title('Akses absensi ditolak')
                ->body('Akun ini tidak memiliki izin check-out.')
                ->danger()
                ->send();

            return;
        }

        if ($normalizedDeviceId === '') {
            Notification::make()
                ->title('Perangkat belum siap')
                ->body('Refresh halaman lalu coba lagi.')
                ->warning()
                ->send();

            return;
        }

        if ($latitude === null || $longitude === null) {
            Notification::make()
                ->title('Lokasi belum terbaca')
                ->body('Izinkan GPS browser, lalu coba kembali.')
                ->warning()
                ->send();

            return;
        }

        $service = app(AttendanceService::class);

        try {
            if ($normalizedAction === 'check_in') {
                $service->checkIn($user, $normalizedDeviceId, $latitude, $longitude);

                Notification::make()
                    ->title('Check-in berhasil')
                    ->body('Absensi masuk berhasil disimpan.')
                    ->success()
                    ->send();
            } elseif ($normalizedAction === 'check_out') {
                $service->checkOut($user, $normalizedDeviceId, $latitude, $longitude);

                Notification::make()
                    ->title('Check-out berhasil')
                    ->body('Absensi pulang berhasil disimpan.')
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Aksi absensi tidak dikenali')
                    ->warning()
                    ->send();

                return;
            }
        } catch (DomainException $exception) {
            Notification::make()
                ->title('Absensi gagal')
                ->body($exception->getMessage())
                ->warning()
                ->send();

            return;
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Terjadi kendala absensi')
                ->body('Silakan coba lagi beberapa saat lagi.')
                ->danger()
                ->send();

            return;
        }

        $this->refreshAttendanceState();
    }

    protected function processCashPayment(): void
    {
        $subtotal = (float) $this->cartSubtotal;
        $paidAmount = $this->resolvedCashPaidAmount();

        if ($paidAmount < $subtotal) {
            Notification::make()
                ->title('Nominal tunai kurang')
                ->body('Nominal dibayar harus sama atau lebih besar dari total.')
                ->danger()
                ->send();
            return;
        }

        try {
            [$order, $payment] = DB::transaction(function () use ($subtotal): array {
                $order = $this->createOrderFromCart([
                    'status' => Order::STATUS_QUEUED,
                    'payment_method' => Order::PAYMENT_CASH,
                    'sync_status' => Order::SYNC_STATUS_SYNCED,
                    'synced_at' => now(),
                ]);

                $payment = $order->payments()->create([
                    'method' => Order::PAYMENT_CASH,
                    'amount' => $subtotal,
                    'status' => 'paid',
                    'paid_at' => now(),
                ]);

                return [$order, $payment];
            });
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Gagal memproses pembayaran tunai')
                ->body('Silakan coba lagi.')
                ->danger()
                ->send();
            return;
        }

        $change = max($paidAmount - $subtotal, 0);
        $this->cart = [];
        $this->closePaymentModal();
        $this->receiptData = $this->buildReceiptData($order->fresh('items.menuVariant.menu', 'payments', 'creator'), $payment->fresh(), $paidAmount);
        $this->showReceiptModal = true;

        Notification::make()
            ->title('Pembayaran tunai berhasil')
            ->body('Kembalian: Rp ' . number_format($change, 0, ',', '.'))
            ->success()
            ->send();
    }

    protected function processMidtransPayment(): void
    {
        if ($this->isOffline) {
            Notification::make()
                ->title('Internet sedang offline')
                ->body('Pembayaran Midtrans hanya tersedia saat online.')
                ->warning()
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
                $order = $this->createOrderFromCart([
                    'status' => Order::STATUS_DRAFT,
                    'payment_method' => Order::PAYMENT_MIDTRANS,
                    'sync_status' => Order::SYNC_STATUS_SYNCED,
                    'synced_at' => now(),
                ]);

                $payment = $order->payments()->create([
                    'method' => 'midtrans_snap',
                    'amount' => (float) $order->grand_total,
                    'status' => 'pending',
                    'gateway_provider' => 'midtrans',
                ]);

                $gatewayOrderId = $order->order_number . '-' . $payment->id;

                $payment->update([
                    'gateway_ref' => $gatewayOrderId,
                ]);

                return [$order, $payment];
            });

            $order->refresh();
            $snap = app(MidtransService::class)->createSnapTransaction($order, $payment->gateway_ref);

            if ($snap['token'] === '') {
                throw new \RuntimeException('Snap token gagal dibuat.');
            }

            $this->activeMidtransOrderId = $order->id;
            $this->activeMidtransPaymentId = $payment->id;
            $this->activeMidtransGatewayRef = $payment->gateway_ref;
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Gagal memulai pembayaran')
                ->body('Periksa konfigurasi Midtrans dan data order.')
                ->danger()
                ->send();
            return;
        }

        $this->dispatch(
            'midtrans-snap',
            token: $snap['token'],
            gatewayRef: $this->activeMidtransGatewayRef,
        );
        $this->cart = [];
        $this->closePaymentModal();
    }

    #[On('midtrans-check-status')]
    public function checkMidtransStatus(string $gatewayRef): void
    {
        $payment = $this->resolveMidtransPaymentByGatewayRef($gatewayRef);

        if (! $payment) {
            Notification::make()
                ->title('Transaksi Midtrans tidak ditemukan')
                ->body('Silakan refresh halaman dan cek transaksi terbaru.')
                ->warning()
                ->send();
            return;
        }

        try {
            $statusPayload = app(MidtransService::class)->getTransactionStatus($gatewayRef);
        } catch (\Throwable $exception) {
            report($exception);

            Notification::make()
                ->title('Gagal cek status Midtrans')
                ->body('Koneksi ke Midtrans gagal. Coba beberapa saat lagi.')
                ->warning()
                ->send();
            return;
        }

        $paymentStatus = $this->applyMidtransStatus($payment, $statusPayload);

        if ($paymentStatus === 'paid') {
            $freshPayment = $payment->fresh();
            $freshOrder = $payment->order?->fresh(['items.menuVariant.menu', 'payments', 'creator']);

            if ($freshPayment && $freshOrder) {
                $this->receiptData = $this->buildReceiptData($freshOrder, $freshPayment, null);
                $this->showReceiptModal = true;
            }
            $this->showPendingModal = false;
            $this->pendingPaymentData = [];

            Notification::make()
                ->title('Pembayaran Midtrans berhasil')
                ->body('Transaksi sudah settlement dan order masuk antrian kitchen.')
                ->success()
                ->send();

            return;
        }

        if ($paymentStatus === 'pending') {
            $this->pendingPaymentData = $this->buildPendingPaymentData($payment->fresh(), $statusPayload);
            $this->showPendingModal = true;
            $this->showReceiptModal = false;
            $this->receiptData = [];

            Notification::make()
                ->title('Menunggu pembayaran Midtrans')
                ->body('Pembayaran belum selesai. Silakan lanjutkan pembayaran pelanggan.')
                ->warning()
                ->send();

            return;
        }

        Notification::make()
            ->title('Pembayaran Midtrans belum berhasil')
            ->body('Status saat ini: ' . strtoupper($paymentStatus))
            ->danger()
            ->send();
    }

    #[On('midtrans-pending-client')]
    public function handleMidtransPendingClient(string $gatewayRef, array $payload = []): void
    {
        $payment = $this->resolveMidtransPaymentByGatewayRef($gatewayRef);

        if (! $payment) {
            return;
        }

        $payment->update([
            'status' => 'pending',
            'method' => (string) ($payload['payment_type'] ?? $payment->method),
        ]);

        $this->pendingPaymentData = $this->buildPendingPaymentData($payment->fresh(), $payload);
        $this->showPendingModal = true;
        $this->showReceiptModal = false;
        $this->receiptData = [];
    }

    #[On('midtrans-close-client')]
    public function handleMidtransCloseClient(): void
    {
        Notification::make()
            ->title('Pembayaran Midtrans ditutup')
            ->body('Transaksi belum selesai. Anda dapat cek status kembali dari POS.')
            ->warning()
            ->send();
    }

    public function closeReceiptModal(): void
    {
        $this->showReceiptModal = false;
        $this->receiptData = [];
    }

    public function closePendingModal(): void
    {
        $this->showPendingModal = false;
        $this->pendingPaymentData = [];
    }

    public function getCartItemsProperty(): array
    {
        if ($this->cart === []) {
            return [];
        }

        $variants = MenuVariant::query()
            ->with('menu')
            ->whereIn('id', array_keys($this->cart))
            ->get()
            ->keyBy('id');

        $items = [];

        foreach ($this->cart as $item) {
            $variant = $variants->get((int) $item['id']);
            $items[] = $this->enrichCartItem($item, $variant);
        }

        return $items;
    }

    public function getCartSubtotalProperty(): float
    {
        return array_reduce($this->cart, function (float $total, array $item): float {
            return $total + ($item['price'] * $item['qty']);
        }, 0.0);
    }

    public function getCashChangeProperty(): float
    {
        return max($this->resolvedCashPaidAmount() - (float) $this->cartSubtotal, 0);
    }

    public function getCanConfirmPaymentProperty(): bool
    {
        if ($this->selectedPaymentMethod === Order::PAYMENT_CASH) {
            return $this->resolvedCashPaidAmount() >= (float) $this->cartSubtotal;
        }

        if ($this->selectedPaymentMethod === Order::PAYMENT_MIDTRANS) {
            return ! $this->isOffline;
        }

        return false;
    }

    protected function ensureCartNotEmpty(): bool
    {
        if (! empty($this->cart)) {
            return true;
        }

        Notification::make()
            ->title('Keranjang kosong')
            ->body('Tambahkan item terlebih dahulu sebelum checkout.')
            ->danger()
            ->send();

        return false;
    }

    protected function ensureCashierCheckedIn(): bool
    {
        if ($this->isCheckedIn) {
            return true;
        }

        $this->refreshAttendanceState();

        if ($this->isCheckedIn) {
            return true;
        }

        Notification::make()
            ->title('Check-in belum dilakukan')
            ->body('Silakan check-in dulu sebelum memproses transaksi.')
            ->warning()
            ->send();

        return false;
    }

    protected function refreshAttendanceState(): void
    {
        $user = auth()->user();

        if (! $user) {
            $this->isCheckedIn = false;
            $this->activeAttendanceId = null;
            $this->attendanceCheckedInAt = null;
            $this->attendanceWorkedMinutesToday = null;

            return;
        }

        $activeAttendance = app(AttendanceService::class)->getActiveAttendance($user);

        $this->isCheckedIn = $activeAttendance !== null;
        $this->activeAttendanceId = $activeAttendance?->id;
        $this->attendanceCheckedInAt = $activeAttendance?->check_in_at?->format('d/m/Y H:i');

        $workedMinutesToday = Attendance::query()
            ->where('user_id', $user->id)
            ->whereDate('shift_date', now()->toDateString())
            ->sum('work_minutes');

        if ($activeAttendance?->check_in_at) {
            $workedMinutesToday += $activeAttendance->check_in_at->diffInMinutes(now());
        }

        $this->attendanceWorkedMinutesToday = (int) $workedMinutesToday;
    }

    protected function ensureCartStockAvailable(): bool
    {
        if ($this->cart === []) {
            return true;
        }

        $variants = MenuVariant::query()
            ->with('menu')
            ->whereIn('id', array_keys($this->cart))
            ->get()
            ->keyBy('id');

        $issues = [];

        foreach ($this->cart as $item) {
            $variant = $variants->get((int) $item['id']);

            if (! $variant) {
                $issues[] = ($item['label'] ?? 'Item') . ' tidak ditemukan';
                continue;
            }

            $maxAllowedQty = $this->resolveMaxAllowedQty($variant);

            if ($maxAllowedQty !== null && (int) $item['qty'] > $maxAllowedQty) {
                $issues[] = ($item['label'] ?? 'Item') . " (stok tersisa {$maxAllowedQty})";
            }
        }

        if ($issues === []) {
            return true;
        }

        Notification::make()
            ->title('Stok tidak mencukupi')
            ->body('Periksa item berikut: ' . implode(', ', $issues))
            ->danger()
            ->send();

        return false;
    }

    protected function resolvedCashPaidAmount(): float
    {
        if (blank($this->cashPaidAmount)) {
            return (float) $this->cartSubtotal;
        }

        return max((float) $this->cashPaidAmount, 0);
    }

    protected function createOrderFromCart(array $orderAttributes): Order
    {
        $variantIds = array_keys($this->cart);

        $variants = MenuVariant::query()
            ->with('menu')
            ->whereIn('id', $variantIds)
            ->get()
            ->keyBy('id');

        if ($variants->isEmpty()) {
            throw new \RuntimeException('Item menu tidak ditemukan.');
        }

        $order = Order::create(array_merge([
            'order_type' => Order::TYPE_DINE_IN,
            'customer_type' => Order::CUSTOMER_WALK_IN,
            'stock_location_id' => StockLocation::query()->value('id'),
            'created_by' => auth()->id(),
        ], $orderAttributes));

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

        return $order->refresh();
    }

    protected function findVariantForCart(int $variantId): ?MenuVariant
    {
        return MenuVariant::query()
            ->with('menu')
            ->find($variantId);
    }

    protected function resolveMaxAllowedQty(MenuVariant $variant): ?int
    {
        $isStockTracked = (bool) ($variant->menu?->is_stock_managed ?? false);

        if (! $isStockTracked) {
            return null;
        }

        return max((int) ($variant->stock ?? 0), 0);
    }

    protected function canSetQtyForVariant(MenuVariant $variant, int $desiredQty, bool $notify = false): bool
    {
        $maxAllowedQty = $this->resolveMaxAllowedQty($variant);

        if ($maxAllowedQty === null) {
            return true;
        }

        if ($maxAllowedQty <= 0) {
            if ($notify) {
                Notification::make()
                    ->title('Stok habis')
                    ->body(($variant->menu?->name ?? 'Menu') . ' - ' . $variant->kd_varian . ' sedang habis.')
                    ->warning()
                    ->duration(2500)
                    ->send();
            }

            return false;
        }

        if ($desiredQty > $maxAllowedQty) {
            if ($notify) {
                Notification::make()
                    ->title('Qty melebihi stok')
                    ->body('Maksimal qty yang bisa dipilih adalah ' . $maxAllowedQty . '.')
                    ->warning()
                    ->duration(2500)
                    ->send();
            }

            return false;
        }

        return true;
    }

    protected function enrichCartItem(array $item, ?MenuVariant $variant): array
    {
        $isStockTracked = (bool) ($item['is_stock_tracked'] ?? false);
        $stockLeft = array_key_exists('stock_left', $item) && $item['stock_left'] !== null
            ? max((int) $item['stock_left'], 0)
            : null;
        $reminderStock = array_key_exists('reminder_stock', $item) && $item['reminder_stock'] !== null
            ? (float) $item['reminder_stock']
            : null;

        if ($variant) {
            $isStockTracked = (bool) ($variant->menu?->is_stock_managed ?? false);
            $stockLeft = $isStockTracked ? max((int) ($variant->stock ?? 0), 0) : null;
            $reminderStock = $isStockTracked && $variant->reminder_stock !== null
                ? (float) $variant->reminder_stock
                : null;
            $item['variant_code'] = (string) ($variant->kd_varian ?? ($item['variant_code'] ?? '-'));
            $item['unit'] = (string) ($variant->menu?->unit ?? ($item['unit'] ?? 'Pcs'));
            $item['is_stock_tracked'] = $isStockTracked;
            $item['stock_left'] = $stockLeft;
            $item['reminder_stock'] = $reminderStock;
        }

        $qty = (int) ($item['qty'] ?? 0);
        $stockAvailableAfterCart = $isStockTracked && $stockLeft !== null
            ? max($stockLeft - $qty, 0)
            : null;
        [$stockStatus, $stockLabel] = $this->resolveStockStatusMeta($isStockTracked, $stockAvailableAfterCart, $reminderStock);
        $canIncrement = ! $isStockTracked || $stockAvailableAfterCart === null || $stockAvailableAfterCart > 0;

        $item['stock_status'] = $stockStatus;
        $item['stock_status_label'] = $stockLabel;
        $item['can_increment'] = $canIncrement;
        $item['stock_total'] = $stockLeft;
        $item['stock_left_display'] = $stockAvailableAfterCart;

        return $item;
    }

    /**
     * @return array{0:string,1:string}
     */
    protected function resolveStockStatusMeta(bool $isStockTracked, ?int $stockLeft, ?float $reminderStock): array
    {
        if (! $isStockTracked) {
            return ['untracked', 'Tidak dilacak'];
        }

        $stockLeft = max((int) ($stockLeft ?? 0), 0);

        if ($stockLeft <= 0) {
            return ['out', 'Habis'];
        }

        if ($reminderStock !== null && $stockLeft <= $reminderStock) {
            return ['low', 'Menipis'];
        }

        return ['ok', 'Aman'];
    }

    protected function resolveMidtransPaymentByGatewayRef(string $gatewayRef): ?Payment
    {
        if ($gatewayRef === '') {
            return null;
        }

        return Payment::query()
            ->where('gateway_provider', 'midtrans')
            ->where('gateway_ref', $gatewayRef)
            ->latest('id')
            ->first();
    }

    /**
     * @param  array<string, mixed>  $statusPayload
     */
    protected function applyMidtransStatus(Payment $payment, array $statusPayload): string
    {
        $transactionStatus = (string) ($statusPayload['transaction_status'] ?? '');
        $fraudStatus = (string) ($statusPayload['fraud_status'] ?? '');
        $paymentType = (string) ($statusPayload['payment_type'] ?? '');
        $transactionId = (string) ($statusPayload['transaction_id'] ?? '');

        $paymentStatus = match ($transactionStatus) {
            'capture' => $fraudStatus === 'accept' ? 'paid' : 'pending',
            'settlement' => 'paid',
            'pending' => 'pending',
            'deny' => 'failed',
            'expire' => 'expired',
            'cancel' => 'canceled',
            default => 'pending',
        };

        $payment->update([
            'method' => $paymentType !== '' ? $paymentType : $payment->method,
            'status' => $paymentStatus,
            'gateway_ref' => $payment->gateway_ref ?: $transactionId,
            'paid_at' => $paymentStatus === 'paid' ? now() : null,
        ]);

        $order = $payment->order;

        if ($order) {
            $orderUpdate = [
                'payment_method' => Order::PAYMENT_MIDTRANS,
                'sync_status' => Order::SYNC_STATUS_SYNCED,
                'synced_at' => now(),
                'sync_error' => null,
            ];

            if ($paymentStatus === 'paid' && $order->status === Order::STATUS_DRAFT) {
                $orderUpdate['status'] = Order::STATUS_QUEUED;
            }

            $order->update($orderUpdate);
        }

        return $paymentStatus;
    }

    protected function resetCheckoutFeedback(): void
    {
        $this->showReceiptModal = false;
        $this->showPendingModal = false;
        $this->receiptData = [];
        $this->pendingPaymentData = [];
        $this->activeMidtransOrderId = null;
        $this->activeMidtransPaymentId = null;
        $this->activeMidtransGatewayRef = null;
    }

    protected function buildReceiptData(Order $order, Payment $payment, ?float $cashPaidAmount): array
    {
        $items = $order->items->map(function ($item): array {
            $label = $item->item_name_snapshot ?: ($item->menuVariant?->menu?->name ?? 'Item');

            return [
                'name' => $label,
                'qty' => (int) $item->qty,
                'price' => (float) $item->price,
                'total' => (float) $item->total,
            ];
        })->values()->all();

        $paidAmount = $cashPaidAmount ?? (float) $payment->amount;
        $grandTotal = (float) $order->grand_total;

        return [
            'order_number' => $order->order_number,
            'ordered_at' => optional($order->ordered_at)->format('d/m/Y H:i:s'),
            'cashier_name' => $order->creator?->name ?? '-',
            'payment_method' => $payment->method,
            'payment_status' => $payment->status,
            'subtotal' => (float) $order->subtotal,
            'discount_total' => (float) $order->discount_total,
            'tax_total' => (float) $order->tax_total,
            'service_total' => (float) $order->service_total,
            'grand_total' => $grandTotal,
            'paid_amount' => $paidAmount,
            'change' => max($paidAmount - $grandTotal, 0),
            'items' => $items,
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    protected function buildPendingPaymentData(?Payment $payment, array $payload): array
    {
        $vaNumbers = $payload['va_numbers'] ?? [];
        $vaLabel = null;

        if (is_array($vaNumbers) && isset($vaNumbers[0]['bank'], $vaNumbers[0]['va_number'])) {
            $vaLabel = strtoupper((string) $vaNumbers[0]['bank']) . ' - ' . (string) $vaNumbers[0]['va_number'];
        }

        return [
            'gateway_ref' => $payment?->gateway_ref,
            'method' => (string) ($payload['payment_type'] ?? $payment?->method ?? 'midtrans'),
            'status' => (string) ($payload['transaction_status'] ?? $payment?->status ?? 'pending'),
            'amount' => (float) ($payload['gross_amount'] ?? $payment?->amount ?? 0),
            'expiry_time' => (string) ($payload['expiry_time'] ?? ''),
            'va_number' => $vaLabel,
        ];
    }
}
