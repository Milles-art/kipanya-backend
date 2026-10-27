@extends('admin.layouts.app', ['title' => 'Edit Admin User', 'heading' => 'Edit Admin User'])

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <x-admin.page-header title="Edit administrator" subtitle="Update {{ $user->name ?: 'this administrator' }} and their staff role." />

    @if($errors->any())
        <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="POST" action="{{ route('admin.users.update', $user) }}">
        @csrf
        @method('PUT')
        <x-admin.card class="p-6">
            @include('admin.users.form')
            <div class="mt-7 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <a href="{{ route('admin.users.index') }}" class="kp-admin-secondary-button">Back</a>
                <button type="submit" class="kp-admin-primary-button">Save changes</button>
            </div>
        </x-admin.card>
    </form>

    @if(!auth()->user()->is($user))
    <form method="POST" action="{{ route('admin.users.status', $user) }}">
        @csrf
        <x-admin.card class="p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="kp-admin-display text-lg">Account access</h3>
                    <p class="mt-1.5 text-xs text-slate-400">{{ $user->status === 'active' ? 'Deactivate this account to prevent admin login.' : 'Reactivate this account to restore admin login.' }}</p>
                </div>
                <button type="submit" class="kp-admin-secondary-button">{{ $user->status === 'active' ? 'Deactivate' : 'Activate' }}</button>
            </div>
        </x-admin.card>
    </form>
    @endif
</div>
@endsection
