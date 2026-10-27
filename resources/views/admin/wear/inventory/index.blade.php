@extends('admin.layouts.app', ['title' => 'Inventory', 'heading' => 'Inventory'])

@section('content')
<div>
    <x-admin.page-header title="Inventory" subtitle="Manage variant stock and see units currently reserved by active orders.">
        <x-slot name="action">
            <a href="{{ route('admin.wear.products.index') }}" class="kp-admin-secondary-button"><x-tabler-package size="15" stroke-width="1.8" />Manage products</a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">{{ $errors->first() }}</div>
    @endif

    {{-- Toolbar --}}
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-3">
        <label class="sr-only" for="inventory-search">Search inventory</label>
        <input id="inventory-search" type="search" name="q" value="{{ request('q') }}" placeholder="Search product, SKU, size or color…" class="kp-admin-input w-full sm:w-64" />

        <div class="ml-auto flex flex-wrap items-center gap-3">
            <label class="sr-only" for="inventory-category">Category</label>
            <select id="inventory-category" name="category" class="kp-admin-input w-auto">
                <option value="">All categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="inventory-status">Stock status</label>
            <select id="inventory-status" name="status" class="kp-admin-input w-auto">
                <option value="">All stock</option>
                <option value="in" @selected(request('status') === 'in')>In stock</option>
                <option value="low" @selected(request('status') === 'low')>Low stock</option>
                <option value="out" @selected(request('status') === 'out')>Out of stock</option>
            </select>

            <button type="submit" class="kp-admin-secondary-button"><x-tabler-filter size="15" stroke-width="1.8" />Filter</button>
        </div>
    </form>

    {{-- Variant stock table --}}
    <x-admin.card class="mb-6 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Product</th>
                        <th class="px-3 py-3.5 text-left font-medium">SKU</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Size</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Color</th>
                        <th class="px-3 py-3.5 text-right font-medium">Stock</th>
                        <th class="hidden px-3 py-3.5 text-right font-medium md:table-cell">Reserved</th>
                        <th class="px-3 py-3.5 text-right font-medium">Available</th>
                        <th class="px-6 py-3.5 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $variant)
                        @php
                            $available = (int) $variant->available_quantity;
                        @endphp
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <p class="truncate font-medium text-slate-900">{{ $variant->product->name }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-400">{{ $variant->product->category }}{{ !$variant->product->is_active ? ' · Inactive' : '' }}</p>
                            </td>
                            <td class="px-3 py-3.5 font-mono text-xs text-slate-600">{{ $variant->sku }}</td>
                            <td class="hidden px-3 py-3.5 text-slate-600 sm:table-cell">{{ $variant->size }}</td>
                            <td class="hidden px-3 py-3.5 text-slate-600 sm:table-cell">{{ $variant->color }}</td>
                            <td class="px-3 py-3.5 text-right font-medium text-slate-900">{{ (int) $variant->stock }}</td>
                            <td class="hidden px-3 py-3.5 text-right text-slate-600 md:table-cell">{{ (int) $variant->reserved_quantity }}</td>
                            <td class="px-3 py-3.5 text-right">
                                <x-admin.badge :status="$available <= 0 ? 'out' : ($available <= 5 ? 'low' : 'green')" :label="(string) $available" />
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end">
                                    <details class="relative inline-block text-left">
                                        <summary class="kp-admin-secondary-button cursor-pointer list-none">Update stock</summary>
                                        <form method="POST" action="{{ route('admin.wear.inventory.stock', $variant) }}" class="absolute right-0 z-10 mt-2 w-72 rounded-2xl border border-slate-200 bg-white p-4 text-left shadow-xl">
                                            @csrf
                                            <label class="block text-xs uppercase tracking-wider text-slate-400" for="stock-{{ $variant->id }}">New stock</label>
                                            <input id="stock-{{ $variant->id }}" type="number" min="0" max="2147483647" name="stock" value="{{ (int) $variant->stock }}" required class="field mt-2">
                                            <label class="mt-3 block text-xs uppercase tracking-wider text-slate-400" for="reason-{{ $variant->id }}">Reason <span class="normal-case font-normal text-slate-400">(optional)</span></label>
                                            <input id="reason-{{ $variant->id }}" name="reason" maxlength="500" placeholder="e.g. Physical count" class="field mt-2">
                                            <button type="submit" class="kp-admin-primary-button mt-3 w-full">Save stock</button>
                                        </form>
                                    </details>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8"><x-admin.empty message="No inventory variants match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variants->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $variants->links() }}</div>
        @endif
    </x-admin.card>

    {{-- Recent stock movements --}}
    <x-admin.card class="overflow-hidden">
        <div class="border-b border-slate-100 px-6 pb-4 pt-6">
            <h3 class="kp-admin-display text-lg">Recent Stock Movements</h3>
            <p class="mt-1.5 text-xs text-slate-400">Last 50 adjustments</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Variant</th>
                        <th class="px-3 py-3.5 text-left font-medium">Change</th>
                        <th class="px-3 py-3.5 text-left font-medium">Stock</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Reason</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Admin</th>
                        <th class="px-6 py-3.5 text-right font-medium">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($movements as $movement)
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <p class="truncate font-medium text-slate-900">{{ $movement->variant->product->name ?? '—' }}</p>
                                <p class="mt-0.5 truncate text-xs text-slate-400">{{ $movement->variant->sku ?? '' }}</p>
                            </td>
                            <td class="px-3 py-3.5 font-medium {{ $movement->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                            <td class="px-3 py-3.5 text-slate-600">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                            <td class="hidden px-3 py-3.5 text-slate-600 md:table-cell">{{ ucwords(str_replace('_', ' ', $movement->reason)) }}</td>
                            <td class="hidden px-3 py-3.5 text-slate-600 sm:table-cell">{{ $movement->creator->name ?? 'System' }}</td>
                            <td class="px-6 py-3.5 text-right text-slate-500">{{ $movement->created_at->format('d M H:i') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty message="No stock movements yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>
</div>
@endsection
