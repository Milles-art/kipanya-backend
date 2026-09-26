{{-- resources/views/pages/pay-simulate.blade.php --}}
@extends('layouts.app')

@section('content')
<div class="mx-auto w-full max-w-xl px-4 pb-20 pt-10 sm:px-6">
    <p class="text-xs font-bold uppercase tracking-[0.22em] text-amber-600">Local payment simulator</p>
    <h1 class="mt-2 text-3xl font-black tracking-tight text-black">Test payment</h1>
    <p class="mt-2 text-sm leading-6 text-black">This stand-in replaces Selcom's hosted page in non-production environments. No real money moves here.</p>

    <section class="mt-7 rounded-2xl border border-emerald-950/12 bg-white p-6 shadow-sm">
        <div class="flex items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-black">Order</p>
                <p class="mt-1 text-lg font-bold text-black">{{ $order->order_number }}</p>
            </div>
            <p class="text-lg font-black text-black">TZS {{ number_format($order->total) }}</p>
        </div>

        @if($payment)
            <p class="mt-3 text-sm text-black">Payment <span class="font-mono">{{ $payment->provider_reference }}</span> is awaiting completion.</p>

            <div class="mt-5 grid gap-3 sm:grid-cols-2">
                <form method="POST" action="{{ route('pay.simulate.complete', $order->order_number) }}">
                    @csrf
                    <button type="submit" class="button-dark w-full">Simulate successful payment</button>
                </form>
                <form method="POST" action="{{ route('pay.simulate.fail', $order->order_number) }}">
                    @csrf
                    <button type="submit" class="kp-button-secondary w-full">Simulate failed payment</button>
                </form>
            </div>
        @else
            <p class="mt-3 rounded-xl bg-emerald-50/50 px-4 py-3 text-sm text-black">There is no payable payment on this order right now.</p>
            <a href="{{ route('order-status', $order->order_number) }}" class="button-dark mt-5 inline-flex">Back to order status</a>
        @endif
    </section>
</div>
@endsection
