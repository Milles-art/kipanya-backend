@extends('layouts.app')

@section('content')
<div class="kp-shop-page pt-20 md:pt-24">
    <div class="mx-auto max-w-[1400px] px-4 sm:px-6 lg:px-8">
        <div class="mb-7 flex flex-col gap-5 border-b border-gray-100 pb-7 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs text-gray-400">
                    <a href="{{ route('home') }}" class="transition hover:text-gray-900">Home</a>
                    <span>/</span>
                    <span class="text-gray-600">Shop</span>
                </div>
                <h1 class="text-3xl font-bold tracking-tight text-gray-950 sm:text-4xl">Shop All Products</h1>
                <p data-catalog-count class="mt-2 text-sm text-gray-500">Loading products…</p>
            </div>

            <div class="flex items-center gap-3">
                <button data-catalog-filter-toggle type="button" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium transition hover:bg-gray-50 lg:hidden">
                    <x-tabler-adjustments-horizontal size="17" stroke-width="1.8" />
                    Filters
                    <span data-catalog-filter-count class="hidden h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1.5 text-[11px] font-semibold text-white">0</span>
                </button>
                <div class="relative w-full sm:w-56">
                    <x-tabler-search size="17" stroke-width="1.8" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input data-catalog-search type="search" value="{{ request('q', '') }}" placeholder="Search products…" class="field w-full py-3 pl-11 pr-4">
                </div>
                <div class="relative hidden sm:block">
                    <select data-catalog-sort class="appearance-none rounded-xl border border-gray-200 bg-white py-3 pl-4 pr-10 text-sm font-medium text-gray-900 outline-none transition hover:border-gray-300 focus:border-gray-900">
                        <option value="featured">Sort by: Featured</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="rating">Top Rated</option>
                        <option value="newest">Newest</option>
                    </select>
                    <x-tabler-chevron-down size="16" stroke-width="1.8" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                </div>
            </div>
        </div>

        <div class="flex gap-10 pb-16">
            <aside data-catalog-sidebar class="hidden w-60 shrink-0 lg:block">
                <div class="sticky top-24 space-y-8">
                    <section>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-gray-950">Category</h2>
                            <button data-catalog-clear type="button" class="hidden text-xs font-medium text-emerald-700 transition hover:text-emerald-800">Clear all</button>
                        </div>
                        <div data-catalog-categories class="space-y-1.5"></div>
                    </section>

                    <section>
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-gray-950">Price Range</h2>
                        <div data-catalog-prices class="space-y-1.5">
                            <button type="button" data-price="all" class="kp-filter-option kp-filter-active w-full rounded-lg px-3 py-2.5 text-left text-sm">All Prices</button>
                            <button type="button" data-price="under-50000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">Under 50,000 TZS</button>
                            <button type="button" data-price="50000-150000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">50,000 – 150,000 TZS</button>
                            <button type="button" data-price="over-150000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">Over 150,000 TZS</button>
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-gray-950">Special</h2>
                        <label class="flex cursor-pointer items-center gap-3">
                            <button data-catalog-sale-toggle type="button" class="relative h-6 w-11 rounded-full bg-gray-200 transition-colors" aria-pressed="false">
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span>
                            </button>
                            <span class="text-sm text-gray-700">On Sale Only</span>
                        </label>
                    </section>
                </div>
            </aside>

            <main class="min-w-0 flex-1">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-400">KP Wear collection</p>
                    <div class="hidden items-center gap-2 text-xs text-gray-400 sm:flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Fresh drops, everyday essentials</span>
                    </div>
                </div>

                <div data-catalog-grid class="grid grid-cols-2 gap-x-5 gap-y-11 xl:grid-cols-4"></div>

                <div data-catalog-empty class="hidden flex-col items-center justify-center py-24 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <x-tabler-search size="26" stroke-width="1.8" class="text-gray-400" />
                    </div>
                    <h3 class="mb-1 text-lg font-semibold text-gray-950">No products found</h3>
                    <p class="mb-5 text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                    <button data-catalog-empty-clear type="button" class="button-dark px-5">Clear All Filters</button>
                </div>
            </main>
        </div>
    </div>
</div>

<div data-catalog-mobile-panel class="fixed inset-0 z-[70] hidden bg-gray-950/40 lg:hidden">
    <div data-catalog-mobile-sheet class="absolute bottom-0 left-0 right-0 max-h-[88vh] overflow-y-auto rounded-t-[2rem] bg-white p-6 shadow-2xl">
        <div class="mb-6 flex items-center justify-between">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Shop</p>
                <h3 class="mt-1 text-xl font-semibold">Filters</h3>
            </div>
            <button data-catalog-filter-close type="button" class="rounded-full p-2 text-gray-600 transition hover:bg-gray-100" aria-label="Close filters">
                <x-tabler-x size="20" stroke-width="1.8" />
            </button>
        </div>
        <div data-catalog-mobile-content></div>
    </div>
</div>
@endsection
