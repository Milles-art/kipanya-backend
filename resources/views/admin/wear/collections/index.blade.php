@extends('admin.layouts.app', ['title' => 'Collections', 'heading' => 'Collections'])

@section('content')
<div>
    <x-admin.page-header title="Collections" subtitle="Group products into the storefront collections that customers browse.">
        <x-slot name="action">
            <a href="{{ route('admin.wear.collections.create') }}" class="kp-admin-primary-button"><x-tabler-plus size="17" stroke-width="2" />New collection</a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif

    {{-- Toolbar --}}
    <form method="GET" class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center">
        <label class="relative block min-w-0 flex-1">
            <span class="sr-only">Search collections</span>
            <x-tabler-search size="18" stroke-width="1.8" class="pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400" />
            <input name="q" value="{{ request('q') }}" placeholder="Search collections" class="kp-admin-input w-full pl-10" />
        </label>
        <button type="submit" class="kp-admin-primary-button px-4"><x-tabler-search size="15" stroke-width="1.8" />Search</button>
        @if(request('q'))
            <a href="{{ route('admin.wear.collections.index') }}" class="kp-admin-secondary-button px-4">Clear</a>
        @endif
    </form>

    {{-- Collections table --}}
    <x-admin.card class="overflow-hidden">
        <div class="border-b border-slate-100 px-6 pb-4 pt-6">
            <h3 class="kp-admin-display text-lg">Storefront Catalogue</h3>
            <p class="mt-1.5 text-xs text-slate-400">{{ $collections->total() }} collection{{ $collections->total() === 1 ? '' : 's' }}</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">Collection</th>
                        <th class="px-3 py-3.5 text-left font-medium">Products</th>
                        <th class="hidden px-3 py-3.5 text-left font-medium sm:table-cell">Display order</th>
                        <th class="px-3 py-3.5 text-left font-medium">Status</th>
                        <th class="px-6 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($collections as $collection)
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5">
                                <div class="flex items-center gap-3">
                                    <span class="grid h-9 w-9 shrink-0 place-items-center rounded-lg bg-emerald-50 text-emerald-600"><x-tabler-stack-2 size="18" stroke-width="1.8" /></span>
                                    <div class="min-w-0">
                                        <p class="truncate font-medium text-slate-900">{{ $collection->name }}</p>
                                        <p class="mt-0.5 truncate text-xs text-slate-400">/{{ $collection->slug }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-3 py-3.5"><span class="font-medium text-slate-700">{{ $collection->products_count }}</span> <span class="text-xs text-slate-400">items</span></td>
                            <td class="hidden px-3 py-3.5 text-slate-500 sm:table-cell">{{ $collection->sort_order }}</td>
                            <td class="px-3 py-3.5">
                                @if($collection->is_active)
                                    <x-admin.badge status="active" label="Active" />
                                @else
                                    <x-admin.badge status="gray" label="Hidden" />
                                @endif
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.wear.collections.edit', $collection) }}" class="kp-admin-icon-button" aria-label="Edit {{ $collection->name }}"><x-tabler-pencil size="15" stroke-width="1.8" /></a>
                                    <form method="POST" action="{{ route('admin.wear.collections.destroy', $collection) }}" data-confirm="Delete this collection? This removes it from the store and cannot be undone." class="inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="grid h-9 w-9 place-items-center rounded-xl text-slate-400 transition hover:bg-rose-50 hover:text-rose-600" aria-label="Delete {{ $collection->name }}"><x-tabler-trash size="15" stroke-width="1.8" /></button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty message="No collections found. Try another search, or create your first collection." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($collections->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $collections->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
