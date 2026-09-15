@extends('layouts.app')

@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
        @include('components.account-sidebar')

        <section class="min-w-0" aria-labelledby="payment-methods-title">
            <div class="border-b border-gray-200 pb-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-gray-950 text-white">
                        <x-tabler-credit-card size="21" />
                    </div>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-gray-400">Account payments</p>
                        <h1 id="payment-methods-title" class="mt-1 text-2xl font-bold tracking-tight text-gray-950 sm:text-3xl">Payment methods</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">Manage the payment details you use when shopping with KP Wear.</p>
                    </div>
                </div>
            </div>

            <div class="mt-7 grid gap-5 md:grid-cols-2">
                <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-700">
                            <x-tabler-device-mobile size="23" />
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">Coming soon</span>
                    </div>
                    <h2 class="mt-5 text-base font-semibold text-gray-950">Mobile money</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">Save your preferred mobile money details for a faster checkout experience.</p>
                    <div class="mt-5 rounded-2xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-400">Supported at checkout</p>
                        <p class="mt-1 text-sm font-semibold text-gray-800">Mobile payment integration is being prepared.</p>
                    </div>
                </article>

                <article class="rounded-3xl border border-gray-200 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-700">
                            <x-tabler-credit-card size="23" />
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">Coming soon</span>
                    </div>
                    <h2 class="mt-5 text-base font-semibold text-gray-950">Card payments</h2>
                    <p class="mt-2 text-sm leading-6 text-gray-500">Securely manage a saved card for future KP Wear purchases.</p>
                    <div class="mt-5 rounded-2xl bg-gray-50 p-4">
                        <p class="text-xs font-medium text-gray-400">Available later</p>
                        <p class="mt-1 text-sm font-semibold text-gray-800">Card storage will be enabled with the payment integration.</p>
                    </div>
                </article>
            </div>

            <div class="mt-6 rounded-3xl border border-gray-200 bg-gray-950 p-6 text-white sm:p-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10">
                        <x-tabler-shield-check size="21" />
                    </div>
                    <div>
                        <h2 class="font-semibold">Your payment security matters</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-300">KP Wear will use the payment provider integration at checkout. Full card details will not be stored directly in this application.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 rounded-3xl border border-gray-200 bg-gray-50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <p class="text-sm font-semibold text-gray-900">Need help with an order?</p>
                    <p class="mt-1 text-sm text-gray-500">Our support page is available for payment or order questions.</p>
                </div>
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-300 bg-white px-5 py-2.5 text-sm font-semibold text-gray-900 transition hover:border-gray-950 hover:bg-gray-950 hover:text-white">
                    Contact support
                    <x-tabler-arrow-right size="16" />
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
