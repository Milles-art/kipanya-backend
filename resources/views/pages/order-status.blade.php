@extends('layouts.app')
@section('content')
<div data-order-status data-order-number="{{ request()->route('orderNumber') }}" class="mx-auto max-w-lg px-4 pb-20 pt-24 text-center">
    <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-emerald-100 text-emerald-700 ring-8 ring-emerald-50">
        <x-tabler-check size="30" />
    </div>
    <p class="mt-5 text-xs font-semibold uppercase tracking-wider text-gray-400">Order confirmed</p>
    <h1 class="mt-2 text-3xl font-bold tracking-tight text-gray-900">{{ request()->route('orderNumber') }}</h1>

    <div class="mt-6 rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
        <p data-order-status-text class="flex items-center justify-center gap-2 leading-7 text-gray-600">
            <span class="h-2 w-2 animate-pulse rounded-full bg-emerald-500"></span>
            Loading order status…
        </p>
    </div>

    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <a href="{{ route('shop') }}" class="button-dark inline-flex items-center justify-center gap-2">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
        <a href="{{ route('account.orders') ?? '#' }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">
            <x-tabler-list-details size="16" />
            View my orders
        </a>
    </div>
</div>
@endsection
