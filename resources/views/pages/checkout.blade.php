@extends('layouts.app')

@section('content')

<div data-checkout-page class="mx-auto w-full max-w-7xl px-4 pb-24 pt-10 sm:px-6 lg:px-8">

    {{-- Top step bar --}}

    <div class="mb-8 flex items-center justify-between gap-4 border-b border-emerald-950/10 pb-4">

        <div class="flex items-center gap-4">

            <a href="{{ route('cart') }}" aria-label="Back to bag" class="text-black transition hover:text-black">

                <x-tabler-arrow-left size="18" />

            </a>

            <a href="{{ route('home') }}" class="text-sm font-black tracking-tight text-black">

                KP WEAR

            </a>

        </div>

        <ol class="hidden items-center gap-2 sm:flex">

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-black text-xs font-semibold text-white">1</span>

                <span class="text-sm font-semibold text-black">Checkout</span>

            </li>

            <x-tabler-chevron-right size="14" class="text-black" />

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50/70 text-xs font-semibold text-black">2</span>

                <span class="text-sm text-black">Payment</span>

            </li>

            <x-tabler-chevron-right size="14" class="text-black" />

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-50/70 text-xs font-semibold text-black">3</span>

                <span class="text-sm text-black">Complete</span>

            </li>

        </ol>

        <p class="inline-flex items-center gap-1.5 text-sm font-medium text-black">

            <x-tabler-lock size="14" />

            <span class="hidden sm:inline">Secure checkout</span>

        </p>

    </div>

    {{-- Header --}}

    <div class="pb-5">

        <p class="text-xs font-semibold uppercase tracking-[0.22em] text-emerald-600">Checkout</p>

        <h1 class="mt-2 text-3xl font-bold tracking-tight text-black sm:text-4xl">Complete your order</h1>

        <p class="mt-2 text-sm text-black">Review your details and choose a payment method.</p>

    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,768px)_400px]">

        {{-- LEFT COLUMN --}}

        <div class="space-y-5">

            {{-- CONTACT INFORMATION --}}

            <section class="rounded-xl border border-emerald-950/10 bg-white p-5">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex items-start gap-3">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">

                            <x-tabler-user size="18" />

                        </span>

                        <div>

                            <p class="text-base font-bold text-black">Contact information</p>

                            <p data-checkout-contact class="mt-0.5 text-sm text-black">Loading…</p>

                        </div>

                    </div>

                    <div class="flex flex-col items-end gap-1">

                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white">

                            <x-tabler-check size="14" />

                        </span>

                        <a href="{{ route('account.profile') }}" class="text-xs font-medium text-emerald-600 hover:underline">Edit</a>

                    </div>

                </div>

            </section>

            {{-- DELIVERY ADDRESS (compact, collapsed) --}}

            <section class="rounded-xl border border-emerald-950/10 bg-white p-5">

                <button type="button" data-open-address-modal class="flex w-full items-start justify-between gap-3 text-left">

                    <div class="flex items-start gap-3">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">

                            <x-tabler-map-pin size="18" />

                        </span>

                        <div>

                            <p class="flex items-center gap-2 text-base font-bold text-black">

                                Delivery address

                                <span data-address-required-badge class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">Required</span>

                            </p>

                            <p data-checkout-address-summary class="mt-0.5 text-sm text-black">Add a delivery address or use your current location.</p>

                        </div>

                    </div>

                    <x-tabler-chevron-right size="18" class="mt-1.5 flex-shrink-0 text-black" />

                </button>

                <div data-checkout-address-actions class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-2">

                    <button type="button" data-use-current-location class="kp-button-secondary min-h-10 rounded-full px-4 py-2.5 shadow-sm">

                        <x-tabler-current-location size="16" class="text-black" />

                        Use my current location

                    </button>

                    {{-- The header button above already opens this same address modal, so a
                         second "Add delivery address" button here just duplicated it. This
                         slot is the order-note shortcut instead — see the removed standalone
                         Order note card below. --}}

                    <button type="button" data-open-note-modal class="kp-button-secondary min-h-10 rounded-full px-4 py-2.5 shadow-sm">

                        <x-tabler-file-text size="16" class="text-black" />

                        Add order note

                    </button>

                </div>

                <p data-checkout-note-summary class="mt-3 hidden items-start gap-1.5 text-sm text-black"></p>

            </section>

            {{-- PAYMENT METHOD --}}

            <section>

                <div class="mb-3 flex items-start gap-3">

                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">

                        <x-tabler-credit-card size="18" />

                    </span>

                    <div>

                        <p class="text-base font-bold text-black">Payment method</p>

                        <p class="mt-0.5 text-sm text-black">Choose how you want to pay.</p>

                    </div>

                </div>

                <div data-checkout-payment-methods class="grid grid-cols-1 gap-3 sm:grid-cols-2" aria-live="polite">

                    <label class="kp-checkout-payment-card flex cursor-pointer items-center gap-3 rounded-lg border-2 border-emerald-600 bg-emerald-50/30 p-4 transition" data-payment-card data-method="mobile_money">

                        <input type="radio" name="payment_method" value="mobile_money" class="h-4 w-4 accent-emerald-600" checked>

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70 text-black">

                            <x-tabler-device-mobile size="18" />

                        </span>

                        <span>

                            <span class="block text-sm font-semibold text-black">Mobile Money</span>

                            <span class="block text-xs text-black">Pay with M-Pesa or Tigopesa</span>

                        </span>

                    </label>

                    <label class="kp-checkout-payment-card flex cursor-pointer items-center gap-3 rounded-lg border border-emerald-950/12 bg-white p-4 transition" data-payment-card data-method="card">

                        <input type="radio" name="payment_method" value="card" class="h-4 w-4 accent-emerald-600">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70 text-black">

                            <x-tabler-credit-card size="18" />

                        </span>

                        <span>

                            <span class="block text-sm font-semibold text-black">Card</span>

                            <span class="block text-xs text-black">Pay with debit or credit card</span>

                        </span>

                    </label>

                </div>

            </section>

            {{-- PAY BUTTON --}}

            <button

                data-place-order

                type="button"

                disabled

                class="button-dark h-14 w-full text-base disabled:opacity-60"

            >

                <x-tabler-lock size="18" />

                <span data-place-order-label>Calculating total…</span>

                <x-tabler-arrow-right size="18" data-place-order-arrow class="hidden" />

            </button>

            <p class="flex items-center justify-center gap-1.5 text-center text-xs text-black">

                <x-tabler-shield-check size="13" />

                Your payment and details are encrypted and secure

            </p>

            <div data-checkout-error class="hidden items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"></div>

        </div>

        {{-- RIGHT COLUMN — ORDER SUMMARY --}}

        <aside class="h-fit rounded-xl border border-emerald-950/10 bg-white p-6 lg:sticky lg:top-24">

            <div class="flex items-center justify-between">

                <p class="text-xl font-bold tracking-tight text-black">Order summary</p>

                <a href="{{ route('cart') }}" class="text-sm font-medium text-emerald-600 hover:underline">Edit cart</a>

            </div>

            {{--

                Populated from GET /api/v1/cart/checkout/preview — this route

                matches your real cart/order preview endpoint. Shows a skeleton

                while loading; on failure shows a retry button rather than a

                static "could not load" dead end.

            --}}

            <div data-checkout-summary class="mt-4 space-y-3 text-sm" aria-live="polite">

                <div class="kp-skeleton animate-pulse space-y-3">

                    <div class="h-14 rounded-lg bg-emerald-50/70"></div>

                    <div class="h-14 rounded-lg bg-emerald-50/70"></div>

                    <div class="h-4 rounded bg-emerald-50/70"></div>

                    <div class="h-4 rounded bg-emerald-50/70"></div>

                </div>

            </div>

            <div class="mt-4 flex items-start gap-3 rounded-lg bg-emerald-50 p-4">

                <x-tabler-shield-check size="18" class="mt-0.5 flex-shrink-0 text-emerald-600" />

                <div>

                    <p class="text-sm font-semibold text-emerald-900">Secure payment</p>

                    <p class="mt-0.5 text-xs leading-5 text-emerald-700">Your payment is processed securely by Selcom.</p>

                </div>

            </div>

            <div class="mt-4 grid grid-cols-3 gap-3 border-t border-emerald-950/10 pt-4 text-center">

                <div>

                    <x-tabler-truck size="20" class="mx-auto text-black" />

                    <p class="mt-1.5 text-[11px] font-medium text-black">Fast delivery</p>

                </div>

                <div>

                    <x-tabler-shield-check size="20" class="mx-auto text-black" />

                    <p class="mt-1.5 text-[11px] font-medium text-black">Secure payment</p>

                </div>

                <div>

                    <x-tabler-headset size="20" class="mx-auto text-black" />

                    <p class="mt-1.5 text-[11px] font-medium text-black">Support 24/7</p>

                </div>

            </div>

        </aside>

    </div>

