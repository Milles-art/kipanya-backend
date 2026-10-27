@extends('admin.layouts.app', ['title' => 'Enquiries', 'heading' => 'Enquiries'])

@section('content')
<div>
    <x-admin.page-header title="Enquiries" subtitle="Read and resolve messages sent from the KP Wear storefront — {{ number_format($messages->total()) }} message{{ $messages->total() === 1 ? '' : 's' }} in the inbox." />

    <form method="GET" class="mb-5 grid gap-3 lg:grid-cols-[minmax(0,1fr)_180px_180px_auto]">
        <input type="search" name="q" value="{{ request('q') }}" placeholder="Search customer, email, or message" class="kp-admin-input" />
        <input name="type" value="{{ request('type') }}" placeholder="Enquiry type" class="kp-admin-input">
        <select name="status" class="kp-admin-input">
            <option value="">All statuses</option>
            @foreach(['new','read','in_progress','resolved','closed'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucwords(str_replace('_', ' ', $status)) }}</option>
            @endforeach
        </select>
        <button type="submit" class="kp-admin-primary-button">Filter</button>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-[820px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Customer</th>
                        <th class="px-3 py-3.5 text-left font-medium">Type</th>
                        <th class="px-3 py-3.5 text-left font-medium">Message</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Received</th>
                        <th class="px-6 py-3.5 text-right font-medium">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $message)
                        @php
                            $status = strtolower((string) $message->status);
                            $statusTone = match ($status) {
                                'resolved' => 'green',
                                'closed' => 'purple',
                                'read', 'in_progress' => 'blue',
                                default => 'amber',
                            };
                        @endphp
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <a href="{{ route('admin.wear.enquiries.show', $message) }}" class="font-medium text-slate-900 hover:text-emerald-600">{{ $message->name }}</a>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $message->email }}</p>
                            </td>
                            <td class="px-3 py-3.5 capitalize text-slate-600">{{ $message->type ?: 'General' }}</td>
                            <td class="max-w-md px-3 py-3.5"><p class="truncate text-slate-600">{{ $message->message }}</p></td>
                            <td class="px-3 py-3.5"><x-admin.badge :status="$statusTone" :label="ucwords(str_replace('_', ' ', $status))" /></td>
                            <td class="hidden px-3 py-3.5 text-slate-500 md:table-cell">{{ $message->created_at->format('d M Y, H:i') }}</td>
                            <td class="px-6 py-3.5 text-right"><a href="{{ route('admin.wear.enquiries.show', $message) }}" class="kp-admin-link">Open <x-tabler-arrow-right size="13" /></a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty message="No enquiries match your filters." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $messages->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
