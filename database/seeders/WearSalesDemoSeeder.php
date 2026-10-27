<?php

namespace Database\Seeders;

use App\Enums\Auth\UserRole;
use App\Enums\Commerce\OrderStatus;
use App\Enums\Commerce\PaymentStatus;
use App\Models\Commerce\OrderStatusHistory;
use App\Models\Commerce\PaymentTransaction;
use App\Models\ContactMessage;
use App\Models\User;
use App\Models\Wear\WearOrder;
use App\Models\Wear\WearOrderItem;
use App\Models\Wear\WearProduct;
use App\Models\Wear\WearProductVariant;
use App\Models\Wear\WearReturnRequest;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Presentation demo data: customers, orders, payments, returns, enquiries
 * and low-stock states, so the admin dashboard tells a real story.
 *
 * Safety rules:
 *  - Never runs in production (also double-guarded by DatabaseSeeder).
 *  - Idempotent: every seeded row is marked with a `demo-` key. Re-running
 *    deletes only previously demo-seeded orders/payments/returns/enquiries
 *    and recreates them; real data is never touched.
 *
 * Run explicitly:  php artisan db:seed --class=WearSalesDemoSeeder
 */
class WearSalesDemoSeeder extends Seeder
{
    /** Day offsets from today → [order status, payment status]. */
    private const ORDER_PLAN = [
        [0, 'confirmed', 'paid'],
        [0, 'confirmed', 'paid'],
        [0, 'pending_payment', 'pending'],
        [1, 'delivered', 'paid'],
        [1, 'confirmed', 'paid'],
        [1, 'processing', 'paid'],
        [2, 'shipped', 'paid'],
        [2, 'delivered', 'paid'],
        [2, 'pending_payment', 'pending'],
        [3, 'confirmed', 'paid'],
        [3, 'processing', 'paid'],
        [3, 'cancelled', 'cancelled'],
        [4, 'delivered', 'paid'],
        [4, 'confirmed', 'paid'],
        [5, 'shipped', 'paid'],
        [5, 'delivered', 'paid'],
        [5, 'pending_payment', 'pending'],
        [6, 'confirmed', 'paid'],
        [6, 'delivered', 'paid'],
    ];

    private const CUSTOMERS = [
        ['name' => 'Asha Mohamed', 'email' => 'asha.mohamed@example.com', 'phone' => '+255713100001', 'address' => 'Mikocheni B, Dar es Salaam', 'city' => 'Dar es Salaam'],
        ['name' => 'Juma Kiwelu', 'email' => 'juma.kiwelu@example.com', 'phone' => '+255713100002', 'address' => 'Kariakoo, Ilala, Dar es Salaam', 'city' => 'Dar es Salaam'],
        ['name' => 'Neema Baraka', 'email' => 'neema.baraka@example.com', 'phone' => '+255713100003', 'address' => 'Mbezi Beach, Ubungo, Dar es Salaam', 'city' => 'Dar es Salaam'],
        ['name' => 'Frank Mushi', 'email' => 'frank.mushi@example.com', 'phone' => '+255711100004', 'address' => 'Njiro, Arusha', 'city' => 'Arusha'],
        ['name' => 'Zawadi Kimaro', 'email' => 'zawadi.kimaro@example.com', 'phone' => '+255715100005', 'address' => 'Matema, Mbeya', 'city' => 'Mbeya'],
        ['name' => 'Emmanuel Shiri', 'email' => 'emmanuel.shiri@example.com', 'phone' => '+255689100006', 'address' => 'Michenzani, Mwanza', 'city' => 'Mwanza'],
    ];

    /** slug => [size => stock] adjustments that light up stock alerts. */
    private const STOCK_ALERTS = [
        'kp-redefined-graphic-hoodie' => ['L' => 3, 'XXL' => 0],
        'kp-icon-hoodie' => ['M' => 4],
        'kp-redefined-graffiti-polo-white' => ['L' => 2],
        'kilimanjaro-heritage-long-sleeve' => ['XL' => 5],
    ];

    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->warn('Skipping demo sales seeder in production.');

