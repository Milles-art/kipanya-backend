@extends('admin.layouts.app', ['title' => 'Admin Users', 'heading' => 'Admin Users'])

@section('content')
<div>
    <x-admin.page-header title="Admin Users" subtitle="Manage the people who can access and operate the KP Wear control panel.">
        <x-slot name="action">
            <a href="{{ route('admin.users.create') }}" class="kp-admin-primary-button"><x-tabler-user-plus size="17" stroke-width="2" />Add admin</a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-700">{{ $errors->first() }}</div>
    @endif

    <form method="GET" action="{{ route('admin.users.index') }}" class="mb-5 flex flex-wrap items-center gap-3">
        <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email or phone"
               class="kp-admin-input w-full sm:w-64" aria-label="Search administrators" />
        <select name="status" class="kp-admin-input w-full sm:w-44" aria-label="Filter by status">
            <option value="">All statuses</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
        <div class="ml-auto flex flex-wrap items-center gap-2">
            <button type="submit" class="kp-admin-primary-button">Filter</button>
            @if($search !== '' || $status !== '')
                <a href="{{ route('admin.users.index') }}" class="kp-admin-secondary-button">Clear</a>
            @endif
        </div>
    </form>

    <x-admin.card class="overflow-hidden">
        <div class="px-6 pb-4 pt-6">
            <h3 class="kp-admin-display text-lg">Administrator directory</h3>
            <p class="mt-1.5 text-xs text-slate-400">{{ $users->total() }} administrator(s) with control panel access</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Administrator</th>
                        <th class="px-3 py-3.5 text-left font-medium">Role</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Contact</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="px-6 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($users as $admin)
                    @php($role = $admin->roles->first())
                    <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                        <td class="px-6 py-3.5">
                            <p class="font-medium text-slate-900">{{ $admin->name ?: 'Unnamed administrator' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">#{{ $admin->id }}</p>
                        </td>
                        <td class="px-3 py-3.5">
                            <p class="font-medium text-slate-800">{{ $role?->name ?? 'Administrator' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $role?->description }}</p>
                        </td>
                        <td class="hidden px-3 py-3.5 sm:table-cell">
                            <p class="text-slate-700">{{ $admin->phone ?: '—' }}</p>
                            <p class="mt-0.5 text-xs text-slate-400">{{ $admin->email ?: '—' }}</p>
                        </td>
                        <td class="px-3 py-3.5">
                            <x-admin.badge :status="$admin->status" :label="ucfirst($admin->status)" />
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <a href="{{ route('admin.users.edit', $admin) }}" class="kp-admin-link">Edit <x-tabler-pencil size="13" /></a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5"><x-admin.empty message="No administrator accounts found." /></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $users->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
