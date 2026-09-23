@extends('layouts.app')

@section('content')
<div class="mx-auto kp-content px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[260px_minmax(0,1fr)]">
        @include('components.account-sidebar')

        <section class="min-w-0" aria-labelledby="payment-methods-title">
            <div class="border-b border-emerald-950/12 pb-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-2xl bg-black text-white">
                        <x-tabler-credit-card size="21" />
                    </div>
                    <div>
                                                <h1 id="payment-methods-title" class="mt-1 text-2xl font-bold tracking-tight text-black sm:text-3xl">Payment methods</h1>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-black">Manage the payment details you use when shopping with KP Wear.</p>
                    </div>
                </div>
            </div>

            <div class="mt-7 grid gap-5 md:grid-cols-2">
                <article class="rounded-3xl border border-emerald-950/12 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50/70 text-black">
                            <x-tabler-device-mobile size="23" />
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">Coming soon</span>
                    </div>
                    <h2 class="mt-5 text-base font-semibold text-black">Mobile money</h2>
                    <p class="mt-2 text-sm leading-6 text-black">Save your preferred mobile money details for a faster checkout experience.</p>
                    <div class="mt-5 rounded-2xl bg-emerald-50/50 p-4">
                        <p class="text-xs font-medium text-black">Supported at checkout</p>
                        <p class="mt-1 text-sm font-semibold text-black">Mobile payment integration is being prepared.</p>
                    </div>
                </article>

                <article class="rounded-3xl border border-emerald-950/12 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-emerald-50/70 text-black">
                            <x-tabler-credit-card size="23" />
                        </div>
                        <span class="rounded-full bg-amber-50 px-3 py-1 text-[11px] font-semibold text-amber-700">Coming soon</span>
                    </div>
                    <h2 class="mt-5 text-base font-semibold text-black">Card payments</h2>
                    <p class="mt-2 text-sm leading-6 text-black">Securely manage a saved card for future KP Wear purchases.</p>
                    <div class="mt-5 rounded-2xl bg-emerald-50/50 p-4">
                        <p class="text-xs font-medium text-black">Available later</p>
                        <p class="mt-1 text-sm font-semibold text-black">Card storage will be enabled with the payment integration.</p>
                    </div>
                </article>
            </div>

            <div class="mt-6 rounded-3xl border border-emerald-950/12 bg-black p-6 text-white sm:p-7">
                <div class="flex items-start gap-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-2xl bg-white/10">
                        <x-tabler-shield-check size="21" />
                    </div>
                    <div>
                        <h2 class="font-semibold">Your payment security matters</h2>
                        <p class="mt-2 max-w-2xl text-sm leading-6 text-white/80">KP Wear will use the payment provider integration at checkout. Full card details will not be stored directly in this application.</p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex flex-col gap-3 rounded-3xl border border-emerald-950/12 bg-emerald-50/50 p-5 sm:flex-row sm:items-center sm:justify-between sm:p-6">
                <div>
                    <p class="text-sm font-semibold text-black">Need help with an order?</p>
                    <p class="mt-1 text-sm text-black">Our support page is available for payment or order questions.</p>
                </div>
                <a href="{{ route('contact') }}" class="kp-button-secondary min-h-10 rounded-full px-5 py-2.5">
                    Contact support
                    <x-tabler-arrow-right size="16" />
                </a>
            </div>
        </section>
    </div>
</div>
@endsection
