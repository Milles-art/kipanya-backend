@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
  .kp-cart-item {
    transition: border-color 220ms ease, background-color 220ms ease;
  }
  .kp-cart-item:hover {
    border-color: color-mix(in srgb, var(--kp-emerald) 18%, transparent);
    background: color-mix(in srgb, var(--kp-emerald-soft) 28%, white);
  }
  .kp-qty-btn {
    transition: background-color 180ms ease, color 180ms ease;
  }
  .kp-qty-btn:hover:not(:disabled) {
    background: var(--kp-emerald-soft);
    color: var(--kp-emerald-dark);
  }
  .kp-qty-btn:disabled { opacity: .4; cursor: not-allowed; }
  .kp-remove-btn { transition: color 180ms ease; }
  .kp-remove-btn:hover { color: #dc2626; }
  .kp-cart-benefit + .kp-cart-benefit {
    border-left: 1px solid color-mix(in srgb, var(--kp-emerald-dark) 10%, transparent);
  }
  @media (max-width: 639px) {
    .kp-cart-benefit + .kp-cart-benefit { border-left: 0; border-top: 1px solid color-mix(in srgb, var(--kp-emerald-dark) 10%, transparent); }
  }
</style>
@endpush

@section('content')
<div data-cart-page class="mx-auto w-full kp-content-wide px-4 pb-24 pt-8 sm:px-6 lg:px-10 xl:px-12">

    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div class="kp-reveal flex flex-col gap-4 border-b border-emerald-950/12 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-600">KP Wear</p>
            <h1 class="mt-2 text-3xl font-black tracking-[-0.03em] text-black sm:text-4xl">
                Your bag
                <span class="font-normal text-black" aria-hidden="true">·</span>
                <span data-cart-header-count class="text-black">0</span>
            </h1>
        </div>
        <a href="{{ route('shop') }}"
           class="hidden items-center gap-1.5 text-sm font-medium text-black underline underline-offset-4 transition hover:text-emerald-700 sm:inline-flex">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- ── Error banner ──────────────────────────────────────────── --}}
    <div data-cart-error class="mt-6 hidden items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" aria-live="polite"></div>

    {{-- ── Loading state: prevents an empty-cart flash while the API resolves --}}
    <div data-cart-loading class="flex flex-col gap-6 py-12" aria-live="polite" aria-busy="true">
        <div class="kp-skeleton h-5 w-36 rounded"></div>
        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px] xl:grid-cols-[minmax(0,1fr)_420px]">
            <div class="space-y-3">
                <div class="kp-skeleton h-36 rounded-2xl"></div>
                <div class="kp-skeleton h-36 rounded-2xl"></div>
                <div class="kp-skeleton h-28 rounded-2xl"></div>
            </div>
            <div class="kp-skeleton h-72 rounded-2xl"></div>
        </div>
    </div>

    {{-- ── Empty state ───────────────────────────────────────────── --}}
    <div data-cart-empty class="hidden flex flex-col items-center py-28 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 ring-8 ring-emerald-50/40">
            <x-tabler-shopping-bag size="32" class="text-emerald-600" />
        </div>
        <h2 class="mt-6 text-xl font-bold text-black">Your bag is empty</h2>
        <p class="mt-2 max-w-xs text-sm text-black">Find something you'll reach for every day.</p>
        <a href="{{ route('shop') }}" class="button-dark mt-7">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- ── Cart content ──────────────────────────────────────────── --}}
    <div data-cart-content class="hidden gap-10 pt-8 lg:grid lg:grid-cols-[minmax(0,1fr)_400px] xl:grid-cols-[minmax(0,1fr)_430px] xl:gap-14">

        {{-- ── Items ──────────────────────────────────────────────────── --}}
        <section aria-label="Cart items">
            <div class="mb-6 flex items-center justify-between gap-4">
                <p class="text-sm text-black">
                    <span data-cart-item-count class="font-semibold text-black">0</span> items in your bag
                </p>
                <button data-cart-clear type="button"
                        class="flex items-center gap-1.5 text-sm font-medium text-black underline underline-offset-4 transition hover:text-red-600">
                    <x-tabler-trash size="15" />
                    Clear bag
                </button>
            </div>

            <div data-cart-items class="divide-y divide-emerald-950/10" aria-live="polite">
                {{-- Each item is expected to be rendered as its own card, e.g.: --}}
                {{--
                <div class="kp-cart-item flex gap-4 rounded-2xl border border-emerald-950/12 bg-white p-4 shadow-sm sm:p-5">
                    <img src="..." class="h-24 w-24 flex-shrink-0 rounded-xl object-cover sm:h-28 sm:w-28" alt="">
                    <div class="flex flex-1 flex-col justify-between">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-black">Product name</p>
                                <p class="mt-0.5 text-xs text-black">Size M · Black</p>
                            </div>
                            <button class="kp-remove-btn text-black" aria-label="Remove item"><x-tabler-x size="16" /></button>
                        </div>
                        <div class="flex items-end justify-between">
                            <div class="flex items-center gap-2 rounded-full border border-emerald-950/12 px-2 py-1">
                                <button class="kp-qty-btn h-6 w-6 rounded-full text-sm" aria-label="Decrease quantity">−</button>
                                <span class="w-4 text-center text-sm">1</span>
                                <button class="kp-qty-btn h-6 w-6 rounded-full text-sm" aria-label="Increase quantity">+</button>
                            </div>
                            <strong class="text-sm">45,000 TZS</strong>
                        </div>
                    </div>
                </div>
                --}}
            </div>

        </section>

        {{-- ── Order summary ────────────────────────────────────────── --}}
        <aside class="h-fit rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-[0_16px_48px_rgba(0,0,0,0.06)] sm:p-6 lg:sticky lg:top-24 xl:p-7" aria-label="Order summary">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold tracking-tight text-black">Order summary</h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                    <x-tabler-shield-check size="13" />
                    Secure
                </span>
            </div>

            <div class="mt-7 flex items-center justify-between border-b border-emerald-950/12 pb-5">
                <span class="text-sm text-black">Subtotal</span>
                <strong data-cart-summary class="text-lg font-bold text-black">0 TZS</strong>
            </div>

            <div class="mt-5 space-y-3 text-xs text-black">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5"><x-tabler-truck size="14" /> Delivery</span>
                    <span>Calculated at checkout</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1.5"><x-tabler-credit-card size="14" /> Payment</span>
                    <span>Secure checkout</span>
                </div>
            </div>

            <a href="{{ route('checkout') }}"
               class="button-dark mt-6 min-h-12 w-full text-center sm:mt-7">
                Proceed to checkout <span aria-hidden="true">→</span>
            </a>

            <p class="mt-4 text-center text-[11px] text-black">Taxes and delivery calculated at the next step</p>
        </aside>
    </div>

    {{-- ── Cart reassurance strip ─────────────────────────────────── --}}
    <div class="mt-12 border-y border-emerald-950/10 py-6 sm:mt-14 sm:py-7">
        <div class="grid gap-5 sm:grid-cols-2 sm:gap-0">
            <div class="kp-cart-benefit flex items-center gap-4 px-0 sm:px-6">
                <x-tabler-shield-check size="28" class="shrink-0 text-black" stroke-width="1.7" />
                <div>
                    <p class="text-sm font-bold text-black">Secure payments</p>
                    <p class="mt-1 text-xs text-black">Your information is protected</p>
                </div>
            </div>
            <div class="kp-cart-benefit flex items-center gap-4 px-0 sm:px-6 sm:pr-0">
                <x-tabler-truck size="28" class="shrink-0 text-black" stroke-width="1.7" />
                <div>
                    <p class="text-sm font-bold text-black">Fast delivery</p>
                    <p class="mt-1 text-xs text-black">Across Tanzania</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Mobile sticky actions ──────────────────────────────────── --}}
    <div data-cart-mobile-actions class="hidden border-t border-emerald-950/12 pt-6 sm:hidden">
        <a href="{{ route('shop') }}" class="mb-3 flex items-center justify-center gap-1.5 text-sm font-medium text-black underline underline-offset-4">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
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
})();
</script>
@endpush
