# POS Module Documentation

## Overview
The POS module provides the core data model and admin resources for orders, items, payments, and split bills.
It is designed to support cashier workflows (order entry, payment, cancel, split bill) and can be extended
with a dedicated POS UI later.

## Status and Types

### Order Status
- `draft`
- `queued`
- `preparing`
- `ready`
- `served`
- `canceled`

### Order Type
- `dine_in`
- `take_away`
- `delivery`

### Customer Type
- `walk_in`
- `member`

### Payment Status
- `pending`
- `paid`
- `failed`
- `refunded`

### Bill Status
- `unpaid`
- `partial`
- `paid`

## Data Model

### customers
Member and walk-in customers.

Key fields:
- `code`, `name`, `phone`, `email`, `is_member`

### orders
The main order header.

Key fields:
- `order_number` (auto generated)
- `ordered_at`
- `order_type`, `status`, `customer_type`
- `customer_id` (nullable)
- `stock_location_id` (nullable)
- `table_number`, `queue_number`
- totals: `subtotal`, `discount_total`, `tax_total`, `service_total`, `grand_total`, `paid_total`
- cancel fields: `cancel_reason`, `canceled_at`
- `created_by`

### order_items
Order line items for menu variants.

Key fields:
- `order_id`, `menu_variant_id`
-, `item_name_snapshot` (menu name + variant code)
- `price`, `qty`, `discount_amount`, `total`
- `notes`

### order_item_modifiers
Optional modifiers for an order item.

Key fields:
- `order_item_id`
- `name`, `value`
- `price_delta`

### payments
Payments for an order.

Key fields:
- `order_id`
- `method` (cash, card, ewallet, gateway)
- `amount`, `status`
- `gateway_provider`, `gateway_ref`, `paid_at`

### bills
Split bill headers.

Key fields:
- `order_id`, `bill_no`
- totals: `subtotal`, `discount_total`, `tax_total`, `service_total`, `grand_total`, `paid_total`
- `status`

### bill_items
Split bill per item.

Key fields:
- `bill_id`, `order_item_id`
- `qty`, `total`

### bill_payments
Split bill per nominal.

Key fields:
- `bill_id`
- `method`, `amount`, `status`
- `gateway_provider`, `gateway_ref`, `paid_at`

## Business Rules

### Order Number
Generated automatically with the format `YYYYMMDD-XXXX` (sequence per day).

### Totals
- `order_items` calculate `total` automatically.
- `orders` recalculate `subtotal`, `discount_total`, and `grand_total` on item changes.
- `payments` refresh `paid_total`.
- `bills` refresh totals from `bill_items` and `bill_payments`.

### Split Bill
Supported in two modes:
- Per item (`bill_items`)
- Per nominal (`bill_payments`)
You can combine both in a single order.

## Filament Resources (Admin)
Navigation group: `POS management`

- Customers
- Orders (with relation managers: Order Items, Payments, Bills)
- Bills (with relation managers: Bill Items, Bill Payments)

## Setup

Run migrations:
```bash
php artisan migrate
```

If you use Filament Shield, generate permissions:
```bash
php artisan shield:generate
```

## Notes
The dedicated cashier UI (order entry, cart, quick menu, barcode, etc.) is not implemented yet. This module
provides the data layer and admin management to build that UI next.
