@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Payments</h2>
            <p class="mt-1 text-sm text-gray-500">Review payment transactions without changing gateway state from the admin panel.</p>
        </div>
    </div>

    @if(($attentionCount ?? 0) > 0)
        <a href="{{ route('admin.wear.payments.index', ['status' => 'reconciliation_required']) }}" class="flex items-center justify-between gap-4 rounded-2xl border border-red-200 bg-red-50 px-5 py-4 text-sm font-bold text-red-800 hover:bg-red-100">
            <span>{{ $attentionCount }} payment{{ $attentionCount === 1 ? '' : 's' }} need attention: money captured that cannot be fulfilled, or a payment awaiting review.</span>
            <span aria-hidden="true">Review →</span>
        </a>
    @endif

    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4">
        <div class="grid gap-3 md:grid-cols-[1fr_220px_auto]">
            <input name="q" value="{{ $search }}" placeholder="Search order, customer, phone, provider or reference..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <select name="status" class="rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
                <option value="all">All statuses</option>
                @foreach($statuses as $paymentStatus)
                    <option value="{{ $paymentStatus->value }}" @selected($status === $paymentStatus->value)>{{ str_replace('_', ' ', ucfirst($paymentStatus->value)) }}</option>
                @endforeach
            </select>
            <button class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">Filter</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wider text-gray-400">
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
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $payment)
                        <tr class="{{ $payment->status->value === 'reconciliation_required' ? 'bg-red-50/60' : '' }} hover:bg-gray-50">
                            <td class="px-5 py-4">
                                <a href="{{ route('admin.wear.orders.show', $payment->order) }}" class="font-bold hover:text-emerald-700">#{{ $payment->order?->order_number ?? '—' }}</a>
                                <p class="mt-1 text-xs text-gray-400">{{ $payment->created_at?->format('d M Y, H:i') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $payment->order?->customer_name ?: $payment->user?->name ?: 'Guest' }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $payment->order?->customer_phone ?: $payment->user?->phone ?: '—' }}</p>
                            </td>
                            <td class="px-5 py-4 font-black">{{ number_format((float) $payment->amount, 0) }} {{ $payment->currency }}</td>
                            <td class="px-5 py-4">{{ $payment->provider }}</td>
                            <td class="px-5 py-4 font-mono text-xs">{{ $payment->provider_reference ?: '—' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 text-xs font-bold text-gray-700">{{ ucfirst($payment->status->value) }}</span>
                            </td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.wear.payments.show', $payment) }}" class="font-bold text-gray-900 hover:text-emerald-700">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-5 py-14 text-center text-sm text-gray-500">No payment transactions found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
