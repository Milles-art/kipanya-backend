@extends('admin.layouts.app', ['title' => 'Products', 'heading' => 'Products'])

@section('content')
<div>
    <x-admin.page-header title="Products" subtitle="{{ $products->total() }} matching products — manage prices, variants, and storefront visibility.">
        <x-slot name="action">
            @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.products.create'))
                <a href="{{ route('admin.wear.products.create') }}" class="kp-admin-primary-button"><x-tabler-plus size="17" stroke-width="2" />Add product</a>
            @endif
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('product'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first('product') }}</div>
    @endif

    {{-- Toolbar: search + filters --}}
    <form method="GET" class="mb-5 flex flex-wrap items-center gap-3">
        <label class="sr-only" for="catalog-search">Search products</label>
        <input id="catalog-search" type="search" name="q" value="{{ $search }}" placeholder="Search products…" class="kp-admin-input w-full sm:w-64" />

        <div class="ml-auto flex flex-wrap items-center gap-3">
            <label class="sr-only" for="catalog-category">Category</label>
            <select id="catalog-category" name="category" class="kp-admin-input w-auto">
                <option value="all">All categories</option>
                @foreach($categories as $item)
                    <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
                @endforeach
            </select>

            <label class="sr-only" for="catalog-status">Status</label>
            <select id="catalog-status" name="status" class="kp-admin-input w-auto">
                <option value="all" @selected($status === 'all')>All status</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>

            <button type="submit" class="kp-admin-secondary-button"><x-tabler-filter size="15" stroke-width="1.8" />Filter</button>

            @if(request()->hasAny(['q', 'category', 'status']))
                <a href="{{ route('admin.wear.products.index') }}" class="kp-admin-link">Clear filters</a>
            @endif
        </div>
    </form>

    {{-- Product table --}}
    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Product</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Category</th>
                        <th class="px-3 py-3.5 text-right font-medium">Price</th>
                        <th class="hidden px-3 py-3.5 text-right font-medium sm:table-cell">Variants</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="px-6 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-10 w-10 shrink-0 rounded-lg bg-slate-100 object-cover" loading="lazy" />
                                    <div class="min-w-0">
                                        <a href="{{ route('admin.wear.products.edit', $product) }}" class="block truncate font-medium text-slate-900 hover:text-emerald-600">{{ $product->name }}</a>
                                        <p class="mt-0.5 truncate text-xs text-slate-400">{{ $product->category }} · {{ $product->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="hidden px-3 py-3.5 text-slate-600 md:table-cell">{{ $product->category }}</td>
                            <td class="px-3 py-3.5 text-right font-medium text-slate-900">TZS {{ number_format((float) $product->price, 0) }}</td>
                            <td class="hidden px-3 py-3.5 text-right text-slate-600 sm:table-cell">{{ $product->variants_count }} {{ \Illuminate\Support\Str::plural('variant', $product->variants_count) }}</td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$product->is_active ? 'active' : 'inactive'" :label="$product->is_active ? 'Active' : 'Inactive'" /></td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.wear.products.variants.index', $product) }}" class="kp-admin-icon-button" aria-label="Manage variants for {{ $product->name }}"><x-tabler-box size="15" stroke-width="1.8" /></a>
                                    <a href="{{ route('admin.wear.products.edit', $product) }}" class="kp-admin-icon-button" aria-label="Edit {{ $product->name }}"><x-tabler-pencil size="15" stroke-width="1.8" /></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty message="No products found. Clear filters or add your first product." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-admin.card>

    @if($products->hasPages())
        <div class="mt-5">{{ $products->links() }}</div>
    @endif
</div>
@endsection