</div>

{{-- ── Address choice modal (location vs manual) ─────────────────── --}}

<div data-address-choice-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="checkout-address-title">

    <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">

        <div class="flex items-center justify-between">

            <h2 id="checkout-address-title" class="flex items-center gap-2 text-lg font-semibold text-black">

                <x-tabler-map-pin size="18" class="text-emerald-600" />

                Add delivery address

            </h2>

            <button type="button" data-close-address-choice class="text-black hover:text-black" aria-label="Close delivery address dialog">

                <x-tabler-x size="20" aria-hidden="true" />

            </button>

        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">

            <button type="button" data-address-tab="location" class="rounded-xl border-2 border-emerald-600 bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white">Use my location</button>

            <button type="button" data-address-tab="manual" class="rounded-xl border border-emerald-950/12 px-4 py-2.5 text-sm font-medium text-black hover:bg-emerald-50/50">Enter manually</button>

        </div>

        {{-- Location panel --}}

        <div data-address-panel="location" class="mt-5">
            <div class="relative overflow-hidden rounded-xl border border-emerald-950/12 bg-emerald-50/70">
                <div class="p-3">
                    <div class="relative">
                        <x-tabler-search size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-black" />
                        <label for="checkout-map-search" class="sr-only">Search for an address or place</label>
                        <input id="checkout-map-search" data-map-search type="search" autocomplete="off"
                               class="field w-full pl-10" placeholder="Search for an address or place">
                        <div data-map-search-results class="absolute left-3 right-3 top-[calc(100%+6px)] z-30 hidden max-h-52 overflow-auto rounded-xl border border-emerald-950/12 bg-white shadow-xl"></div>
                    </div>
                </div>

                <div data-mapbox-container class="h-72 w-full"></div>

                <div class="absolute bottom-4 left-4 right-4 z-10 flex gap-2">
                    <button type="button" data-request-location
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-black shadow-lg ring-1 ring-black/5 hover:bg-emerald-50/50">
                        <x-tabler-current-location size="16" />
                        Use my current location
                    </button>
                    <button type="button" data-map-confirm disabled
                            class="button-dark min-h-10 flex-1 rounded-full px-4 py-2.5 shadow-lg disabled:opacity-50">
                        <x-tabler-check size="16" />
                        Confirm location
                    </button>
                </div>
            </div>

            <div class="mt-3 rounded-xl border border-emerald-950/12 bg-emerald-50/50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-black">Selected location</p>
                <p data-map-address class="mt-1 text-sm text-black">Search, click the map, drag the pin, or use your current location.</p>
            </div>
            <p data-map-error class="mt-3 hidden text-sm text-red-600"></p>
        </div>

        {{-- Manual entry panel --}}

        <form data-address-form data-address-panel="manual" class="mt-5 hidden grid-cols-2 gap-3">

            <label class="sr-only" for="checkout-recipient-name">Recipient name</label>
            <input id="checkout-recipient-name" name="recipient_name" class="field w-full" placeholder="Recipient name" required>

            <label class="sr-only" for="checkout-address-phone">Phone number</label>
            <input id="checkout-address-phone" name="phone" class="field w-full" placeholder="Phone number" required>

            <label class="sr-only" for="checkout-region">Region</label>
            <input id="checkout-region" name="region" class="field w-full" placeholder="Region" required>

            <label class="sr-only" for="checkout-district">District</label>
            <input id="checkout-district" name="district" class="field w-full" placeholder="District" required>

            <label class="sr-only" for="checkout-ward">Ward</label>
            <input id="checkout-ward" name="ward" class="field w-full" placeholder="Ward">

            <label class="sr-only" for="checkout-street">Street or house address</label>
            <input id="checkout-street" name="street" class="field w-full col-span-2" placeholder="Street / house address" required>

            <button class="button-dark col-span-2 py-3">Save address</button>

        </form>

    </div>

