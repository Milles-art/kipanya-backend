@extends('admin.layouts.app', ['title' => 'Enquiry', 'heading' => 'Customer Enquiry'])

@section('content')
@php
    $statusTone = match (strtolower((string) $message->status)) {
        'new' => 'amber',
        'resolved' => 'green',
        'closed' => 'purple',
        default => 'blue',
    };
@endphp
<div>
    <x-admin.page-header :title="$message->name" :subtitle="$message->type.' · '.$message->email">
        <x-slot name="action">
            <a href="{{ route('admin.wear.enquiries.index') }}" class="kp-admin-secondary-button"><x-tabler-arrow-left size="16" />All enquiries</a>
            <x-admin.badge :status="$statusTone" :label="ucwords(str_replace('_', ' ', (string) $message->status))" />
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <x-admin.card class="p-6">
            <h3 class="kp-admin-display text-lg">Message</h3>
            <div class="mt-5 rounded-xl bg-slate-50 px-5 py-5">
                <p class="whitespace-pre-wrap text-sm leading-7 text-slate-700">{{ $message->message }}</p>
            </div>
        </x-admin.card>

        <x-admin.card class="h-fit p-6">
            <h3 class="kp-admin-display text-lg">Update Status</h3>
            <form method="POST" action="{{ route('admin.wear.enquiries.status', $message) }}" class="mt-5 space-y-4">
                @csrf
                <select name="status" class="kp-admin-input">
                    @foreach(['new','read','in_progress','resolved','closed'] as $s)
                        <option value="{{ $s }}" @selected($message->status===$s)>{{ ucwords(str_replace('_', ' ', $s)) }}</option>
                    @endforeach
                </select>
                <button class="kp-admin-primary-button w-full">Save status</button>
            </form>
        </x-admin.card>
    </div>
</div>
@endsection
