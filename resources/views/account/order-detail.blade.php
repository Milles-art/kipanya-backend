@extends('layouts.app')

@section('content')
<div data-order-detail data-order-number="{{ request()->route('orderId') }}" class="mx-auto max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="flex flex-wrap items-center justify-between gap-4 border-b border-emerald-950/10 pb-6">
        <div>
            <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-emerald-600 transition hover:text-emerald-700">← Back to orders</a>
            <p class="mt-5 text-sm font-semibold uppercase tracking-wider text-black">Order details</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight text-black">Order {{ request()->route('orderId') }}</h1>
        </div>
        <a href="{{ route('shop') }}" class="inline-flex items-center gap-2 rounded-xl border border-emerald-950/12 px-4 py-2.5 text-sm font-semibold text-black transition hover:bg-emerald-50/50">
            Continue shopping <span aria-hidden="true">→</span>
        </a>
    </div>

    <div data-order-detail-content class="mt-7">
        @if($order)
        <div class="rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-sm sm:p-7">
            <div class="grid gap-5 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-black">Order number</p>
                    <p class="mt-1 text-lg font-bold text-black">{{ $order->order_number }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-black">Status</p>
                    <p class="mt-1 text-lg font-bold text-black">{{ str_replace('_', ' ', $order->status->value) }}</p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wider text-black">Placed on</p>
                    <p class="mt-1 text-lg font-bold text-black">{{ $order->placed_at?->format('j M Y, H:i') }}</p>
                </div>
            </div>

            <h2 class="mt-8 text-sm font-semibold uppercase tracking-wider text-black">Items</h2>
            <ul class="mt-3 divide-y divide-emerald-950/10">
                @forelse($order->items as $item)
                <li class="flex flex-wrap items-center justify-between gap-3 py-4">
                    <div>
                        <p class="font-medium text-black">{{ $item->product_name }}</p>
                        <p class="mt-0.5 text-xs text-black">SKU {{ $item->sku }} · Size {{ $item->size }}@if($item->color) · {{ $item->color }}@endif · Qty {{ $item->quantity }}</p>
                    </div>
                    <p class="font-semibold text-black">TZS {{ number_format($item->line_total) }}</p>
                </li>
                @empty
                <li class="py-4 text-sm text-black">No items recorded for this order.</li>
                @endforelse
            </ul>

            <div class="mt-6 space-y-2 rounded-xl bg-emerald-50/50 p-5 text-sm">
                <div class="flex justify-between gap-3"><span class="text-black">Subtotal</span><span class="text-black">{{ number_format($order->subtotal) }}</span></div>
                <div class="flex justify-between gap-3"><span class="text-black">Delivery fee</span><span class="text-black">{{ number_format($order->delivery_fee) }}</span></div>
                <div class="flex justify-between gap-3 border-t border-emerald-950/10 pt-3 text-base font-bold"><span class="text-black">Total</span><span class="text-black">TZS {{ number_format($order->total) }}</span></div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
