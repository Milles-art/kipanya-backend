@extends('admin.layouts.app', ['title' => 'Dashboard', 'heading' => 'Wear Dashboard'])

@section('content')
<div class="space-y-8">
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Store overview</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">Monitor sales, orders, products and inventory from one place.</p>
        </div>
        @if(auth()->user()->hasPermission('commerce.manage'))
            <a href="{{ route('admin.wear.products.create') }}" class="button-dark">+ Add product</a>
        @endif
    </section>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <section>
        <div class="mb-3 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Sales</p>
                <h3 class="mt-1 text-lg font-bold">Revenue</h3>
            </div>
            <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">Paid orders</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach([
                ['Today', $metrics['today_revenue']],
                ['This week', $metrics['week_revenue']],
                ['This month', $metrics['month_revenue']],
                ['All time', $metrics['total_revenue']],
            ] as [$label, $value])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-black tracking-tight">TZS {{ number_format($value, 0) }}</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Orders</p>
                    <h3 class="mt-1 text-lg font-bold">Order pipeline</h3>
                </div>
                <span class="text-2xl font-black">{{ $metrics['orders_total'] }}</span>
            </div>
            <div class="mt-5 grid grid-cols-2 gap-3 sm:grid-cols-4">
                @foreach([
                    ['Pending payment', $metrics['orders_pending_payment'], 'text-amber-700 bg-amber-50'],
                    ['Processing', $metrics['orders_processing'], 'text-blue-700 bg-blue-50'],
                    ['Shipped', $metrics['orders_shipped'], 'text-violet-700 bg-violet-50'],
                    ['Delivered', $metrics['orders_delivered'], 'text-emerald-700 bg-emerald-50'],
                ] as [$label, $value, $classes])
                    <div class="rounded-xl {{ $classes }} p-3">
                        <p class="text-xs font-semibold">{{ $label }}</p>
                        <p class="mt-1 text-xl font-black">{{ $value }}</p>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Inventory</p>
                    <h3 class="mt-1 text-lg font-bold">Stock health</h3>
                </div>
                <span class="text-2xl font-black">{{ $metrics['variants_total'] }}</span>
            </div>
            <div class="mt-5 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-gray-50 p-3">
                    <p class="text-xs text-gray-500">Variants</p>
                    <p class="mt-1 text-xl font-black">{{ $metrics['variants_total'] }}</p>
                </div>
                <div class="rounded-xl bg-amber-50 p-3 text-amber-800">
                    <p class="text-xs">Low stock</p>
                    <p class="mt-1 text-xl font-black">{{ $metrics['low_stock'] }}</p>
                </div>
                <div class="rounded-xl bg-red-50 p-3 text-red-800">
                    <p class="text-xs">Out of stock</p>
                    <p class="mt-1 text-xl font-black">{{ $metrics['out_of_stock'] }}</p>
                </div>
            </div>
        </div>
    </section>

    <section>
        <div class="mb-3 flex items-center justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Statistics</p>
                <h3 class="mt-1 text-lg font-bold">Store counters</h3>
            </div>
            <span class="text-xs font-semibold text-gray-400">Live store totals</span>
        </div>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-6">
            @foreach([
                ['Orders', $metrics['orders_total']],
                ['Paid orders', $metrics['orders_paid']],
                ['Customers', $metrics['customers']],
                ['Products', $metrics['products_total']],
                ['Stock units', $metrics['stock_units_total']],
                ['Needs attention', $metrics['orders_pending_payment'] + $metrics['low_stock'] + $metrics['out_of_stock']],
            ] as [$label, $value])
                <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-3xl font-black tracking-tight" data-stat-counter="{{ (int) $value }}">0</p>
                </div>
            @endforeach
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Performance</p>
                <h3 class="mt-1 text-lg font-bold">Sales overview</h3>
                <p class="mt-1 text-sm text-gray-500">Paid revenue over the selected period.</p>
            </div>
            <div class="flex rounded-xl bg-gray-100 p-1" role="group" aria-label="Sales chart period">
                <button type="button" data-chart-period="7" class="rounded-lg bg-white px-3 py-1.5 text-xs font-bold text-gray-900 shadow-sm">7 days</button>
                <button type="button" data-chart-period="30" class="rounded-lg px-3 py-1.5 text-xs font-bold text-gray-500">30 days</button>
            </div>
        </div>

        <div class="mt-6 overflow-hidden rounded-xl border border-gray-100 bg-gray-50/60">
            <div class="relative h-72 w-full">
                <svg id="sales-chart" viewBox="0 0 1000 320" preserveAspectRatio="none" class="h-full w-full" role="img" aria-label="Sales revenue chart">
                    <defs>
                        <linearGradient id="sales-area-gradient" x1="0" x2="0" y1="0" y2="1">
                            <stop offset="0%" stop-color="currentColor" stop-opacity="0.16"></stop>
                            <stop offset="100%" stop-color="currentColor" stop-opacity="0"></stop>
                        </linearGradient>
                    </defs>
                    <g id="sales-grid"></g>
                    <path id="sales-area" fill="url(#sales-area-gradient)" stroke="none"></path>
                    <path id="sales-line" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                    <g id="sales-points"></g>
                </svg>
                <div id="sales-empty" class="pointer-events-none absolute inset-0 hidden items-center justify-center text-sm text-gray-400">No paid sales in this period.</div>
            </div>
            <div id="sales-labels" class="grid grid-cols-7 gap-1 border-t border-gray-100 px-3 py-3 text-[11px] text-gray-400"></div>
        </div>
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-sm">
            <div><span class="text-gray-500">Period revenue</span> <strong id="sales-chart-total" class="ml-1">TZS 0</strong></div>
            <span class="text-xs text-gray-400">Paid orders only</span>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-[1.65fr_1fr]">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Sales activity</p>
                    <h3 class="mt-1 text-lg font-bold">Recent orders</h3>
                </div>
                <span class="text-xs font-semibold text-gray-400">Latest 8</span>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                        <tr>
                            <th class="px-5 py-3">Order</th>
                            <th class="px-5 py-3">Customer</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Payment</th>
                            <th class="px-5 py-3">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                    @forelse($recentOrders as $order)
                        @php
                            $status = $order->status;
                            $payment = $order->payment_status;
                            $statusClasses = match($status?->value) {
                                'delivered' => 'bg-emerald-50 text-emerald-700',
                                'shipped' => 'bg-violet-50 text-violet-700',
                                'processing' => 'bg-blue-50 text-blue-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            };
                            $paymentClasses = match($payment?->value) {
                                'paid' => 'bg-emerald-50 text-emerald-700',
                                'failed', 'cancelled' => 'bg-red-50 text-red-700',
                                'refunded' => 'bg-violet-50 text-violet-700',
                                default => 'bg-amber-50 text-amber-700',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50/70">
                            <td class="px-5 py-4 font-bold">#{{ $order->order_number }}</td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $order->customer_name ?: $order->user?->name ?: 'Guest' }}</p>
                                <p class="text-xs text-gray-400">{{ $order->items->sum('quantity') }} item(s)</p>
                            </td>
                            <td class="px-5 py-4 font-semibold">TZS {{ number_format((float) $order->total, 0) }}</td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $paymentClasses }}">{{ $payment?->value ? str_replace('_', ' ', ucfirst($payment->value)) : 'Unknown' }}</span></td>
                            <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">{{ $status?->value ? str_replace('_', ' ', ucfirst($status->value)) : 'Unknown' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Inventory alert</p>
                <h3 class="mt-1 text-lg font-bold">Low stock</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($lowStockVariants as $variant)
                    <a href="{{ route('admin.wear.products.variants.edit', [$variant->product, $variant]) }}" class="flex items-center justify-between gap-4 px-5 py-4 hover:bg-gray-50">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $variant->product?->name ?: 'Unknown product' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ $variant->size }} · {{ $variant->color }} · {{ $variant->sku }}</p>
                        </div>
                        <span class="shrink-0 rounded-full bg-amber-50 px-3 py-1 text-xs font-black text-amber-700">{{ $variant->stock }} left</span>
                    </a>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-500">No low-stock variants. Nice.</div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="rounded-2xl border border-gray-200 bg-gray-950 p-5 text-white sm:p-6">
        <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-400">Next operational steps</p>
                <h3 class="mt-1 text-xl font-black">Manage the store, not the code.</h3>
                <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-400">Products and variants are ready. Inventory, orders, payments and customer operations are the next admin modules.</p>
            </div>
            @if(auth()->user()->hasPermission('commerce.manage'))
                <a href="{{ route('admin.wear.products.index') }}" class="shrink-0 rounded-xl bg-white px-4 py-3 text-sm font-bold text-gray-950 hover:bg-gray-100">Manage products →</a>
            @endif
        </div>
    </section>
