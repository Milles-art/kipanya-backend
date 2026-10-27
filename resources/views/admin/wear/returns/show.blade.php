@extends('admin.layouts.app', ['title' => 'Return Request #'.$returnRequest->id, 'heading' => 'Return Request #'.$returnRequest->id])

@section('content')
@php
    $returnTransitions = [
        'under_review' => ['approved', 'rejected'],
        'approved' => ['received'],
        'received' => ['processed'],
        'refunded' => ['completed'],
    ];
    $returnNext = $returnTransitions[$returnRequest->status] ?? [];
    if ($returnRequest->status === 'processed' && ($returnRequest->refund_status ?? null) === 'not_required') {
        $returnNext = ['completed'];
    }
    $statusTone = match (strtolower((string) $returnRequest->status)) {
        'approved', 'completed' => 'green',
        'rejected' => 'red',
        'received' => 'blue',
        'refunded' => 'purple',
        default => 'amber',
    };
    $typeTone = $returnRequest->request_type === 'exchange' ? 'blue' : 'amber';
@endphp

<div>
    <x-admin.page-header :title="ucwords(str_replace('_', ' ', $returnRequest->reason))" :subtitle="ucfirst($returnRequest->request_type).' · Order '.($returnRequest->order->order_number ?? '—').' · '.($returnRequest->user->name ?? '—')">
        <x-slot name="action">
            <a href="{{ route('admin.wear.returns.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />All requests</a>
            <x-admin.badge :status="$typeTone" :label="ucfirst($returnRequest->request_type)" />
            <x-admin.badge :status="$statusTone" :label="ucwords(str_replace('_', ' ', $returnRequest->status))" />
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <div class="space-y-6">
            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Customer Notes</h3>
                <p class="mt-3 whitespace-pre-wrap text-sm leading-6 text-slate-600">{{ $returnRequest->notes ?: 'No notes provided.' }}</p>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Requested Items</h3>
                <div class="mt-4 divide-y divide-slate-100">
                    @foreach($returnRequest->order->items->whereIn('id', $returnRequest->order_item_ids ?? []) as $item)
                        <div class="flex justify-between py-3 text-sm">
                            <span class="text-slate-700">{{ $item->product_name }} · {{ $item->size }} / {{ $item->color }}</span>
                            <span class="font-medium text-slate-900">×{{ $item->quantity }}</span>
                        </div>
                    @endforeach
                </div>
            </x-admin.card>
        </div>

        <x-admin.card class="h-fit p-6">
            <h3 class="kp-admin-display text-lg">Process Request</h3>
            <form method="POST" action="{{ route('admin.wear.returns.status', $returnRequest) }}" class="mt-5 space-y-4">
                @csrf
                <p class="text-xs uppercase tracking-wider text-slate-400">Current status — {{ ucwords(str_replace('_', ' ', $returnRequest->status)) }}</p>
                <select name="status" @if($returnNext) required @endif class="kp-admin-input" @if(!$returnNext) disabled @endif>
                    <option value="" disabled selected>@if($returnNext)Choose next status…@else No further status changes available.@endif</option>
                    @foreach($returnNext as $s)<option value="{{ $s }}">{{ ucwords(str_replace('_', ' ', $s)) }}</option>@endforeach
                </select>
                <textarea name="notes" rows="5" placeholder="Admin notes" class="kp-admin-input">{{ $returnRequest->notes }}</textarea>
                <button class="kp-admin-primary-button w-full">Save status</button>
            </form>
        </x-admin.card>
    </div>

    @if($returnRequest->status === 'processed' && $returnRequest->refund_status === 'pending')
        <x-admin.card class="mt-6 border border-amber-200 bg-amber-50 p-6">
            <h3 class="kp-admin-display text-lg">Record Completed Refund</h3>
            <p class="mt-1.5 text-xs text-amber-800">Enter the provider or manual refund reference only after the money has actually been refunded.</p>
            <form method="POST" action="{{ route('admin.wear.returns.refund', $returnRequest) }}" class="mt-4 flex flex-col gap-3 sm:flex-row">
                @csrf
                <input name="refund_reference" required maxlength="120" placeholder="Refund reference" class="kp-admin-input flex-1">
                @if(\App\Support\AdminStepUp::enabled())
                    <input name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="Authenticator code" class="kp-admin-input sm:w-44">
                @endif
                <button class="kp-admin-primary-button">Record refund</button>
            </form>
        </x-admin.card>
    @endif
</div>
@endsection
