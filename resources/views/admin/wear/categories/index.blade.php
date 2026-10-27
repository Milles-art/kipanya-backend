@extends('admin.layouts.app', ['title' => 'Categories', 'heading' => 'Categories'])

@section('content')
<div>
    <x-admin.page-header title="Categories" subtitle="The Wear catalog uses five approved product categories. They are kept fixed so storefront filters, product validation, and existing catalog data cannot drift apart." />

    <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-5">
        @foreach($categories as $category)
            <x-admin.card class="p-5">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <h3 class="kp-admin-display text-lg">{{ $category['name'] }}</h3>
                        <p class="mt-1.5 text-xs text-slate-400">Approved Wear category</p>
                    </div>
                    <x-admin.badge status="gray" :label="$category['total'].' products'" />
                </div>

                <div class="mt-6 flex items-end justify-between gap-3">
                    <div>
                        <p class="font-display text-2xl leading-none text-slate-950">{{ $category['active'] }}</p>
                        <p class="mt-1 text-xs uppercase tracking-wider text-slate-400">active</p>
                        <span class="sr-only">{{ $category['active'] }} active</span>
                    </div>
                    <a href="{{ route('admin.wear.products.index', ['category' => $category['name']]) }}" class="kp-admin-link">View products <x-tabler-arrow-right size="13" /></a>
                </div>
            </x-admin.card>
        @endforeach
    </div>

    <div class="rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <p class="font-semibold">Category integrity</p>
        <p class="mt-1">
            New categories are intentionally not created from this screen. The current Wear storefront is defined
            around these five categories, and product creation already validates against the same list.
        </p>
    </div>
</div>
@endsection
