@extends('layouts.app')

@section('content')

<div data-checkout-page class="mx-auto w-full max-w-6xl px-4 pb-24 pt-10 sm:px-6 lg:px-8">

    {{-- Top step bar --}}

    <div class="mb-8 flex items-center justify-between gap-4 border-b border-gray-100 pb-4">

        <div class="flex items-center gap-4">

            <a href="{{ route('cart') }}" aria-label="Back to bag" class="text-gray-400 transition hover:text-gray-900">

                <x-tabler-arrow-left size="18" />

            </a>

            <a href="{{ route('home') }}" class="text-sm font-black tracking-tight text-gray-900">

                KP WEAR

            </a>

        </div>

        <ol class="hidden items-center gap-2 sm:flex">

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">1</span>

                <span class="text-sm font-semibold text-gray-900">Checkout</span>

            </li>

            <x-tabler-chevron-right size="14" class="text-gray-300" />

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-400">2</span>

                <span class="text-sm text-gray-400">Payment</span>

            </li>

            <x-tabler-chevron-right size="14" class="text-gray-300" />

            <li class="flex items-center gap-2">

                <span class="flex h-6 w-6 items-center justify-center rounded-full bg-gray-100 text-xs font-semibold text-gray-400">3</span>

                <span class="text-sm text-gray-400">Complete</span>

            </li>

        </ol>

        <p class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500">

            <x-tabler-lock size="14" />

            <span class="hidden sm:inline">Secure checkout</span>

        </p>

    </div>

    {{-- Header --}}

    <div class="pb-6">

        <h1 class="text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Checkout</h1>

        <p class="mt-2 text-sm text-gray-500">Review your details and choose a payment method.</p>

    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,768px)_362px]">

        {{-- LEFT COLUMN --}}

        <div class="space-y-5">

            {{-- CONTACT INFORMATION --}}

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

                <div class="flex items-start justify-between gap-3">

                    <div class="flex items-start gap-3">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">

                            <x-tabler-user size="18" />

                        </span>

                        <div>

                            <p class="text-base font-bold text-gray-900">Contact information</p>

                            <p data-checkout-contact class="mt-0.5 text-sm text-gray-500">Loading…</p>

                        </div>

                    </div>

                    <div class="flex flex-col items-end gap-1">

                        <span class="flex h-6 w-6 items-center justify-center rounded-full bg-emerald-500 text-white">

                            <x-tabler-check size="14" />

                        </span>

                        <a href="{{ route('account.profile') }}" class="text-xs font-medium text-blue-600 hover:underline">Edit</a>

                    </div>

                </div>

            </section>

            {{-- DELIVERY ADDRESS (compact, collapsed) --}}

            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">

                <button type="button" data-open-address-modal class="flex w-full items-start justify-between gap-3 text-left">

                    <div class="flex items-start gap-3">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">

                            <x-tabler-map-pin size="18" />

                        </span>

                        <div>

                            <p class="flex items-center gap-2 text-base font-bold text-gray-900">

                                Delivery address

                                <span data-address-required-badge class="rounded-full bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-700">Required</span>

                            </p>

                            <p data-checkout-address-summary class="mt-0.5 text-sm text-gray-500">Add a delivery address or use your current location.</p>

                        </div>

                    </div>

                    <x-tabler-chevron-right size="18" class="mt-1.5 flex-shrink-0 text-gray-300" />

                </button>

                <div data-checkout-address-actions class="mt-4 grid grid-cols-1 gap-2.5 sm:grid-cols-2">

                    <button type="button" data-use-current-location class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:shadow">

                        <x-tabler-current-location size="16" class="text-gray-400" />

                        Use my current location

                    </button>

                    {{-- The header button above already opens this same address modal, so a
                         second "Add delivery address" button here just duplicated it. This
                         slot is the order-note shortcut instead — see the removed standalone
                         Order note card below. --}}

                    <button type="button" data-open-note-modal class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-semibold text-gray-700 shadow-sm transition hover:border-gray-300 hover:bg-gray-50 hover:shadow">

                        <x-tabler-file-text size="16" class="text-gray-400" />

                        Add order note

                    </button>

                </div>

                <p data-checkout-note-summary class="mt-3 hidden items-start gap-1.5 text-sm text-gray-500"></p>

            </section>

            {{-- PAYMENT METHOD --}}

            <section>

                <div class="mb-3 flex items-start gap-3">

                    <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-blue-50 text-blue-600">

                        <x-tabler-credit-card size="18" />

                    </span>

                    <div>

                        <p class="text-base font-bold text-gray-900">Payment method</p>

                        <p class="mt-0.5 text-sm text-gray-500">Choose how you want to pay.</p>

                    </div>

                </div>

                <div data-checkout-payment-methods class="grid grid-cols-1 gap-3 sm:grid-cols-2" aria-live="polite">

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-blue-600 bg-white p-4 transition hover:shadow-sm" data-payment-card data-method="mobile_money">

                        <input type="radio" name="payment_method" value="mobile_money" class="h-4 w-4 accent-blue-600" checked>

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700">

                            <x-tabler-device-mobile size="18" />

                        </span>

                        <span>

                            <span class="block text-sm font-semibold text-gray-900">Mobile Money</span>

                            <span class="block text-xs text-gray-500">Pay with M-Pesa or Tigopesa</span>

                        </span>

                    </label>

                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-gray-200 bg-white p-4 transition hover:shadow-sm" data-payment-card data-method="card">

                        <input type="radio" name="payment_method" value="card" class="h-4 w-4 accent-blue-600">

                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700">

                            <x-tabler-credit-card size="18" />

                        </span>

                        <span>

                            <span class="block text-sm font-semibold text-gray-900">Card</span>

                            <span class="block text-xs text-gray-500">Pay with debit or credit card</span>

                        </span>

                    </label>

                </div>

            </section>

            {{-- PAY BUTTON --}}

            <button

                data-place-order

                type="button"

                disabled

                class="flex h-14 w-full items-center justify-center gap-2 rounded-xl bg-gray-950 text-base font-semibold text-white transition hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-60"

            >

                <x-tabler-lock size="18" />

                <span data-place-order-label>Calculating total…</span>

                <x-tabler-arrow-right size="18" data-place-order-arrow class="hidden" />

            </button>

            <p class="flex items-center justify-center gap-1.5 text-center text-xs text-gray-400">

                <x-tabler-shield-check size="13" />

                Your payment and details are encrypted and secure

            </p>

            <div data-checkout-error class="hidden items-start gap-2 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"></div>

        </div>

        {{-- RIGHT COLUMN — ORDER SUMMARY --}}

        <aside class="h-fit rounded-2xl border border-gray-200 bg-white p-5 shadow-sm lg:sticky lg:top-24">

            <div class="flex items-center justify-between">

                <p class="text-lg font-bold text-gray-900">Order summary</p>

                <a href="{{ route('cart') }}" class="text-sm font-medium text-blue-600 hover:underline">Edit cart</a>

            </div>

            {{--

                Populated from GET /api/v1/cart/checkout/preview — this route

                matches your real cart/order preview endpoint. Shows a skeleton

                while loading; on failure shows a retry button rather than a

                static "could not load" dead end.

            --}}

            <div data-checkout-summary class="mt-5 space-y-3 text-sm" aria-live="polite">

                <div class="animate-pulse space-y-3">

                    <div class="h-14 rounded-lg bg-gray-100"></div>

                    <div class="h-14 rounded-lg bg-gray-100"></div>

                    <div class="h-4 rounded bg-gray-100"></div>

                    <div class="h-4 rounded bg-gray-100"></div>

                </div>

            </div>

            <div class="mt-5 flex items-start gap-3 rounded-xl bg-blue-50 p-4">

                <x-tabler-shield-check size="18" class="mt-0.5 flex-shrink-0 text-blue-600" />

                <div>

                    <p class="text-sm font-semibold text-blue-900">Secure payment</p>

                    <p class="mt-0.5 text-xs leading-5 text-blue-700">Your payment is processed securely by Selcom.</p>

                </div>

            </div>

            <div class="mt-5 grid grid-cols-3 gap-3 border-t border-gray-100 pt-5 text-center">

                <div>

                    <x-tabler-truck size="20" class="mx-auto text-gray-400" />

                    <p class="mt-1.5 text-[11px] font-medium text-gray-500">Fast delivery</p>

                </div>

                <div>

                    <x-tabler-shield-check size="20" class="mx-auto text-gray-400" />

                    <p class="mt-1.5 text-[11px] font-medium text-gray-500">Secure payment</p>

                </div>

                <div>

                    <x-tabler-headset size="20" class="mx-auto text-gray-400" />

                    <p class="mt-1.5 text-[11px] font-medium text-gray-500">Support 24/7</p>

                </div>

            </div>

        </aside>

    </div>

