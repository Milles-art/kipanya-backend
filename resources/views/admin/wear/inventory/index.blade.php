@extends('admin.layouts.app', ['title' => 'Inventory'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Inventory</h2>
            <p class="mt-1 text-sm text-gray-500">Manage variant stock and see units currently reserved by active orders.</p>
        </div>
        <a href="{{ route('admin.wear.products.index') }}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold hover:bg-gray-50">Manage products →</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ $errors->first() }}</div>
    @endif

    <form method="GET" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_auto_auto_auto]">
        <input name="q" value="{{ request('q') }}" placeholder="Search product, SKU, size or color" class="w-full rounded-xl border border-gray-200 px-4 py-2.5 text-sm outline-none focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100">
        <select name="category" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm bg-white">
            <option value="">All categories</option>
            @foreach($categories as $category)
                <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
            @endforeach
        </select>
        <select name="status" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm bg-white">
            <option value="">All stock</option>
            <option value="in" @selected(request('status') === 'in')>In stock</option>
            <option value="low" @selected(request('status') === 'low')>Low stock</option>
            <option value="out" @selected(request('status') === 'out')>Out of stock</option>
        </select>
        <button class="rounded-xl bg-gray-950 px-5 py-2.5 text-sm font-semibold text-white hover:bg-gray-800">Filter</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-[900px] w-full text-left">
                <thead class="border-b border-gray-100 bg-gray-50 text-[11px] font-bold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3">Product</th>
                        <th class="px-4 py-3">SKU</th>
                        <th class="px-4 py-3">Size</th>
                        <th class="px-4 py-3">Color</th>
                        <th class="px-4 py-3">Stock</th>
                        <th class="px-4 py-3">Reserved</th>
                        <th class="px-4 py-3">Available</th>
                        <th class="px-5 py-3 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($variants as $variant)
                        @php
                            $available = (int) $variant->available_quantity;
                            $badge = $available <= 0 ? 'bg-red-50 text-red-700' : ($available <= 5 ? 'bg-amber-50 text-amber-700' : 'bg-emerald-50 text-emerald-700');
                        @endphp
                        <tr class="align-middle hover:bg-gray-50/70">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-gray-950">{{ $variant->product->name }}</div>
                                <div class="text-xs text-gray-400">{{ $variant->product->category }}{{ !$variant->product->is_active ? ' · Inactive' : '' }}</div>
                            </td>
                            <td class="px-4 py-4 font-mono text-xs text-gray-600">{{ $variant->sku }}</td>
                            <td class="px-4 py-4 text-sm">{{ $variant->size }}</td>
                            <td class="px-4 py-4 text-sm">{{ $variant->color }}</td>
                            <td class="px-4 py-4 text-sm font-semibold">{{ (int) $variant->stock }}</td>
                            <td class="px-4 py-4 text-sm text-gray-600">{{ (int) $variant->reserved_quantity }}</td>
                            <td class="px-4 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $badge }}">{{ $available }}</span></td>
                            <td class="px-5 py-4 text-right">
                                <details class="relative inline-block text-left">
                                    <summary class="cursor-pointer list-none rounded-lg border border-gray-200 px-3 py-2 text-xs font-semibold hover:bg-white">Update stock</summary>
                                    <form method="POST" action="{{ route('admin.wear.inventory.stock', $variant) }}" class="absolute right-0 z-10 mt-2 w-72 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-xl">
                                        @csrf
                                        <label class="text-xs font-bold uppercase tracking-wide text-gray-500">New stock</label>
                                        <input type="number" min="0" max="2147483647" name="stock" value="{{ (int) $variant->stock }}" required class="mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <label class="mt-3 block text-xs font-bold uppercase tracking-wide text-gray-500">Reason <span class="font-normal normal-case">(optional)</span></label>
                                        <input name="reason" maxlength="500" placeholder="e.g. Physical count" class="mt-2 w-full rounded-lg border border-gray-200 px-3 py-2 text-sm">
                                        <button class="mt-3 w-full rounded-lg bg-gray-950 px-3 py-2 text-sm font-semibold text-white">Save stock</button>
                                    </form>
                                </details>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-5 py-16 text-center text-sm text-gray-500">No inventory variants match your filters.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variants->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $variants->links() }}</div>
        @endif
    </div>
</div>
<div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm"><div class="border-b border-gray-100 px-5 py-4"><p class="text-xs font-bold uppercase tracking-widest text-gray-400">Recent stock movements</p><h3 class="mt-1 text-lg font-bold">Last 50 adjustments</h3></div><div class="overflow-x-auto"><table class="min-w-full text-left text-sm"><thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400"><tr><th class="px-5 py-3">Variant</th><th class="px-5 py-3">Change</th><th class="px-5 py-3">Stock</th><th class="px-5 py-3">Reason</th><th class="px-5 py-3">Admin</th><th class="px-5 py-3">Date</th></tr></thead><tbody class="divide-y divide-gray-100">@forelse($movements as $movement)<tr><td class="px-5 py-4"><p class="font-semibold">{{ $movement->variant->product->name ?? '—' }}</p><p class="text-xs text-gray-400">{{ $movement->variant->sku ?? '' }}</p></td><td class="px-5 py-4 font-bold">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td><td class="px-5 py-4">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td><td class="px-5 py-4">{{ ucwords(str_replace('_',' ',$movement->reason)) }}</td><td class="px-5 py-4">{{ $movement->creator->name ?? 'System' }}</td><td class="px-5 py-4 text-gray-500">{{ $movement->created_at->format('d M H:i') }}</td></tr>@empty<tr><td colspan="6" class="px-5 py-10 text-center text-gray-500">No stock movements yet.</td></tr>@endforelse</tbody></table></div></div>
@endsection
