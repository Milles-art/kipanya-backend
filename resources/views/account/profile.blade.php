@extends('layouts.app')
@section('content')
<div data-account-profile class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[256px_minmax(0,1fr)]">
        @include('components.account-sidebar')
        <section class="min-w-0">
            <div class="border-b border-gray-100 pb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950">Profile</h1>
                <p class="mt-2 text-sm text-gray-500">Keep your basic account information up to date.</p>
            </div>
            <form data-profile-form class="mt-7 max-w-2xl space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
                <label class="block">
                    <span class="text-sm font-medium text-gray-700">Full name</span>
                    <input data-profile-name-input name="name" class="field mt-2 w-full" required minlength="2" maxlength="120" autocomplete="name">
                </label>
                <div>
                    <p class="text-sm font-medium text-gray-700">Phone number</p>
                    <p data-profile-phone class="mt-2 rounded-xl bg-gray-50 px-4 py-3 text-sm text-gray-600">—</p>
                    <p class="mt-2 text-xs text-gray-400">Phone changes require a verified OTP security flow and are not available from this page yet.</p>
                </div>
                <p data-profile-error class="hidden rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-sm text-rose-700" role="alert"></p>
                <button type="submit" class="button-dark inline-flex items-center justify-center px-6">Save changes</button>
            </form>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
    const page = document.querySelector('[data-account-profile]');
    if (!page) return;
    if (document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') !== '1') { location.href = '/login'; return; }
    const form = page.querySelector('[data-profile-form]');
    const input = page.querySelector('[data-profile-name-input]');
    const phone = page.querySelector('[data-profile-phone]');
    const error = page.querySelector('[data-profile-error]');
    const button = form?.querySelector('button[type="submit"]');
    const api = async (path, options = {}) => {
        const response = await fetch(`/api/v1${path}`, { ...options, headers: { Accept: 'application/json', 'Content-Type': 'application/json', ...(options.headers || {}) } });
        const data = await response.json().catch(() => null);
        if (!response.ok) {
            if (response.status === 401) { location.href = '/login'; return null; }
            throw new Error(data?.message || Object.values(data?.errors || {}).flat?.()?.[0] || `Request failed (${response.status})`);
        }
        return data;
    };
    (async () => {
        try {
            const me = await api('/auth/me');
            input.value = me.data?.name || '';
            phone.textContent = me.data?.phone || '—';
        } catch (e) { error.textContent = e.message; error.classList.remove('hidden'); }
    })();
    form?.addEventListener('submit', async e => {
        e.preventDefault();
        error.classList.add('hidden');
        button.disabled = true;
        const old = button.textContent;
        button.textContent = 'Saving…';
        try {
            const data = await api('/account/profile', { method: 'PUT', body: JSON.stringify({ name: input.value.trim() }) });
            const user = data.data;
            document.querySelectorAll('[data-profile-name], [data-sidebar-name]').forEach(n => n.textContent = user.name);
            document.querySelectorAll('[data-account-name]').forEach(n => n.textContent = `Welcome back, ${user.name}`);
            window.dispatchEvent(new CustomEvent('kp:toast', { detail: 'Profile updated.' }));
        } catch (e) { error.textContent = e.message; error.classList.remove('hidden'); }
        finally { button.disabled = false; button.textContent = old; }
    });
})();
</script>
@endpush
