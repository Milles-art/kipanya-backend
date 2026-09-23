@extends('layouts.app')

@section('content')
<div data-orders-page class="mx-auto kp-content px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[256px_1fr]">
        @include('components.account-sidebar')

        <section class="min-w-0">
            <div class="flex flex-col gap-2 border-b border-emerald-950/10 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-black">My Orders</h1>
                    <p class="mt-2 text-sm text-black">View your purchases, payment status and order details.</p>
                </div>
                <a href="{{ route('shop') }}" class="kp-button-secondary w-fit">
                    Continue shopping
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div data-orders-list class="mt-7 space-y-4">
                <div class="kp-skeleton animate-pulse rounded-2xl border border-emerald-950/10 bg-white p-5">
                    <div class="h-4 w-32 rounded bg-emerald-50/70"></div>
                    <div class="mt-3 h-3 w-48 rounded bg-emerald-50/70"></div>
                    <div class="mt-5 h-10 rounded-xl bg-emerald-50/70"></div>
                </div>
                <div class="kp-skeleton animate-pulse rounded-2xl border border-emerald-950/10 bg-white p-5">
                    <div class="h-4 w-40 rounded bg-emerald-50/70"></div>
                    <div class="mt-3 h-3 w-44 rounded bg-emerald-50/70"></div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
