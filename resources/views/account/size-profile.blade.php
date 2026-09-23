@extends('layouts.app')
@section('content')
<div class="mx-auto kp-content px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">

        @include('components.account-sidebar')

        <div data-size-profile-page>
            <div class="border-b border-emerald-950/12 pb-6">
                <p class="inline-flex items-center gap-1.5 text-xs font-semibold uppercase tracking-wider text-black">
                    <x-tabler-ruler-2 size="14" />
                    Fit
                </p>
                <h1 class="mt-2 text-2xl font-bold tracking-tight text-black sm:text-3xl">Size profile</h1>
                <p class="mt-2 text-sm text-black">Save your usual sizes so we can pre-select them for you on product pages.</p>
            </div>

            <form data-size-profile-form class="mt-6 space-y-4">

                {{-- Tops --}}
                <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50/70">
                            <x-tabler-shirt size="17" class="text-black" />
                        </div>
                        <p class="text-sm font-medium text-black">Tops</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['XS','S','M','L','XL','XXL'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-emerald-950/12 text-sm font-medium text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                                <input type="radio" name="size_top" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Bottoms --}}
                <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50/70">
                            <x-tabler-hanger size="17" class="text-black" />
                        </div>
                        <p class="text-sm font-medium text-black">Bottoms</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['28','30','32','34','36','38'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-emerald-950/12 text-sm font-medium text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                                <input type="radio" name="size_bottom" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                {{-- Shoes --}}
                <section class="rounded-2xl border border-emerald-950/12 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-emerald-50/70">
                            <x-tabler-shoe size="17" class="text-black" />
                        </div>
                        <p class="text-sm font-medium text-black">Shoes</p>
                    </div>
                    <div class="mt-4 flex flex-wrap gap-2">
                        @foreach(['39','40','41','42','43','44','45'] as $size)
                            <label class="flex h-10 w-14 cursor-pointer items-center justify-center rounded-lg border border-emerald-950/12 text-sm font-medium text-black has-[:checked]:border-black has-[:checked]:bg-black has-[:checked]:text-white">
                                <input type="radio" name="size_shoe" value="{{ $size }}" class="hidden">
                                {{ $size }}
                            </label>
                        @endforeach
                    </div>
                </section>

                <button class="button-dark px-6">Save size profile</button>
            </form>

            <div class="mt-6 flex items-start gap-2.5 rounded-2xl border border-emerald-950/12 bg-emerald-50/50 p-4 text-xs text-black">
                <x-tabler-info-circle size="16" class="mt-0.5 flex-shrink-0 text-black" />
                We'll pre-select these sizes automatically on product pages — you can still change your selection anytime before adding to bag.
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
    const page = document.querySelector('[data-size-profile-page]');
    if (!page) return;
    if (document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') !== '1') { location.href = '/login'; return; }
    const form = page.querySelector('[data-size-profile-form]');
    const button = form?.querySelector('button[type="submit"]');
    const api = async (path, options = {}) => {
        const response = await fetch(`/api/v1${path}`, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(options.headers || {}) } });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            if (response.status === 401) { location.href = '/login'; return null; }
            throw new Error(data?.message || Object.values(data?.errors || {})?.flat?.()?.[0] || `Request failed (${response.status})`);
        }
        return data;
    };
    (async () => {
        try {
            const data = (await api('/account/preferences'))?.data?.size_profile || {};
            ['top','bottom','shoe'].forEach(key => { if (data[key]) form.querySelector(`[name="size_${key}"][value="${CSS.escape(data[key])}"]`)?.click(); });
        } catch (e) { window.dispatchEvent(new CustomEvent('kp:toast', { detail: e.message || 'Unable to save your size profile.' })); }
    })();
    form?.addEventListener('submit', async e => {
        e.preventDefault(); button.disabled = true; const old = button.textContent; button.textContent = 'Saving…';
        try {
            await api('/account/preferences/size-profile', { method: 'PUT', body: JSON.stringify({ top: form.querySelector('[name="size_top"]:checked')?.value || null, bottom: form.querySelector('[name="size_bottom"]:checked')?.value || null, shoe: form.querySelector('[name="size_shoe"]:checked')?.value || null }) });
            button.textContent = 'Saved';
            setTimeout(() => { button.textContent = old; }, 1400);
        } catch (e) { button.textContent = old; window.dispatchEvent(new CustomEvent('kp:toast', { detail: e.message || 'Unable to save your size profile.' })); }
        finally { button.disabled = false; }
    });
})();
</script>
@endpush
