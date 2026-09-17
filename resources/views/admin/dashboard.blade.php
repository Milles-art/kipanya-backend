@extends('admin.layouts.app', ['title' => 'Dashboard', 'heading' => 'Wear Dashboard'])

@section('content')
<div class="mx-auto max-w-[1450px] space-y-5">
    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <section class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <p class="text-xs font-semibold text-slate-400">Kipanya Wear</p>
            <h2 class="mt-1 text-[27px] font-black tracking-tight text-slate-950">Welcome back, {{ auth()->user()->name }} <span aria-hidden="true">👋</span></h2>
            <p class="mt-1 text-sm text-slate-500">Here's what's happening with your store today.</p>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.products.create'))
                <a href="{{ route('admin.wear.products.create') }}" class="kp-admin-primary-button"><x-tabler-plus size="17" /> Add product</a>
            @endif
            <div class="hidden items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-2.5 sm:flex">
                <x-tabler-calendar size="21" class="text-slate-700" stroke-width="1.8" />
                <div class="leading-tight">
                    <p class="text-xs font-bold text-slate-800">{{ now()->format('l, d F Y') }}</p>
                    <p class="mt-0.5 text-[10px] text-slate-400">Kipanya Wear Admin</p>
                </div>
            </div>
        </div>
    </section>

    <section class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $summaryCards = [
                ['Revenue', 'TZS '.number_format($metrics['today_revenue'], 0), 'Today', 'chart-bar', 'emerald', $metrics['orders_paid_today'] > 0 ? $metrics['orders_paid_today'].' paid today' : 'No paid sales today'],
                ['Orders', number_format($metrics['orders_total']), 'Total', 'shopping-cart', 'blue', $metrics['orders_paid_today'].' paid today'],
                ['Customers', number_format($metrics['customers']), 'Total', 'users', 'violet', '+ '.$metrics['customers_today'].' new today'],
                ['Products', number_format($metrics['products_total']), 'Total', 'box', 'amber', number_format($metrics['variants_total']).' variants'],
            ];
        @endphp
        @foreach($summaryCards as [$label, $value, $sub, $icon, $tone, $foot])
            <div class="kp-summary-card tone-{{ $tone }}">
                <span class="kp-summary-icon"><x-dynamic-component :component="'tabler-'.$icon" size="24" stroke-width="1.8" /></span>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-slate-500">{{ $label }}</p>
                    <p class="mt-1 truncate text-[23px] font-black tracking-tight text-slate-950">{{ $value }}</p>
                    <p class="mt-0.5 text-[11px] text-slate-400">{{ $sub }}</p>
                    <p class="mt-2 text-[11px] font-medium text-slate-500">{{ $foot }}</p>
                </div>
            </div>
        @endforeach
    </section>

    <section class="kp-admin-card p-4 sm:p-5">
        <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span class="kp-section-icon"><x-tabler-truck size="22" stroke-width="1.8" /></span>
                <div>
                    <h3 class="text-[15px] font-extrabold text-slate-900">Order pipeline</h3>
                    <p class="text-xs text-slate-400">Track your orders from purchase to delivery.</p>
                </div>
            </div>
            @if(Route::has('admin.wear.orders.index'))
                <a href="{{ route('admin.wear.orders.index') }}" class="kp-admin-link">View all orders <x-tabler-arrow-right size="16" /></a>
            @endif
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2.5 lg:grid-cols-4">
            @foreach([
                ['Pending payment', $metrics['orders_pending_payment'], 'clock', 'amber'],
                ['Processing', $metrics['orders_processing'], 'settings', 'blue'],
                ['Shipped', $metrics['orders_shipped'], 'truck-delivery', 'violet'],
                ['Delivered', $metrics['orders_delivered'], 'circle-check', 'emerald'],
            ] as [$label, $value, $icon, $tone])
                <div class="kp-pipeline-item tone-{{ $tone }}">
                    <div><p class="text-[11px] font-semibold text-slate-500">{{ $label }}</p><p class="mt-2 text-[21px] font-black text-slate-950">{{ $value }}</p></div>
                    <span class="kp-pipeline-icon"><x-dynamic-component :component="'tabler-'.$icon" size="22" stroke-width="1.8" /></span>
                </div>
            @endforeach
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.65fr)_minmax(310px,0.8fr)]">
        <div class="kp-admin-card p-4 sm:p-5">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex items-center gap-3">
                    <span class="kp-section-icon"><x-tabler-chart-bar size="21" stroke-width="1.8" /></span>
                    <div>
                        <h3 class="text-[15px] font-extrabold text-slate-900">Sales overview</h3>
                        <p class="text-xs text-slate-400">Paid revenue over the selected period.</p>
                    </div>
                </div>
                <div class="flex rounded-lg border border-slate-200 bg-slate-50 p-0.5" role="group" aria-label="Sales chart period">
                    <button type="button" data-chart-period="7" class="rounded-md bg-slate-900 px-3 py-1.5 text-[11px] font-bold text-white shadow-sm">7 days</button>
                    <button type="button" data-chart-period="30" class="rounded-md px-3 py-1.5 text-[11px] font-bold text-slate-500">30 days</button>
                    <button type="button" data-chart-period="365" class="rounded-md px-3 py-1.5 text-[11px] font-bold text-slate-500">All time</button>
                </div>
            </div>
            <div class="mt-4 overflow-hidden rounded-xl bg-slate-50/70">
                <div class="relative h-60 w-full">
                    <svg id="sales-chart" viewBox="0 0 1000 320" preserveAspectRatio="none" class="h-full w-full text-blue-600" role="img" aria-label="Sales revenue chart">
                        <defs><linearGradient id="sales-area-gradient" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="currentColor" stop-opacity="0.14"></stop><stop offset="100%" stop-color="currentColor" stop-opacity="0"></stop></linearGradient></defs>
                        <g id="sales-grid"></g><path id="sales-area" fill="url(#sales-area-gradient)" stroke="none"></path><path id="sales-line" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path><g id="sales-points"></g>
                    </svg>
                    <div id="sales-empty" class="pointer-events-none absolute inset-0 hidden items-center justify-center text-xs font-medium text-slate-400">No paid sales in this period.</div>
                </div>
                <div id="sales-labels" class="grid grid-cols-7 gap-1 px-3 pb-3 text-[10px] text-slate-400"></div>
            </div>
            <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                <div><strong id="sales-chart-total" class="text-xl font-black text-slate-950">TZS 0</strong><p class="text-[11px] text-slate-400">Period revenue</p></div>
                <span class="text-[10px] font-medium text-slate-400">Paid orders only</span>
            </div>
        </div>

        <div class="kp-admin-card p-4 sm:p-5">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3"><span class="kp-section-icon"><x-tabler-stack-2 size="21" stroke-width="1.8" /></span><div><h3 class="text-[15px] font-extrabold text-slate-900">Inventory status</h3><p class="text-xs text-slate-400">Current stock overview.</p></div></div>
                @if(Route::has('admin.wear.inventory.index'))<a href="{{ route('admin.wear.inventory.index') }}" class="kp-admin-link">View inventory <x-tabler-arrow-right size="16" /></a>@endif
            </div>
            <div class="mt-4 space-y-2">
                @foreach([
                    ['Variants', number_format($metrics['variants_total']), 'box', 'blue'],
                    ['Stock units', number_format($metrics['stock_units_total']), 'database', 'emerald'],
                    ['Low stock', number_format($metrics['low_stock']), 'alert-triangle', 'amber'],
                    ['Out of stock', number_format($metrics['out_of_stock']), 'circle-x', 'red'],
                ] as [$label, $value, $icon, $tone])
                    <div class="kp-inventory-row tone-{{ $tone }}"><span class="kp-inventory-icon"><x-dynamic-component :component="'tabler-'.$icon" size="21" stroke-width="1.8" /></span><div class="min-w-0"><p class="text-[16px] font-black text-slate-900">{{ $value }}</p><p class="text-[10px] text-slate-400">{{ $label }}</p></div>@if($label === 'Variants')<x-tabler-chevron-right size="17" class="ml-auto text-slate-400" />@endif</div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="grid gap-4 xl:grid-cols-[minmax(0,1.65fr)_minmax(280px,0.65fr)]">
        <div class="kp-admin-card overflow-hidden">
            <div class="flex items-center justify-between border-b border-slate-100 px-4 py-4 sm:px-5">
                <div class="flex items-center gap-3"><span class="kp-section-icon"><x-tabler-file-invoice size="21" stroke-width="1.8" /></span><div><h3 class="text-[15px] font-extrabold text-slate-900">Recent orders</h3><p class="text-xs text-slate-400">Latest orders from your store.</p></div></div>
                @if(Route::has('admin.wear.orders.index'))<a href="{{ route('admin.wear.orders.index') }}" class="kp-admin-link">View all orders <x-tabler-arrow-right size="16" /></a>@endif
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full text-left text-sm">
                    <thead class="bg-slate-50 text-[10px] uppercase tracking-wider text-slate-400"><tr><th class="px-4 py-3 sm:px-5">Order</th><th class="px-4 py-3">Customer</th><th class="px-4 py-3">Items</th><th class="px-4 py-3">Total</th><th class="px-4 py-3">Payment</th><th class="px-4 py-3">Status</th></tr></thead>
                    <tbody class="divide-y divide-slate-100">
                    @forelse($recentOrders->take(5) as $order)
                        @php
                            $status = $order->status;
                            $payment = $order->payment_status;
                            $statusClasses = match($status?->value) { 'processing' => 'bg-blue-50 text-blue-700', 'shipped' => 'bg-violet-50 text-violet-700', 'delivered' => 'bg-emerald-50 text-emerald-700', 'cancelled' => 'bg-red-50 text-red-700', 'refunded' => 'bg-violet-50 text-violet-700', default => 'bg-amber-50 text-amber-700' };
                            $paymentClasses = match($payment?->value) { 'paid' => 'bg-emerald-50 text-emerald-700', 'failed' => 'bg-red-50 text-red-700', 'refunded' => 'bg-violet-50 text-violet-700', default => 'bg-amber-50 text-amber-700' };
                        @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="whitespace-nowrap px-4 py-3.5 font-bold text-slate-800 sm:px-5">#{{ $order->order_number }}</td>
                            <td class="max-w-[180px] px-4 py-3.5"><p class="truncate font-semibold text-slate-700">{{ $order->customer_name ?: $order->user?->name ?: 'Guest' }}</p><p class="mt-0.5 text-[10px] text-slate-400">{{ $order->delivery_address ?: '—' }}</p></td>
                            <td class="px-4 py-3.5 font-semibold text-slate-700">{{ $order->items->sum('quantity') }}</td>
                            <td class="whitespace-nowrap px-4 py-3.5 font-semibold text-slate-800">TZS {{ number_format((float) $order->total, 0) }}</td>
                            <td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $paymentClasses }}">{{ $payment?->value ? str_replace('_', ' ', ucfirst($payment->value)) : 'Unknown' }}</span></td>
                            <td class="px-4 py-3.5"><span class="rounded-full px-2.5 py-1 text-[10px] font-bold {{ $statusClasses }}">{{ $status?->value ? str_replace('_', ' ', ucfirst($status->value)) : 'Unknown' }}</span></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-12 text-center text-sm text-slate-500">No orders yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-4">
            <div class="kp-admin-card p-4 sm:p-5">
                <div class="flex items-center gap-3"><span class="kp-section-icon"><x-tabler-bell size="20" stroke-width="1.8" /></span><div><h3 class="text-[15px] font-extrabold text-slate-900">Attention</h3><p class="text-xs text-slate-400">Items that need your attention.</p></div></div>
                @if(($metrics['low_stock'] + $metrics['out_of_stock']) > 0)
                    <div class="mt-4 rounded-xl bg-amber-50 p-3"><p class="text-sm font-bold text-amber-800">Inventory needs attention</p><p class="mt-1 text-xs text-amber-700">{{ $metrics['low_stock'] }} low-stock and {{ $metrics['out_of_stock'] }} out-of-stock variants.</p></div>
                @else
                    <div class="mt-4 flex items-center gap-3 rounded-xl bg-emerald-50 p-3"><span class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-500 text-white"><x-tabler-check size="20" /></span><div><p class="text-sm font-bold text-emerald-800">All good!</p><p class="mt-0.5 text-xs text-emerald-700">No low-stock variants. Nice.</p></div></div>
                @endif
            </div>
            <div class="kp-admin-card p-4 sm:p-5">
                <div class="flex items-center gap-3"><span class="kp-section-icon"><x-tabler-bolt size="20" stroke-width="1.8" /></span><div><h3 class="text-[15px] font-extrabold text-slate-900">Quick actions</h3><p class="text-xs text-slate-400">Common tasks.</p></div></div>
                <div class="mt-3 space-y-1.5">
                    @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.products.create'))<a class="kp-quick-action" href="{{ route('admin.wear.products.create') }}"><x-tabler-plus size="17" /> Add product <x-tabler-chevron-right size="16" class="ml-auto text-slate-400" /></a>@endif
                    @if(Route::has('admin.wear.orders.index'))<a class="kp-quick-action" href="{{ route('admin.wear.orders.index') }}"><x-tabler-file-invoice size="17" /> View orders <x-tabler-chevron-right size="16" class="ml-auto text-slate-400" /></a>@endif
                    @if(Route::has('admin.wear.inventory.index'))<a class="kp-quick-action" href="{{ route('admin.wear.inventory.index') }}"><x-tabler-box size="17" /> Manage inventory <x-tabler-chevron-right size="16" class="ml-auto text-slate-400" /></a>@endif
                    @if(Route::has('admin.wear.enquiries.index'))<a class="kp-quick-action" href="{{ route('admin.wear.enquiries.index') }}"><x-tabler-message-circle-2 size="17" /> View enquiries <x-tabler-chevron-right size="16" class="ml-auto text-slate-400" /></a>@endif
                </div>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
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
        const source = days >= salesData.length ? salesData : salesData.slice(-days);
        const data = source.length ? source : [{ revenue: 0, label: '' }];
        const width = 1000, height = 320, padX = 34, padTop = 22, padBottom = 28;
        const plotHeight = height - padTop - padBottom, plotWidth = width - (padX * 2);
        const max = Math.max(...data.map(item => Number(item.revenue)), 1);
        const step = data.length > 1 ? plotWidth / (data.length - 1) : plotWidth;
        const coords = data.map((item, index) => ({ x: data.length > 1 ? padX + (step * index) : width / 2, y: padTop + plotHeight - ((Number(item.revenue) / max) * plotHeight), revenue: Number(item.revenue), label: item.label }));
        const points = coords.map(point => `${point.x},${point.y}`).join(' ');
        const areaPoints = `${padX},${height - padBottom} ${points} ${coords.at(-1)?.x ?? padX},${height - padBottom}`;
        line.setAttribute('d', `M ${points.replaceAll(' ', ' L ')}`);
        area.setAttribute('d', `M ${areaPoints.replaceAll(' ', ' L ')} Z`);
        grid.innerHTML = [0,1,2,3].map(level => { const y = padTop + ((plotHeight / 3) * level); return `<line x1="${padX}" y1="${y}" x2="${width-padX}" y2="${y}" stroke="currentColor" stroke-opacity="0.08" stroke-width="1" />`; }).join('');
        pointsGroup.innerHTML = coords.map(point => `<circle cx="${point.x}" cy="${point.y}" r="4" fill="currentColor" stroke="white" stroke-width="2" />`).join('');
        const labelIndexes = data.length <= 7 ? data.map((_, index) => index) : [0,5,10,15,20,25,29].filter(index => index < data.length);
        labels.innerHTML = labelIndexes.map(index => `<span class="text-center">${data[index].label}</span>`).join('');
        const revenue = data.reduce((sum, item) => sum + Number(item.revenue), 0);
        total.textContent = `TZS ${Math.round(revenue).toLocaleString()}`;
        empty.classList.toggle('hidden', revenue > 0); empty.classList.toggle('flex', revenue <= 0);
        chart.setAttribute('aria-label', `Sales revenue for the selected period. Total TZS ${Math.round(revenue).toLocaleString()}.`);
    };
    periodButtons.forEach(button => button.addEventListener('click', () => { periodButtons.forEach(item => item.classList.remove('bg-slate-900','text-white','shadow-sm')); periodButtons.forEach(item => item.classList.add('text-slate-500')); button.classList.add('bg-slate-900','text-white','shadow-sm'); button.classList.remove('text-slate-500'); renderSalesChart(Number(button.dataset.chartPeriod)); }));
    renderSalesChart(7);
});
</script>
@endsection
