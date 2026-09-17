@extends('layouts.app')
@section('content')
<div data-product-page data-slug="{{ $slug ?? request()->route('slug') }}" class="mx-auto w-full max-w-[1500px] px-4 pb-20 pt-6 sm:px-6 sm:pt-8 lg:px-8 xl:px-10">
    <div class="mb-6 flex items-center gap-2 text-xs text-gray-500 sm:mb-8 sm:text-sm">
        <a href="{{ route('shop') }}" class="transition hover:text-gray-950">Shop</a>
        <x-tabler-chevron-right size="14" class="text-gray-300" />
        <span class="text-gray-400">Product</span>
    </div>

    <div
        data-product-loading
        class="grid gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,.92fr)] lg:gap-14 xl:gap-20"
        aria-live="polite"
        aria-busy="true"
    >
        <div class="animate-pulse">
            <div class="aspect-[4/5] overflow-hidden rounded-2xl bg-gray-100 sm:aspect-square"></div>
            <div class="mt-3 grid max-w-[420px] grid-cols-4 gap-2.5 sm:mt-4">
                <div class="aspect-square rounded-xl bg-gray-100"></div>
                <div class="aspect-square rounded-xl bg-gray-100"></div>
                <div class="aspect-square rounded-xl bg-gray-100"></div>
                <div class="aspect-square rounded-xl bg-gray-100"></div>
            </div>
        </div>

        <div class="animate-pulse lg:py-2 xl:py-6">
            <div class="h-3 w-24 rounded bg-gray-100"></div>
            <div class="mt-3 h-10 w-4/5 rounded-lg bg-gray-100 sm:h-12"></div>

            <div class="mt-5 flex items-center gap-3">
                <div class="h-8 w-32 rounded-lg bg-gray-100"></div>
                <div class="h-6 w-24 rounded-lg bg-gray-100"></div>
            </div>

            <div class="mt-6 space-y-2">
                <div class="h-4 w-full rounded bg-gray-100"></div>
                <div class="h-4 w-11/12 rounded bg-gray-100"></div>
                <div class="h-4 w-3/4 rounded bg-gray-100"></div>
            </div>

            <div class="mt-5 h-4 w-28 rounded bg-gray-100"></div>

            <div class="mt-8">
                <div class="mb-3 h-4 w-24 rounded bg-gray-100"></div>
                <div class="flex flex-wrap gap-2">
                    <div class="h-10 w-20 rounded-lg bg-gray-100"></div>
                    <div class="h-10 w-24 rounded-lg bg-gray-100"></div>
                    <div class="h-10 w-20 rounded-lg bg-gray-100"></div>
                </div>
            </div>

            <div class="mt-8 flex flex-col gap-3 sm:flex-row">
                <div class="h-12 w-full rounded-xl bg-gray-100 sm:w-36"></div>
                <div class="h-12 flex-1 rounded-xl bg-gray-100"></div>
                <div class="h-12 w-full rounded-xl bg-gray-100 sm:w-32"></div>
            </div>

            <div class="mt-8 grid gap-3 border-t border-gray-100 pt-6 sm:grid-cols-2">
                <div class="h-4 w-40 rounded bg-gray-100"></div>
                <div class="h-4 w-40 rounded bg-gray-100"></div>
            </div>
        </div>
    </div>

    <div
        data-product-load-error
        class="hidden rounded-2xl border border-gray-100 bg-gray-50 px-6 py-12 text-center"
        role="alert"
    >
        <p class="text-base font-semibold text-gray-950">We couldn't load this product.</p>
        <p data-product-load-error-message class="mt-2 text-sm text-gray-500"></p>
        <button
            type="button"
            data-product-retry
            class="mt-5 rounded-xl bg-gray-950 px-5 py-3 text-sm font-medium text-white transition hover:opacity-90"
        >
            Try again
        </button>
    </div>

    <div data-product-content class="hidden grid gap-8 lg:grid-cols-[minmax(0,1.08fr)_minmax(360px,.92fr)] lg:gap-14 xl:gap-20">
        <div>
            <div class="relative overflow-hidden rounded-2xl bg-gray-50 aspect-[4/5] sm:aspect-square">
                <img data-product-image src="" class="h-full w-full object-contain p-5 sm:p-10 lg:p-14" alt="Product">
            </div>
            <div class="mt-3 grid max-w-[420px] grid-cols-4 gap-2.5 sm:mt-4" data-product-gallery></div>
        </div>

        <div class="lg:py-2 xl:py-6">
            <p data-product-category class="text-xs font-medium uppercase tracking-[0.14em] text-gray-500 sm:text-sm"></p>
            <h1 data-product-name class="mt-2 text-3xl font-semibold tracking-[-0.025em] text-gray-950 sm:text-4xl lg:text-[2.7rem]"></h1>

            <div class="mt-4 flex flex-wrap items-center gap-3 sm:mt-5">
                <span data-product-price class="text-2xl font-semibold text-gray-950"></span>
                <span data-product-compare class="hidden text-lg text-gray-400 line-through"></span>
                <span data-product-badge class="hidden rounded-full bg-gray-950 px-3 py-1 text-xs font-medium text-white"></span>
            </div>

            <p data-product-description class="mt-5 max-w-2xl whitespace-pre-line text-sm leading-7 text-gray-600 sm:mt-6 sm:text-base"></p>
            <p data-product-stock class="mt-4 flex items-center gap-1.5 text-sm font-medium"></p>

            <div class="mt-7 sm:mt-8">
                <div class="mb-3 flex items-center justify-between gap-4">
                    <p class="text-sm font-medium text-gray-950">Size / colour</p>
                    <p data-selected-variant class="text-xs text-gray-500"></p>
                </div>
                <div data-product-variants class="flex flex-wrap gap-2"></div>
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:mt-8 sm:flex-row">
                <div class="flex h-12 w-full items-center rounded-xl border border-gray-200 sm:w-auto">
                    <button type="button" data-quantity-minus class="h-full w-11 text-lg" aria-label="Decrease quantity">−</button>
                    <input data-quantity type="number" min="1" max="50" value="1" class="h-full w-12 border-0 bg-transparent p-0 text-center text-sm focus:ring-0" aria-label="Quantity">
                    <button type="button" data-quantity-plus class="h-full w-11 text-lg" aria-label="Increase quantity">+</button>
                </div>
                <button data-add-selected type="button" class="button-dark h-12 flex-1 transition hover:opacity-90 disabled:cursor-not-allowed disabled:opacity-40">Add to bag</button>
                <button data-product-wishlist type="button" class="flex h-12 items-center justify-center gap-1.5 rounded-xl border border-gray-200 px-5 text-sm font-medium transition hover:border-gray-300 hover:bg-gray-50 sm:min-w-[126px]">
                    <x-tabler-heart size="16" />
                    Wishlist
                </button>
            </div>

            <p data-product-error class="mt-5 hidden rounded-xl bg-red-50 px-4 py-3 text-sm text-red-700"></p>

            <div class="mt-7 grid gap-3 border-t border-gray-100 pt-6 sm:mt-8 sm:grid-cols-2">
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
