@extends('admin.layouts.app', ['title' => 'Admin Users', 'heading' => 'Admin Users'])

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">{{ $errors->first() }}</div>
    @endif

    <section class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Administration</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight sm:text-3xl">Admin Users</h2>
            <p class="mt-2 max-w-2xl text-sm leading-6 text-gray-500">Manage the people who can access and operate the KP Wear control panel.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white shadow-sm hover:bg-gray-800">+ Add admin</a>
    </section>

    <form method="GET" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
        <div class="grid gap-3 sm:grid-cols-[1fr_180px_auto_auto]">
            <input type="search" name="search" value="{{ $search }}" placeholder="Search name, email or phone" class="rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400 focus:ring-2 focus:ring-gray-100">
            <select name="status" class="rounded-xl border border-gray-200 px-4 py-3 text-sm outline-none focus:border-gray-400">
                <option value="">All statuses</option>
                <option value="active" @selected($status === 'active')>Active</option>
                <option value="inactive" @selected($status === 'inactive')>Inactive</option>
            </select>
            <button class="rounded-xl bg-gray-950 px-5 py-3 text-sm font-bold text-white hover:bg-gray-800">Filter</button>
            @if($search !== '' || $status !== '')<a href="{{ route('admin.users.index') }}" class="rounded-xl border border-gray-200 px-5 py-3 text-center text-sm font-bold text-gray-700 hover:bg-gray-50">Clear</a>@endif
        </div>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
            <p class="text-xs font-bold uppercase tracking-widest text-gray-400">Administrator directory</p>
            <h3 class="mt-1 text-lg font-bold">{{ $users->total() }} administrator(s)</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="bg-gray-50 text-xs font-bold uppercase tracking-wider text-gray-400"><tr><th class="px-5 py-3">Administrator</th><th class="px-5 py-3">Role</th><th class="px-5 py-3">Contact</th><th class="px-5 py-3">Status</th><th class="px-5 py-3 text-right">Action</th></tr></thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($users as $admin)
                    @php($role = $admin->roles->first())
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4"><p class="font-bold">{{ $admin->name ?: 'Unnamed administrator' }}</p><p class="mt-1 text-xs text-gray-400">#{{ $admin->id }}</p></td>
                        <td class="px-5 py-4"><p class="font-semibold">{{ $role?->name ?? 'Administrator' }}</p><p class="mt-1 text-xs text-gray-400">{{ $role?->description }}</p></td>
                        <td class="px-5 py-4"><p class="font-medium">{{ $admin->phone ?: '—' }}</p><p class="mt-1 text-xs text-gray-400">{{ $admin->email ?: '—' }}</p></td>
                        <td class="px-5 py-4"><span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $admin->status === 'active' ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">{{ ucfirst($admin->status) }}</span></td>
                        <td class="px-5 py-4 text-right"><a href="{{ route('admin.users.edit', $admin) }}" class="font-bold text-gray-900 hover:underline">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-sm text-gray-500">No administrator accounts found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())<div class="border-t border-gray-100 px-5 py-4">{{ $users->links() }}</div>@endif
    </div>
</div>
@endsection
