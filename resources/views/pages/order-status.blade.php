@extends('layouts.app')
@section('content')
<div data-order-status data-order-number="{{ request()->route('orderNumber') }}" class="mx-auto max-w-lg px-4 pb-20 pt-20 text-center">
    <div data-order-status-icon class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-amber-100 text-amber-700 ring-8 ring-amber-50">
        <x-tabler-clock size="30" />
    </div>
    <p class="mt-6 text-xs font-semibold uppercase tracking-wider text-black">KP Wear order</p>
    <h1 data-order-status-title class="mt-2 text-3xl font-bold tracking-tight text-black">Order created</h1>
    <p class="mt-1 font-mono text-sm text-black">{{ request()->route('orderNumber') }}</p>

    <div class="mt-7 rounded-2xl border border-emerald-950/12 bg-white p-6 shadow-sm">
        <p data-order-status-text class="flex min-h-8 items-center justify-center gap-2 leading-7 text-black" aria-live="polite">
            <span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span>
            Loading order status…
        </p>
        <p class="mt-4 text-xs leading-5 text-black">Payment must be completed before the order is confirmed. You can safely leave this page and return to your orders.</p>
    </div>

    <div data-order-status-actions class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
        <a href="{{ route('shop') }}" class="button-dark inline-flex items-center justify-center gap-2">Continue shopping <span aria-hidden="true">→</span></a>
        <a href="{{ route('account.orders') }}" class="inline-flex items-center justify-center gap-2 rounded-full border border-emerald-950/12 px-6 py-3 text-sm font-medium text-black hover:bg-emerald-50/50">
            <x-tabler-list-details size="16" />
            View my orders
        </a>
    </div>
</div>
@endsection
