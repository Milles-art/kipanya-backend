@extends('layouts.app')

@section('content')
<div data-order-detail data-order-number="{{ request()->route('orderId') }}" class="mx-auto max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-gray-100 pb-6">
        <div>
            <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">← Back to orders</a>
            <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-gray-400">Order details</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-gray-950">Order {{ request()->route('orderId') }}</h1>
        </div>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    <div data-order-detail-content class="mt-7">
        <div class="animate-pulse rounded-2xl border border-gray-100 bg-white p-6">
            <div class="grid gap-5 sm:grid-cols-3">
                <div class="h-14 rounded-xl bg-gray-100"></div>
                <div class="h-14 rounded-xl bg-gray-100"></div>
                <div class="h-14 rounded-xl bg-gray-100"></div>
            </div>
            <div class="mt-8 h-32 rounded-xl bg-gray-100"></div>
        </div>
    </div>
</div>
@endsection
