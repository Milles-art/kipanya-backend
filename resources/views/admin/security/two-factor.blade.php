@extends('admin.layouts.app', ['title' => 'Two-Factor Authentication'])

@section('content')
<div class="mx-auto max-w-2xl">
    <header class="mb-6">
        <p class="text-xs font-bold uppercase tracking-[0.2em] text-emerald-600">Security</p>
        <h1 class="mt-1 text-2xl font-black">Two-Factor Authentication</h1>
        <p class="mt-1 text-sm text-gray-500">Add a one-time code from your authenticator app to every administrator sign-in.</p>
    </header>

    @if(session('success'))
        <div class="mb-5 rounded-xl bg-emerald-50 p-4 text-sm text-emerald-700">{{ session('success') }}</div>
    @endif
    @if(session('info'))
        <div class="mb-5 rounded-xl bg-sky-50 p-4 text-sm text-sky-700">{{ session('info') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl bg-red-50 p-4 text-sm text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="rounded-3xl border border-gray-100 bg-white p-6 shadow-sm">
        @if($enabled)
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">Enabled</h2>
                    <p class="mt-1 text-sm text-gray-500">Every administrator sign-in for this account now requires a six-digit code.</p>
                </div>
                <span class="rounded-full bg-emerald-100 px-3 py-1 text-xs font-bold text-emerald-700">ACTIVE</span>
            </div>

            <form method="POST" action="{{ route('admin.security.two-factor.disable') }}" class="mt-6 space-y-4">
                @csrf
                <label class="block text-sm font-semibold">Current authenticator code<input name="code" required inputmode="numeric" maxlength="6" class="field mt-2 w-full tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code"></label>
                <p class="text-xs text-gray-400">Enter a code from your authenticator app to disable the second factor.</p>
                <button class="inline-flex items-center justify-center rounded-xl bg-red-600 px-5 py-3 text-sm font-semibold text-white transition hover:bg-red-700" onclick="return confirm('Disable two-factor authentication for this account?')">Disable two-factor authentication</button>
            </form>
        @else
            <div class="flex items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold">Not enabled</h2>
                    <p class="mt-1 text-sm text-gray-500">Your account signs in with a phone code only.</p>
                </div>
                <span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-bold text-gray-500">OFF</span>
            </div>

            <div class="mt-6 rounded-2xl border border-dashed border-gray-200 p-5">
                <h3 class="text-sm font-bold">Step 1 — Add a secret to your authenticator app</h3>
                <p class="mt-1 text-sm text-gray-500">Use Google Authenticator, Authy, 1Password, or any compatible app. Enter this secret manually or scan:</p>
                @if($uri)
                    <a href="{{ $uri }}" class="mt-3 inline-block rounded-xl bg-gray-950 px-4 py-2 text-sm font-semibold text-white">Open on this device</a>
                @endif
                @if($secret)
                    <p class="mt-4 break-all font-mono text-sm tracking-wide bg-gray-50 rounded-lg p-3">{{ $secret }}</p>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.security.two-factor.enable') }}" class="mt-6 space-y-4">
                @csrf
                <label class="block text-sm font-semibold">Step 2 — Confirm with a code<input name="code" required inputmode="numeric" maxlength="6" class="field mt-2 w-full tracking-[0.4em]" placeholder="000000" autocomplete="one-time-code"></label>
                <button class="button-dark">Enable two-factor authentication</button>
            </form>

            <p class="mt-4 text-xs text-gray-400">Back up your secret somewhere safe. If you lose access to your authenticator app you will need an administrator to reset it.</p>
        @endif
    </section>
</div>
@endsection