</div>

{{-- ── Address choice modal (location vs manual) ─────────────────── --}}

<div data-address-choice-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center">

    <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">

        <div class="flex items-center justify-between">

            <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900">

                <x-tabler-map-pin size="18" class="text-blue-600" />

                Add delivery address

            </h2>

            <button type="button" data-close-address-choice class="text-gray-400 hover:text-gray-700">

                <x-tabler-x size="20" />

            </button>

        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">

            <button type="button" data-address-tab="location" class="rounded-xl border-2 border-blue-600 bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white">Use my location</button>

            <button type="button" data-address-tab="manual" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Enter manually</button>

        </div>

        {{-- Location panel --}}

        <div data-address-panel="location" class="mt-5">
            <div class="relative overflow-hidden rounded-xl border border-gray-200 bg-gray-100">
                <div class="p-3">
                    <div class="relative">
                        <x-tabler-search size="17" class="pointer-events-none absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" />
                        <input id="checkout-map-search" data-map-search type="search" autocomplete="off"
                               class="field w-full pl-10" placeholder="Search for an address or place">
                        <div data-map-search-results class="absolute left-3 right-3 top-[calc(100%+6px)] z-30 hidden max-h-52 overflow-auto rounded-xl border border-gray-200 bg-white shadow-xl"></div>
                    </div>
                </div>

                <div data-mapbox-container class="h-72 w-full"></div>

                <div class="absolute bottom-4 left-4 right-4 z-10 flex gap-2">
                    <button type="button" data-request-location
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-gray-800 shadow-lg ring-1 ring-black/5 hover:bg-gray-50">
                        <x-tabler-current-location size="16" />
                        Use my current location
                    </button>
                    <button type="button" data-map-confirm disabled
                            class="inline-flex flex-1 items-center justify-center gap-2 rounded-full bg-gray-950 px-4 py-2.5 text-sm font-semibold text-white shadow-lg hover:bg-gray-800 disabled:cursor-not-allowed disabled:opacity-50">
                        <x-tabler-check size="16" />
                        Confirm location
                    </button>
                </div>
            </div>

            <div class="mt-3 rounded-xl border border-gray-200 bg-gray-50 p-3">
                <p class="text-xs font-semibold uppercase tracking-wide text-gray-400">Selected location</p>
                <p data-map-address class="mt-1 text-sm text-gray-700">Search, click the map, drag the pin, or use your current location.</p>
            </div>
            <p data-map-error class="mt-3 hidden text-sm text-red-600"></p>
        </div>

        {{-- Manual entry panel --}}

        <form data-address-form data-address-panel="manual" class="mt-5 hidden grid-cols-2 gap-3">

            <input name="recipient_name" class="field w-full" placeholder="Recipient name" required>

            <input name="phone" class="field w-full" placeholder="Phone number" required>

            <input name="region" class="field w-full" placeholder="Region" required>

            <input name="district" class="field w-full" placeholder="District" required>

            <input name="ward" class="field w-full" placeholder="Ward">

            <input name="street" class="field w-full col-span-2" placeholder="Street / house address" required>

            <button class="button-dark col-span-2 py-3">Save address</button>

        </form>

    </div>

