@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
        <h2 class="mt-1 text-2xl font-black tracking-tight">Categories</h2>
        <p class="mt-1 max-w-2xl text-sm text-black">
            The Wear catalog uses five approved product categories. They are kept fixed so storefront filters,
            product validation, and existing catalog data cannot drift apart.
        </p>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($categories as $category)
            <article class="rounded-2xl border border-black bg-white p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-base font-black">{{ $category['name'] }}</p>
                        <p class="mt-1 text-xs text-black">Approved Wear category</p>
                    </div>
                    <span class="rounded-full bg-white px-2.5 py-1 text-xs font-bold text-black">
                        {{ $category['total'] }} products
                    </span>
                </div>

                <div class="mt-6 flex items-end justify-between">
                    <div>
                        <p class="text-2xl font-black">{{ $category['active'] }}</p>
                        <p class="text-xs text-black">active</p>
                        <span class="sr-only">{{ $category['active'] }} active</span>
                    </div>
                    <a
                        href="{{ route('admin.wear.products.index', ['category' => $category['name']]) }}"
                        class="text-sm font-bold text-black hover:text-emerald-700"
                    >
                        View products →
                    </a>
                </div>
            </article>
        @endforeach
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <p class="font-bold">Category integrity</p>
        <p class="mt-1">
            New categories are intentionally not created from this screen. The current Wear storefront is defined
            around these five categories, and product creation already validates against the same list.
        </p>
    </div>
</div>
@endsection
