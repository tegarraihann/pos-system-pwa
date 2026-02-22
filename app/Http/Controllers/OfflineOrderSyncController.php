<?php

namespace App\Http\Controllers;

use App\Models\MenuVariant;
use App\Models\Order;
use App\Models\StockLocation;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class OfflineOrderSyncController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $user = $request->user();

        if (! $user) {
            return response()->json(['message' => 'Unauthenticated'], 401);
        }

        $validator = Validator::make($request->all(), [
            'client_txn_id' => ['required', 'string', 'max:100'],
            'ordered_at' => ['nullable', 'date'],
            'order_type' => ['nullable', Rule::in([
                Order::TYPE_DINE_IN,
                Order::TYPE_TAKE_AWAY,
                Order::TYPE_DELIVERY,
            ])],
            'customer_type' => ['nullable', Rule::in([
                Order::CUSTOMER_WALK_IN,
                Order::CUSTOMER_MEMBER,
            ])],
            'customer_id' => ['nullable', 'integer', 'exists:customers,id'],
            'stock_location_id' => ['nullable', 'integer', 'exists:stock_locations,id'],
            'table_number' => ['nullable', 'string', 'max:50'],
            'queue_number' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
            'tax_total' => ['nullable', 'numeric', 'min:0'],
            'service_total' => ['nullable', 'numeric', 'min:0'],
            'paid_at' => ['nullable', 'date'],
            'payment_amount' => ['nullable', 'numeric', 'min:0'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.menu_variant_id' => ['required', 'integer', 'exists:menu_variants,id'],
            'items.*.qty' => ['required', 'numeric', 'min:0.001'],
            'items.*.price' => ['nullable', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.notes' => ['nullable', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        $payload = $validator->validated();
        $clientTxnId = (string) $payload['client_txn_id'];

        $existingOrder = Order::query()
            ->where('client_txn_id', $clientTxnId)
            ->first();

        if ($existingOrder) {
            return response()->json([
                'message' => 'Order already synced',
                'idempotent' => true,
                'order_id' => $existingOrder->id,
                'order_number' => $existingOrder->order_number,
            ]);
        }

        try {
            $order = DB::transaction(function () use ($payload, $user, $clientTxnId): Order {
                $itemRows = $payload['items'];
                $variantIds = collect($itemRows)
                    ->pluck('menu_variant_id')
                    ->map(static fn (mixed $id): int => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $variants = MenuVariant::query()
                    ->with('menu')
                    ->whereIn('id', $variantIds)
                    ->get()
                    ->keyBy('id');

                if (count($variantIds) !== $variants->count()) {
                    throw new \InvalidArgumentException('Sebagian varian menu tidak ditemukan.');
                }

                $customerType = (string) ($payload['customer_type'] ?? Order::CUSTOMER_WALK_IN);
                $stockLocationId = $payload['stock_location_id'] ?? StockLocation::query()
                    ->where('is_active', true)
                    ->value('id');

                $order = Order::create([
                    'ordered_at' => isset($payload['ordered_at']) ? Carbon::parse($payload['ordered_at']) : now(),
                    'order_type' => (string) ($payload['order_type'] ?? Order::TYPE_DINE_IN),
                    'status' => Order::STATUS_DRAFT,
                    'customer_type' => $customerType,
                    'customer_id' => $customerType === Order::CUSTOMER_MEMBER ? ($payload['customer_id'] ?? null) : null,
                    'payment_method' => Order::PAYMENT_CASH,
                    'sync_status' => Order::SYNC_STATUS_SYNCED,
                    'client_txn_id' => $clientTxnId,
                    'synced_at' => now(),
                    'sync_error' => null,
                    'stock_location_id' => $stockLocationId,
                    'table_number' => $payload['table_number'] ?? null,
                    'queue_number' => $payload['queue_number'] ?? null,
                    'notes' => $payload['notes'] ?? null,
                    'tax_total' => (float) ($payload['tax_total'] ?? 0),
                    'service_total' => (float) ($payload['service_total'] ?? 0),
                    'created_by' => $user->id,
                ]);

                foreach ($itemRows as $item) {
                    $variantId = (int) $item['menu_variant_id'];
                    $variant = $variants->get($variantId);

                    $order->items()->create([
                        'menu_variant_id' => $variantId,
                        'price' => (float) ($item['price'] ?? $variant->price),
                        'qty' => (float) $item['qty'],
                        'discount_amount' => (float) ($item['discount_amount'] ?? 0),
                        'notes' => $item['notes'] ?? null,
                    ]);
                }

                $order->refresh();

                $order->payments()->create([
                    'method' => Order::PAYMENT_CASH,
                    'amount' => (float) ($payload['payment_amount'] ?? $order->grand_total),
                    'status' => 'paid',
                    'paid_at' => isset($payload['paid_at']) ? Carbon::parse($payload['paid_at']) : now(),
                ]);

                if ($order->status === Order::STATUS_DRAFT) {
                    $order->update([
                        'status' => Order::STATUS_QUEUED,
                    ]);
                }

                return $order->fresh();
            });
        } catch (\InvalidArgumentException $exception) {
            return response()->json([
                'message' => $exception->getMessage(),
            ], 422);
        } catch (QueryException $exception) {
            if (str_contains(strtolower($exception->getMessage()), 'client_txn_id')) {
                $idempotentOrder = Order::query()
                    ->where('client_txn_id', $clientTxnId)
                    ->first();

                if ($idempotentOrder) {
                    return response()->json([
                        'message' => 'Order already synced',
                        'idempotent' => true,
                        'order_id' => $idempotentOrder->id,
                        'order_number' => $idempotentOrder->order_number,
                    ]);
                }
            }

            report($exception);

            return response()->json([
                'message' => 'Sync failed',
            ], 500);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json([
                'message' => 'Sync failed',
            ], 500);
        }

        return response()->json([
            'message' => 'Synced',
            'idempotent' => false,
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'status' => $order->status,
        ], 201);
    }
}