</div>

{{-- ── Order note modal ────────────────────────────────────────────── --}}

<div data-note-modal class="fixed inset-0 z-50 hidden items-end justify-center bg-black/40 sm:items-center">

    <div class="w-full max-w-md rounded-t-2xl bg-white p-6 shadow-xl sm:rounded-2xl">

        <div class="flex items-center justify-between">

            <h2 class="flex items-center gap-2 text-lg font-semibold text-gray-900">

                <x-tabler-file-text size="18" class="text-blue-600" />

                Add order note

            </h2>

            <button type="button" data-close-note-modal class="text-gray-400 hover:text-gray-700">

                <x-tabler-x size="20" />

            </button>

        </div>

        <form data-note-form class="mt-5">

            <textarea name="note" maxlength="250" data-note-textarea class="field min-h-32 w-full resize-y" placeholder="e.g. Call when you arrive, gate code, landmark, etc."></textarea>

            <p class="mt-1 text-right text-xs text-gray-400"><span data-note-count>0</span>/250</p>

            <div class="mt-4 flex gap-3">

                <button type="button" data-close-note-modal class="flex-1 rounded-xl border border-gray-200 px-5 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>

                <button type="submit" class="button-dark flex-1 py-3">Save note</button>

            </div>

        </form>

    </div>

</div>

@endsection

@push('scripts')

<script nonce="{{ Vite::cspNonce() }}">

