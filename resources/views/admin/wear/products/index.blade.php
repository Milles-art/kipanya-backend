@extends('admin.layouts.app', ['title' => 'Products', 'heading' => 'Products'])

@section('content')
<div class="kp-catalog-admin mx-auto max-w-[1180px] space-y-5">
    <header class="flex flex-col gap-4 border-b border-stone-200 pb-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="kp-admin-eyebrow">Catalog management</p>
            <h2 class="kp-admin-display mt-1.5 text-3xl text-stone-950">Products</h2>
            <p class="mt-1.5 text-sm leading-6 text-stone-500">Manage the collection your customers see, from details to availability.</p>
        </div>
        <a href="{{ route('admin.wear.products.create') }}" class="kp-catalog-add"><x-tabler-plus size="18" /> Add product</a>
    </header>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('product'))
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $errors->first('product') }}</div>
    @endif

    <form method="GET" class="kp-catalog-filter flex flex-wrap items-center gap-3 rounded-2xl border p-4">
        <label class="sr-only" for="catalog-search">Search products</label>
        <div class="relative min-w-0 flex-1 sm:max-w-sm">
            <x-tabler-search size="19" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-stone-400" />
            <input id="catalog-search" class="field w-full pl-11" name="q" value="{{ $search }}" placeholder="Search the catalog">
        </div>
        <select class="field w-44" name="category" aria-label="Filter by category">
            <option value="all">All categories</option>
            @foreach($categories as $item)
                <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
            @endforeach
        </select>
        <select class="field w-44" name="status" aria-label="Filter by status">
            <option value="all" @selected($status === 'all')">All status</option>
            <option value="active" @selected($status === 'active')">Active</option>
            <option value="inactive" @selected($status === 'inactive')">Inactive</option>
        </select>
        <button class="kp-catalog-filter-button" type="submit">Apply filters</button>
    </form>

    <div class="flex items-center justify-between gap-4">
        <p class="text-sm text-stone-500"><span class="font-semibold text-stone-900">{{ $products->total() }}</span> products in this view</p>
        @if(request()->hasAny(['q', 'category', 'status']))
            <a href="{{ route('admin.wear.products.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">Clear filters</a>
        @endif
    </div>

    @forelse($products as $product)
        @if($loop->first)<div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">@endif
            <article class="kp-catalog-product-card group">
                <div class="relative aspect-[4/3] overflow-hidden bg-stone-100">
                    <img src="{{ $product->image_url }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.035]">
                    <span class="absolute left-3 top-3 rounded-full px-3 py-1 text-[11px] font-bold {{ $product->is_active ? 'bg-[#e9f8f1] text-emerald-700' : 'bg-stone-100 text-stone-600' }}">{{ $product->is_active ? 'Active' : 'Inactive' }}</span>
                    <a href="{{ route('admin.wear.products.edit', $product) }}" class="absolute right-3 top-3 flex h-9 w-9 items-center justify-center rounded-full bg-white/95 text-stone-900 opacity-0 shadow-sm transition hover:bg-emerald-50 hover:text-emerald-700 group-hover:opacity-100" aria-label="Edit {{ $product->name }}"><x-tabler-pencil size="17" /></a>
                </div>
                <div class="p-4">
                    <p class="text-[11px] font-semibold uppercase tracking-[.13em] text-emerald-700">{{ $product->category }}</p>
                    <div class="mt-1 flex items-baseline justify-between gap-3">
                        <h3 class="min-w-0 text-base font-bold leading-snug text-stone-950">{{ $product->name }}</h3>
                        <p class="shrink-0 text-sm font-bold text-stone-950">TZS {{ number_format((float) $product->price, 0) }}</p>
                    </div>
                    <p class="mt-1 truncate text-xs text-stone-400">{{ $product->slug }}</p>
                    <div class="mt-4 flex items-center justify-between border-t border-stone-100 pt-3">
                        <span class="inline-flex items-center gap-1.5 text-xs text-stone-500"><x-tabler-stack-2 size="15" class="text-emerald-700" /> {{ $product->variants_count }} {{ \Illuminate\Support\Str::plural('variant', $product->variants_count) }}</span>
                        <a href="{{ route('admin.wear.products.edit', $product) }}" class="inline-flex items-center gap-1 text-xs font-bold text-stone-900 transition hover:text-emerald-700">Manage <x-tabler-arrow-up-right size="15" /></a>
                    </div>
                </div>
            </article>
        @if($loop->last)</div>@endif
    @empty
        <section class="rounded-2xl border border-dashed border-stone-300 bg-white/70 px-6 py-16 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-emerald-700"><x-tabler-package size="23" /></span>
            <h3 class="mt-4 text-lg font-bold text-stone-950">No products found.</h3>
            <p class="mt-1 text-sm text-stone-500">Try a different search, or add a new product to begin.</p>
        </section>
    @endforelse

    @if($products->hasPages())
        <div class="border-t border-stone-200 pt-5">{{ $products->links() }}</div>
    @endif
</div>
@endsection
