@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">

  .kp-filter-option {
    color: var(--kp-ink-secondary);
    transition: background-color 200ms ease, color 200ms ease, padding-left 200ms ease;
  }
  .kp-filter-option:hover {
    background: var(--kp-emerald-soft);
    color: var(--kp-ink);
    padding-left: 1rem;
  }
  .kp-filter-active {
    background: var(--kp-emerald-soft);
    color: var(--kp-emerald-dark);
    font-weight: 600;
    padding-left: 1rem;
  }
  .kp-filter-active:hover {
    background: color-mix(in srgb, var(--kp-emerald-soft) 78%, var(--kp-emerald) 22%);
  }

  .kp-catalog-hero {
    position: relative;
    overflow: hidden;
    isolation: isolate;
    min-height: 330px;
    background-color: #eaf7f2;
    background-image: linear-gradient(90deg, rgba(255,255,255,.90) 0%, rgba(255,255,255,.72) 34%, rgba(255,255,255,.20) 64%, rgba(255,255,255,.04) 100%), url("/assets/wear/catalog/kp-catalog-hero-background.webp");
    background-position: right center;
    background-size: cover;
    background-repeat: no-repeat;
    border: 1px solid color-mix(in srgb, var(--kp-emerald) 10%, transparent);
  }
  .kp-catalog-hero::after {
    content: "";
    position: absolute;
    inset: 0;
    background: linear-gradient(180deg, rgba(255,255,255,.04), rgba(255,255,255,.10));
    pointer-events: none;
    z-index: 0;
  }
  @media (max-width: 1023px) {
    .kp-catalog-hero {
      min-height: 320px;
      background-position: 64% center;
      background-image: linear-gradient(90deg, rgba(255,255,255,.94) 0%, rgba(255,255,255,.78) 46%, rgba(255,255,255,.18) 100%), url("/assets/wear/catalog/kp-catalog-hero-background.webp");
    }
  }
  @media (max-width: 640px) {
    /* Keep the approved artwork visible on small screens instead of hiding it behind the white wash. */
    .kp-catalog-hero {
      min-height: 390px;
      background-position: 70% center;
      background-size: cover;
      background-image: linear-gradient(180deg, rgba(255,255,255,.96) 0%, rgba(255,255,255,.86) 42%, rgba(255,255,255,.20) 100%), url("/assets/wear/catalog/kp-catalog-hero-background.webp");
    }
  }
  .kp-product-card {
    transition: transform 400ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-product-card:hover { transform: translateY(-6px); }
  .kp-product-card .kp-product-img { transition: transform 600ms cubic-bezier(0.4,0,0.2,1); }
  .kp-product-card:hover .kp-product-img { transform: scale(1.06); }
</style>
@endpush

@section('content')
<div class="kp-shop-page pt-8 md:pt-10">
    <div class="w-full px-4 sm:px-6 lg:px-8">

        {{-- ── Admin-controlled shop banner ───────────────────────────── --}}
        <div data-storefront-shop-banner class="mb-7 hidden overflow-hidden rounded-[1.5rem] bg-black text-white">
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

        {{-- ── Editorial catalog hero ─────────────────────────────────── --}}
        <section class="kp-catalog-hero kp-reveal relative mb-7 w-full px-4 py-8 sm:px-6 sm:py-10 lg:px-10 lg:py-11">
            <div class="mx-auto flex w-full max-w-[1800px] min-h-[250px] flex-col justify-between gap-8 px-0 lg:min-h-[275px]">
                <div class="max-w-2xl pt-1 lg:pt-3">
                    <div class="mb-4 flex items-center gap-2 text-xs font-medium text-black/70">
                        <a href="{{ route('home') }}" class="transition hover:text-emerald-700">Home</a>
                        <span aria-hidden="true">/</span>
                        <span class="font-semibold text-black">Shop</span>
                    </div>

                    <h1 class="text-4xl font-black tracking-[-0.045em] text-black sm:text-5xl lg:text-[3.6rem] lg:leading-[.98]">Shop All <span class="text-emerald-700">Products</span></h1>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-black/65 sm:text-base">
                        Discover everyday pieces made for movement, comfort and confidence.
                    </p>

                    <div class="mt-5 flex flex-wrap items-center gap-x-5 gap-y-3 text-sm font-medium text-black">
                        <div class="flex items-center gap-2">
                            <span class="flex h-8 w-8 items-center justify-center rounded-full bg-white/80 ring-1 ring-emerald-950/8">
                                <x-tabler-shopping-bag size="16" stroke-width="1.8" />
                            </span>
                            <span data-catalog-count class="min-h-5">26 products found</span>
                        </div>
                        <span class="hidden h-6 w-px bg-emerald-950/15 sm:block" aria-hidden="true"></span>
                        <div class="flex items-center gap-2 text-black/75">
                            <x-tabler-tag size="17" stroke-width="1.8" class="text-emerald-700" />
                            <span>Fresh drops, everyday essentials</span>
                        </div>
                    </div>
                </div>

                <div class="flex flex-col gap-3 lg:absolute lg:bottom-7 lg:right-[max(2rem,calc((100vw-1600px)/2))] lg:max-w-[45%] lg:flex-row lg:items-center">
                    <div class="relative w-full lg:w-64">
                        <label for="kp-catalog-search" class="sr-only">Search products</label>
                        <x-tabler-search size="18" stroke-width="1.8" class="pointer-events-none absolute left-4 top-1/2 -translate-y-1/2 text-black" />
                        <input id="kp-catalog-search" data-catalog-search type="search" value="{{ request('q', '') }}" placeholder="Search products…" autocomplete="off" class="field w-full rounded-full bg-white/95 py-3.5 pl-11 pr-10 shadow-sm ring-1 ring-emerald-950/8">
                        <button data-catalog-search-clear type="button" class="absolute right-2 top-1/2 hidden h-8 w-8 -translate-y-1/2 rounded-full text-black transition hover:bg-emerald-50 hover:text-emerald-700" aria-label="Clear search">
                            <x-tabler-x size="16" stroke-width="1.8" />
                        </button>
                    </div>

                    <div class="relative w-full lg:w-auto">
                        <label for="kp-catalog-sort" class="sr-only">Sort products</label>
                        <select id="kp-catalog-sort" data-catalog-sort class="w-full appearance-none rounded-full border-0 bg-white/95 py-3.5 pl-5 pr-11 text-sm font-semibold text-black shadow-sm ring-1 ring-emerald-950/8 outline-none transition hover:ring-emerald-600/25 focus:ring-2 focus:ring-emerald-600/30 lg:min-w-[205px]">
                            <option value="featured">Sort by: Featured</option>
                            <option value="price-asc">Price: Low to High</option>
                            <option value="price-desc">Price: High to Low</option>
                            <option value="newest">Newest</option>
                        </select>
                        <x-tabler-chevron-down size="16" stroke-width="1.8" class="pointer-events-none absolute right-4 top-1/2 -translate-y-1/2 text-black" />
                    </div>
                </div>
            </div>
        </section>

        {{-- ── Mobile filter trigger ──────────────────────────────────── --}}
        <div class="mb-5 flex justify-end lg:hidden">
            <button data-catalog-filter-toggle type="button" aria-label="Open filters" class="kp-button-secondary inline-flex items-center gap-2">
                <x-tabler-adjustments-horizontal size="17" stroke-width="1.8" />
                Filters
                <span data-catalog-filter-count class="hidden h-5 min-w-5 items-center justify-center rounded-full bg-emerald-600 px-1.5 text-[11px] font-semibold text-white">0</span>
            </button>
        </div>

        <div class="flex gap-8 pb-16 xl:gap-12">

            {{-- ── Sidebar ───────────────────────────────────────────────── --}}
            <aside data-catalog-sidebar class="hidden w-56 shrink-0 lg:block xl:w-64">
                <div class="sticky top-24 space-y-8">
                    <section>
                        <div class="mb-4 flex items-center justify-between">
                            <h2 class="text-xs font-bold uppercase tracking-[0.18em] text-black">Category</h2>
                            <button data-catalog-clear type="button" class="hidden text-xs font-medium text-emerald-700 transition hover:text-emerald-800">Clear all</button>
                        </div>
                        <div data-catalog-categories class="space-y-1.5">
                            @for($i = 0; $i < 5; $i++)
                                <div class="h-9 kp-skeleton animate-pulse rounded-lg bg-emerald-50/70"></div>
                            @endfor
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-black">Price Range</h2>
                        <div data-catalog-prices class="space-y-1.5">
                            <button type="button" data-price="all" class="kp-filter-option kp-filter-active w-full rounded-lg px-3 py-2.5 text-left text-sm">All Prices</button>
                            <button type="button" data-price="under-50000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">Under 50,000 TZS</button>
                            <button type="button" data-price="50000-150000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">50,000 – 150,000 TZS</button>
                            <button type="button" data-price="over-150000" class="kp-filter-option w-full rounded-lg px-3 py-2.5 text-left text-sm">Over 150,000 TZS</button>
                        </div>
                    </section>

                    <section>
                        <h2 class="mb-4 text-xs font-bold uppercase tracking-[0.18em] text-black">Special</h2>
                        <button data-catalog-sale-toggle type="button" aria-pressed="false" class="flex w-full items-center justify-between gap-3 rounded-lg py-1.5 text-left transition hover:bg-emerald-50/50">
                            <span class="text-sm text-black">On Sale Only</span>
                            <span class="relative h-6 w-11 flex-shrink-0 rounded-full bg-white transition-colors">
                                <span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span>
                            </span>
                        </button>
                    </section>

                </div>
            </aside>

            {{-- ── Product grid ──────────────────────────────────────────── --}}
            <main class="min-w-0 flex-1">
                <div class="mb-5 flex items-center justify-between gap-4">
                    <p class="text-sm text-black">KP Wear collection</p>
                    <div class="hidden items-center gap-2 text-xs text-black sm:flex">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                        <span>Fresh drops, everyday essentials</span>
                    </div>
                </div>

                <div data-catalog-grid class="grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-8" aria-live="polite" aria-busy="true">
                    @for($i = 0; $i < 8; $i++)
                        <div class="kp-skeleton animate-pulse">
                            <div class="aspect-[3/4] rounded-2xl bg-emerald-50/70"></div>
                            <div class="mt-3 h-3 w-3/4 rounded bg-emerald-50/70"></div>
                            <div class="mt-2 h-3 w-1/3 rounded bg-emerald-50/70"></div>
                        </div>
                    @endfor
                </div>

                <div data-catalog-empty class="hidden flex-col items-center justify-center py-24 text-center">
                    <div class="mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-emerald-50/70">
                        <x-tabler-search size="26" stroke-width="1.8" class="text-black" />
                    </div>
                    <h3 class="mb-1 text-lg font-semibold text-black">No products found</h3>
                    <p class="mb-5 text-sm text-black">Try adjusting your filters or search terms.</p>
                    <button data-catalog-empty-clear type="button" class="button-dark px-5">Clear All Filters</button>
                </div>

                <div data-catalog-pagination aria-live="polite"></div>
            </main>
        </div>
    </div>
</div>

{{-- ── Mobile filter panel ─────────────────────────────────────────── --}}
<div data-catalog-mobile-panel class="fixed inset-0 z-[70] hidden bg-black/40 lg:hidden" role="dialog" aria-modal="true" aria-label="Filter products">
    <div data-catalog-mobile-sheet class="absolute bottom-0 left-0 right-0 flex max-h-[88vh] flex-col overflow-hidden rounded-t-[2rem] bg-white shadow-2xl">
        <div class="flex items-center justify-between border-b border-emerald-950/10 p-6">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-emerald-700">Shop</p>
                <h3 class="mt-1 text-xl font-semibold">Filters</h3>
            </div>
            <button data-catalog-filter-close type="button" class="rounded-full p-2 text-black transition hover:bg-emerald-50/70" aria-label="Close filters">
                <x-tabler-x size="20" stroke-width="1.8" />
            </button>
        </div>

        <div data-catalog-mobile-content class="flex-1 overflow-y-auto p-6"></div>

        <div class="flex items-center gap-3 border-t border-emerald-950/10 p-4">
            <button data-catalog-mobile-clear type="button" class="kp-button-secondary flex-1">
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
<script nonce="{{ Vite::cspNonce() }}">
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

  // Editorial campaign break after the first 12 products.
  // renderProductGrid creates the mount after product #12, so this re-runs
  // safely whenever filters, search or pagination re-render the catalog.
  let editorialTimer = null;
  let editorialMount = null;

  const initEditorialSlider = () => {
    if (editorialTimer) {
      window.clearInterval(editorialTimer);
      editorialTimer = null;
    }

    editorialMount = document.querySelector('[data-catalog-editorial-slider]');
    if (!editorialMount) return;

    const cards = Array.from(document.querySelectorAll('[data-catalog-grid] [data-product-card]'));
    if (cards.length < 12) return;

    const productImages = cards
      .slice(0, 4)
      .map((card) => card.querySelector('img'))
      .filter(Boolean)
      .map((img) => img.currentSrc || img.src);

    if (!productImages.length) return;

    const slides = [
      {
        eyebrow: 'KP Wear',
        title: 'Wear Your Story',
        copy: 'Everyday pieces. Distinctly KP.',
        action: 'Shop Now',
      },
      {
        eyebrow: 'Made for movement',
        title: 'Move in KP',
        copy: 'Clean silhouettes, bold details and everyday comfort.',
        action: 'Explore Collection',
      },
      {
        eyebrow: 'Everyday essentials',
        title: 'Built for Your Rotation',
        copy: 'Pieces designed to stay with you from day to day.',
        action: 'Discover More',
      },
    ];

    editorialMount.innerHTML = `
      <section class="relative isolate min-h-[235px] overflow-hidden rounded-[1.75rem] bg-emerald-950 text-white shadow-sm sm:min-h-[255px] lg:min-h-[275px]" aria-label="KP Wear campaign slider">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_72%_40%,rgba(16,185,129,.24),transparent_38%),linear-gradient(110deg,#022c22_0%,#064e3b_52%,#022c22_100%)]" aria-hidden="true"></div>
        <div class="absolute -left-20 -top-28 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>
        <div class="absolute -right-20 -bottom-40 h-96 w-96 rounded-full border border-emerald-300/10" aria-hidden="true"></div>
        <div class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-black/20 to-transparent" aria-hidden="true"></div>

        <div class="relative grid min-h-[235px] items-center gap-5 px-5 py-7 sm:min-h-[255px] sm:px-8 lg:min-h-[275px] lg:grid-cols-[1fr_1.15fr_.55fr] lg:px-10">
          <div class="relative z-20 max-w-md">
            <p data-editorial-eyebrow class="text-[10px] font-bold uppercase tracking-[.24em] text-emerald-300"></p>
            <h2 data-editorial-title class="mt-2 text-3xl font-black leading-none tracking-[-.04em] sm:text-4xl lg:text-5xl"></h2>
            <p data-editorial-copy class="mt-3 max-w-sm text-sm leading-5 text-white/70 sm:text-[15px]"></p>
            <a href="{{ route('shop') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 text-xs font-bold text-black transition hover:bg-emerald-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-950">
              <span data-editorial-action-label></span>
              <x-tabler-arrow-right size="15" stroke-width="2" />
            </a>
          </div>

          <div class="relative hidden h-full min-h-[205px] items-end justify-center sm:flex">
            <div class="absolute bottom-2 h-40 w-40 rounded-full bg-emerald-400/10 blur-2xl" aria-hidden="true"></div>
            <img data-editorial-image-main class="relative z-10 h-[205px] w-[150px] object-contain drop-shadow-[0_22px_30px_rgba(0,0,0,.45)] transition-opacity duration-300 lg:h-[235px] lg:w-[175px]" alt="KP Wear featured product" />
            <img data-editorial-image-secondary class="absolute bottom-3 left-1/2 z-0 h-32 w-24 -translate-x-[115%] rotate-[-7deg] object-contain opacity-80 drop-shadow-xl" alt="KP Wear featured product" />
          </div>

          <div class="hidden text-right lg:block">
            <p class="text-[10px] font-semibold uppercase tracking-[.34em] text-white/45">KP Wear</p>
            <p class="mt-3 text-sm font-semibold uppercase leading-7 tracking-[.18em] text-white/80">Made<br>for<br>movement</p>
          </div>
        </div>

        <div class="absolute bottom-4 left-5 right-5 z-30 flex items-center justify-between sm:left-8 sm:right-8 lg:left-10 lg:right-10">
          <div class="flex items-center gap-1.5" data-editorial-dots aria-label="Campaign slides"></div>
          <div class="flex items-center gap-2">
            <button type="button" data-editorial-prev class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow-sm transition hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300" aria-label="Previous campaign slide">
              <x-tabler-chevron-left size="17" stroke-width="1.8" />
            </button>
            <button type="button" data-editorial-next class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow-sm transition hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300" aria-label="Next campaign slide">
              <x-tabler-chevron-right size="17" stroke-width="1.8" />
            </button>
          </div>
        </div>
      </section>`;

    const title = editorialMount.querySelector('[data-editorial-title]');
    const eyebrow = editorialMount.querySelector('[data-editorial-eyebrow]');
    const copy = editorialMount.querySelector('[data-editorial-copy]');
    const actionLabel = editorialMount.querySelector('[data-editorial-action-label]');
    const imageMain = editorialMount.querySelector('[data-editorial-image-main]');
    const imageSecondary = editorialMount.querySelector('[data-editorial-image-secondary]');
    const dots = editorialMount.querySelector('[data-editorial-dots]');
    let index = 0;

    const render = () => {
      const slide = slides[index];
      eyebrow.textContent = slide.eyebrow;
      title.textContent = slide.title;
      copy.textContent = slide.copy;
      actionLabel.textContent = slide.action;
      imageMain.src = productImages[index % productImages.length];
      imageSecondary.src = productImages[(index + 1) % productImages.length];

      Array.from(dots.children).forEach((dot, i) => {
        dot.classList.toggle('w-7', i === index);
        dot.classList.toggle('w-2', i !== index);
        dot.classList.toggle('bg-emerald-300', i === index);
        dot.classList.toggle('bg-white/30', i !== index);
      });
    };

    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'h-1.5 rounded-full transition-all duration-200';
      dot.setAttribute('aria-label', `Go to campaign slide ${i + 1}`);
      dot.addEventListener('click', () => {
        index = i;
        render();
        restart();
      });
      dots.appendChild(dot);
    });

    const next = () => {
      index = (index + 1) % slides.length;
      render();
    };

    const prev = () => {
      index = (index - 1 + slides.length) % slides.length;
      render();
    };

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const restart = () => {
      window.clearInterval(editorialTimer);
      editorialTimer = null;
      if (!reduceMotion) editorialTimer = window.setInterval(next, 5000);
    };

    editorialMount.querySelector('[data-editorial-next]').addEventListener('click', () => {
      next();
      restart();
    });
    editorialMount.querySelector('[data-editorial-prev]').addEventListener('click', () => {
      prev();
      restart();
    });
    // Keep autoplay running while the pointer is over the campaign.
    // Only keyboard focus pauses it, so simply leaving the cursor on the banner
    // cannot make the carousel appear stuck.
    editorialMount.addEventListener('focusin', () => window.clearInterval(editorialTimer));
    editorialMount.addEventListener('focusout', restart);

    render();
    restart();
  };

  document.addEventListener('kp:grid-rendered', initEditorialSlider);
  initEditorialSlider();
})();
</script>
@endpush
