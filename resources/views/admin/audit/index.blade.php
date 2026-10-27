@extends('admin.layouts.app', ['title' => 'Audit Log', 'heading' => 'Audit Log'])

@section('content')
<div>
    <x-admin.page-header title="Audit Log" subtitle="Review important administrative actions and system activity." />

    <form method="GET" action="{{ route('admin.audit.index') }}" class="mb-5">
        <x-admin.card class="p-4">
            <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_160px_160px_auto]">
                <input name="q" value="{{ $search }}" placeholder="Search action, user, IP or request ID..." class="kp-admin-input" aria-label="Search audit log">
                <input name="action" value="{{ $action }}" placeholder="Action e.g. product.updated" class="kp-admin-input" aria-label="Filter by action">
                <input name="actor" value="{{ $actor }}" placeholder="Admin name or email..." class="kp-admin-input" aria-label="Filter by actor">
                <input type="date" name="from" value="{{ $from }}" class="kp-admin-input" aria-label="From date">
                <input type="date" name="to" value="{{ $to }}" class="kp-admin-input" aria-label="To date">
                <button type="submit" class="kp-admin-primary-button">Filter</button>
            </div>
        </x-admin.card>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Time</th>
                        <th class="px-3 py-3.5 text-left font-medium">Actor</th>
                        <th class="px-3 py-3.5 text-left font-medium">Action</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium md:table-cell">Resource</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium lg:table-cell">IP</th>
                        <th class="px-6 py-3.5 text-right font-medium"><span class="sr-only">Details</span></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="whitespace-nowrap px-6 py-3.5">
                                <p class="font-medium text-slate-900">{{ $log->created_at?->format('d M Y') }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $log->created_at?->format('H:i:s') }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <p class="font-medium text-slate-800">{{ $log->actor?->name ?: 'System / Unknown' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $log->actor?->email ?: ($log->actor?->phone ?: '—') }}</p>
                            </td>
                            <td class="px-3 py-3.5">
                                <span class="inline-flex whitespace-nowrap rounded-full border border-slate-200 bg-slate-100 px-2.5 py-1 font-mono text-xs font-medium text-slate-600">{{ $log->action }}</span>
                            </td>
                            <td class="hidden px-3 py-3.5 md:table-cell">
                                <p class="font-medium text-slate-800">{{ $log->auditable_type ? class_basename($log->auditable_type) : '—' }}</p>
                                <p class="mt-0.5 text-xs text-slate-400">{{ $log->auditable_id ? '#'.$log->auditable_id : 'No resource' }}</p>
                            </td>
                            <td class="hidden px-3 py-3.5 font-mono text-xs text-slate-500 lg:table-cell">{{ $log->ip_address ?: '—' }}</td>
                            <td class="px-6 py-3.5 text-right">
                                <a href="{{ route('admin.audit.show', $log) }}" class="kp-admin-link">View <x-tabler-arrow-right size="13" /></a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty message="No audit events found." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $logs->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
