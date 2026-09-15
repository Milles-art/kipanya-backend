@extends('layouts.app')

@push('head')
<style>
  .kp-reveal {
    opacity: 0;
    transform: translateY(24px);
    transition: opacity 500ms cubic-bezier(0.4,0,0.2,1),
                transform 500ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-reveal.is-visible { opacity: 1; transform: translateY(0); }

  .kp-cart-item {
    transition: border-color 300ms ease, box-shadow 300ms ease, transform 300ms ease;
  }
  .kp-cart-item:hover {
    border-color: #d1d5db;
    box-shadow: 0 8px 32px rgba(0,0,0,0.06);
    transform: translateY(-2px);
  }

  .kp-qty-btn {
    transition: background 200ms ease, color 200ms ease;
  }
  .kp-qty-btn:hover { background: #f3f4f6; }
  .kp-qty-btn:active { background: #e5e7eb; }

  .kp-remove-btn { transition: color 200ms ease, transform 200ms ease; }
  .kp-remove-btn:hover { color: #dc2626; transform: scale(1.1); }

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-cart-item, .kp-qty-btn, .kp-remove-btn { transition: none !important; }
  }
</style>
@endpush

@section('content')
<div data-cart-page class="mx-auto w-full max-w-[1600px] px-4 pb-24 pt-8 sm:px-6 lg:px-10 xl:px-12">

    {{-- ── Header ────────────────────────────────────────────────── --}}
    <div class="kp-reveal flex flex-col gap-4 border-b border-gray-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-[11px] font-semibold uppercase tracking-[0.22em] text-emerald-600">Kipanya Wear</p>
            <h1 class="mt-2 text-3xl font-black tracking-[-0.03em] text-gray-900 sm:text-4xl">
                Your bag
                <span class="font-normal text-gray-300" aria-hidden="true">·</span>
                <span data-cart-header-count class="text-gray-400">0</span>
            </h1>
        </div>
        <a href="{{ route('shop') }}"
           class="hidden items-center gap-1.5 text-sm font-medium text-gray-700 underline underline-offset-4 transition hover:text-emerald-700 sm:inline-flex">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- ── Error banner ──────────────────────────────────────────── --}}
    <div data-cart-error class="mt-6 hidden items-start gap-2 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert" aria-live="polite"></div>

    {{-- ── Empty state ───────────────────────────────────────────── --}}
    <div data-cart-empty class="flex flex-col items-center py-28 text-center">
        <div class="flex h-20 w-20 items-center justify-center rounded-full bg-emerald-50 ring-8 ring-emerald-50/40">
            <x-tabler-shopping-bag size="32" class="text-emerald-600" />
        </div>
        <h2 class="mt-6 text-xl font-bold text-gray-900">Your bag is empty</h2>
        <p class="mt-2 max-w-xs text-sm text-gray-500">Find something you'll reach for every day.</p>
        <a href="{{ route('shop') }}" class="button-dark mt-7 inline-flex items-center gap-2 rounded-full px-6 transition hover:-translate-y-0.5">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    {{-- ── Cart content ──────────────────────────────────────────── --}}
    <div data-cart-content class="hidden gap-12 pt-9 lg:grid lg:grid-cols-[minmax(0,1fr)_380px] xl:grid-cols-[minmax(0,1fr)_420px]">

        {{-- ── Items ──────────────────────────────────────────────────── --}}
        <section aria-label="Cart items">
            <div class="mb-5 flex items-center justify-between gap-4">
                <p class="text-sm text-gray-500">
                    <span data-cart-item-count class="font-semibold text-gray-900">0</span> items in your bag
                </p>
                <button data-cart-clear type="button"
                        class="flex items-center gap-1.5 text-sm font-medium text-gray-500 underline underline-offset-4 transition hover:text-red-600">
                    <x-tabler-trash size="15" />
                    Clear bag
                </button>
            </div>

            <div data-cart-items class="space-y-3" aria-live="polite">
                {{-- Each item is expected to be rendered as its own card, e.g.: --}}
                {{--
                <div class="kp-cart-item flex gap-4 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                    <img src="..." class="h-24 w-24 flex-shrink-0 rounded-xl object-cover sm:h-28 sm:w-28" alt="">
                    <div class="flex flex-1 flex-col justify-between">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="font-medium text-gray-900">Product name</p>
                                <p class="mt-0.5 text-xs text-gray-500">Size M · Black</p>
                            </div>
                            <button class="kp-remove-btn text-gray-400" aria-label="Remove item"><x-tabler-x size="16" /></button>
                        </div>
                        <div class="flex items-end justify-between">
                            <div class="flex items-center gap-2 rounded-full border border-gray-200 px-2 py-1">
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

            <a href="{{ route('shop') }}"
               class="mt-7 hidden items-center gap-1.5 text-sm font-medium text-gray-700 underline underline-offset-4 transition hover:text-emerald-700 sm:inline-flex">
                Continue shopping <span aria-hidden="true">→</span>
            </a>
        </section>

        {{-- ── Order summary ────────────────────────────────────────── --}}
        <aside class="h-fit rounded-2xl border border-gray-200 bg-white p-6 shadow-[0_16px_48px_rgba(0,0,0,0.06)] lg:sticky lg:top-24 xl:p-7" aria-label="Order summary">
            <div class="flex items-center justify-between gap-4">
                <h2 class="text-lg font-bold tracking-tight text-gray-900">Order summary</h2>
                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-medium text-emerald-700">
                    <x-tabler-shield-check size="13" />
                    Secure
                </span>
            </div>

            <div class="mt-7 flex items-center justify-between border-b border-gray-200 pb-5">
                <span class="text-sm text-gray-600">Subtotal</span>
                <strong data-cart-summary class="text-lg font-bold text-gray-900">0 TZS</strong>
            </div>

            <div class="mt-5 space-y-3 text-xs text-gray-500">
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
               class="button-dark mt-7 flex min-h-12 w-full items-center justify-center gap-2 rounded-full text-center font-medium transition hover:-translate-y-0.5">
                Proceed to checkout <span aria-hidden="true">→</span>
            </a>

            <p class="mt-4 text-center text-[11px] text-gray-400">Taxes and delivery calculated at the next step</p>
        </aside>
    </div>

    {{-- ── Mobile sticky actions ──────────────────────────────────── --}}
    <div data-cart-mobile-actions class="hidden border-t border-gray-200 pt-6 sm:hidden">
        <a href="{{ route('shop') }}" class="mb-3 flex items-center justify-center gap-1.5 text-sm font-medium text-gray-700 underline underline-offset-4">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
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
