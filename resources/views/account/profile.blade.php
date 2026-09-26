@extends('layouts.app')
@section('content')
<div data-account-profile class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')
        <section class="min-w-0">
            <div class="border-b border-emerald-950/10 pb-6">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                <h1 class="mt-2 text-3xl font-bold tracking-tight text-black">Profile</h1>
                <p class="mt-2 text-sm text-black">Keep your basic account information up to date.</p>
            </div>
            <form data-profile-form class="mt-7 max-w-2xl space-y-5 rounded-2xl border border-emerald-950/12 bg-white p-6 shadow-sm sm:p-7">
                <label class="block">
                    <span class="text-sm font-medium text-black">Full name</span>
                    <input data-profile-name-input name="name" class="field mt-2 w-full" required minlength="2" maxlength="120" autocomplete="name">
                </label>
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-black">Phone number</p>
                        <button type="button" data-phone-change class="text-xs font-semibold text-emerald-700 underline underline-offset-4">Change</button>
                    </div>
                    <p data-profile-phone class="mt-2 rounded-xl bg-emerald-50/50 px-4 py-3 text-sm text-black">—</p>
                    <div data-phone-editor class="hidden mt-3 space-y-3 rounded-xl bg-emerald-50/50 p-4">
                        <div data-phone-step-new>
                            <label class="text-xs font-semibold text-black" for="profile-new-phone">New phone number</label>
                            <input id="profile-new-phone" data-phone-new-input type="tel" class="field mt-2 w-full" placeholder="07XXXXXXXX" autocomplete="tel">
                            <button type="button" data-phone-request class="button-dark mt-3 px-5 py-2.5 text-sm">Send verification code</button>
                        </div>
                        <div data-phone-step-code class="hidden">
                            <p class="text-xs text-black">We sent a 6-digit code to <strong data-phone-pending></strong>.</p>
                            <label class="mt-3 block text-xs font-semibold text-black" for="profile-phone-code">Verification code</label>
                            <input id="profile-phone-code" data-phone-code-input class="field mt-2 w-full" maxlength="6" inputmode="numeric" placeholder="000000" autocomplete="one-time-code">
                            <button type="button" data-phone-verify class="button-dark mt-3 px-5 py-2.5 text-sm">Verify &amp; update number</button>
                        </div>
                        <p data-phone-error class="hidden rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-700" role="alert"></p>
                    </div>
                    <p class="mt-2 text-xs text-black">Changing your number requires a verification code sent to the new number.</p>
                </div>
                <div>
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-medium text-black">Email address</p>
                        <button type="button" data-email-change class="text-xs font-semibold text-emerald-700 underline underline-offset-4">Change</button>
                    </div>
                    <p data-profile-email class="mt-2 rounded-xl bg-emerald-50/50 px-4 py-3 text-sm text-black">—</p>
                    <div data-email-editor class="hidden mt-3 space-y-3 rounded-xl bg-emerald-50/50 p-4">
                        <div data-email-step-new>
                            <label class="text-xs font-semibold text-black" for="profile-new-email">New email address</label>
                            <input id="profile-new-email" data-email-new-input type="email" class="field mt-2 w-full" placeholder="you@example.com" autocomplete="email">
                            <button type="button" data-email-request class="button-dark mt-3 px-5 py-2.5 text-sm">Send verification code</button>
                            <p class="mt-2 text-xs text-black">The code goes to your verified phone number.</p>
                        </div>
                        <div data-email-step-code class="hidden">
                            <p class="text-xs text-black">We sent a 6-digit code to your phone to confirm <strong data-email-pending></strong>.</p>
                            <label class="mt-3 block text-xs font-semibold text-black" for="profile-email-code">Verification code</label>
                            <input id="profile-email-code" data-email-code-input class="field mt-2 w-full" maxlength="6" inputmode="numeric" placeholder="000000" autocomplete="one-time-code">
                            <button type="button" data-email-verify class="button-dark mt-3 px-5 py-2.5 text-sm">Verify &amp; update email</button>
                        </div>
                        <p data-email-error class="hidden rounded-xl border border-rose-100 bg-rose-50 px-4 py-3 text-xs text-rose-700" role="alert"></p>
                    </div>
                    <p class="mt-2 text-xs text-black">Changing your email requires a verification code sent to your phone.</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-black">Account status</p>
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                        <span data-profile-verified class="rounded-full bg-emerald-50 px-3 py-1 text-[11px] font-semibold text-emerald-700">Checking…</span>
                        <span data-profile-member class="rounded-full bg-emerald-50/50 px-3 py-1 text-[11px] font-semibold text-black"></span>
                    </div>
                    <p class="mt-2 text-xs text-black">Manage your password, two-factor authentication and signed-in devices from <a href="{{ route('account.security') }}" class="font-semibold text-emerald-700 underline underline-offset-4">Security &amp; login</a>.</p>
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
    const email = page.querySelector('[data-profile-email]');
    const verified = page.querySelector('[data-profile-verified]');
    const member = page.querySelector('[data-profile-member]');
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
            if (email) email.textContent = me.data?.email || '—';
            if (verified) {
                const isVerified = !!me.data?.phone_verified_at;
                verified.textContent = isVerified ? 'Phone verified' : 'Phone not verified';
            }
            if (member) {
                const joined = me.data?.created_at ? new Date(me.data.created_at) : null;
                member.textContent = joined && !isNaN(joined)
                    ? 'Member since ' + joined.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
                    : '';
                member.style.display = member.textContent ? '' : 'none';
            }
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

    const setupContactEditor = config => {
        const changeBtn = page.querySelector(config.change);
        const editor = page.querySelector(config.editor);
        const stepNew = page.querySelector(config.stepNew);
        const stepCode = page.querySelector(config.stepCode);
        const newInput = page.querySelector(config.newInput);
        const codeInput = page.querySelector(config.codeInput);
        const pending = page.querySelector(config.pending);
        const errBox = page.querySelector(config.error);
        if (!changeBtn || !editor) return;
        const showErr = msg => { errBox.textContent = msg; errBox.classList.remove('hidden'); };
        const clearErr = () => errBox.classList.add('hidden');
        changeBtn.addEventListener('click', () => { clearErr(); editor.classList.toggle('hidden'); });
        page.querySelector(config.request)?.addEventListener('click', async () => {
            clearErr();
            const value = newInput.value.trim();
            if (!value) { showErr('Enter a value first.'); return; }
            try {
                const data = await api(config.requestPath, { method: 'POST', body: JSON.stringify({ [config.field]: value }) });
                const confirmed = data?.data?.[config.field] || value;
                if (pending) pending.textContent = confirmed;
                editor.dataset.value = confirmed;
                stepNew.classList.add('hidden');
                stepCode.classList.remove('hidden');
            } catch (e) { showErr(e.message); }
        });
        page.querySelector(config.verify)?.addEventListener('click', async () => {
            clearErr();
            try {
                const data = await api(config.verifyPath, { method: 'POST', body: JSON.stringify({ [config.field]: editor.dataset.value, code: codeInput.value.trim() }) });
                const user = data.data;
                if (config.field === 'phone') {
                    phone.textContent = user.phone || '—';
                    if (verified) verified.textContent = 'Phone verified';
                    document.querySelectorAll('[data-sidebar-phone]').forEach(n => { n.textContent = user.phone; });
                } else if (email) {
                    email.textContent = user.email || '—';
                }
                editor.classList.add('hidden');
                stepCode.classList.add('hidden');
                stepNew.classList.remove('hidden');
                codeInput.value = '';
                window.dispatchEvent(new CustomEvent('kp:toast', { detail: config.done }));
            } catch (e) { showErr(e.message); }
        });
    };
    setupContactEditor({ change: '[data-phone-change]', editor: '[data-phone-editor]', stepNew: '[data-phone-step-new]', stepCode: '[data-phone-step-code]', newInput: '[data-phone-new-input]', codeInput: '[data-phone-code-input]', pending: '[data-phone-pending]', error: '[data-phone-error]', request: '[data-phone-request]', verify: '[data-phone-verify]', requestPath: '/account/profile/phone/request', verifyPath: '/account/profile/phone/verify', field: 'phone', done: 'Phone number updated.' });
    setupContactEditor({ change: '[data-email-change]', editor: '[data-email-editor]', stepNew: '[data-email-step-new]', stepCode: '[data-email-step-code]', newInput: '[data-email-new-input]', codeInput: '[data-email-code-input]', pending: '[data-email-pending]', error: '[data-email-error]', request: '[data-email-request]', verify: '[data-email-verify]', requestPath: '/account/profile/email/request', verifyPath: '/account/profile/email/verify', field: 'email', done: 'Email address updated.' });
})();
</script>
@endpush
