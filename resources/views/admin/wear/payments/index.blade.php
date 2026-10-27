@extends('admin.layouts.app', ['title' => 'Payments', 'heading' => 'Payments'])

@section('content')
<div>
    <x-admin.page-header title="Payments" subtitle="Review payment transactions without changing gateway state from the admin panel." />

    @if(($attentionCount ?? 0) > 0)
        <a href="{{ route('admin.wear.payments.index', ['status' => 'reconciliation_required']) }}" class="mb-6 flex items-center justify-between gap-4 rounded-xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm font-medium text-rose-800 transition-colors hover:bg-rose-100">
            <span>{{ $attentionCount }} payment{{ $attentionCount === 1 ? '' : 's' }} need attention: money captured that cannot be fulfilled, or a payment awaiting review.</span>
            <span class="shrink-0 font-semibold">Review <x-tabler-arrow-right size="13" /></span>
        </a>
    @endif

    <form method="GET" class="mb-5 grid gap-3 md:grid-cols-[minmax(0,1fr)_220px_auto]">
        <input type="search" name="q" value="{{ $search }}" placeholder="Search order, customer, phone, provider or reference…" class="kp-admin-input" />
        <select name="status" class="kp-admin-input">
            <option value="all">All statuses</option>
            @foreach($statuses as $paymentStatus)
                <option value="{{ $paymentStatus->value }}" @selected($status === $paymentStatus->value)>{{ str_replace('_', ' ', ucfirst($paymentStatus->value)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="kp-admin-primary-button">Filter</button>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-[880px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Order</th>
                        <th class="px-3 py-3.5 text-left font-medium">Customer</th>
                        <th class="px-3 py-3.5 text-right font-medium">Amount</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Provider</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Reference</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="px-6 py-3.5 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payments as $payment)
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
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60 {{ $paymentStatusValue === 'reconciliation_required' ? 'bg-rose-50/60' : '' }}">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.wear.orders.show', $payment->order) }}" class="font-medium text-slate-900 hover:text-emerald-600">#{{ $payment->order?->order_number ?? '—' }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $payment->created_at?->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <p class="font-medium text-slate-800">{{ $payment->order?->customer_name ?: $payment->user?->name ?: 'Guest' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $payment->order?->customer_phone ?: $payment->user?->phone ?: '—' }}</p>
                            </td>
                            <td class="px-3 py-3.5 text-right font-medium text-slate-900">{{ $payment->currency }} {{ number_format((float) $payment->amount, 0) }}</td>
                            <td class="hidden px-3 py-3.5 text-slate-600 sm:table-cell">{{ $payment->provider }}</td>
                            <td class="hidden px-3 py-3.5 font-mono text-xs text-slate-500 md:table-cell">{{ $payment->provider_reference ?: '—' }}</td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$paymentTone" :label="str_replace('_', ' ', ucfirst($paymentStatusValue))" /></td>
                            <td class="px-6 py-3.5 text-right"><a href="{{ route('admin.wear.payments.show', $payment) }}" class="kp-admin-link">View <x-tabler-arrow-right size="13" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty message="No payment transactions found." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $payments->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
