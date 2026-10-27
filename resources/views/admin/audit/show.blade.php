@extends('admin.layouts.app', ['title' => 'Audit Event', 'heading' => 'Audit Event'])

@section('content')
<div class="mx-auto max-w-4xl">
    <x-admin.page-header title="Audit Event" subtitle="Detailed record of an administrative action.">
        <x-slot name="action">
            <a href="{{ route('admin.audit.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />Back to audit log</a>
        </x-slot>
    </x-admin.page-header>

    <div class="space-y-4">
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
                <x-admin.card class="p-5">
                    <p class="text-xs uppercase tracking-wider text-slate-400">{{ $label }}</p>
                    <p class="mt-2 break-words text-sm font-medium text-slate-800">{{ $value }}</p>
                </x-admin.card>
            @endforeach
        </div>

        <x-admin.card class="p-5">
            <p class="text-xs uppercase tracking-wider text-slate-400">Metadata</p>
            @if($auditLog->metadata)
                <pre class="mt-3 overflow-x-auto rounded-xl bg-slate-950 p-4 text-xs leading-6 text-slate-100">{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
            @else
                <p class="mt-3 text-sm text-slate-400">No metadata recorded.</p>
            @endif
        </x-admin.card>

        @if($auditLog->user_agent)
            <x-admin.card class="p-5">
                <p class="text-xs uppercase tracking-wider text-slate-400">User Agent</p>
                <p class="mt-2 break-words font-mono text-xs leading-5 text-slate-600">{{ $auditLog->user_agent }}</p>
            </x-admin.card>
        @endif
    </div>
</div>
@endsection
