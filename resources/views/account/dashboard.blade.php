@extends('layouts.app')
@section('content')
<div data-account-page class="mx-auto kp-content px-4 pb-20 pt-12 sm:px-6 lg:px-8">
    <div class="grid gap-8 lg:grid-cols-[256px_1fr]">
        @include('components.account-sidebar')
        <div>
            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">My account</p>
            <h1 data-account-name class="mt-2 text-3xl font-bold">Welcome back, {{ $user->name }}</h1>
            <p data-account-phone class="mt-1 text-sm text-black">{{ $user->phone }}<span class="mx-2 text-black/40">·</span>{{ $user->email }}</p>

            <div class="mt-8 grid gap-4 sm:grid-cols-3">
                <div class="rounded-2xl bg-emerald-50/50 p-5">
                    <p class="text-sm text-black">Orders</p>
                    <p data-account-orders-count class="mt-2 text-3xl font-bold">{{ $totalOrders }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/50 p-5">
                    <p class="text-sm text-black">Wishlist</p>
                    <p data-account-wishlist-count class="mt-2 text-3xl font-bold">{{ $wishlistCount }}</p>
                </div>
                <div class="rounded-2xl bg-emerald-50/50 p-5">
                    <p class="text-sm text-black">Loyalty points</p>
                    <p class="mt-2 text-3xl font-bold">{{ $loyalty->points ?? 0 }}</p>
                </div>
            </div>

            @if($recentOrders->isNotEmpty())
            <section class="mt-8">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-black">Recent orders</h2>
                    <a href="{{ route('account.orders') }}" class="text-sm font-semibold text-emerald-600 hover:text-emerald-700">View all</a>
                </div>
                <ul class="mt-3 space-y-3">
                    @foreach($recentOrders as $order)
                    <li class="rounded-2xl border border-emerald-950/10 bg-white p-4 shadow-sm">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <a href="{{ route('account.order-detail', $order->order_number) }}" class="font-bold text-black">{{ $order->order_number }}</a>
                            <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ str_replace('_', ' ', $order->status->value) }}</span>
                        </div>
                        <p class="mt-1.5 text-sm text-black">{{ $order->items->pluck('product_name')->unique()->implode(', ') }}</p>
                        <p class="mt-2 text-sm font-semibold text-black">TZS {{ number_format($order->total) }}</p>
                    </li>
                    @endforeach
                </ul>
            </section>
            @endif

            <div class="mt-8 grid gap-4 md:grid-cols-2">
                @if($defaultAddress)
                <section class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-black">Default address</h2>
                    <p class="mt-2 text-sm font-medium text-black">{{ $defaultAddress->recipient_name }}</p>
                    <p class="mt-1 text-sm leading-6 text-black">{{ $defaultAddress->phone }}<br>{{ $defaultAddress->street }}, {{ collect([$defaultAddress->ward, $defaultAddress->district, $defaultAddress->region])->filter()->implode(', ') }}</p>
                </section>
                @endif

                @if($defaultPayment)
                <section class="rounded-2xl border border-emerald-950/10 bg-white p-5 shadow-sm">
                    <h2 class="text-sm font-bold uppercase tracking-wider text-black">Default payment</h2>
                    <p class="mt-2 text-sm font-medium text-black">{{ $defaultPayment->brand }}</p>
                    <p class="mt-1 text-sm text-black">•••• •••• •••• {{ $defaultPayment->last4 }}<br>Expires {{ $defaultPayment->exp_month }}/{{ $defaultPayment->exp_year }}</p>
                </section>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection