@extends('layouts.app')

@section('content')
<div data-orders-page class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
        @include('components.account.sidebar')

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
                @forelse($orders as $order)
                <article data-server-order="{{ $order->order_number }}" class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <a href="{{ route('account.order-detail', $order->order_number) }}" class="font-bold text-black">{{ $order->order_number }}</a>
                        <span class="rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-700">{{ str_replace('_', ' ', $order->status->value) }}</span>
                    </div>
                    <p class="mt-2 text-sm text-black">{{ $order->items->pluck('product_name')->unique()->implode(', ') }}</p>
                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 border-t border-emerald-950/10 pt-3 text-sm">
                        <span class="text-black">Placed {{ $order->placed_at?->format('j M Y') }}</span>
                        <span class="flex flex-wrap items-center gap-3">
                            <a href="{{ route('order-status', $order->order_number) }}" class="font-semibold text-emerald-700 transition hover:text-emerald-800">Track order →</a>
                            @if($order->status->value === 'pending_payment')
                                <a href="{{ route('order-status', $order->order_number) }}" class="rounded-full bg-black px-4 py-2 text-xs font-bold text-white transition hover:bg-emerald-700">Continue payment</a>
                            @endif
                            <span class="font-semibold text-black">TZS {{ number_format($order->total) }}</span>
                        </span>
                    </div>
                </article>
                @empty
                <p class="rounded-2xl border border-dashed border-emerald-950/12 bg-white p-10 text-center text-sm text-black">You have no orders yet. Once you place an order it will appear here.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