(function () {

    // ── Small helpers ────────────────────────────────────────────

    function el(sel, root) { return (root || document).querySelector(sel); }

    function fmtTZS(n) { return 'TZS ' + Number(n || 0).toLocaleString('en-TZ'); }

    function escapeHtml(value) {

        return String(value ?? '')

            .replace(/&/g, '&amp;')

            .replace(/\</g, '&lt;')

            .replace(/>/g, '&gt;')

            .replace(/"/g, '&quot;')

            .replace(/'/g, '&#039;');

    }

    // Checkout requires an authenticated user (preview & order creation are

    // guarded by auth:sanctum). Redirect guests the same way the old

    // store.js wiring did, instead of showing endless retry states.

    const signedIn = document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') === '1';

    if (!signedIn) {

        window.location.href = '/login';

        return;

    }

    let state = {

        addressId: null,

        note: '',

        paymentMethod: null,

        total: null,

    };

    // ── Boot the two critical data loads FIRST and independently.

    //    Previously these were the last two lines of this file — if

    //    anything earlier in the script threw synchronously, execution

    //    never reached them and both sections stayed frozen on their

    //    initial skeleton forever (exactly what you saw: no retry button

    //    ever appeared, because the fetch never even started). Function

    //    declarations are hoisted, so calling them here works even

    //    though they're defined further down this file. Each is wrapped

    //    so a problem in one can never block the other. ──

    try { loadSummary(); } catch (e) { console.error('loadSummary failed to start:', e); }

    try { bindPaymentMethods(); } catch (e) { console.error('bindPaymentMethods failed to start:', e); }

    try { loadDefaultAddress(); } catch (e) { console.error('loadDefaultAddress failed to start:', e); }

    // ── Contact info (assumes an authenticated user is available server-side;

    //    swap this for however your app already exposes the current user to JS,

    //    e.g. a data attribute on <body> or an inline @json(auth()->user())) ──

    try {

    (function loadContact() {

        const target = el('[data-checkout-contact]');

        if (!target) return; // never write into a null element

        try {

            const renderUser = (user) => {
                if (user) {
                    target.textContent =
                        [user.name, user.phone, user.email].filter(Boolean).join(' · ') ||
                        'Contact details';
                } else {
                    target.textContent = 'Contact details unavailable';
                }
            };

            if (window.KP_USER) {
                renderUser(window.KP_USER);
            } else {
                fetch('/api/v1/auth/me', { headers: { Accept: 'application/json' } })
                    .then(res => res.ok ? res.json() : null)
                    .then(payload => renderUser(payload?.data ?? payload?.user ?? null))
                    .catch(() => renderUser(null));
            }

        } catch (e) {

            console.error('Failed to render contact info:', e);

        }

    })();

    } catch (e) { console.error('Contact info block failed:', e); }

    // ── Order summary (fetched, with retry — never a dead-end message) ──

    function renderSummarySkeleton(root) {

        root.innerHTML = `

            <div class="animate-pulse space-y-3">

                <div class="h-14 rounded-lg bg-gray-100"></div>

                <div class="h-14 rounded-lg bg-gray-100"></div>

                <div class="h-4 rounded bg-gray-100"></div>

                <div class="h-4 rounded bg-gray-100"></div>

            </div>`;

    }

    function renderSummaryError(root, message) {

        root.innerHTML = `

            <div class="flex flex-col items-center gap-3 py-6 text-center">

                <p class="text-sm text-gray-500">${escapeHtml(message || 'Something went wrong loading your order summary.')}</p>

                <button type="button" data-retry-summary class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Try again</button>

            </div>`;

        const retry = root.querySelector('[data-retry-summary]');

        if (retry) retry.addEventListener('click', loadSummary);

    }

    function renderSummary(root, data) {

        const items = Array.isArray(data.items) ? data.items : [];

        const rows = items.map(item => `

            <div class="flex items-start gap-3">

                <img src="${escapeHtml(item.image_url || item.image || '')}" alt="${escapeHtml(item.name || '')}" class="h-14 w-14 flex-shrink-0 rounded-lg bg-gray-100 object-cover">

                <div class="min-w-0 flex-1">

                    <p class="truncate text-sm font-semibold text-gray-900">${escapeHtml(item.name || '')}</p>

                    <p class="text-xs text-gray-500">${escapeHtml([item.size, item.color].filter(Boolean).join(' · '))}</p>

                    <p class="text-xs text-gray-400">Qty: ${item.quantity || 1}</p>

                </div>

                <p class="flex-shrink-0 text-sm font-semibold text-gray-900">${fmtTZS(item.line_total)}</p>

            </div>`).join('');

        root.innerHTML = `

            <div class="space-y-4">${rows}</div>

            <div class="mt-4 space-y-2 border-t border-gray-100 pt-4">

                <div class="flex justify-between text-gray-600"><span>Subtotal</span><span>${fmtTZS(data.subtotal)}</span></div>

                <div class="flex justify-between text-gray-600"><span>Delivery</span><span>${fmtTZS(data.delivery_fee ?? data.delivery)}</span></div>

                <div class="mt-2 flex justify-between border-t border-gray-100 pt-3 text-base font-bold text-gray-900"><span>Total</span><span>${fmtTZS(data.total)}</span></div>

            </div>`;

    }

    async function loadSummary() {

        const root = el('[data-checkout-summary]');

        if (!root) return;

        renderSummarySkeleton(root);

        try {

            const res = await fetch('/api/v1/cart/checkout/preview', { headers: { Accept: 'application/json' } });

            if (!res.ok) throw new Error(`Summary request failed (${res.status})`);

            const payload = await res.json();

            const data = payload?.data ?? payload;

            state.total = Number(data.total || 0);

            renderSummary(root, data);

            updatePayButton();

        } catch (e) {

            console.error('Failed to load checkout summary:', e);

            renderSummaryError(root);

        }

    }

    // ── Payment methods (static cards rendered server-side) ────────

    function bindPaymentMethods() {

        const root = el('[data-checkout-payment-methods]');

        if (!root) return;

        const applySelection = (card) => {

            root.querySelectorAll('[data-payment-card]').forEach(c => {

                const selected = c === card;

                c.classList.toggle('border-blue-600', selected);

                c.classList.toggle('border-gray-200', !selected);

            });

            state.paymentMethod = card?.dataset.method ?? null;

            updatePayButton();

        };

        root.querySelectorAll('[data-payment-card]').forEach(card => {

            card.addEventListener('click', () => applySelection(card));

        });

        const checked = root.querySelector('input[name="payment_method"]:checked')?.closest('[data-payment-card]');

        applySelection(checked || root.querySelector('[data-payment-card]'));

    }

    // ── Pre-select the customer's default shipping address (if any) ──

    async function loadDefaultAddress() {

        try {

            const res = await fetch('/api/v1/addresses', { headers: { Accept: 'application/json' } });

            if (!res.ok) return;

            const payload = await res.json();

            const addresses = payload?.data ?? [];

            const def = addresses.find(a => a.is_default) || addresses[0];

            if (!def) return;

            state.addressId = Number(def.id);

            const summary = el('[data-checkout-address-summary]');

            if (summary) {

                summary.textContent = formatAddressSummary(def);

            }

            el('[data-address-required-badge]')?.classList.add('hidden');

            updatePayButton();

        } catch (e) {

            console.error('Failed to load default address:', e);

        }

    }

    // De-duplicates parts so a street that's also stored as the region/district
    // (bad legacy address data) doesn't render twice, e.g. "X · X".
    function formatAddressSummary(address) {

        const seen = new Set();

        return [
            address?.recipient_name,
            [address?.region, address?.district, address?.ward].filter(Boolean).join(', '),
            address?.street,
        ]
            .filter(Boolean)
            .filter(part => {
                const key = part.trim().toLowerCase();
                if (seen.has(key)) return false;
                seen.add(key);
                return true;
            })
            .join(' · ');

    }

    // ── Pay button state (never shows "Pay TZS 0") ─────────────────

    function updatePayButton() {

        const btn = el('[data-place-order]');

        const label = el('[data-place-order-label]');

        const arrow = el('[data-place-order-arrow]');

        if (!btn || !label) return; // guarded — never assume these exist

        if (state.total === null) {

            label.textContent = 'Calculating total…';

            btn.disabled = true;

            arrow?.classList.add('hidden');

            return;

        }

        if (!state.addressId) {

            label.textContent = 'Add a delivery address to continue';

            btn.disabled = true;

            arrow?.classList.add('hidden');

            return;

        }

        label.textContent = `Pay ${fmtTZS(state.total)}`;

        arrow?.classList.remove('hidden');

        btn.disabled = !state.paymentMethod;

    }

    // ── Guarded submit — rapid clicks cannot fire more than one request ──

    try {

    let submitting = false;

    el('[data-place-order]')?.addEventListener('click', async () => {

        if (submitting) return;

        const btn = el('[data-place-order]');

        const label = el('[data-place-order-label]');

        if (!btn || !label || btn.disabled) return;

        submitting = true;

        btn.disabled = true;

        const original = label.textContent;

        label.textContent = 'Placing order…';

        const errorBox = el('[data-checkout-error]');

        errorBox?.classList.add('hidden');

        try {

            const res = await fetch('/api/v1/checkout', {

                method: 'POST',

                headers: {

                    'Content-Type': 'application/json',

                    'Accept': 'application/json',

                    'Idempotency-Key': `kp-${Date.now()}-${Math.random().toString(36).slice(2, 18)}`,

                },

                body: JSON.stringify({

                    address_id: state.addressId || null,

                    notes: state.note || null,

                    payment_method: state.paymentMethod || null,

                }),

            });

            const payload = await res.json().catch(() => null);

            if (!res.ok) throw new Error(payload?.message || `Order failed (${res.status})`);

            const data = payload?.data ?? payload;

            const gatewayUrl = data?.payment?.[0]?.payment_gateway_url;

            if (gatewayUrl) {

                // Only follow an https (or same-origin) payment URL; never javascript:/data: URLs.

                let target = null;

                try {

                    const u = new URL(gatewayUrl, window.location.origin);

                    if (u.protocol === 'https:' || u.origin === window.location.origin) target = u.href;

                } catch (_) { /* ignore malformed URL */ }

                if (target) {

                    window.location.href = target;

                    return;

                }

            }

            if (data?.order_number) {

                window.location.href = `/orders/${encodeURIComponent(data.order_number)}`;

            }

        } catch (e) {

            console.error('Failed to place order:', e);

            if (errorBox) {

                errorBox.textContent = e.message || 'Something went wrong placing your order. Please try again.';

                errorBox.classList.remove('hidden');

                errorBox.classList.add('flex');

            }

            submitting = false;

            btn.disabled = false;

            label.textContent = original;

        }

    });

    } catch (e) { console.error('Submit handler failed to bind:', e); }

    // ── Address modal ────────────────────────────────────────────

    try {

    const addressModal = el('[data-address-choice-modal]');

    document.querySelectorAll('[data-open-address-modal]').forEach(b => b.addEventListener('click', () => {
        addressModal?.classList.remove('hidden');
        addressModal?.classList.add('flex');
        document.querySelector('[data-address-tab="location"]')?.click();
        openLocationPanel();
    }));

    document.querySelectorAll('[data-close-address-choice]').forEach(b => b.addEventListener('click', () => {

        addressModal?.classList.add('hidden');

        addressModal?.classList.remove('flex');

    }));

    document.querySelectorAll('[data-address-tab]').forEach(tab => {

        tab.addEventListener('click', () => {

            const target = tab.dataset.addressTab;

            document.querySelectorAll('[data-address-tab]').forEach(t => {

                const active = t === tab;

                t.classList.toggle('bg-blue-600', active);

                t.classList.toggle('text-white', active);

                t.classList.toggle('border-blue-600', active);

                t.classList.toggle('border-gray-200', !active);

                t.classList.toggle('text-gray-600', !active);

            });

            document.querySelectorAll('[data-address-panel]').forEach(p => {

                p.classList.toggle('hidden', p.dataset.addressPanel !== target);

                if (p.dataset.addressPanel === target && target === 'manual') {

                    p.classList.add('grid');

                }

            });

        });

    });

    // Location permission is requested only after the customer explicitly
    // clicks the location button.
    function openAddressModal() {
        const modal = el('[data-address-choice-modal]');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
    }

    function openManualAddressPanel() {
        const tab = el('[data-address-tab="manual"]');
        tab?.click();
        const form = el('[data-address-form]');
        form?.classList.remove('hidden');
        form?.classList.add('grid');
    }

    function setAddressField(name, value) {
        const input = el(`[data-address-form] [name="${name}"]`);
        if (input && value) input.value = value;
    }

    // ── Mapbox location picker ─────────────────────────────────────
    const MAPBOX_TOKEN = @json(config('services.mapbox.token'));

    let mapboxMap = null;
    let mapboxMarker = null;
    let selectedCoordinates = null;
    let selectedMapAddress = null;
    let mapboxLoad = null;
    let searchTimer = null;

    function setMapError(message) {
        const box = el('[data-map-error]');
        if (!box) return;
        box.textContent = message || '';
        box.classList.toggle('hidden', !message);
    }

    function setMapAddress(message) {
        const box = el('[data-map-address]');
        if (box) box.textContent = message || '';
    }

    function loadMapbox() {
        if (window.mapboxgl) return Promise.resolve();
        if (mapboxLoad) return mapboxLoad;

        if (!MAPBOX_TOKEN) {
            return Promise.reject(new Error('MAPBOX_ACCESS_TOKEN is missing from .env.'));
        }

        mapboxLoad = new Promise((resolve, reject) => {
            if (!document.querySelector('[data-mapbox-css]')) {
                const css = document.createElement('link');
                css.rel = 'stylesheet';
                css.href = 'https://api.mapbox.com/mapbox-gl-js/v3.15.0/mapbox-gl.css';
                css.dataset.mapboxCss = '1';
                document.head.appendChild(css);
            }

            const script = document.createElement('script');
            script.src = 'https://api.mapbox.com/mapbox-gl-js/v3.15.0/mapbox-gl.js';
            script.async = true;
            script.dataset.mapboxJs = '1';
            script.onload = resolve;
            script.onerror = () => reject(new Error('Could not load Mapbox.'));
            document.head.appendChild(script);
        });

        return mapboxLoad;
    }

    function addressFromFeature(feature) {
        const c = feature?.properties?.context || {};
        const pick = (...keys) => keys.map(k => c[k]?.name).find(Boolean) || '';

        const street = feature?.properties?.address || feature?.properties?.name || '';
        const ward = pick('locality', 'neighborhood');
        const district = pick('district', 'place', 'locality');
        const region = pick('region');

        return {
            recipient_name: window.KP_USER?.name || '',
            phone: window.KP_USER?.phone || '',
            region,
            district,
            ward,
            street,
            formatted: feature?.properties?.full_address ||
                feature?.properties?.place_formatted ||
                [street, ward, district, region].filter(Boolean).join(', ')
        };
    }

    function applyMapSelection(feature, lng, lat) {
        selectedCoordinates = { longitude: Number(lng), latitude: Number(lat) };
        selectedMapAddress = addressFromFeature(feature);

        mapboxMarker?.setLngLat([lng, lat]);
        setMapAddress(selectedMapAddress.formatted || 'Location selected.');
        el('[data-map-confirm]')?.removeAttribute('disabled');
        setMapError('');
    }

    async function reverseGeocode(lat, lng) {
        const url = new URL('https://api.mapbox.com/search/geocode/v6/reverse');
        url.searchParams.set('latitude', lat);
        url.searchParams.set('longitude', lng);
        url.searchParams.set('limit', '1');
        url.searchParams.set('language', 'en');
        url.searchParams.set('access_token', MAPBOX_TOKEN);

        const res = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!res.ok) throw new Error(`Mapbox address lookup failed (${res.status}).`);

        const payload = await res.json();
        if (!payload?.features?.[0]) throw new Error('No readable address was found here.');
        return payload.features[0];
    }

    async function chooseCoordinates(lng, lat, fly = true) {
        try {
            setMapError('');
            setMapAddress('Finding address…');

            const feature = await reverseGeocode(lat, lng);

            if (fly) {
                mapboxMap?.flyTo({ center: [lng, lat], zoom: 16, essential: true });
            }

            applyMapSelection(feature, lng, lat);
        } catch (error) {
            console.error(error);
            el('[data-map-confirm]')?.setAttribute('disabled', '');
            setMapAddress('Location found, but no address could be resolved.');
            setMapError(error.message || 'Could not resolve this location.');
        }
    }

    async function initMapbox() {
        await loadMapbox();

        if (mapboxMap) {
            mapboxMap.resize();
            return;
        }

        mapboxgl.accessToken = MAPBOX_TOKEN;

        mapboxMap = new mapboxgl.Map({
            container: el('[data-mapbox-container]'),
            style: 'mapbox://styles/mapbox/streets-v12',
            center: [39.2083, -6.7924],
            zoom: 11
        });

        mapboxMap.addControl(new mapboxgl.NavigationControl(), 'top-right');

        mapboxMarker = new mapboxgl.Marker({ draggable: true })
            .setLngLat([39.2083, -6.7924])
            .addTo(mapboxMap);

        mapboxMarker.on('dragend', async () => {
            const p = mapboxMarker.getLngLat();
            await chooseCoordinates(p.lng, p.lat);
        });

        mapboxMap.on('click', async (event) => {
            mapboxMarker.setLngLat(event.lngLat);
            await chooseCoordinates(event.lngLat.lng, event.lngLat.lat);
        });

        mapboxMap.once('load', () => mapboxMap.resize());
    }

    async function requestCurrentLocation() {
        if (!navigator.geolocation) {
            setMapError('Location is not supported on this device. Search for your address instead.');
            return;
        }

        const button = el('[data-request-location]');
        const original = button?.innerHTML;

        if (button) {
            button.disabled = true;
            button.textContent = 'Finding location…';
        }

        navigator.geolocation.getCurrentPosition(
            async position => {
                try {
                    await initMapbox();
                    await chooseCoordinates(
                        position.coords.longitude,
                        position.coords.latitude,
                        true
                    );
                } catch (error) {
                    setMapError(error.message || 'Could not load the map.');
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.innerHTML = original || 'Use my current location';
                    }
                }
            },
            error => {
                setMapError(
                    error.code === 1
                        ? 'Location permission was denied. Search for your address or drag the pin.'
                        : 'Could not get your current location. Search for your address or drag the pin.'
                );

                if (button) {
                    button.disabled = false;
                    button.innerHTML = original || 'Use my current location';
                }
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 60000 }
        );
    }

    async function openLocationPanel() {
        try {
            await initMapbox();
            requestAnimationFrame(() => mapboxMap?.resize());
        } catch (error) {
            setMapError(error.message || 'Could not load Mapbox.');
        }
    }

    function confirmMapLocation() {
        if (!selectedMapAddress) {
            setMapError('Select a location on the map first.');
            return;
        }

        setAddressField('recipient_name', selectedMapAddress.recipient_name);
        setAddressField('phone', selectedMapAddress.phone);
        setAddressField('region', selectedMapAddress.region);
        setAddressField('district', selectedMapAddress.district);
        setAddressField('ward', selectedMapAddress.ward);
        setAddressField('street', selectedMapAddress.street || selectedMapAddress.formatted);

        openManualAddressPanel();
    }

    async function searchMapbox(query) {
        const url = new URL('https://api.mapbox.com/search/geocode/v6/forward');
        url.searchParams.set('q', query);
        url.searchParams.set('country', 'TZ');
        url.searchParams.set('language', 'en');
        url.searchParams.set('limit', '5');
        url.searchParams.set('access_token', MAPBOX_TOKEN);

        const res = await fetch(url, { headers: { Accept: 'application/json' }, cache: 'no-store' });
        if (!res.ok) throw new Error(`Mapbox search failed (${res.status}).`);

        const payload = await res.json();
        return payload?.features || [];
    }

    el('[data-map-search]')?.addEventListener('input', event => {
        clearTimeout(searchTimer);

        const query = event.target.value.trim();
        const box = el('[data-map-search-results]');
        if (!box) return;

        if (query.length < 3) {
            box.innerHTML = '';
            box.classList.add('hidden');
            return;
        }

        searchTimer = setTimeout(async () => {
            try {
                const features = await searchMapbox(query);

                box.innerHTML = features.map((feature, index) => `
                    <button type="button" data-map-result="${index}"
                        class="block w-full border-b border-gray-100 px-3 py-3 text-left text-sm text-gray-700 hover:bg-gray-50">
                        ${escapeHtml(feature?.properties?.full_address || feature?.properties?.place_formatted || feature?.properties?.name || 'Location')}
                    </button>
                `).join('');

                box.classList.toggle('hidden', !features.length);

                box.querySelectorAll('[data-map-result]').forEach(button => {
                    button.addEventListener('click', async () => {
                        const feature = features[Number(button.dataset.mapResult)];
                        const coords = feature?.geometry?.coordinates;
                        if (!Array.isArray(coords)) return;

                        await initMapbox();
                        mapboxMap.flyTo({ center: coords, zoom: 16, essential: true });
                        mapboxMarker.setLngLat(coords);
                        applyMapSelection(feature, coords[0], coords[1]);
                        box.classList.add('hidden');
                    });
                });
            } catch (error) {
                box.classList.add('hidden');
                setMapError(error.message || 'Address search failed.');
            }
        }, 350);
    });

    el('[data-map-confirm]')?.addEventListener('click', confirmMapLocation);

    // Opening the checkout never requests location permission.
    // "Use my current location" jumps straight to the browser's geolocation
    // prompt once the map is ready; "Add delivery address" just opens the
    // modal and lets the customer search, click the map, or enter manually.
    // Previously both buttons ran the exact same three lines below and were
    // functionally identical — confusing since they read as different actions.
    document.querySelectorAll('[data-use-current-location]')
        .forEach(b => b.addEventListener('click', async () => {
            openAddressModal();
            document.querySelector('[data-address-tab="location"]')?.click();
            await openLocationPanel();
            requestCurrentLocation();
        }));

    el('[data-request-location]')?.addEventListener('click', requestCurrentLocation);

    el('[data-address-form]')?.addEventListener('submit', async function (e) {

        e.preventDefault();

        const saveBtn = this.querySelector('button[type="submit"]');

        const original = saveBtn?.textContent;

        if (saveBtn) { saveBtn.disabled = true; saveBtn.textContent = 'Saving…'; }

        const errorBox = el('[data-checkout-error]');

        errorBox?.classList.add('hidden');

        try {

            const res = await fetch('/api/v1/addresses', {

                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                },

                body: JSON.stringify({

                    type: 'shipping',

                    recipient_name: this.recipient_name.value.trim(),

                    phone: this.phone.value.trim(),

                    region: this.region.value.trim(),

                    district: this.district.value.trim(),

                    ward: this.ward.value.trim() || null,

                    street: this.street.value.trim(),

                    is_default: true,

                }),

            });

            const payload = await res.json().catch(() => null);

            if (!res.ok) throw new Error(payload?.message || `Failed to save address (${res.status})`);

            const data = payload?.data ?? payload;

            state.addressId = Number(data?.id);

            const summary = el('[data-checkout-address-summary]');

            if (summary) {

                summary.textContent = formatAddressSummary(data);

            }

            el('[data-address-required-badge]')?.classList.add('hidden');

            updatePayButton();

            addressModal?.classList.add('hidden');

            addressModal?.classList.remove('flex');

        } catch (err) {

            console.error('Failed to save address:', err);

            if (errorBox) {

                errorBox.textContent = err.message || 'Something went wrong saving your address. Please try again.';

                errorBox.classList.remove('hidden');

                errorBox.classList.add('flex');

            }

        } finally {

            if (saveBtn) { saveBtn.disabled = false; saveBtn.textContent = original; }

        }

    });

    } catch (e) { console.error('Address modal failed to bind:', e); }

    // ── Order note modal ────────────────────────────────────────────

    try {

    const noteModal = el('[data-note-modal]');

    document.querySelectorAll('[data-open-note-modal]').forEach(b => b.addEventListener('click', () => {

        noteModal?.classList.remove('hidden');

        noteModal?.classList.add('flex');

    }));

    document.querySelectorAll('[data-close-note-modal]').forEach(b => b.addEventListener('click', () => {

        noteModal?.classList.add('hidden');

        noteModal?.classList.remove('flex');

    }));

    const noteTextarea = el('[data-note-textarea]');

    const noteCount = el('[data-note-count]');

    noteTextarea?.addEventListener('input', () => {

        if (noteCount) noteCount.textContent = noteTextarea.value.length;

    });

    el('[data-note-form]')?.addEventListener('submit', function (e) {

        e.preventDefault();

        state.note = noteTextarea?.value || '';

        const summary = el('[data-checkout-note-summary]');

        if (summary) {

            summary.classList.toggle('hidden', !state.note);

            summary.classList.toggle('flex', !!state.note);

            summary.textContent = state.note ? '📝 ' + state.note.slice(0, 60) + (state.note.length > 60 ? '…' : '') : '';

        }

        noteModal?.classList.add('hidden');

        noteModal?.classList.remove('flex');

    });

    } catch (e) { console.error('Note modal failed to bind:', e); }

})();

</script>

@endpush
