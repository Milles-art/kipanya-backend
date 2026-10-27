@extends('admin.layouts.app', ['title' => 'Returns & Refunds', 'heading' => 'Returns & Refunds'])

@section('content')
<div>
    <x-admin.page-header title="Returns & Refunds" subtitle="Review customer return, exchange, and refund requests with full order context — {{ number_format($requests->total()) }} request{{ $requests->total() === 1 ? '' : 's' }} in the queue." />

    <form method="GET" class="mb-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_200px_200px_auto]">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search order, customer, or request" class="kp-admin-input" />
        <select name="status" class="kp-admin-input">
            <option value="">All statuses</option>
            @foreach(['under_review','approved','rejected','received','refunded','completed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <select name="request_type" class="kp-admin-input">
            <option value="">Return + exchange</option>
            <option value="return" @selected(request('request_type') === 'return')>Return</option>
            <option value="exchange" @selected(request('request_type') === 'exchange')>Exchange</option>
        </select>
        <button type="submit" class="kp-admin-primary-button">Filter</button>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-[820px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Request</th>
                        <th class="px-3 py-3.5 text-left font-medium">Customer</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Order</th>
                        <th class="px-3 py-3.5 text-left font-medium">Type</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Requested</th>
                        <th class="px-6 py-3.5 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $returnRequest)
                        @php
                            $status = strtolower((string) $returnRequest->status);
                            $statusTone = match ($status) {
                                'approved', 'completed' => 'green',
                                'rejected' => 'red',
                                'received' => 'blue',
                                'refunded' => 'purple',
                                default => 'amber',
                            };
                            $typeTone = $returnRequest->request_type === 'exchange' ? 'blue' : 'amber';
                        @endphp
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.wear.returns.show', $returnRequest) }}" class="font-medium text-slate-900 hover:text-emerald-600">#{{ $returnRequest->id }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $returnRequest->reason }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <p class="font-medium text-slate-800">{{ $returnRequest->user->name ?? '—' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $returnRequest->user->email ?? '' }}</p>
                            </td>
                            <td class="hidden px-3 py-3.5 font-medium text-slate-700 sm:table-cell">{{ $returnRequest->order->order_number ?? '—' }}</td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$typeTone" :label="ucfirst($returnRequest->request_type)" /></td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$statusTone" :label="ucwords(str_replace('_', ' ', $status))" /></td>
                            <td class="hidden px-3 py-3.5 text-slate-500 md:table-cell">{{ $returnRequest->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-3.5 text-right"><a href="{{ route('admin.wear.returns.show', $returnRequest) }}" class="kp-admin-link">Review <x-tabler-arrow-right size="13" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7"><x-admin.empty message="No return requests match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($requests->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $requests->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