            return;
        }

        $products = WearProduct::query()->where('is_active', true)->orderBy('sort_order')->get();

        if ($products->isEmpty()) {
            $this->command?->warn('No active products found. Run WearDemoSeeder first.');

            return;
        }

        // Remove only our own previous demo rows (cascades items, payments,
        // status history and returns through their FK definitions).
        WearOrder::query()->where('checkout_idempotency_key', 'like', 'demo-%')->delete();
        ContactMessage::query()
            ->whereIn('email_hash', collect(self::CUSTOMERS)->map(fn (array $c) => ContactMessage::emailHash($c['email']))->all())
            ->delete();

        $customers = $this->seedCustomers();
        $this->seedOrders($products->values(), $customers);
        $this->seedStockAlerts();
        $this->seedEnquiries($customers);

        $this->command?->info('Demo sales data seeded: '.WearOrder::query()->count().' orders, '
            .User::query()->where('role', UserRole::User->value)->count().' customers, '
            .ContactMessage::query()->count().' enquiries.');
    }

    /** @return array<int, User> */
    private function seedCustomers(): array
    {
        return collect(self::CUSTOMERS)->map(function (array $definition): User {
            $user = User::query()->whereEmail($definition['email'])->first();

            if ($user === null) {
                $user = new User([
                    'name' => $definition['name'],
                    'email' => $definition['email'],
                    'phone' => $definition['phone'],
                ]);
            }

            $user->forceFill([
                'status' => 'active',
                'role' => UserRole::User,
                'phone_verified_at' => now()->subMonths(3),
                'onboarding_completed_at' => now()->subMonths(3),
            ])->save();

            return $user;
        })->all();
    }

    /**
     * @param array<int, WearProduct> $products
     * @param array<int, User>        $customers
     */
    private function seedOrders($products, array $customers): void
    {
        $fee = 3000;
        $itemCursor = 0;

        foreach (self::ORDER_PLAN as $index => [$daysAgo, $status, $paymentStatus]) {
            $definition = self::CUSTOMERS[$index % count(self::CUSTOMERS)];
            $customer = $customers[$index % count($customers)];

            $placedAt = $daysAgo === 0
                ? now()->subHours(2 + ($index % 4))
                : now()->subDays($daysAgo)->setTime(9 + ($index % 9), ($index * 7) % 60);

            $lineCount = ($index % 3 === 0) ? 2 : 1;
            $lines = [];

            for ($line = 0; $line < $lineCount; $line++) {
                $product = $products[$itemCursor % $products->count()];
                $itemCursor++;

                $variant = $product->variants()->orderBy('id')->first();
                $quantity = ($index % 4 === 0 && $line === 0) ? 2 : 1;
                $unitPrice = (float) $product->price;

                $lines[] = [
                    'product' => $product,
                    'variant' => $variant,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'line_total' => $unitPrice * $quantity,
                ];
            }

            $subtotal = collect($lines)->sum('line_total');
            $isPaid = $paymentStatus === PaymentStatus::Paid->value;

            $order = WearOrder::query()->create([
                'order_number' => 'KP-'.$placedAt->format('ymd').'-DEMO'.strtoupper(str_pad((string) $index, 4, '0', STR_PAD_LEFT)),
                'checkout_idempotency_key' => 'demo-order-'.$index,
                'checkout_fingerprint' => hash('sha256', 'demo-'.$index),
                'user_id' => $customer->id,
                'customer_name' => $definition['name'],
                'customer_phone' => $definition['phone'],
                'customer_email' => $definition['email'],
                'delivery_address' => $definition['address'],
                'delivery_city' => $definition['city'],
                'notes' => null,
                'subtotal' => $subtotal,
                'delivery_fee' => $fee,
                'total' => $subtotal + $fee,
                'status' => OrderStatus::from($status),
                'payment_status' => PaymentStatus::from($paymentStatus),
                'payment_method' => 'mobile_money',
                'placed_at' => $placedAt,
                'delivery_provider' => in_array($status, ['shipped', 'delivered'], true) ? 'Bongo Express' : null,
                'tracking_number' => in_array($status, ['shipped', 'delivered'], true) ? 'BEX-DEMO-'.strtoupper(Str::random(6)) : null,
                'fulfillment_notes' => null,
                'shipped_at' => in_array($status, ['shipped', 'delivered'], true) ? $placedAt->copy()->addDay() : null,
                'delivered_at' => $status === 'delivered' ? $placedAt->copy()->addDays(2) : null,
            ]);

            foreach ($lines as $line) {
                $variant = $line['variant'];

                WearOrderItem::query()->create([
                    'wear_order_id' => $order->id,
                    'wear_product_id' => $line['product']->id,
                    'wear_product_variant_id' => $variant?->id,
                    'product_name' => $line['product']->name,
                    'sku' => $variant?->sku,
                    'size' => $variant?->size,
                    'color' => $variant?->color,
                    'quantity' => $line['quantity'],
                    'unit_price' => $line['unit_price'],
                    'line_total' => $line['line_total'],
                ]);
            }

            OrderStatusHistory::query()->create([
                'wear_order_id' => $order->id,
                'from_status' => null,
                'to_status' => $status,
                'reason' => 'Demo order seeded for presentation.',
                'changed_by' => null,
            ]);

            PaymentTransaction::query()->create([
                'wear_order_id' => $order->id,
                'user_id' => $customer->id,
                'provider' => 'selcom',
                'provider_reference' => 'SELCO-DEMO-'.strtoupper(str_pad((string) $index, 4, '0', STR_PAD_LEFT)),
                'idempotency_key' => 'demo-pay-'.$index,
                'amount' => $order->total,
                'currency' => 'TZS',
                'status' => PaymentStatus::from($paymentStatus),
                'payload' => null,
                'initiated_at' => $placedAt,
                'completed_at' => $isPaid ? $placedAt->copy()->addMinutes(3) : null,
                'failed_at' => $paymentStatus === PaymentStatus::Failed->value ? $placedAt->copy()->addMinutes(5) : null,
            ]);
        }

        $this->seedReturns();
    }

    private function seedReturns(): void
    {
        $delivered = WearOrder::query()
            ->where('checkout_idempotency_key', 'like', 'demo-%')
            ->where('status', OrderStatus::Delivered->value)
            ->whereNotNull('user_id')
            ->orderBy('id')
            ->limit(2)
            ->get();

        $plans = [
            [
                'request_type' => 'return',
                'reason' => 'Size too small',
                'status' => 'processed',
                'refund_status' => 'pending',
                'notes' => 'Customer asks for a refund to their mobile money number.',
            ],
            [
                'request_type' => 'exchange',
                'reason' => 'Wrong size received',
                'status' => 'under_review',
                'refund_status' => null,
                'notes' => 'Customer wants to swap the hoodie for a size larger.',
            ],
        ];

        foreach ($delivered->values() as $position => $order) {
            $plan = $plans[$position] ?? $plans[0];
            $items = $order->items()->get();

            WearReturnRequest::query()->create([
                'wear_order_id' => $order->id,
                'user_id' => $order->user_id,
                'request_type' => $plan['request_type'],
                'reason' => $plan['reason'],
                'order_item_ids' => $items->pluck('id')->all(),
                'notes' => $plan['notes'],
                'status' => $plan['status'],
                'refund_amount' => $plan['refund_status'] === 'pending' ? $order->total : null,
                'refund_status' => $plan['refund_status'],
                'approved_at' => $plan['status'] === 'processed' ? now()->subDay() : null,
                'processed_at' => $plan['status'] === 'processed' ? now()->subDay() : null,
            ]);
        }
    }

    private function seedStockAlerts(): void
    {
        foreach (self::STOCK_ALERTS as $slug => $sizes) {
            $product = WearProduct::query()->where('slug', $slug)->first();

            if ($product === null) {
                continue;
            }

            foreach ($sizes as $size => $stock) {
                $product->variants()->where('size', $size)->update(['stock' => $stock]);
            }
        }
    }

    /**
     * @param array<int, User> $customers
     */
    private function seedEnquiries(array $customers): void
    {
        $messages = [
            [0, 'Order enquiry', 'Hi, I placed an order for the KP Icon Hoodie — when will it be dispatched to Mbezi Beach?', 'new'],
            [1, 'Product / sizing', 'Do the polos run true to size? I am usually between M and L.', 'read'],
            [2, 'Returns', 'The hoodie I received is one size too large. I would like to exchange it for a medium.', 'in_progress'],
            [3, 'Wholesale', 'I run a boutique in Arusha and would like to order 20 pieces at trade pricing. Do you offer wholesale?', 'new'],
            [4, 'Customer support', 'My mobile money payment went through but the order still shows pending on my account. Please check.', 'new'],
            [5, 'Collaboration', 'We would love to feature KP Wear at our upcoming pop-up in Mwanza. Can we talk?', 'new'],
        ];

        foreach ($messages as $index => [$customerIndex, $type, $message, $status]) {
            $customer = $customers[$customerIndex] ?? $customers[0];

            ContactMessage::query()->create([
                'name' => $customer->name,
                'email' => $customer->email,
                'type' => $type,
                'message' => $message,
                'status' => $status,
            ]);
        }
    }
}
