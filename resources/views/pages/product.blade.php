@extends('layouts.app')
@section('content')
<div data-product-page data-slug="{{ $slug ?? request()->route('slug') }}" class="mx-auto w-full max-w-[1600px] px-4 pb-16 pt-8 sm:px-6 lg:px-8 xl:px-10">
    <div class="mb-8 flex items-center gap-2 text-sm text-gray-500">
        <a href="{{ route('shop') }}" class="hover:text-emerald-600">Shop</a>
        <x-tabler-chevron-right size="14" class="text-gray-300" />
        <span class="text-gray-400">Product</span>
    </div>

    <div data-product-loading class="flex flex-col items-center gap-3 py-24 text-center text-sm text-gray-500">
        <span class="h-6 w-6 animate-spin rounded-full border-2 border-gray-200 border-t-gray-900"></span>
        Loading product…
    </div>

    <div data-product-content class="hidden grid gap-10 lg:grid-cols-[minmax(0,1.05fr)_minmax(360px,.95fr)] lg:gap-16">
        <div>
            <div class="aspect-square overflow-hidden rounded-2xl bg-gray-50">
                <img data-product-image src="" class="h-full w-full object-contain p-6 sm:p-10" alt="Product">
            </div>
            <div class="mt-4 grid grid-cols-4 gap-3" data-product-gallery></div>
        </div>

        <div class="lg:py-4">
            <p data-product-category class="text-sm text-gray-500"></p>
            <h1 data-product-name class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl"></h1>

            <div class="mt-5 flex flex-wrap items-center gap-3">
                <span data-product-price class="text-2xl font-semibold text-gray-950"></span>
                <span data-product-compare class="hidden text-lg text-gray-400 line-through"></span>
                <span data-product-badge class="hidden rounded-full bg-gray-950 px-3 py-1 text-xs font-medium text-white"></span>
            </div>

            <p data-product-description class="mt-6 whitespace-pre-line leading-7 text-gray-600"></p>
            <p data-product-stock class="mt-5 flex items-center gap-1.5 text-sm font-medium"></p>

            <div class="mt-8">
                <div class="mb-3 flex items-center justify-between">
                    <p class="text-sm font-medium text-gray-950">Size / colour</p>
                    <p data-selected-variant class="text-xs text-gray-500"></p>
                </div>
                <div data-product-variants class="flex flex-wrap gap-2"></div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <div class="flex h-12 w-full items-center rounded-xl border border-gray-200 sm:w-auto">
                    <button type="button" data-quantity-minus class="h-full w-11 text-lg" aria-label="Decrease quantity">−</button>
                    <input data-quantity type="number" min="1" max="50" value="1" class="h-full w-12 border-0 bg-transparent p-0 text-center text-sm focus:ring-0" aria-label="Quantity">
                    <button type="button" data-quantity-plus class="h-full w-11 text-lg" aria-label="Increase quantity">+</button>
                </div>
                <button data-add-selected type="button" class="button-dark h-12 flex-1">Add to bag</button>
                <button data-product-wishlist type="button" class="flex h-12 items-center gap-1.5 rounded-xl border border-gray-200 px-5 text-sm font-medium hover:bg-gray-50">
                    <x-tabler-heart size="16" />
                    Wishlist
                </button>
            </div>

            <p data-product-error class="mt-5 hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"></p>

            <div class="mt-8 grid gap-3 border-t border-gray-100 pt-6 sm:grid-cols-2">
                <div class="flex items-start gap-2.5 text-sm text-gray-600">
                    <x-tabler-truck size="17" class="mt-0.5 flex-shrink-0 text-gray-400" />
                    Delivery calculated at checkout
                </div>
                <div class="flex items-start gap-2.5 text-sm text-gray-600">
                    <x-tabler-rotate size="17" class="mt-0.5 flex-shrink-0 text-gray-400" />
                    Easy exchange within 7 days
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
