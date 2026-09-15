@extends('layouts.app')
@section('content')
<div data-checkout-page class="mx-auto w-full max-w-7xl px-4 pb-24 pt-10 sm:px-6 lg:px-8">

    {{-- Header --}}
    <div class="flex flex-col gap-3 border-b border-gray-200 pb-7 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="inline-flex items-center gap-1.5 text-sm font-semibold uppercase tracking-wider text-emerald-600">
                <x-tabler-shield-check size="16" />
                Secure checkout
            </p>
            <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900 sm:text-4xl">Complete your order</h1>
            <p class="mt-2 text-sm text-gray-500">Review your delivery details and order before placing it.</p>
        </div>
        <a href="{{ route('cart') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition hover:text-emerald-600">
            <x-tabler-arrow-left size="15" />
            Back to bag
        </a>
    </div>

    {{-- Auth notice --}}
    <div data-checkout-auth class="mt-8 hidden items-start gap-3 rounded-2xl border border-amber-200 bg-amber-50 p-5 text-sm text-amber-900">
        <x-tabler-alert-triangle size="18" class="mt-0.5 flex-shrink-0 text-amber-500" />
        <span>You need to <a class="font-semibold underline underline-offset-2" href="{{ route('login') }}">sign in</a> before placing an order.</span>
    </div>

    <div class="mt-8 grid gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">
        <div class="space-y-6">

            {{-- Step 1: Delivery address --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">1</span>
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Delivery</p>
                            <h2 class="text-lg font-semibold text-gray-900">Delivery address</h2>
                        </div>
                    </div>
                    <span class="rounded-full bg-red-50 px-2.5 py-1 text-[11px] font-medium text-red-500">Required</span>
                </div>

                <div data-address-list class="mt-5 space-y-3">
                    {{-- Each saved address is expected to render as its own selectable card, e.g.: --}}
                    {{--
                    <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-200 p-4 transition has-[:checked]:border-gray-900 has-[:checked]:bg-gray-50">
                        <input type="radio" name="address" class="mt-1 accent-gray-900">
                        <div class="flex-1">
                            <p class="font-medium text-gray-900">Recipient name</p>
                            <p class="mt-0.5 text-sm text-gray-500">Street, Ward, District, Region</p>
                            <p class="mt-0.5 text-sm text-gray-500">+255 XXX XXX XXX</p>
                        </div>
                        <x-tabler-map-pin size="18" class="mt-1 text-gray-300" />
                    </label>
                    --}}
                </div>

                <form data-address-form class="mt-5 grid gap-3 border-t border-gray-100 pt-5 sm:grid-cols-2">
                    <p class="text-xs font-medium text-gray-500 sm:col-span-2">Add a new address</p>
                    <input name="recipient_name" class="field" placeholder="Recipient name" required>
                    <input name="phone" class="field" placeholder="Phone number" required>
                    <input name="region" class="field" placeholder="Region" required>
                    <input name="district" class="field" placeholder="District" required>
                    <input name="ward" class="field" placeholder="Ward">
                    <input name="street" class="field sm:col-span-2" placeholder="Street / house address" required>
                    <button class="button-dark inline-flex items-center justify-center gap-2 sm:col-span-2">
                        <x-tabler-map-pin-plus size="16" />
                        Save address
                    </button>
                </form>
            </section>

            {{-- Step 2: Order note --}}
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">2</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Optional</p>
                        <h2 class="text-lg font-semibold text-gray-900">Order note</h2>
                    </div>
                </div>
                <textarea data-order-notes class="field mt-4 min-h-28 w-full" placeholder="Optional note for your order — delivery instructions, gate code, etc."></textarea>
            </section>

            {{-- Error + place order --}}
            <div data-checkout-error class="hidden items-start gap-2 rounded-2xl border border-red-200 bg-red-50 p-4 text-sm text-red-700" role="alert"></div>

            <button data-place-order disabled class="button-dark flex w-full items-center justify-center gap-2 py-3.5 text-base disabled:cursor-not-allowed disabled:opacity-50">
                <x-tabler-lock size="16" />
                Place order
            </button>
            <p class="flex items-center justify-center gap-1.5 text-center text-xs text-gray-400">
                <x-tabler-shield-check size="13" />
                Your payment and details are encrypted and secure
            </p>
        </div>

        {{-- Order summary --}}
        <aside class="h-fit rounded-2xl border border-gray-200 bg-gray-50 p-5 shadow-sm lg:sticky lg:top-24 sm:p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <span class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-gray-900 text-xs font-semibold text-white">3</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Review</p>
                        <h2 class="text-lg font-semibold text-gray-900">Order summary</h2>
                    </div>
                </div>
                <span data-checkout-item-count class="rounded-full bg-white px-2.5 py-1 text-xs font-medium text-gray-500 shadow-sm"></span>
            </div>

            <div data-checkout-summary class="mt-5 space-y-3 text-sm">
                <div class="animate-pulse space-y-3">
                    <div class="h-4 rounded bg-gray-200"></div>
                    <div class="h-4 rounded bg-gray-200"></div>
                    <div class="h-4 rounded bg-gray-200"></div>
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