</div>
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const counters = document.querySelectorAll('[data-stat-counter]');
        const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

        counters.forEach((counter) => {
            const target = Number(counter.dataset.statCounter || 0);
            if (reducedMotion || target === 0) {
                counter.textContent = target.toLocaleString();
                return;
            }

            const duration = 700;
            const start = performance.now();
            const tick = (now) => {
                const progress = Math.min((now - start) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 3);
                counter.textContent = Math.round(target * eased).toLocaleString();
                if (progress < 1) window.requestAnimationFrame(tick);
            };
            window.requestAnimationFrame(tick);
        });

        const salesData = @json($salesChart);
        const chart = document.getElementById('sales-chart');
        const grid = document.getElementById('sales-grid');
        const area = document.getElementById('sales-area');
        const line = document.getElementById('sales-line');
        const pointsGroup = document.getElementById('sales-points');
        const labels = document.getElementById('sales-labels');
        const empty = document.getElementById('sales-empty');
        const total = document.getElementById('sales-chart-total');
        const periodButtons = document.querySelectorAll('[data-chart-period]');

        const renderSalesChart = (days) => {
            const data = salesData.slice(-days);
            const width = 1000;
            const height = 320;
            const padX = 34;
            const padTop = 22;
            const padBottom = 28;
            const plotHeight = height - padTop - padBottom;
            const plotWidth = width - (padX * 2);
            const max = Math.max(...data.map(item => Number(item.revenue)), 1);
            const step = data.length > 1 ? plotWidth / (data.length - 1) : plotWidth;
            const coords = data.map((item, index) => ({
                x: data.length > 1 ? padX + (step * index) : width / 2,
                y: padTop + plotHeight - ((Number(item.revenue) / max) * plotHeight),
                revenue: Number(item.revenue),
                label: item.label,
            }));

            const points = coords.map(point => `${point.x},${point.y}`).join(' ');
            const areaPoints = `${padX},${height - padBottom} ${points} ${coords.at(-1)?.x ?? padX},${height - padBottom}`;
            line.setAttribute('d', `M ${points.replaceAll(' ', ' L ')}`);
            area.setAttribute('d', `M ${areaPoints.replaceAll(' ', ' L ')} Z`);

            grid.innerHTML = [0, 1, 2, 3].map(level => {
                const y = padTop + ((plotHeight / 3) * level);
                return `<line x1="${padX}" y1="${y}" x2="${width - padX}" y2="${y}" stroke="currentColor" stroke-opacity="0.08" stroke-width="1" />`;
            }).join('');

            pointsGroup.innerHTML = coords.map(point =>
                `<circle cx="${point.x}" cy="${point.y}" r="4" fill="currentColor" stroke="white" stroke-width="2" />`
            ).join('');

            const labelIndexes = data.length <= 7
                ? data.map((_, index) => index)
                : [0, 5, 10, 15, 20, 25, 29].filter(index => index < data.length);
            labels.innerHTML = labelIndexes.map(index => `<span class="text-center">${data[index].label}</span>`).join('');

            const revenue = data.reduce((sum, item) => sum + Number(item.revenue), 0);
            total.textContent = `TZS ${Math.round(revenue).toLocaleString()}`;
            empty.classList.toggle('hidden', revenue > 0);
            empty.classList.toggle('flex', revenue <= 0);
            chart.setAttribute('aria-label', `Sales revenue for the last ${days} days. Total TZS ${Math.round(revenue).toLocaleString()}.`);
        };

        periodButtons.forEach(button => {
            button.addEventListener('click', () => {
                periodButtons.forEach(item => item.classList.remove('bg-white', 'text-gray-900', 'shadow-sm'));
                periodButtons.forEach(item => item.classList.add('text-gray-500'));
                button.classList.add('bg-white', 'text-gray-900', 'shadow-sm');
                button.classList.remove('text-gray-500');
                renderSalesChart(Number(button.dataset.chartPeriod));
            });
        });

        renderSalesChart(7);
    });
</script>
@endsection
