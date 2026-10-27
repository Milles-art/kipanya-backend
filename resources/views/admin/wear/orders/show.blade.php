@extends('admin.layouts.app', ['title' => $order->order_number, 'heading' => 'Order '.$order->order_number])

@section('content')
@php
    $status = $order->status?->value ?? (string) $order->status;
    $payment = $order->payment_status?->value ?? (string) $order->payment_status;
    $statusLabel = str_replace('_', ' ', ucfirst($status));
    $paymentLabel = ucfirst($payment);
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

<div>
    <x-admin.page-header :title="'Order '.$order->order_number" :subtitle="'Placed '.($order->placed_at?->format('d M Y, H:i') ?? 'Not placed')">
        <x-slot name="action">
            <a href="{{ route('admin.wear.orders.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />Back to orders</a>
            <x-admin.badge :status="$statusTone" :label="$statusLabel" />
            <x-admin.badge :status="$paymentTone" :label="'Payment: '.$paymentLabel" />
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            <p class="font-semibold">Could not update the order.</p>
            <ul class="mt-1 list-disc pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-6">
            <x-admin.card class="overflow-hidden">
                <div class="px-6 pb-4 pt-6">
                    <h3 class="kp-admin-display text-lg">Items</h3>
                    <p class="mt-1.5 text-xs text-slate-400">{{ $order->items->sum('quantity') }} item{{ $order->items->sum('quantity') === 1 ? '' : 's' }} on this order</p>
                </div>
                <div class="divide-y divide-slate-100 border-t border-slate-100">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 px-6 py-5">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl border border-slate-100 bg-slate-50">
                                @if($item->product?->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-medium text-slate-900">{{ $item->product_name }}</h4>
                                <p class="mt-1 text-xs text-slate-400">{{ $item->sku ?: 'No SKU' }} · {{ $item->size ?: 'One size' }} · {{ $item->color ?: 'Default color' }}</p>
                                <p class="mt-2 text-sm text-slate-500">Qty {{ $item->quantity }} × TZS {{ number_format((float) $item->unit_price, 0) }}</p>
                            </div>
                            <div class="font-display text-lg text-slate-950">TZS {{ number_format((float) $item->line_total, 0) }}</div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-slate-100 px-6 py-5">
                    <div class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-slate-500">Subtotal</span><span class="font-medium text-slate-800">TZS {{ number_format((float) $order->subtotal, 0) }}</span></div>
                        <div class="flex justify-between"><span class="text-slate-500">Delivery</span><span class="font-medium text-slate-800">TZS {{ number_format((float) $order->delivery_fee, 0) }}</span></div>
                        <div class="flex justify-between border-t border-slate-100 pt-2 text-base"><span class="font-medium text-slate-900">Total</span><span class="font-display text-lg text-slate-950">TZS {{ number_format((float) $order->total, 0) }}</span></div>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Order History</h3>
                <div class="mt-5 space-y-4">
                    @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                        <div class="flex gap-3">
                            <div class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-medium text-slate-800">{{ str_replace('_', ' ', ucfirst($history->to_status)) }}</p>
                                <p class="mt-1 text-xs text-slate-400">{{ $history->created_at?->format('d M Y, H:i') }} · {{ $history->changedBy?->name ?? 'System' }}</p>
                                @if($history->reason)<p class="mt-1 text-sm text-slate-600">{{ $history->reason }}</p>@endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">No status history recorded.</p>
                    @endforelse
                </div>
            </x-admin.card>
        </div>

        <div class="space-y-6">
            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Customer</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <div><p class="text-xs uppercase tracking-wider text-slate-400">Name</p><p class="mt-1 font-medium text-slate-800">{{ $order->customer_name }}</p></div>
                    <div><p class="text-xs uppercase tracking-wider text-slate-400">Phone</p><p class="mt-1 font-medium text-slate-800">{{ $order->customer_phone }}</p></div>
                    @if($order->customer_email)<div><p class="text-xs uppercase tracking-wider text-slate-400">Email</p><p class="mt-1 font-medium break-all text-slate-800">{{ $order->customer_email }}</p></div>@endif
                </div>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Delivery</h3>
                <div class="mt-4 text-sm text-slate-600">
                    <p class="whitespace-pre-line">{{ $order->delivery_address }}</p>
                    @if($order->delivery_city)<p class="mt-2 font-medium text-slate-800">{{ $order->delivery_city }}</p>@endif
                    @if($order->delivery_provider || $order->tracking_number)
                        <div class="mt-4 rounded-xl bg-slate-50 px-4 py-3 text-sm">
                            @if($order->delivery_provider)<p><span class="font-medium text-slate-700">Provider:</span> {{ $order->delivery_provider }}</p>@endif
                            @if($order->tracking_number)<p class="mt-1"><span class="font-medium text-slate-700">Tracking:</span> {{ $order->tracking_number }}</p>@endif
                        </div>
                    @endif
                    @if($order->notes)<p class="mt-4 rounded-xl bg-slate-50 px-4 py-3"><span class="font-medium text-slate-700">Note:</span> {{ $order->notes }}</p>@endif
                </div>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Fulfillment</h3>
                <p class="mt-1.5 text-xs text-slate-400">Internal handover note without changing payment state.</p>
                <form method="POST" action="{{ route('admin.wear.orders.delivery', $order) }}" class="mt-5 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="mb-2 block text-xs uppercase tracking-wider text-slate-400">Fulfillment note</span>
                        <textarea name="fulfillment_notes" rows="3" class="kp-admin-input" placeholder="Internal handover note">{{ old('fulfillment_notes', $order->fulfillment_notes) }}</textarea>
                    </label>
                    <div class="grid gap-3 text-xs text-slate-600 sm:grid-cols-2">
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5"><span class="font-medium text-slate-700">Shipped:</span> {{ $order->shipped_at?->format('d M Y, H:i') ?? 'Not yet' }}</div>
                        <div class="rounded-xl bg-slate-50 px-3 py-2.5"><span class="font-medium text-slate-700">Delivered:</span> {{ $order->delivered_at?->format('d M Y, H:i') ?? 'Not yet' }}</div>
                    </div>
                    <button class="kp-admin-secondary-button w-full">Save fulfillment details</button>
                </form>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Update Order Status</h3>
                <p class="mt-1.5 text-xs text-slate-400">Payment status is managed separately from fulfillment status.</p>
                <form method="POST" action="{{ route('admin.wear.orders.status', $order) }}" class="mt-5 space-y-4">
                    @csrf
                    <select name="status" class="kp-admin-input">
                        <option value="{{ $status }}" selected>{{ $statusLabel }}</option>
                        @forelse($allowedStatuses as $option)
                            <option value="{{ $option->value }}">{{ str_replace('_', ' ', ucfirst($option->value)) }}</option>
                        @empty
                            <option value="" disabled>No further status changes allowed</option>
                        @endforelse
                    </select>
                    <textarea name="reason" rows="3" placeholder="Optional reason or internal note" class="kp-admin-input">{{ old('reason') }}</textarea>
                    <button class="kp-admin-primary-button w-full">Update status</button>
                </form>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Payment</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Status</span><span class="font-medium text-slate-800">{{ $paymentLabel }}</span></div>
                    <div class="flex justify-between gap-4"><span class="text-slate-500">Method</span><span class="font-medium text-slate-800">{{ $order->payment_method ?: 'Not selected' }}</span></div>
                </div>
                @if($order->payments->isNotEmpty())
                    <div class="mt-5 border-t border-slate-100 pt-4">
                        <p class="text-xs uppercase tracking-wider text-slate-400">Transactions</p>
                        <div class="mt-3 space-y-3">
                            @foreach($order->payments as $paymentTransaction)
                                <div class="rounded-xl bg-slate-50 px-4 py-3 text-xs">
                                    <div class="flex justify-between font-medium text-slate-700"><span>{{ $paymentTransaction->provider }}</span><span>{{ str_replace('_', ' ', ucfirst($paymentTransaction->status?->value ?? $paymentTransaction->status)) }}</span></div>
                                    <p class="mt-1 text-slate-500">{{ $paymentTransaction->provider_reference ?: 'No provider reference' }}</p>
                                    <p class="mt-1 font-medium text-slate-800">{{ $paymentTransaction->currency }} {{ number_format((float) $paymentTransaction->amount, 0) }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </x-admin.card>
        </div>
    </div>
</div>
@endsection
