@extends('admin.layouts.app', ['title' => 'Dashboard', 'heading' => 'Dashboard'])

@section('content')
@php
    $periodRevenue = collect($salesChart)->sum('revenue');
    $maxRevenue = max((float) collect($salesChart)->max('revenue'), 1);
    $averageOrder = $metrics['orders_paid'] > 0 ? $metrics['total_revenue'] / $metrics['orders_paid'] : 0;
@endphp
<div>
    <x-admin.page-header title="Dashboard" subtitle="Welcome back, {{ auth()->user()->name }} — here is what is happening today.">
        <x-slot name="action">
            @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.products.create'))
                <a href="{{ route('admin.wear.products.create') }}" class="kp-admin-primary-button"><x-tabler-plus size="17" stroke-width="2" />Add product</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    {{-- Stat tiles --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4">
        <x-admin.stat label="Revenue ({{ now()->format('M') }})" value="TZS {{ number_format($metrics['month_revenue'], 0) }}" icon="currency-dollar" detail="Paid sales this month" />
        <x-admin.stat label="Orders" value="{{ number_format($metrics['orders_total']) }}" icon="shopping-cart" tone="sky" detail="{{ number_format($metrics['orders_paid_today']) }} paid today" />
        <x-admin.stat label="Customers" value="{{ number_format($metrics['customers']) }}" icon="users" tone="violet" detail="{{ number_format($metrics['customers_today']) }} new today" />
        <x-admin.stat label="Avg. order" value="TZS {{ number_format($averageOrder, 0) }}" icon="trending-up" tone="amber" detail="{{ number_format($metrics['products_active']) }} active products" />
    </div>

    {{-- Revenue chart + low stock --}}
    <div class="mb-6 grid grid-cols-1 gap-5 lg:grid-cols-3">
        <x-admin.card class="p-6 lg:col-span-2">
            <div class="mb-6 flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h3 class="kp-admin-display text-lg">Revenue Overview</h3>
                    <p class="mt-1.5 text-xs text-slate-400">Paid revenue over the selected period (TZS)</p>
                </div>
                <div class="flex flex-wrap gap-2">
                    @foreach([7, 30, 365] as $period)
                        <x-admin.pill :href="route('admin.dashboard', ['days' => $period])" :active="$chartDays === $period">{{ $period === 365 ? 'All time' : $period.' days' }}</x-admin.pill>
                    @endforeach
                </div>
            </div>

            <div class="flex h-48 items-end justify-between gap-1.5 sm:gap-2">
                @forelse($salesChart as $point)
                    <div class="group flex h-full min-w-0 flex-1 flex-col items-center justify-end gap-2">
                        <div class="relative flex h-full w-full items-end">
                            <span class="pointer-events-none absolute -top-5 left-1/2 hidden -translate-x-1/2 whitespace-nowrap rounded bg-slate-950 px-1.5 py-0.5 text-[10px] font-semibold text-white group-hover:block">TZS {{ number_format($point['revenue'], 0) }}</span>
                            <div class="w-full rounded-t-lg bg-emerald-500/80 transition-colors group-hover:bg-emerald-600" style="height: {{ max(2, (($point['revenue'] / $maxRevenue) * 100)) }}%"></div>
                        </div>
                        <span class="text-[10px] text-slate-400">{{ $point['label'] }}</span>
                    </div>
                @empty
                    <p class="m-auto text-sm text-slate-400">No paid sales in this period.</p>
                @endforelse
            </div>

            <div class="mt-5 border-t border-slate-100 pt-4">
                <p class="text-xs uppercase tracking-wider text-slate-400">Period revenue</p>
                <p class="mt-1 font-display text-xl text-slate-950">TZS {{ number_format($periodRevenue, 0) }}</p>
            </div>
        </x-admin.card>

        <x-admin.card class="p-6">
            <div class="mb-5 flex items-center justify-between gap-3">
                <div>
                    <h3 class="kp-admin-display text-lg">Low Stock</h3>
                    <p class="mt-1.5 text-xs text-slate-400">{{ number_format($metrics['variants_total']) }} variants · {{ number_format($metrics['stock_units_total']) }} units in stock</p>
                </div>
                <a href="{{ route('admin.wear.inventory.index') }}" class="kp-admin-link">View all <x-tabler-arrow-right size="13" /></a>
            </div>
            <div class="space-y-3">
                @forelse($lowStockVariants->take(4) as $variant)
                    <div class="flex items-center gap-3">
                        <span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-amber-50 text-amber-600"><x-tabler-alert-triangle size="18" stroke-width="1.8" /></span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $variant->product?->name ?? $variant->sku }}</p>
                            <p class="mt-0.5 truncate text-xs text-slate-400">{{ $variant->sku }}</p>
                        </div>
                        <x-admin.badge :status="$variant->stock <= 0 ? 'out' : 'low'" :label="$variant->stock.' left'" />
                    </div>
                @empty
                    <div class="rounded-xl bg-emerald-50 px-4 py-4 text-sm text-emerald-800">Everything is well stocked.</div>
                @endforelse
            </div>
        </x-admin.card>
    </div>

    {{-- Recent orders + top products --}}
    <div class="grid grid-cols-1 gap-5 lg:grid-cols-3">
        <x-admin.card class="overflow-hidden lg:col-span-2">
            <div class="flex items-center justify-between gap-3 px-6 pb-4 pt-6">
                <div>
                    <h3 class="kp-admin-display text-lg">Recent Orders</h3>
                    <p class="mt-1.5 text-xs text-slate-400">Latest activity from your store</p>
                </div>
                <a href="{{ route('admin.wear.orders.index') }}" class="kp-admin-link">View all <x-tabler-arrow-right size="13" /></a>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-[720px] text-sm">
                    <thead>
                        <tr class="text-xs uppercase tracking-wider text-slate-400">
                            <th class="px-6 py-3 text-left font-medium">Order</th>
                            <th class="px-3 py-3 text-left font-medium">Customer</th>
                            <th class="hidden px-3 py-3 text-left font-medium sm:table-cell">Date</th>
                            <th class="px-3 py-3 text-right font-medium">Total</th>
                            <th class="px-6 py-3 text-left font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders->take(5) as $order)
                            @php $status = $order->status?->value ?? ''; @endphp
                            <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                                <td class="px-6 py-3.5 font-medium text-slate-900">
                                    <a href="{{ route('admin.wear.orders.show', $order) }}" class="hover:text-emerald-600">{{ $order->order_number }}</a>
                                </td>
                                <td class="px-3 py-3.5">
                                    <p class="font-medium text-slate-800">{{ $order->customer_name ?: $order->user?->name ?: 'Guest' }}</p>
                                    <p class="mt-0.5 text-xs text-slate-400">{{ $order->items->sum('quantity') }} items</p>
                                </td>
                                <td class="hidden px-3 py-3.5 text-slate-500 sm:table-cell">{{ $order->placed_at?->format('d M Y') }}</td>
                                <td class="px-3 py-3.5 text-right font-medium text-slate-900">TZS {{ number_format((float) $order->total, 0) }}</td>
                                <td class="px-6 py-3.5"><x-admin.badge :status="$status ?: 'pending'" :label="str_replace('_', ' ', ucfirst($status ?: 'pending'))" /></td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><x-admin.empty message="No orders yet." /></td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-admin.card>

        <x-admin.card class="p-6">
            <h3 class="kp-admin-display mb-5 text-lg">Top Products</h3>
            <div class="space-y-4">
                @forelse($topProducts as $index => $product)
                    <div class="flex items-center gap-3">
                        <span class="font-display text-lg text-slate-300 w-5">{{ $index + 1 }}</span>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-medium text-slate-800">{{ $product->product_name }}</p>
                            <p class="text-xs text-slate-400">{{ number_format((int) $product->units_sold) }} sold</p>
                        </div>
                        <span class="text-sm font-medium text-slate-700">TZS {{ number_format((float) $product->revenue, 0) }}</span>
                    </div>
                @empty
                    <p class="py-4 text-center text-sm text-slate-400">No paid sales yet.</p>
                @endforelse
            </div>

            <div class="mt-6 space-y-2 border-t border-slate-100 pt-5">
                @foreach([
                    ['Pending payment', $metrics['orders_pending_payment'], 'amber'],
                    ['Processing', $metrics['orders_processing'], 'blue'],
                    ['Shipped', $metrics['orders_shipped'], 'blue'],
                    ['Delivered', $metrics['orders_delivered'], 'green'],
                ] as [$label, $count, $tone])
                    <div class="flex items-center justify-between rounded-xl bg-slate-50 px-4 py-2.5 text-sm">
                        <span class="text-slate-600">{{ $label }}</span>
                        <x-admin.badge :status="$tone" :label="number_format($count)" />
                    </div>
                @endforeach
            </div>
        </x-admin.card>
    </div>
</div>
@endsection
