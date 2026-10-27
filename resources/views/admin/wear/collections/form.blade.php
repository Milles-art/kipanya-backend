@extends('admin.layouts.app', ['title' => $collection->exists ? 'Edit Collection' : 'New Collection', 'heading' => $collection->exists ? 'Edit Collection' : 'New Collection'])

@section('content')
<div class="mx-auto max-w-6xl">
    <x-admin.page-header :title="$collection->exists ? 'Edit Collection' : 'New Collection'" :subtitle="$collection->exists ? 'Update the collection details and the products it features.' : 'Create a storefront collection and pick the products it features.'" />

    @if($errors->any())
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800">
            <p class="font-semibold">Please fix the following:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <form enctype="multipart/form-data" method="POST" action="{{ $collection->exists ? route('admin.wear.collections.update', $collection) : route('admin.wear.collections.store') }}" class="space-y-6">
        @csrf
        @if($collection->exists) @method('PUT') @endif

        <div class="grid gap-6 lg:grid-cols-2">
            <x-admin.card class="p-6">
                <h3 class="kp-admin-display mb-5 text-lg">Collection Details</h3>
                <div class="space-y-4">
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="collection-name">Name</label>
                        <input id="collection-name" name="name" value="{{ old('name', $collection->name) }}" required class="field w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="collection-slug">Slug</label>
                        <input id="collection-slug" name="slug" value="{{ old('slug', $collection->slug) }}" class="field w-full">
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="collection-description">Description</label>
                        <textarea id="collection-description" name="description" rows="5" class="field w-full">{{ old('description', $collection->description) }}</textarea>
                    </div>
                    <div>
                        <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="collection-cover">Collection cover</label>
                        @if($collection->cover_url)
                            <img src="{{ $collection->cover_url }}" alt="{{ $collection->name }}" class="mt-1 h-32 w-full rounded-2xl object-cover ring-1 ring-slate-200">
                        @endif
                        <input id="collection-cover" type="file" name="cover_image" accept="image/jpeg,image/png,image/webp,image/avif" class="field mt-2 w-full">
                        <p class="mt-1 text-xs text-slate-400">Up to 5 MB. A path or remote URL can also be used below.</p>
                        <input name="cover_path" value="{{ old('cover_path', $collection->cover_path) }}" class="field mt-2 w-full" placeholder="assets/wear/collections/…" aria-label="Cover path">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="mb-2 block text-xs uppercase tracking-wider text-slate-400" for="collection-sort-order">Sort order</label>
                            <input id="collection-sort-order" type="number" min="0" name="sort_order" value="{{ old('sort_order', $collection->sort_order ?? 0) }}" class="field w-full">
                        </div>
                        <label class="flex cursor-pointer items-end gap-2 pb-3 text-sm font-medium text-slate-700">
                            <input type="checkbox" name="is_active" value="1" class="h-4 w-4 rounded border-slate-300 accent-emerald-600" @checked(old('is_active', $collection->exists ? $collection->is_active : true))>
                            Active
                        </label>
                    </div>
                </div>
            </x-admin.card>

            <x-admin.card class="p-6">
                <h3 class="kp-admin-display text-lg">Products</h3>
                <p class="mt-1.5 text-xs text-slate-400">Select products and drag/order later through sort order.</p>
                <div class="mt-4 max-h-[520px] space-y-2 overflow-y-auto">
                    @php
                        $selected = collect(old('product_ids', $collection->exists ? $collection->products->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
                    @endphp
                    @foreach($products as $p)
                        <label class="flex cursor-pointer items-center gap-3 rounded-xl border border-slate-200 px-3 py-2.5 transition hover:bg-slate-50">
                            <input type="checkbox" name="product_ids[]" value="{{ $p->id }}" class="h-4 w-4 rounded border-slate-300 accent-emerald-600" @checked(in_array($p->id, $selected, true))>
                            <span class="text-sm font-medium text-slate-700">{{ $p->name }}</span>
                        </label>
                    @endforeach
                </div>
            </x-admin.card>
        </div>

        <div class="flex justify-end gap-3">
            <a href="{{ route('admin.wear.collections.index') }}" class="kp-admin-secondary-button">Cancel</a>
            <button type="submit" class="kp-admin-primary-button">{{ $collection->exists ? 'Save changes' : 'Create collection' }}</button>
        </div>
    </form>
</div>
@endsection
