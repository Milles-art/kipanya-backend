@extends('admin.layouts.app', ['title' => 'Two-Factor Authentication', 'heading' => 'Two-Factor Authentication'])

@section('content')
<div class="mx-auto max-w-2xl">
    <x-admin.page-header title="Two-Factor Authentication" subtitle="Add a one-time code from your authenticator app to every administrator sign-in." />

    @if(session('success'))
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-5 rounded-xl border border-sky-200 bg-sky-50 px-4 py-3 text-sm font-medium text-sky-700">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
    @endif

    <x-admin.card class="p-6">
        @if($enabled)
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="kp-admin-display text-lg">Enabled</h3>
                    <p class="mt-1.5 text-xs text-slate-400">Every administrator sign-in for this account now requires a six-digit code.</p>
                </div>
                <x-admin.badge status="green" label="Active" />
            </div>

            <form method="POST" action="{{ route('admin.security.two-factor.disable') }}" data-confirm="Disable two-factor authentication for this account?" class="mt-6 space-y-4">
                @csrf
                <label class="block text-xs uppercase tracking-wider text-slate-400">Current authenticator code
                    <input name="code" required inputmode="numeric" maxlength="6" class="field mt-2 w-full tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code">
                </label>
                <p class="text-xs text-slate-400">Enter a code from your authenticator app to disable the second factor.</p>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-rose-600 px-5 py-3 text-sm font-semibold uppercase tracking-wider text-white transition hover:bg-rose-700">Disable two-factor authentication</button>
            </form>
        @else
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div>
                    <h3 class="kp-admin-display text-lg">Not enabled</h3>
                    <p class="mt-1.5 text-xs text-slate-400">Your account signs in with a phone code only.</p>
                </div>
                <x-admin.badge status="gray" label="Off" />
            </div>

            <div class="mt-6 rounded-xl border border-dashed border-slate-300 bg-slate-50/70 p-5">
                <h4 class="text-sm font-semibold text-slate-800">Step 1 — Add a secret to your authenticator app</h4>
                <p class="mt-1 text-xs leading-5 text-slate-400">Use Google Authenticator, Authy, 1Password, or any compatible app. Enter this secret manually or scan:</p>
                @if($uri)
                    <a href="{{ $uri }}" class="kp-admin-secondary-button mt-4"><x-tabler-external-link size="15" />Open on this device</a>
                @endif
                @if($secret)
                    <p class="mt-4 break-all rounded-xl bg-white p-3 font-mono text-sm tracking-wide text-slate-700">{{ $secret }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.security.two-factor.enable') }}" class="mt-6 space-y-4">
                @csrf
                <label class="block text-xs uppercase tracking-wider text-slate-400">Step 2 — Confirm with a code
                    <input name="code" required inputmode="numeric" maxlength="6" class="field mt-2 w-full tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code">
                </label>
                <button type="submit" class="kp-admin-primary-button">Enable two-factor authentication</button>
            </form>

            <p class="mt-4 text-xs text-slate-400">Back up your secret somewhere safe. If you lose access to your authenticator app you will need an administrator to reset it.</p>
        @endif
    </x-admin.card>
</div>
@endsection