</div>

{{-- ── Order note modal ────────────────────────────────────────────── --}}

<div data-note-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center" role="dialog" aria-modal="true" aria-labelledby="checkout-note-title">

    <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">

        <div class="flex items-center justify-between">

            <h2 id="checkout-note-title" class="flex items-center gap-2 text-lg font-semibold text-black">

                <x-tabler-file-text size="18" class="text-emerald-600" />

                Add order note

            </h2>

            <button type="button" data-close-note-modal class="text-black hover:text-black" aria-label="Close order note dialog">

                <x-tabler-x size="20" aria-hidden="true" />

            </button>

        </div>

        <form data-note-form class="mt-5">

            <label for="checkout-order-note" class="sr-only">Order note</label>
            <textarea id="checkout-order-note" name="note" maxlength="250" data-note-textarea class="field min-h-32 w-full resize-y" placeholder="e.g. Call when you arrive, gate code, landmark, etc."></textarea>

            <p class="mt-1 text-right text-xs text-black"><span data-note-count>0</span>/250</p>

            <div class="mt-4 flex gap-3">

                <button type="button" data-close-note-modal class="flex-1 rounded-xl border border-emerald-950/12 px-5 py-3 text-sm font-medium text-black hover:bg-emerald-50/50">Cancel</button>

                <button type="submit" class="button-dark flex-1 py-3">Save note</button>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')



@endpush
