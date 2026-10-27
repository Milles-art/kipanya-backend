@extends('admin.layouts.app', ['title' => 'Edit variant', 'heading' => 'Edit variant'])

@section('content')
<div class="mx-auto max-w-3xl">
    <x-admin.page-header title="Edit variant" subtitle="{{ $product->name }} · {{ $variant->sku }}" />

    <x-admin.card class="p-6">
        <form method="POST" action="{{ route('admin.wear.products.variants.update', [$product, $variant]) }}" class="space-y-5">
            @csrf @method('PUT')
            @include('admin.wear.variants.form', ['submitLabel' => 'Save changes'])
        </form>
    </x-admin.card>

    <form method="POST" action="{{ route('admin.wear.products.variants.destroy', [$product, $variant]) }}" data-confirm="Delete this variant? This cannot be undone." class="mt-5">
        @csrf @method('DELETE')
        <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold uppercase tracking-wider text-rose-600 transition hover:bg-rose-50"><x-tabler-trash size="15" stroke-width="1.8" />Delete variant</button>
    </form>
</div>
@endsection
