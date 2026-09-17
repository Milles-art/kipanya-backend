@extends('layouts.app')

@push('head')
<style>
  .kp-reveal {
    opacity: 0;
    transform: translateY(30px);
    transition: opacity 600ms cubic-bezier(0.4,0,0.2,1),
                transform 600ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-reveal.is-visible { opacity: 1; transform: translateY(0); }

  .kp-filter-option {
    color: #374151;
    transition: background 200ms ease, color 200ms ease, padding-left 200ms ease;
  }
  .kp-filter-option:hover { background: #f3f4f6; padding-left: 1rem; }
  .kp-filter-active {
    background: #ecfdf5;
    color: #047857;
    font-weight: 600;
    padding-left: 1rem;
  }
  .kp-filter-active:hover { background: #d1fae5; }

  .kp-product-card {
    transition: transform 400ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-product-card:hover { transform: translateY(-6px); }
  .kp-product-card .kp-product-img { transition: transform 600ms cubic-bezier(0.4,0,0.2,1); }
  .kp-product-card:hover .kp-product-img { transform: scale(1.06); }

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-filter-option, .kp-product-card, .kp-product-card .kp-product-img { transition: none !important; }
  }
</style>
@endpush

@section('content')
<div class="kp-shop-page pt-8 md:pt-10">
    <div class="mx-auto w-full px-4 sm:px-6 lg:px-8 xl:px-10">

        {{-- ── Admin-controlled shop banner ───────────────────────────── --}}
        <div data-storefront-shop-banner class="mb-7 hidden overflow-hidden rounded-[1.5rem] bg-gray-950 text-white">
          <div data-storefront-shop-banner-inner class="relative min-h-[220px]">
            <img data-storefront-shop-banner-image class="absolute inset-0 h-full w-full object-cover opacity-70" alt="" loading="eager">
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/45 to-black/10"></div>
            <div class="relative max-w-2xl px-7 py-10 sm:px-10 sm:py-12">
              <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-400">KP Wear</p>
              <h2 data-storefront-shop-banner-title class="mt-3 text-3xl font-black tracking-tight sm:text-4xl"></h2>
              <p data-storefront-shop-banner-description class="mt-3 max-w-xl text-sm leading-6 text-white/75"></p>
            </div>
          </div>
        </div>

        {{-- ── Header bar ────────────────────────────────────────────── --}}
        <div class="kp-reveal mb-7 flex flex-col gap-5 border-b border-gray-100 pb-7 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <div class="mb-3 flex items-center gap-2 text-xs text-gray-400">
                    <a href="{{ route('home') }}" class="transition hover:text-gray-900">Home</a>
                    <span aria-hidden="true">/</span>
                    <span class="text-gray-600">Shop</span>
                </div>
                <h1 class="text-3xl font-black tracking-[-0.03em] text-gray-950 sm:text-4xl">Shop All Products</h1>
                <p data-catalog-count class="mt-2 min-h-5 text-sm text-gray-500"></p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <button data-catalog-filter-toggle type="button" aria-label="Open filters" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-medium transition hover:border-gray-300 hover:bg-gray-50 lg:hidden">
                    <x-tabler-adjustments-horizontal size="17" stroke-width="1.8" />
                    Filters
                    <span data-catalog-filter-count class="hidden h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1.5 text-[11px] font-semibold text-white">0</span>
                </button>

                <div class="relative w-full sm:w-56">
                    <label for="kp-catalog-search" class="sr-only">Search products</label>
                    <x-tabler-search size="17" stroke-width="1.8" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-gray-400" />
                    <input id="kp-catalog-search" data-catalog-search type="search" value="{{ request('q', '') }}" placeholder="Search products…" autocomplete="off" class="field w-full py-3 pl-11 pr-10 transition focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200">
                    <button data-catalog-search-clear type="button" class="absolute right-3 top-1/2 hidden -translate-y-1/2 text-gray-400 transition hover:text-gray-700" aria-label="Clear search">
                        <x-tabler-x size="16" stroke-width="1.8" />
                    </button>
                </div>

                <div class="relative w-full sm:w-auto">
                    <label for="kp-catalog-sort" class="sr-only">Sort products</label>
                    <select id="kp-catalog-sort" data-catalog-sort class="w-full appearance-none rounded-xl border border-gray-200 bg-white py-3 pl-4 pr-10 text-sm font-medium text-gray-900 outline-none transition hover:border-gray-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200 sm:w-auto">
                        <option value="featured">Sort by: Featured</option>
                        <option value="price-asc">Price: Low to High</option>
                        <option value="price-desc">Price: High to Low</option>
                        <option value="newest">Newest</option>
                    </select>
                    <x-tabler-chevron-down size="16" stroke-width="1.8" class="pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-gray-400" />
                </div>
            </div>
        </div>

        <div class="flex gap-8 pb-16 xl:gap-12">

            {{-- ── Sidebar ───────────────────────────────────────────────── --}}
            <aside data-catalog-sidebar class="hidden w-56 shrink-0 lg:block xl:w-64">
                <div class="sticky top-24 space-y-8">
                    <section>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-gray-950">Category</h2>
                            <button data-catalog-clear type="button" class="hidden text-xs font-medium text-emerald-700 transition hover:text-emerald-800">Clear all</button>
                        </div>
                        <div data-catalog-categories class="space-y-1.5">
                            @for($i = 0; $i < 5; $i++)
                                <div class="h-9 animate-pulse rounded-lg bg-gray-100"></div>
                            @endfor
                        </div>
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
                        <button data-catalog-sale-toggle type="button" aria-pressed="false" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left transition hover:bg-gray-50">
                            <span class="text-sm text-gray-700">On Sale Only</span>
                            <span class="relative h-6 w-11 flex-shrink-0 rounded-full bg-gray-200 transition-colors">
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span>
                            </span>
                        </button>
                    </section>

                </div>
            </aside>

            {{-- ── Product grid ──────────────────────────────────────────── --}}
            <main class="min-w-0 flex-1">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <p class="text-sm text-gray-400">KP Wear collection</p>
                    <div class="hidden items-center gap-2 text-xs text-gray-400 sm:flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Fresh drops, everyday essentials</span>
                    </div>
                </div>

                <div data-catalog-grid class="grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-8" aria-live="polite" aria-busy="true">
                    @for($i = 0; $i < 8; $i++)
                        <div class="animate-pulse">
                            <div class="aspect-[3/4] rounded-2xl bg-gray-100"></div>
                            <div class="mt-3 h-3 w-3/4 rounded bg-gray-100"></div>
                            <div class="mt-2 h-3 w-1/3 rounded bg-gray-100"></div>
                        </div>
                    @endfor
                </div>

                <div data-catalog-empty class="hidden flex-col items-center justify-center py-24 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-gray-100">
                        <x-tabler-search size="26" stroke-width="1.8" class="text-gray-400" />
                    </div>
                    <h3 class="mb-1 text-lg font-semibold text-gray-950">No products found</h3>
                    <p class="mb-5 text-sm text-gray-500">Try adjusting your filters or search terms.</p>
                    <button data-catalog-empty-clear type="button" class="button-dark px-5">Clear All Filters</button>
                </div>

                <div data-catalog-pagination aria-live="polite"></div>
            </main>
        </div>
    </div>
</div>

{{-- ── Mobile filter panel ─────────────────────────────────────────── --}}
<div data-catalog-mobile-panel class="fixed inset-0 z-[70] hidden bg-gray-950/40 lg:hidden" role="dialog" aria-modal="true" aria-label="Filter products">
    <div data-catalog-mobile-sheet class="absolute bottom-0 left-0 right-0 flex max-h-[88vh] flex-col overflow-hidden rounded-t-[2rem] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-gray-100 p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Shop</p>
                <h3 class="mt-1 text-xl font-semibold">Filters</h3>
            </div>
            <button data-catalog-filter-close type="button" class="rounded-full p-2 text-gray-600 transition hover:bg-gray-100" aria-label="Close filters">
                <x-tabler-x size="20" stroke-width="1.8" />
            </button>
        </div>

        <div data-catalog-mobile-content class="flex-1 overflow-y-auto p-6"></div>

        <div class="flex items-center gap-3 border-t border-gray-100 p-4">
            <button data-catalog-mobile-clear type="button" class="flex-1 rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 transition hover:bg-gray-50">
                Clear all
            </button>
            <button data-catalog-mobile-apply type="button" class="button-dark flex-1 py-3">
                Show results
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
  const reveals = document.querySelectorAll('.kp-reveal');
  if (!('IntersectionObserver' in window)) {
    reveals.forEach(el => el.classList.add('is-visible'));
    return;
  }
  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12 });
  reveals.forEach(el => obs.observe(el));
})();
</script>
@endpush
