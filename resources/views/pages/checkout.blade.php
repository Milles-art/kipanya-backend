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
                        <a href="{{ route('account.profile') }}" class="text-xs font-medium text-emerald-600 hover:underline">Edit</a>
                    </div>
                </div>
            </section>

            {{-- DELIVERY ADDRESS (compact, collapsed) --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <button type="button" data-open-address-modal class="flex w-full items-start justify-between gap-3 text-left">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-violet-50 text-violet-600">
                            <x-tabler-map-pin size="18" />
                        </span>
                        <div>
                            <p class="flex items-center gap-2 text-base font-bold text-gray-900">
                                Delivery address
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">Optional</span>
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
                    <button type="button" data-open-address-modal class="inline-flex items-center justify-center gap-2 rounded-full bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md">
                        <x-tabler-plus size="16" />
                        Add delivery address
                    </button>
                </div>
            </section>

            {{-- ORDER NOTE (compact, collapsed) --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <button type="button" data-open-note-modal class="flex w-full items-start justify-between gap-3 text-left">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-50 text-emerald-600">
                            <x-tabler-file-text size="18" />
                        </span>
                        <div>
                            <p class="flex items-center gap-2 text-base font-bold text-gray-900">
                                Order note
                                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-[11px] font-medium text-gray-500">Optional</span>
                            </p>
                            <p data-checkout-note-summary class="mt-0.5 text-sm text-gray-500">Add special instructions for your order.</p>
                        </div>
                    </div>
                    <x-tabler-chevron-right size="18" class="mt-1.5 flex-shrink-0 text-gray-300" />
                </button>

                <div data-checkout-note-actions class="mt-4">
                    <button type="button" data-open-note-modal class="inline-flex items-center gap-2 rounded-full bg-emerald-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:-translate-y-0.5 hover:bg-emerald-700 hover:shadow-md">
                        <x-tabler-plus size="16" />
                        Add order note
                    </button>
                </div>
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
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-gray-900 bg-white p-4 transition hover:shadow-sm" data-payment-card data-method="mobile_money">
                        <input type="radio" name="payment_method" value="mobile_money" class="h-4 w-4 accent-gray-900" checked>
                        <span class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-gray-100 text-gray-700">
                            <x-tabler-device-mobile size="18" />
                        </span>
                        <span>
                            <span class="block text-sm font-semibold text-gray-900">Mobile Money</span>
                            <span class="block text-xs text-gray-500">Pay with M-Pesa or Tigopesa</span>
                        </span>
                    </label>
                    <label class="flex cursor-pointer items-center gap-3 rounded-xl border-2 border-gray-200 bg-white p-4 transition hover:shadow-sm" data-payment-card data-method="card">
                        <input type="radio" name="payment_method" value="card" class="h-4 w-4 accent-gray-900">
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
                <a href="{{ route('cart') }}" class="text-sm font-medium text-emerald-600 hover:underline">Edit cart</a>
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
                <x-tabler-map-pin size="18" class="text-violet-600" />
                Add delivery address
            </h2>
            <button type="button" data-close-address-choice class="text-gray-400 hover:text-gray-700">
                <x-tabler-x size="20" />
            </button>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-2">
            <button type="button" data-address-tab="location" class="rounded-xl border-2 border-gray-900 bg-gray-900 px-4 py-2.5 text-sm font-semibold text-white">Use my location</button>
            <button type="button" data-address-tab="manual" class="rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-600 hover:bg-gray-50">Enter manually</button>
        </div>

        {{-- Location panel --}}
        <div data-address-panel="location" class="mt-5">
            <div class="flex h-40 items-center justify-center rounded-xl bg-gray-100 text-sm text-gray-400">
                Map preview
            </div>
            <p class="mt-4 text-sm text-gray-500">Allow KP Wear to access your location to find your address automatically.</p>
            {{--
                Permission is requested only when this button is clicked —
                never on modal open or page load.
            --}}
            <button type="button" data-request-location class="button-dark mt-4 flex w-full items-center justify-center gap-2 py-3">
                <x-tabler-current-location size="16" />
                Use my current location
            </button>
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
                <x-tabler-file-text size="18" class="text-emerald-600" />
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
            const user = window.KP_USER || null; // expected to be set elsewhere in your layout
            if (user) {
                target.textContent = [user.name, user.phone, user.email].filter(Boolean).join(' · ');
            } else {
                target.textContent = 'Sign in to see your contact details';
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
                <p class="text-sm text-gray-500">${message || 'Something went wrong loading your order summary.'}</p>
                <button type="button" data-retry-summary class="rounded-full border border-gray-300 px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Try again</button>
            </div>`;
        const retry = root.querySelector('[data-retry-summary]');
        if (retry) retry.addEventListener('click', loadSummary);
    }

    function renderSummary(root, data) {
        const items = Array.isArray(data.items) ? data.items : [];
        const rows = items.map(item => `
            <div class="flex items-start gap-3">
                <img src="${item.image_url || item.image || ''}" alt="${item.name || ''}" class="h-14 w-14 flex-shrink-0 rounded-lg bg-gray-100 object-cover">
                <div class="min-w-0 flex-1">
                    <p class="truncate text-sm font-semibold text-gray-900">${item.name || ''}</p>
                    <p class="text-xs text-gray-500">${[item.size, item.color].filter(Boolean).join(' · ')}</p>
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
                c.classList.toggle('border-gray-900', selected);
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
                summary.textContent = [def.recipient_name, [def.region, def.district, def.ward].filter(Boolean).join(', '), def.street].filter(Boolean).join(' · ');
            }
        } catch (e) {
            console.error('Failed to load default address:', e);
        }
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
            if (!state.addressId) throw new Error('Add or select a delivery address before placing your order.');

            const res = await fetch('/api/v1/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'Idempotency-Key': `kp-${Date.now()}-${Math.random().toString(36).slice(2, 18)}`,
                },
                body: JSON.stringify({
                    address_id: state.addressId,
                    notes: state.note || null,
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
                t.classList.toggle('bg-gray-900', active);
                t.classList.toggle('text-white', active);
                t.classList.toggle('border-gray-900', active);
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

    // Location permission requested only on explicit click — never on load
    function requestCurrentLocation() {
        if (!('geolocation' in navigator)) {
            alert('Location is not supported on this device — please enter your address manually.');
            return;
        }
        navigator.geolocation.getCurrentPosition(
            (pos) => {
                // No reverse-geocoding endpoint exists yet, so we guide the user
                // to complete the manual form rather than pretending we resolved
                // a full address from coordinates.
                const manual = document.querySelector('[data-address-panel="manual"]');
                el('[data-address-tab="manual"]')?.click();
                manual?.classList.remove('hidden');
                manual?.classList.add('grid');
                window.dispatchEvent(new CustomEvent('kp:toast', { detail: 'Location found — please enter your delivery details below.' }));
            },
            (err) => {
                alert('Could not get your location — please enter your address manually.');
            }
        );
    }

    document.querySelectorAll('[data-use-current-location]').forEach(b => b.addEventListener('click', () => {
        const modal = el('[data-address-choice-modal]');
        modal?.classList.remove('hidden');
        modal?.classList.add('flex');
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
                headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
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
                summary.textContent = [data?.recipient_name, [data?.region, data?.district, data?.ward].filter(Boolean).join(', '), data?.street].filter(Boolean).join(' · ');
            }

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
        if (summary) summary.textContent = state.note ? state.note.slice(0, 60) + (state.note.length > 60 ? '…' : '') : 'Add special instructions for your order.';
        noteModal?.classList.add('hidden');
        noteModal?.classList.remove('flex');
    });
    } catch (e) { console.error('Note modal failed to bind:', e); }

})();
</script>
@endpush
