@extends('admin.layouts.app', ['title' => 'Payment #'.$payment->id, 'heading' => 'Payment #'.$payment->id])

@section('content')
@php
    $paymentStatusValue = $payment->status->value;
    $paymentTone = match ($paymentStatusValue) {
        'paid' => 'green',
        'failed', 'cancelled', 'user_cancelled', 'rejected' => 'red',
        'pending', 'reconciliation_required' => 'amber',
        'processing', 'in_progress' => 'blue',
        'refunded' => 'purple',
        default => 'gray',
    };
@endphp

<div>
    <x-admin.page-header :title="'Payment #'.$payment->id" :subtitle="'Transaction initiated '.($payment->initiated_at?->format('d M Y, H:i') ?: '—')">
        <x-slot name="action">
            <a href="{{ route('admin.wear.payments.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />Payments</a>
            <x-admin.badge :status="$paymentTone" :label="str_replace('_', ' ', ucfirst($paymentStatusValue))" />
        </x-slot>
    </x-admin.page-header>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-admin.card class="p-6 lg:col-span-2">
            <h3 class="kp-admin-display text-lg">Transaction</h3>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Amount</dt>
                    <dd class="mt-1 font-display text-2xl text-slate-950">{{ $payment->currency }} {{ number_format((float) $payment->amount, 0) }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Provider</dt>
                    <dd class="mt-1 font-medium text-slate-800">{{ $payment->provider }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Provider reference</dt>
                    <dd class="mt-1 break-all font-mono text-sm text-slate-700">{{ $payment->provider_reference ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Idempotency key</dt>
                    <dd class="mt-1 break-all font-mono text-xs text-slate-500">{{ $payment->idempotency_key }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Completed</dt>
                    <dd class="mt-1 text-slate-700">{{ $payment->completed_at?->format('d M Y, H:i') ?: '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs uppercase tracking-wider text-slate-400">Failed</dt>
                    <dd class="mt-1 text-slate-700">{{ $payment->failed_at?->format('d M Y, H:i') ?: '—' }}</dd>
                </div>
            </dl>
        </x-admin.card>

        <x-admin.card class="p-6">
            <h3 class="kp-admin-display text-lg">Order</h3>
            @if($payment->order)
                <a href="{{ route('admin.wear.orders.show', $payment->order) }}" class="mt-4 block rounded-xl border border-slate-200 p-4 transition-colors hover:border-emerald-400 hover:bg-emerald-50/40">
                    <p class="font-medium text-slate-900">#{{ $payment->order->order_number }}</p>
                    <p class="mt-1 text-sm text-slate-500">TZS {{ number_format((float) $payment->order->total, 0) }}</p>
                    <p class="mt-3 kp-admin-link">View order <x-tabler-arrow-right size="13" /></p>
                </a>
            @else
                <p class="mt-4 text-sm text-slate-400">Order record unavailable.</p>
            @endif
        </x-admin.card>
    </div>

    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <x-admin.card class="p-6">
            <h3 class="kp-admin-display text-lg">Customer</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Name</dt><dd class="text-right font-medium text-slate-800">{{ $payment->order?->customer_name ?: $payment->user?->name ?: 'Guest' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Phone</dt><dd class="text-right text-slate-700">{{ $payment->order?->customer_phone ?: $payment->user?->phone ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Email</dt><dd class="break-all text-right text-slate-700">{{ $payment->order?->customer_email ?: $payment->user?->email ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>

        <x-admin.card class="p-6">
            <h3 class="kp-admin-display text-lg">Order Payment State</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Order payment</dt><dd class="font-medium text-slate-800">{{ $payment->order?->payment_status?->value ? ucfirst($payment->order->payment_status->value) : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Order fulfillment</dt><dd class="font-medium text-slate-800">{{ $payment->order?->status?->value ? ucfirst(str_replace('_', ' ', $payment->order->status->value)) : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-slate-500">Payment method</dt><dd class="text-slate-700">{{ $payment->order?->payment_method ?: '—' }}</dd></div>
            </dl>
        </x-admin.card>
    </div>

    @if(session('status'))
        <div class="mt-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="mt-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @php($needsRefund = ($payment->payload['needs_refund'] ?? false) === true)
    @php($needsReview = ($payment->payload['needs_review'] ?? false) === true)
    @if($paymentStatusValue === 'reconciliation_required')
        <x-admin.card class="mt-6 border border-rose-200 bg-rose-50 p-6">
            <h3 class="kp-admin-display text-lg text-rose-900">{{ $needsRefund ? 'Refund required' : 'Needs review' }}</h3>
            <p class="mt-2 text-sm text-rose-800">
                @if($needsRefund)
                    The provider captured this customer's money, but the order cannot be fulfilled. Refund the customer in the Selcom dashboard, then record the refund reference here.
                @else
                    The provider reported this payment as completed, but it could not be verified automatically (for example the amount was missing).
                @endif
            </p>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-xs uppercase tracking-wider text-rose-700">Reason</dt><dd class="mt-1 font-mono text-xs text-rose-900">{{ $payment->payload['reconciliation_reason'] ?? '—' }}</dd></div>
                <div><dt class="text-xs uppercase tracking-wider text-rose-700">Provider transaction</dt><dd class="mt-1 font-mono text-xs text-rose-900">{{ $payment->provider_transid ?: '—' }}</dd></div>
            </dl>

            <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start">
                <form method="POST" action="{{ route('admin.wear.payments.recheck', $payment) }}">
                    @csrf
                    <button class="kp-admin-secondary-button">Re-check with provider</button>
                </form>

                @if($needsRefund)
                    <form method="POST" action="{{ route('admin.wear.payments.refund', $payment) }}" data-confirm="Record this refund? This closes the reconciliation item." class="flex flex-1 flex-col gap-2 sm:flex-row">
                        @csrf
                        <input name="refund_reference" value="{{ old('refund_reference') }}" required minlength="4" maxlength="100" placeholder="Provider refund reference" class="kp-admin-input">
                        @if(\App\Support\AdminStepUp::enabled())
                            <input name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="Authenticator code" class="kp-admin-input sm:w-44">
                        @endif
                        <button class="kp-admin-primary-button">Record refund</button>
                    </form>
                @endif
            </div>
        </x-admin.card>
    @elseif(in_array($paymentStatusValue, ['pending', 'processing', 'in_progress'], true))
        <x-admin.card class="mt-6 p-6">
            <h3 class="kp-admin-display text-lg">Unresolved Payment</h3>
            <p class="mt-1.5 text-xs text-slate-400">The system checks the provider automatically every few minutes. You can also check now.</p>
            <form method="POST" action="{{ route('admin.wear.payments.recheck', $payment) }}" class="mt-4">
                @csrf
                <button class="kp-admin-secondary-button">Re-check with provider</button>
            </form>
        </x-admin.card>
    @endif

    <x-admin.card class="mt-6 p-6">
        <h3 class="kp-admin-display text-lg">Admin Note</h3>
        <p class="mt-1.5 text-xs text-slate-400">Payment state changes only through gateway callbacks, the provider re-check above, or by recording a refund for a flagged payment.</p>
    </x-admin.card>
</div>
@endsection
