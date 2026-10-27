@extends('admin.layouts.app', ['title' => 'Edit product', 'heading' => 'Edit product'])

@section('content')
<div class="mx-auto max-w-4xl">
    <x-admin.page-header title="Edit product" subtitle="{{ $product->name }} — update details, pricing, and visibility.">
        <x-slot name="action">
            <a href="{{ route('admin.wear.products.variants.index', $product) }}" class="kp-admin-secondary-button"><x-tabler-box size="15" stroke-width="1.8" />Variants</a>
            @if(!$product->variants()->exists())
                <form method="POST" action="{{ route('admin.wear.products.destroy', $product) }}" data-confirm="Delete this product permanently?">
                    @csrf @method('DELETE')
                    <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-rose-200 bg-white px-4 py-2.5 text-sm font-semibold uppercase tracking-wider text-rose-600 transition hover:bg-rose-50"><x-tabler-trash size="15" stroke-width="1.8" />Delete</button>
                </form>
            @endif
        </x-slot>
    </x-admin.page-header>

    @include('admin.wear.products.form', ['formAction' => route('admin.wear.products.update', $product), 'method' => 'PUT'])
</div>
@endsection
