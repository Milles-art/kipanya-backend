@extends('admin.layouts.app', ['title' => 'Orders', 'heading' => 'Orders'])

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-sm text-black">Manage customer orders and track their fulfillment status.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="GET" class="grid gap-3 rounded-2xl border border-black bg-white p-4 md:grid-cols-4">
        <input name="q" value="{{ request('q') }}" placeholder="Search order, customer, phone..." class="rounded-xl border border-black px-3 py-2.5 text-sm outline-none focus:border-emerald-500">
        <select name="status" class="rounded-xl border border-black px-3 py-2.5 text-sm outline-none focus:border-emerald-500">
            <option value="">All order statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ str_replace('_', ' ', ucfirst($status->value)) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="rounded-xl border border-black px-3 py-2.5 text-sm outline-none focus:border-emerald-500">
            <option value="">All payment statuses</option>
            @foreach($paymentStatuses as $status)
                <option value="{{ $status->value }}" @selected(request('payment_status') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
        <div class="flex gap-2">
            <button class="flex-1 rounded-xl bg-black px-4 py-2.5 text-sm font-semibold text-white hover:bg-black">Filter</button>
            <a href="{{ route('admin.wear.orders.index') }}" class="rounded-xl border border-black px-4 py-2.5 text-sm font-semibold text-black hover:bg-white">Reset</a>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-black bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-black bg-white text-xs uppercase tracking-wider text-black">
                    <tr>
                        <th class="px-5 py-4">Order</th>
                        <th class="px-5 py-4">Customer</th>
                        <th class="px-5 py-4">Items</th>
                        <th class="px-5 py-4">Total</th>
                        <th class="px-5 py-4">Payment</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black">
                    @forelse($orders as $order)
                        @php
                            $status = $order->status?->value ?? (string) $order->status;
                            $payment = $order->payment_status?->value ?? (string) $order->payment_status;
                            $statusClasses = match($status) {
                                'delivered' => 'bg-emerald-50 text-emerald-700',
                                'shipped' => 'bg-blue-50 text-blue-700',
                                'processing' => 'bg-amber-50 text-amber-700',
                                'confirmed' => 'bg-violet-50 text-violet-700',
                                'cancelled' => 'bg-red-50 text-red-700',
                                default => 'bg-white text-black',
                            };
                            $paymentClasses = match($payment) {
                                'paid' => 'bg-emerald-50 text-emerald-700',
                                'failed', 'cancelled' => 'bg-red-50 text-red-700',
                                'refunded' => 'bg-violet-50 text-violet-700',
                                default => 'bg-white text-black',
                            };
                        @endphp
                        <tr class="hover:bg-white/70">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.wear.orders.show', $order) }}" class="font-bold text-black hover:text-emerald-700">{{ $order->order_number }}</a>
                                <div class="mt-1 text-xs text-black">{{ $order->placed_at?->format('d M Y, H:i') ?? 'Not placed' }}</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-semibold text-black">{{ $order->customer_name }}</div>
                                <div class="mt-1 text-xs text-black">{{ $order->customer_phone }}</div>
                            </td>
                            <td class="px-5 py-4 text-black">{{ $order->items_count }}</td>
                            <td class="px-5 py-4 font-bold text-black">{{ number_format((float) $order->total, 0) }} TZS</td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $paymentClasses }}">{{ ucfirst($payment) }}</span></td>
                            <td class="px-5 py-4"><span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $statusClasses }}">{{ str_replace('_', ' ', ucfirst($status)) }}</span></td>
                            <td class="px-5 py-4 text-right"><a href="{{ route('admin.wear.orders.show', $order) }}" class="font-semibold text-emerald-700 hover:text-emerald-800">View →</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center"><p class="font-bold text-black">No orders found</p><p class="mt-1 text-sm text-black">Try changing your filters or wait for the next customer order.</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-black px-5 py-4">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
@endsection
