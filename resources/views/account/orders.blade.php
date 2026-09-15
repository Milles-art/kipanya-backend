@extends('layouts.app')

@section('content')
<div data-orders-page class="mx-auto max-w-7xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[256px_1fr]">
        @include('components.account-sidebar')

        <section class="min-w-0">
            <div class="flex flex-col gap-2 border-b border-gray-100 pb-6 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">Your account</p>
                    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-950">My Orders</h1>
                    <p class="mt-2 text-sm text-gray-500">View your purchases, payment status and order details.</p>
                </div>
                <a href="{{ route('shop') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">
                    Continue shopping
                    <span aria-hidden="true">→</span>
                </a>
            </div>

            <div data-orders-list class="mt-7 space-y-4">
                <div class="animate-pulse rounded-2xl border border-gray-100 bg-white p-5">
                    <div class="h-4 w-32 rounded bg-gray-100"></div>
                    <div class="mt-3 h-3 w-48 rounded bg-gray-100"></div>
                    <div class="mt-5 h-10 rounded-xl bg-gray-100"></div>
                </div>
                <div class="animate-pulse rounded-2xl border border-gray-100 bg-white p-5">
                    <div class="h-4 w-40 rounded bg-gray-100"></div>
                    <div class="mt-3 h-3 w-44 rounded bg-gray-100"></div>
                </div>
            </div>
        </section>
    </div>
</div>
@endsection
