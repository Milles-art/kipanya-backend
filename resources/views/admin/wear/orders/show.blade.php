@extends('admin.layouts.app', ['title' => $order->order_number, 'heading' => 'Order '.$order->order_number])

@section('content')
@php
    $status = $order->status?->value ?? (string) $order->status;
    $payment = $order->payment_status?->value ?? (string) $order->payment_status;
    $statusLabel = str_replace('_', ' ', ucfirst($status));
    $paymentLabel = ucfirst($payment);
@endphp

<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <a href="{{ route('admin.wear.orders.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">← Back to orders</a>
            <p class="mt-2 text-sm text-gray-500">Placed {{ $order->placed_at?->format('d M Y, H:i') ?? 'Not placed' }}</p>
        </div>
        <div class="flex gap-2">
            <span class="rounded-full bg-gray-100 px-3 py-1.5 text-xs font-bold text-gray-700">{{ $statusLabel }}</span>
            <span class="rounded-full bg-emerald-50 px-3 py-1.5 text-xs font-bold text-emerald-700">Payment: {{ $paymentLabel }}</span>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800"><p class="font-bold">Could not update the order.</p><ul class="mt-1 list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[1.5fr_1fr]">
        <div class="space-y-6">
            <section class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
                <div class="border-b border-gray-100 px-5 py-4"><h2 class="font-bold">Items</h2></div>
                <div class="divide-y divide-gray-100">
                    @foreach($order->items as $item)
                        <div class="flex gap-4 px-5 py-5">
                            <div class="h-20 w-20 shrink-0 overflow-hidden rounded-xl bg-gray-100">
                                @if($item->product?->image_url)
                                    <img src="{{ $item->product->image_url }}" alt="{{ $item->product_name }}" class="h-full w-full object-cover">
                                @endif
                            </div>
                            <div class="min-w-0 flex-1">
                                <h3 class="font-bold text-gray-950">{{ $item->product_name }}</h3>
                                <p class="mt-1 text-sm text-gray-500">{{ $item->sku ?: 'No SKU' }} · {{ $item->size ?: 'One size' }} · {{ $item->color ?: 'Default color' }}</p>
                                <p class="mt-2 text-sm text-gray-600">Qty {{ $item->quantity }} × {{ number_format((float) $item->unit_price, 0) }} TZS</p>
                            </div>
                            <div class="font-bold text-gray-950">{{ number_format((float) $item->line_total, 0) }} TZS</div>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-100 bg-gray-50 px-5 py-5">
                    <div class="ml-auto max-w-xs space-y-2 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Subtotal</span><span>{{ number_format((float) $order->subtotal, 0) }} TZS</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Delivery</span><span>{{ number_format((float) $order->delivery_fee, 0) }} TZS</span></div>
                        <div class="flex justify-between border-t border-gray-200 pt-2 text-base font-bold"><span>Total</span><span>{{ number_format((float) $order->total, 0) }} TZS</span></div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Order history</h2>
                <div class="mt-5 space-y-4">
                    @forelse($order->statusHistory->sortByDesc('created_at') as $history)
                        <div class="flex gap-3">
                            <div class="mt-1 h-2.5 w-2.5 shrink-0 rounded-full bg-emerald-500"></div>
                            <div>
                                <p class="text-sm font-semibold">{{ str_replace('_', ' ', ucfirst($history->to_status)) }}</p>
                                <p class="mt-1 text-xs text-gray-500">{{ $history->created_at?->format('d M Y, H:i') }} · {{ $history->changedBy?->name ?? 'System' }}</p>
                                @if($history->reason)<p class="mt-1 text-sm text-gray-600">{{ $history->reason }}</p>@endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No status history recorded.</p>
                    @endforelse
                </div>
            </section>
        </div>

        <div class="space-y-6">
            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Customer</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div><p class="text-xs uppercase tracking-wide text-gray-400">Name</p><p class="mt-1 font-semibold">{{ $order->customer_name }}</p></div>
                    <div><p class="text-xs uppercase tracking-wide text-gray-400">Phone</p><p class="mt-1 font-semibold">{{ $order->customer_phone }}</p></div>
                    @if($order->customer_email)<div><p class="text-xs uppercase tracking-wide text-gray-400">Email</p><p class="mt-1 font-semibold break-all">{{ $order->customer_email }}</p></div>@endif
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Delivery</h2>
                <div class="mt-4 text-sm text-gray-700">
                    <p class="whitespace-pre-line">{{ $order->delivery_address }}</p>
                    @if($order->delivery_city)<p class="mt-2 font-semibold">{{ $order->delivery_city }}</p>@endif
                    @if($order->delivery_provider || $order->tracking_number)
                        <div class="mt-4 rounded-xl bg-gray-50 p-3 text-sm">
                            @if($order->delivery_provider)<p><span class="font-semibold">Provider:</span> {{ $order->delivery_provider }}</p>@endif
                            @if($order->tracking_number)<p class="mt-1"><span class="font-semibold">Tracking:</span> {{ $order->tracking_number }}</p>@endif
                        </div>
                    @endif
                    @if($order->notes)<p class="mt-4 rounded-xl bg-gray-50 p-3"><span class="font-semibold">Note:</span> {{ $order->notes }}</p>@endif
                </div>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Fulfillment & tracking</h2>
                <p class="mt-1 text-sm text-gray-500">Add the delivery provider and tracking reference without changing payment state.</p>
                <form method="POST" action="{{ route('admin.wear.orders.delivery', $order) }}" class="mt-5 space-y-4">
                    @csrf
                    <div class="grid gap-4 sm:grid-cols-2">
                        <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Delivery provider</span><input name="delivery_provider" value="{{ old('delivery_provider', $order->delivery_provider) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm outline-none focus:border-emerald-500" placeholder="Courier / rider / provider"></label>
                        <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Tracking number</span><input name="tracking_number" value="{{ old('tracking_number', $order->tracking_number) }}" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm outline-none focus:border-emerald-500" placeholder="Tracking reference"></label>
                    </div>
                    <label class="block"><span class="text-xs font-semibold uppercase tracking-wide text-gray-400">Fulfillment note</span><textarea name="fulfillment_notes" rows="3" class="mt-2 w-full rounded-xl border border-gray-200 px-3 py-3 text-sm outline-none focus:border-emerald-500" placeholder="Internal delivery note">{{ old('fulfillment_notes', $order->fulfillment_notes) }}</textarea></label>
                    <div class="grid gap-3 text-xs text-gray-500 sm:grid-cols-2">
                        <div class="rounded-xl bg-gray-50 p-3"><span class="font-semibold text-gray-700">Shipped:</span> {{ $order->shipped_at?->format('d M Y, H:i') ?? 'Not yet' }}</div>
                        <div class="rounded-xl bg-gray-50 p-3"><span class="font-semibold text-gray-700">Delivered:</span> {{ $order->delivered_at?->format('d M Y, H:i') ?? 'Not yet' }}</div>
                    </div>
                    <button class="w-full rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-900 hover:bg-gray-50">Save fulfillment details</button>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Update order status</h2>
                <p class="mt-1 text-sm text-gray-500">Payment status is managed separately from fulfillment status.</p>
                <form method="POST" action="{{ route('admin.wear.orders.status', $order) }}" class="mt-5 space-y-4">
                    @csrf
                    <select name="status" class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm font-semibold outline-none focus:border-emerald-500">
                        @foreach($statuses as $option)
                            <option value="{{ $option->value }}" @selected($status === $option->value)>{{ str_replace('_', ' ', ucfirst($option->value)) }}</option>
                        @endforeach
                    </select>
                    <textarea name="reason" rows="3" placeholder="Optional reason or internal note" class="w-full rounded-xl border border-gray-200 px-3 py-3 text-sm outline-none focus:border-emerald-500">{{ old('reason') }}</textarea>
                    <button class="w-full rounded-xl bg-gray-950 px-4 py-3 text-sm font-bold text-white hover:bg-gray-800">Update status</button>
                </form>
            </section>

            <section class="rounded-2xl border border-gray-200 bg-white p-5">
                <h2 class="font-bold">Payment</h2>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500">Status</span><span class="font-bold">{{ $paymentLabel }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500">Method</span><span class="font-semibold">{{ $order->payment_method ?: 'Not selected' }}</span></div>
                </div>
                @if($order->payments->isNotEmpty())
                    <div class="mt-5 border-t border-gray-100 pt-4">
                        <p class="text-xs font-bold uppercase tracking-wide text-gray-400">Transactions</p>
                        <div class="mt-3 space-y-3">
                            @foreach($order->payments as $paymentTransaction)
                                <div class="rounded-xl bg-gray-50 p-3 text-xs">
                                    <div class="flex justify-between font-semibold"><span>{{ $paymentTransaction->provider }}</span><span>{{ ucfirst($paymentTransaction->status?->value ?? $paymentTransaction->status) }}</span></div>
                                    <p class="mt-1 text-gray-500">{{ $paymentTransaction->provider_reference ?: 'No provider reference' }}</p>
                                    <p class="mt-1 text-gray-500">{{ number_format((float) $paymentTransaction->amount, 0) }} {{ $paymentTransaction->currency }}</p>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
