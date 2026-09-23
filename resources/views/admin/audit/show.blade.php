@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex items-start justify-between gap-4">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Audit Event</h2>
            <p class="mt-1 text-sm text-black">Detailed record of an administrative action.</p>
        </div>
        <a href="{{ route('admin.audit.index') }}" class="rounded-xl border border-black bg-white px-4 py-2 text-sm font-bold hover:bg-white">← Back</a>
    </div>

    <div class="grid gap-4 sm:grid-cols-2">
        @foreach([
            'Action' => $auditLog->action,
            'Actor' => $auditLog->actor?->name ?: 'System / Unknown',
            'Email / Phone' => $auditLog->actor?->email ?: ($auditLog->actor?->phone ?: '—'),
            'Created' => $auditLog->created_at?->format('d M Y, H:i:s'),
            'Resource' => $auditLog->auditable_type ? class_basename($auditLog->auditable_type) : '—',
            'Resource ID' => $auditLog->auditable_id ?: '—',
            'IP address' => $auditLog->ip_address ?: '—',
            'Request ID' => $auditLog->request_id ?: '—',
        ] as $label => $value)
            <div class="rounded-2xl border border-black bg-white p-5">
                <p class="text-xs font-bold uppercase tracking-wider text-black">{{ $label }}</p>
                <p class="mt-2 break-words text-sm font-semibold text-black">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <div class="rounded-2xl border border-black bg-white p-5">
        <p class="text-xs font-bold uppercase tracking-wider text-black">Metadata</p>
        @if($auditLog->metadata)
            <pre class="mt-3 overflow-x-auto rounded-xl bg-black p-4 text-xs leading-6 text-black">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
        @else
            <p class="mt-3 text-sm text-black">No metadata recorded.</p>
        @endif
    </div>

    @if($auditLog->user_agent)
        <div class="rounded-2xl border border-black bg-white p-5">
            <p class="text-xs font-bold uppercase tracking-wider text-black">User Agent</p>
            <p class="mt-2 break-words font-mono text-xs leading-5 text-black">{{ $auditLog->user_agent }}</p>
        </div>
    @endif
</div>
@endsection
