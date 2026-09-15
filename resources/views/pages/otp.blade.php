@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-md px-4 pb-20 pt-28 text-center">
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50">
        <x-tabler-mail-opened size="26" class="text-emerald-600" />
    </div>
    <h1 class="mt-5 text-2xl font-bold tracking-tight text-gray-900">Verify your email</h1>
    <p class="mt-2 text-sm text-gray-500">Enter the six-digit code we sent you.</p>

    <form action="{{ url('/account') }}" class="mt-8 rounded-2xl border border-gray-200 bg-white p-6 text-left shadow-sm sm:p-7">
        <label class="mb-1.5 flex items-center justify-center gap-1.5 text-sm font-medium text-gray-700">
            <x-tabler-shield-lock size="15" class="text-gray-400" />
            Verification code
        </label>
        <input class="field w-full text-center text-xl tracking-[.5em]" maxlength="6" inputmode="numeric" placeholder="000000">
        <button class="button-dark mt-5 flex w-full items-center justify-center gap-2 py-3">
            Verify code <span aria-hidden="true">→</span>
        </button>
        <p class="mt-4 text-center text-xs text-gray-400">
            Didn't get a code? <button type="button" class="font-medium text-emerald-600 hover:underline">Resend</button>
        </p>
    </form>
</div>
@endsection
