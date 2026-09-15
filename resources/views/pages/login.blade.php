@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-md px-4 pb-20 pt-20">
    <div class="mb-8 text-center">
        <p class="text-2xl font-black text-gray-900">KP<span class="text-emerald-600">.</span></p>
        <h1 class="mt-5 text-2xl font-bold tracking-tight text-gray-900">Welcome back</h1>
        <p class="mt-2 text-sm text-gray-500">Sign in using your phone number and verification code.</p>
    </div>

    <form novalidate data-login-form class="space-y-5 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm sm:p-7">
        <div data-primary-step>
            <label class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-gray-700">
                <x-tabler-device-mobile size="15" class="text-gray-400" />
                Phone number
            </label>
            <input name="phone" type="tel" class="field w-full" required placeholder="07XXXXXXXX">
        </div>

        <div data-otp-step class="hidden">
            <label class="mb-1.5 flex items-center gap-1.5 text-sm font-medium text-gray-700">
                <x-tabler-shield-lock size="15" class="text-gray-400" />
                Verification code
            </label>
            <input name="code" class="field w-full text-center text-lg tracking-[.5em]" maxlength="6" inputmode="numeric" placeholder="000000">
            <p class="mt-2 text-xs text-gray-400">We sent a 6-digit code to your phone.</p>
        </div>

        <button class="button-dark flex w-full items-center justify-center gap-2 py-3">
            Continue <span aria-hidden="true">→</span>
        </button>

        <p class="pt-1 text-center text-sm text-gray-500">
            New here?
            <a class="font-medium text-emerald-600 underline-offset-2 hover:underline" href="{{ route('register') }}">Create an account</a>
        </p>
    </form>
</div>
@endsection
