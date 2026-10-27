@extends('admin.layouts.app', ['title' => 'Orders', 'heading' => 'Orders'])

@section('content')
<div>
    <x-admin.page-header title="Orders" subtitle="Track customer orders, fulfilment, and payment status from one workspace — {{ number_format($orders->total()) }} order{{ $orders->total() === 1 ? '' : 's' }} in total." />

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <form method="GET" class="mb-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_200px_200px_auto]">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search order, customer, or phone" class="kp-admin-input" />
        <select name="status" class="kp-admin-input">
            <option value="">All order statuses</option>
            @foreach($statuses as $status)
                <option value="{{ $status->value }}" @selected(request('status') === $status->value)>{{ str_replace('_', ' ', ucfirst($status->value)) }}</option>
            @endforeach
        </select>
        <select name="payment_status" class="kp-admin-input">
            <option value="">All payment statuses</option>
            @foreach($paymentStatuses as $status)
                <option value="{{ $status->value }}" @selected(request('payment_status') === $status->value)>{{ ucfirst($status->value) }}</option>
            @endforeach
        </select>
        <div class="flex items-center gap-2">
            <button type="submit" class="kp-admin-primary-button">Filter</button>
            <a href="{{ route('admin.wear.orders.index') }}" class="kp-admin-secondary-button">Reset</a>
        </div>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-[900px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Order</th>
                        <th class="px-3 py-3.5 text-left font-medium">Customer</th>
                        <th class="hidden px-3 py-3.5 text-center font-medium md:table-cell">Items</th>
                        <th class="px-3 py-3.5 text-right font-medium">Total</th>
                        <th class="px-3 py-3.5 text-left font-medium">Payment</th>
                        <th class="px-3 py-3.5 text-left font-medium">Fulfilment</th>
                        <th class="px-6 py-3.5 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        @php
                            $status = $order->status?->value ?? (string) $order->status;
                            $payment = $order->payment_status?->value ?? (string) $order->payment_status;
                            $statusTone = match ($status) {
                                'pending_payment' => 'amber',
                                'confirmed' => 'green',
                                'processing', 'shipped' => 'blue',
                                'delivered' => 'green',
                                'cancelled' => 'red',
                                default => 'gray',
                            };
                            $paymentTone = match ($payment) {
                                'paid' => 'green',
                                'failed', 'cancelled', 'user_cancelled', 'rejected' => 'red',
                                'pending', 'reconciliation_required' => 'amber',
                                'processing', 'in_progress' => 'blue',
                                'refunded' => 'purple',
                                default => 'gray',
                            };
                        @endphp
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.wear.orders.show', $order) }}" class="font-medium text-slate-900 hover:text-emerald-600">{{ $order->order_number }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $order->placed_at?->format('d M Y, H:i') ?? 'Not placed' }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <p class="font-medium text-slate-800">{{ $order->customer_name }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $order->customer_phone }}</p>
                            </td>
                            <td class="hidden px-3 py-3.5 text-center text-slate-600 md:table-cell">{{ $order->items_count }}</td>
                            <td class="px-3 py-3.5 text-right font-medium text-slate-900">TZS {{ number_format((float) $order->total, 0) }}</td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$paymentTone" :label="str_replace('_', ' ', ucfirst($payment ?: 'pending'))" /></td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$statusTone" :label="str_replace('_', ' ', ucfirst($status ?: 'pending'))" /></td>
                            <td class="px-6 py-3.5 text-right"><a href="{{ route('admin.wear.orders.show', $order) }}" class="kp-admin-link">View <x-tabler-arrow-right size="13" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty message="No orders match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $orders->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
