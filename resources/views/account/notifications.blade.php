@extends('layouts.app')
@section('content')
<div class="mx-auto kp-content px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('components.account-sidebar')

        <div data-notifications-page>
            <div data-notifications-feedback class="mb-5 hidden rounded-xl border px-4 py-3 text-sm"></div>
            <div class="border-b border-emerald-950/12 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-black">
                    <x-tabler-bell size="14" />
                    Preferences
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-black sm:text-3xl">Notifications</h1>
                <p class="mt-2 text-sm text-black">Choose what you hear from us and how.</p>
            </div>

            <div class="mt-6 rounded-2xl border border-emerald-950/12 bg-white shadow-sm">
                <div class="divide-y divide-black">

                    {{-- Order updates - mandatory --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70">
                                <x-tabler-package size="17" class="text-black" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-black">Order updates</p>
                                <p class="mt-0.5 text-sm text-black">Confirmation, shipping, and delivery status for your orders.</p>
                            </div>
                        </div>
                        <span class="mt-1 flex-shrink-0 rounded-full bg-emerald-50/70 px-2.5 py-1 text-[11px] font-medium text-black">Always on</span>
                    </div>

                    {{-- Promotions --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70">
                                <x-tabler-sparkles size="17" class="text-black" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-black">Promotions &amp; new drops</p>
                                <p class="mt-0.5 text-sm text-black">Sales, discount codes, and new collection launches.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_promotions" class="peer sr-only" checked>
                            <div class="h-6 w-11 rounded-full bg-white transition peer-checked:bg-black after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Restock alerts --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70">
                                <x-tabler-heart size="17" class="text-black" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-black">Restock alerts</p>
                                <p class="mt-0.5 text-sm text-black">When a wishlist item you saved comes back in stock.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_restock" class="peer sr-only" checked>
                            <div class="h-6 w-11 rounded-full bg-white transition peer-checked:bg-black after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>

                    {{-- Price drop alerts --}}
                    <div class="flex items-start justify-between gap-4 p-5 sm:p-6">
                        <div class="flex items-start gap-3">
                            <div class="mt-0.5 flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg bg-emerald-50/70">
                                <x-tabler-tag size="17" class="text-black" />
                            </div>
                            <div>
                                <p class="text-sm font-medium text-black">Price drop alerts</p>
                                <p class="mt-0.5 text-sm text-black">When a saved item's price is reduced.</p>
                            </div>
                        </div>
                        <label class="relative mt-1 inline-flex flex-shrink-0 cursor-pointer items-center">
                            <input type="checkbox" name="notify_price_drop" class="peer sr-only">
                            <div class="h-6 w-11 rounded-full bg-white transition peer-checked:bg-black after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:bg-white after:transition after:content-[''] peer-checked:after:translate-x-5"></div>
                        </label>
                    </div>
                </div>
            </div>

            {{-- Delivery channel --}}
            <div class="mt-6 rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                <p class="text-sm font-medium text-black">Send notifications via</p>
                <div class="mt-3 flex flex-wrap gap-2">
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-emerald-950/12 px-4 py-2 text-sm text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                        <input type="checkbox" name="channel_sms" class="hidden" checked>
                        <x-tabler-message size="15" />
                        SMS
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-emerald-950/12 px-4 py-2 text-sm text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                        <input type="checkbox" name="channel_email" class="hidden" checked>
                        <x-tabler-mail size="15" />
                        Email
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 rounded-full border border-emerald-950/12 px-4 py-2 text-sm text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                        <input type="checkbox" name="channel_push" class="hidden">
                        <x-tabler-bell-ringing size="15" />
                        Push
                    </label>
                </div>
            </div>

            <div class="mt-6 flex flex-wrap items-center gap-4">
                <button data-save-notifications type="button" class="button-dark px-6">
                    <x-tabler-device-floppy size="16" />
                    Save preferences
                </button>
                <span data-notifications-saved class="hidden text-sm text-black">Preferences saved to your account.</span>
            </div>

            <p class="mt-5 flex items-start gap-2 text-xs leading-5 text-black">
                <x-tabler-info-circle size="15" class="mt-0.5 shrink-0" />
                Order confirmations and essential service messages remain enabled so you do not miss important updates.
            </p>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
    const page = document.querySelector('[data-notifications-page]');
    if (!page) return;
    if (document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') !== '1') { location.href = '/login'; return; }

    const api = async (path, options = {}) => {
        const response = await fetch(`/api/v1${path}`, {
            ...options,
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(options.headers || {}) },
        });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            if (response.status === 401) { location.href = '/login'; return null; }
            throw new Error(data?.message || Object.values(data?.errors || {})?.flat?.()?.[0] || `Request failed (${response.status})`);
        }
        return data;
    };

    const feedback = page.querySelector('[data-notifications-feedback]');
    const saved = page.querySelector('[data-notifications-saved]');
    const button = page.querySelector('[data-save-notifications]');
    const setFeedback = (message, error = false) => {
        feedback.textContent = message;
        feedback.className = `mb-5 rounded-xl border px-4 py-3 text-sm ${error ? 'border-rose-200 bg-rose-50 text-rose-700' : 'border-emerald-950/12 bg-emerald-50/50 text-black'}`;
        feedback.classList.remove('hidden');
    };

    const set = (name, value) => { const input = page.querySelector(`[name="${name}"]`); if (input) input.checked = !!value; };

    (async () => {
        try {
            const response = await api('/account/preferences');
            const p = response?.data?.notifications || {};
            set('notify_promotions', p.marketing_enabled);
            set('notify_restock', p.restock_enabled);
            set('notify_price_drop', p.price_drop_enabled);
            set('channel_sms', p.sms_enabled);
            set('channel_email', p.email_enabled);
            set('channel_push', p.push_enabled);
        } catch (e) { setFeedback(e.message, true); }
    })();

    button?.addEventListener('click', async () => {
        button.disabled = true;
        try {
            const get = name => !!page.querySelector(`[name="${name}"]`)?.checked;
            await api('/account/preferences/notifications', { method: 'PUT', body: JSON.stringify({
                push_enabled: get('channel_push'), email_enabled: get('channel_email'), sms_enabled: get('channel_sms'), marketing_enabled: get('notify_promotions'), restock_enabled: get('notify_restock'), price_drop_enabled: get('notify_price_drop'),
            }) });
            setFeedback('Notification preferences saved.');
            saved?.classList.remove('hidden');
            window.setTimeout(() => saved?.classList.add('hidden'), 3500);
        } catch (e) { setFeedback(e.message, true); }
        finally { button.disabled = false; }
    });
})();
</script>
@endpush
