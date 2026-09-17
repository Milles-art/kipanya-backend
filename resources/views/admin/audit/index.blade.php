@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p>
        <h2 class="mt-1 text-2xl font-black tracking-tight">Audit Log</h2>
        <p class="mt-1 text-sm text-gray-500">Review important administrative actions and system activity.</p>
    </div>

    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4">
        <div class="grid gap-3 md:grid-cols-2 xl:grid-cols-[1.4fr_1fr_1fr_160px_160px_auto]">
            <input name="q" value="{{ $search }}" placeholder="Search action, user, IP or request ID..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <input name="action" value="{{ $action }}" placeholder="Action e.g. product.updated" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <input name="actor" value="{{ $actor }}" placeholder="Admin name or email..." class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <input type="date" name="from" value="{{ $from }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <input type="date" name="to" value="{{ $to }}" class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
            <button class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">Filter</button>
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wider text-gray-400">
                    <tr>
                        <th class="px-5 py-4 font-bold">Time</th>
                        <th class="px-5 py-4 font-bold">Actor</th>
                        <th class="px-5 py-4 font-bold">Action</th>
                        <th class="px-5 py-4 font-bold">Resource</th>
                        <th class="px-5 py-4 font-bold">IP</th>
                        <th class="px-5 py-4"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <p class="font-semibold">{{ $log->created_at?->format('d M Y') }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $log->created_at?->format('H:i:s') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $log->actor?->name ?: 'System / Unknown' }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $log->actor?->email ?: ($log->actor?->phone ?: '—') }}</p>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-gray-100 px-2.5 py-1 font-mono text-xs font-bold text-gray-700">{{ $log->action }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <p class="font-semibold">{{ $log->auditable_type ? class_basename($log->auditable_type) : '—' }}</p>
                                <p class="mt-1 text-xs text-gray-400">{{ $log->auditable_id ? '#'.$log->auditable_id : 'No resource' }}</p>
                            </td>
                            <td class="px-5 py-4 font-mono text-xs text-gray-500">{{ $log->ip_address ?: '—' }}</td>
                            <td class="px-5 py-4 text-right">
                                <a href="{{ route('admin.audit.show', $log) }}" class="font-bold text-gray-900 hover:text-emerald-700">View →</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-5 py-14 text-center text-sm text-gray-500">No audit events found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($logs->hasPages())
            <div class="border-t border-gray-100 px-5 py-4">{{ $logs->links() }}</div>
        @endif
    </div>
</div>
@endsection
