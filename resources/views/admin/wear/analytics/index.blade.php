@extends('admin.layouts.app', ['title' => 'Analytics', 'heading' => 'Analytics'])

@section('content')
<div>
    <x-admin.page-header title="Store analytics" subtitle="Track paid sales, order volume and product performance.">
        <x-slot name="action">
            <form method="GET" class="flex items-center gap-2">
                <label for="period" class="sr-only">Period</label>
                <select id="period" name="period" data-submit-on-change class="kp-admin-input w-full sm:w-48" aria-label="Analytics period">
                    @foreach([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                        <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </x-slot>
    </x-admin.page-header>

    {{-- Stat tiles --}}
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 sm:gap-5 xl:grid-cols-4">
        <x-admin.stat label="Revenue" value="TZS {{ number_format($metrics['revenue'], 0) }}" icon="currency-dollar" detail="Paid sales in this period" />
        <x-admin.stat label="Paid orders" value="{{ number_format($metrics['orders']) }}" icon="shopping-cart" tone="sky" detail="Payment completed" />
        <x-admin.stat label="Units sold" value="{{ number_format($metrics['items_sold']) }}" icon="package" tone="violet" detail="Across all paid orders" />
        <x-admin.stat label="Average order" value="TZS {{ number_format($metrics['average_order_value'], 0) }}" icon="trending-up" tone="amber" detail="Revenue per paid order" />
    </div>

    {{-- Sales trend --}}
    <x-admin.card class="mb-6 p-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h3 class="kp-admin-display text-lg">Paid revenue</h3>
                <p class="mt-1.5 text-xs text-slate-400">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</p>
            </div>
            <span class="rounded-full border border-slate-200 bg-slate-50 px-3 py-1 text-xs font-medium text-slate-600">TZS</span>
        </div>

        <div class="mt-6 overflow-hidden">
            <div class="relative h-72">
                <svg id="analytics-chart" viewBox="0 0 1000 320" class="h-full w-full" role="img" aria-label="Paid revenue chart">
                    <g id="analytics-grid" class="text-slate-950"></g>
                    <path id="analytics-area" fill="currentColor" fill-opacity="0.05"></path>
                    <path id="analytics-line" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                    <g id="analytics-points" class="text-emerald-600"></g>
                </svg>
                <div id="analytics-empty" class="pointer-events-none absolute inset-0 hidden items-center justify-center text-sm text-slate-400">No paid sales in this period.</div>
            </div>
            <div id="analytics-labels" class="mt-2 grid grid-cols-7 gap-1 text-[10px] text-slate-400"></div>
        </div>
    </x-admin.card>

    {{-- Product + category performance --}}
    <div class="grid grid-cols-1 gap-5 xl:grid-cols-2">
        <x-admin.card class="overflow-hidden">
            <div class="px-6 pb-4 pt-6">
                <p class="text-xs uppercase tracking-wider text-slate-400">Product performance</p>
                <h3 class="kp-admin-display mt-1.5 text-lg">Top products</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($topProducts as $product)
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $product->product_name }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ number_format((int) $product->units_sold) }} unit(s)</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-slate-800">TZS {{ number_format((float) $product->revenue, 0) }}</p>
                    </div>
                @empty
                    <x-admin.empty message="No paid product sales in this period." />
                @endforelse
            </div>
        </x-admin.card>

        <x-admin.card class="overflow-hidden">
            <div class="px-6 pb-4 pt-6">
                <p class="text-xs uppercase tracking-wider text-slate-400">Merchandising</p>
                <h3 class="kp-admin-display mt-1.5 text-lg">Sales by category</h3>
            </div>
            <div class="divide-y divide-slate-50">
                @forelse($categorySales as $category)
                    <div class="flex items-center justify-between gap-4 px-6 py-4">
                        <div class="min-w-0">
                            <p class="truncate text-sm font-medium text-slate-900">{{ $category->category ?: 'Uncategorised' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ number_format((int) $category->units_sold) }} unit(s)</p>
                        </div>
                        <p class="shrink-0 text-sm font-semibold text-slate-800">TZS {{ number_format((float) $category->revenue, 0) }}</p>
                    </div>
                @empty
                    <x-admin.empty message="No category sales in this period." />
                @endforelse
            </div>
        </x-admin.card>
    </div>
</div>

<script nonce="{{ Vite::cspNonce() }}">
    document.addEventListener('DOMContentLoaded', () => {
        const data = @json($dailySales);
        const width = 1000, height = 320, padX = 34, padTop = 22, padBottom = 28;
        const plotHeight = height - padTop - padBottom;
        const plotWidth = width - (padX * 2);
        const max = Math.max(...data.map(item => Number(item.revenue)), 1);
        const step = data.length > 1 ? plotWidth / (data.length - 1) : plotWidth;
        const coords = data.map((item, index) => ({
            x: data.length > 1 ? padX + step * index : width / 2,
            y: padTop + plotHeight - (Number(item.revenue) / max) * plotHeight,
            revenue: Number(item.revenue),
            label: item.label,
        }));
        const points = coords.map(p => `${p.x},${p.y}`).join(' ');
        const areaPoints = `${padX},${height-padBottom} ${points} ${coords.at(-1)?.x ?? padX},${height-padBottom}`;
        document.getElementById('analytics-line').setAttribute('d', `M ${points.replaceAll(' ', ' L ')}`);
        document.getElementById('analytics-area').setAttribute('d', `M ${areaPoints.replaceAll(' ', ' L ')} Z`);
        document.getElementById('analytics-grid').innerHTML = [0,1,2,3].map(level => {
            const y = padTop + (plotHeight / 3) * level;
            return `<line x1="${padX}" y1="${y}" x2="${width-padX}" y2="${y}" stroke="currentColor" stroke-opacity="0.08" />`;
        }).join('');
        document.getElementById('analytics-points').innerHTML = coords.map(p => `<circle cx="${p.x}" cy="${p.y}" r="4" fill="currentColor" stroke="white" stroke-width="2" />`).join('');
        const indexes = data.length <= 7 ? data.map((_, i) => i) : [0, Math.floor((data.length-1)/2), data.length-1];
        document.getElementById('analytics-labels').innerHTML = indexes.map(i => `<span class="text-center">${data[i].label}</span>`).join('');
        const hasRevenue = data.some(item => Number(item.revenue) > 0);
        document.getElementById('analytics-empty').classList.toggle('hidden', hasRevenue);
        document.getElementById('analytics-empty').classList.toggle('flex', !hasRevenue);
    });
</script>
@endsection
