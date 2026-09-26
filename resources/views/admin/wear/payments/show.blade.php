@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <a href="{{ route('admin.wear.payments.index') }}" class="text-sm font-bold text-black hover:text-black">← Payments</a>
            <h2 class="mt-2 text-2xl font-black tracking-tight">Payment #{{ $payment->id }}</h2>
            <p class="mt-1 text-sm text-black">Transaction initiated {{ $payment->initiated_at?->format('d M Y, H:i') ?: '—' }}</p>
        </div>
        <span class="inline-flex w-fit rounded-full bg-white px-3 py-1.5 text-sm font-bold text-black">{{ ucfirst($payment->status->value) }}</span>
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <section class="rounded-2xl border border-black bg-white p-6 lg:col-span-2">
            <h3 class="font-black">Transaction</h3>
            <dl class="mt-5 grid gap-5 sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Amount</dt><dd class="mt-1 text-2xl font-black">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Provider</dt><dd class="mt-1 font-semibold">{{ $payment->provider }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Provider reference</dt><dd class="mt-1 break-all font-mono text-sm">{{ $payment->provider_reference ?: '—' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Idempotency key</dt><dd class="mt-1 break-all font-mono text-xs text-black">{{ $payment->idempotency_key }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Completed</dt><dd class="mt-1">{{ $payment->completed_at?->format('d M Y, H:i') ?: '—' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-black">Failed</dt><dd class="mt-1">{{ $payment->failed_at?->format('d M Y, H:i') ?: '—' }}</dd></div>
            </dl>
        </section>

        <section class="rounded-2xl border border-black bg-white p-6">
            <h3 class="font-black">Order</h3>
            @if($payment->order)
                <a href="{{ route('admin.wear.orders.show', $payment->order) }}" class="mt-4 block rounded-xl border border-black p-4 hover:border-black">
                    <p class="font-black">#{{ $payment->order->order_number }}</p>
                    <p class="mt-1 text-sm text-black">{{ number_format((float) $payment->order->total, 0) }} TZS</p>
                    <p class="mt-3 text-sm font-bold">View order →</p>
                </a>
            @else
                <p class="mt-4 text-sm text-black">Order record unavailable.</p>
            @endif
        </section>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-2xl border border-black bg-white p-6">
            <h3 class="font-black">Customer</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-black">Name</dt><dd class="text-right font-semibold">{{ $payment->order?->customer_name ?: $payment->user?->name ?: 'Guest' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-black">Phone</dt><dd class="text-right">{{ $payment->order?->customer_phone ?: $payment->user?->phone ?: '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-black">Email</dt><dd class="break-all text-right">{{ $payment->order?->customer_email ?: $payment->user?->email ?: '—' }}</dd></div>
            </dl>
        </section>
        <section class="rounded-2xl border border-black bg-white p-6">
            <h3 class="font-black">Order payment state</h3>
            <dl class="mt-4 space-y-3 text-sm">
                <div class="flex justify-between gap-4"><dt class="text-black">Order payment</dt><dd class="font-bold">{{ $payment->order?->payment_status?->value ? ucfirst($payment->order->payment_status->value) : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-black">Order fulfillment</dt><dd class="font-bold">{{ $payment->order?->status?->value ? ucfirst(str_replace('_', ' ', $payment->order->status->value)) : '—' }}</dd></div>
                <div class="flex justify-between gap-4"><dt class="text-black">Payment method</dt><dd>{{ $payment->order?->payment_method ?: '—' }}</dd></div>
            </dl>
        </section>
    </div>

    @if(session('status'))
        <p class="rounded-xl bg-emerald-50 px-4 py-3 text-sm font-bold text-emerald-800">{{ session('status') }}</p>
    @endif
    @if($errors->any())
        <div class="rounded-xl bg-red-50 px-4 py-3 text-sm font-bold text-red-800">
            @foreach($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    @php($needsRefund = ($payment->payload['needs_refund'] ?? false) === true)
    @php($needsReview = ($payment->payload['needs_review'] ?? false) === true)
    @if($payment->status->value === 'reconciliation_required')
        <section class="rounded-2xl border border-red-200 bg-red-50 p-6">
            <h3 class="font-black text-red-900">{{ $needsRefund ? 'Refund required' : 'Needs review' }}</h3>
            <p class="mt-2 text-sm text-red-800">
                @if($needsRefund)
                    The provider captured this customer's money, but the order cannot be fulfilled. Refund the customer in the Selcom dashboard, then record the refund reference here.
                @else
                    The provider reported this payment as completed, but it could not be verified automatically (for example the amount was missing).
                @endif
            </p>
            <dl class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                <div><dt class="text-xs font-bold uppercase tracking-wider text-red-700">Reason</dt><dd class="mt-1 font-mono text-xs">{{ $payment->payload['reconciliation_reason'] ?? '—' }}</dd></div>
                <div><dt class="text-xs font-bold uppercase tracking-wider text-red-700">Provider transaction</dt><dd class="mt-1 font-mono text-xs">{{ $payment->provider_transid ?: '—' }}</dd></div>
            </dl>

            <div class="mt-5 flex flex-col gap-4 sm:flex-row sm:items-start">
                <form method="POST" action="{{ route('admin.wear.payments.recheck', $payment) }}">
                    @csrf
                    <button class="rounded-xl border border-black bg-white px-4 py-2.5 text-sm font-bold hover:border-black">Re-check with provider</button>
                </form>

                @if($needsRefund)
                    <form method="POST" action="{{ route('admin.wear.payments.refund', $payment) }}" data-confirm="Record this refund? This closes the reconciliation item." class="flex flex-1 flex-col gap-2 sm:flex-row">
                        @csrf
                        <input name="refund_reference" value="{{ old('refund_reference') }}" required minlength="4" maxlength="100" placeholder="Provider refund reference" class="w-full rounded-xl border border-black px-4 py-2.5 text-sm outline-none focus:border-black">
                        @if(\App\Support\AdminStepUp::enabled())
                            <input name="totp_code" inputmode="numeric" pattern="[0-9]{6}" maxlength="6" required autocomplete="one-time-code" placeholder="Authenticator code" class="w-full rounded-xl border border-black px-4 py-2.5 text-sm outline-none focus:border-black sm:w-44">
                        @endif
                        <button class="rounded-xl bg-black px-4 py-2.5 text-sm font-bold text-white hover:bg-black">Record refund</button>
                    </form>
                @endif
            </div>
        </section>
    @elseif(in_array($payment->status->value, ['pending', 'processing', 'in_progress'], true))
        <section class="rounded-2xl border border-black bg-white p-6">
            <h3 class="font-black">Unresolved payment</h3>
            <p class="mt-2 text-sm text-black">The system checks the provider automatically every few minutes. You can also check now.</p>
            <form method="POST" action="{{ route('admin.wear.payments.recheck', $payment) }}" class="mt-4">
                @csrf
                <button class="rounded-xl border border-black bg-white px-4 py-2.5 text-sm font-bold hover:border-black">Re-check with provider</button>
            </form>
        </section>
    @endif

    <section class="rounded-2xl border border-black bg-white p-6">
        <h3 class="font-black">Admin note</h3>
        <p class="mt-2 text-sm text-black">Payment state changes only through gateway callbacks, the provider re-check above, or by recording a refund for a flagged payment.</p>
    </section>
</div>
@endsection
