@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Payments</h2>
            <p class="mt-1 text-sm text-black">Review payment transactions without changing gateway state from the admin panel.</p>
        </div>
    </div>

    @if(($attentionCount ?? 0) > 0)
        <a href="{{ route('admin.wear.payments.index', ['status' => 'reconciliation_required']) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 hover:bg-red-100">
            <span>{{ $attentionCount }} payment{{ $attentionCount === 1 ? '' : 's' }} need attention: money captured that cannot be fulfilled, or a payment awaiting review.</span>
            <span aria-hidden="true">Review →</span>
        </a>
    @endif

    <form method="GET" class="rounded-2xl border border-black bg-white p-4">
        <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input name="q" value="{{ $search }}" placeholder="Search order, customer, phone, provider or reference..." class="w-full rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black">
            <select name="status" class="rounded-xl border border-black px-4 py-3 text-sm outline-none focus:border-black">
                <option value="all">All statuses</option>
                @foreach($statuses as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected($status === $paymentStatus->value)>{{ str_replace('_', ' ', ucfirst($paymentStatus->value)) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-black px-5 py-3 text-sm font-bold text-white hover:bg-black">Filter</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-black bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-black bg-white text-xs uppercase tracking-wider text-black">
                    <tr>
                        <th class="px-5 py-4 font-bold">Order</th>
                        <th class="px-5 py-4 font-bold">Customer</th>
                        <th class="px-5 py-4 font-bold">Amount</th>
                        <th class="px-5 py-4 font-bold">Provider</th>
                        <th class="px-5 py-4 font-bold">Reference</th>
                        <th class="px-5 py-4 font-bold">Status</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-black">
                    @forelse($payments as $payment)
                        <tr class="{{ $payment->status->value === 'reconciliation_required' ? 'bg-red-50/60' : '' }} hover:bg-white">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.wear.orders.show', $payment->order) }}" class="font-bold hover:text-emerald-700">#{{ $payment->order?->order_number ?? '—' }}</a>
                                <p class="mt-1 text-xs text-black">{{ $payment->created_at?->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $payment->order?->customer_name ?: $payment->user?->name ?: 'Guest' }}</p>
                                <p class="mt-1 text-xs text-black">{{ $payment->order?->customer_phone ?: $payment->user?->phone ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-4 font-black">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</td>
                            <td class="px-5 py-4">{{ $payment->provider }}</td>
                            <td class="px-5 py-4 font-mono text-xs">{{ $payment->provider_reference ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-white px-2.5 py-1 text-xs font-bold text-black">{{ ucfirst($payment->status->value) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.wear.payments.show', $payment) }}" class="font-bold text-black hover:text-emerald-700">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-black">No payment transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="border-t border-black px-5 py-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
