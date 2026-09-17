@extends('admin.layouts.app', ['title' => 'Analytics', 'heading' => 'Analytics'])

@section('content')
<div class="space-y-6">
    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Store analytics</h2>
            <p class="mt-2 text-sm leading-6 text-gray-500">Track paid sales, order volume and product performance.</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <label for="period" class="sr-only">Period</label>
            <select id="period" name="period" onchange="this.form.submit()" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-semibold outline-none focus:border-gray-400">
                @foreach([7 => 'Last 7 days', 30 => 'Last 30 days', 90 => 'Last 90 days'] as $value => $label)
                    <option value="{{ $value }}" @selected($period === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </form>
    </section>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @foreach([
            ['Revenue', 'TZS '.number_format($metrics['revenue'], 0)],
            ['Paid orders', number_format($metrics['orders'])],
            ['Units sold', number_format($metrics['items_sold'])],
            ['Average order', 'TZS '.number_format($metrics['average_order_value'], 0)],
        ] as [$label, $value])
            <div class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <p class="text-sm text-gray-500">{{ $label }}</p>
                <p class="mt-2 text-2xl font-black tracking-tight">{{ $value }}</p>
            </div>
        @endforeach
    </section>

    <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Sales trend</p>
                <h3 class="mt-1 text-lg font-bold">Paid revenue</h3>
            </div>
            <span class="text-xs font-semibold text-gray-400">{{ $start->format('d M Y') }} — {{ $end->format('d M Y') }}</span>
        </div>
        <div class="mt-6 overflow-hidden">
            <div class="relative h-72">
                <svg id="analytics-chart" viewBox="0 0 1000 320" class="h-full w-full" role="img" aria-label="Paid revenue chart">
                    <g id="analytics-grid" class="text-gray-950"></g>
                    <path id="analytics-area" fill="currentColor" fill-opacity="0.05"></path>
                    <path id="analytics-line" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                    <g id="analytics-points" class="text-emerald-600"></g>
                </svg>
                <div id="analytics-empty" class="pointer-events-none absolute inset-0 hidden items-center justify-center text-sm font-medium text-gray-400">No paid sales in this period.</div>
            </div>
            <div id="analytics-labels" class="mt-2 grid grid-cols-7 gap-1 text-[10px] text-gray-400"></div>
        </div>
    </section>

    <section class="grid gap-6 xl:grid-cols-2">
        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Product performance</p>
                <h3 class="mt-1 text-lg font-bold">Top products</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($topProducts as $product)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div class="min-w-0">
                            <p class="truncate font-semibold">{{ $product->product_name }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ number_format((int) $product->units_sold) }} unit(s)</p>
                        </div>
                        <p class="shrink-0 font-bold">TZS {{ number_format((float) $product->revenue, 0) }}</p>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-500">No paid product sales in this period.</div>
                @endforelse
            </div>
        </div>

        <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
            <div class="border-b border-gray-100 px-5 py-4">
                <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Merchandising</p>
                <h3 class="mt-1 text-lg font-bold">Sales by category</h3>
            </div>
            <div class="divide-y divide-gray-100">
                @forelse($categorySales as $category)
                    <div class="flex items-center justify-between gap-4 px-5 py-4">
                        <div>
                            <p class="font-semibold">{{ $category->category ?: 'Uncategorised' }}</p>
                            <p class="mt-1 text-xs text-gray-400">{{ number_format((int) $category->units_sold) }} unit(s)</p>
                        </div>
                        <p class="font-bold">TZS {{ number_format((float) $category->revenue, 0) }}</p>
                    </div>
                @empty
                    <div class="px-5 py-12 text-center text-sm text-gray-500">No category sales in this period.</div>
                @endforelse
            </div>
        </div>
    </section>
</div>

<script>
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
