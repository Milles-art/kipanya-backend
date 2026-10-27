@extends('admin.layouts.app', ['title' => 'Add variant', 'heading' => 'Add variant'])

@section('content')
<div class="mx-auto max-w-3xl">
    <x-admin.page-header title="Add variant" subtitle="{{ $product->name }}" />

    <x-admin.card class="p-6">
        <form method="POST" action="{{ route('admin.wear.products.variants.store', $product) }}" class="space-y-5">
            @csrf
            @include('admin.wear.variants.form', ['submitLabel' => 'Create variant'])
        </form>
    </x-admin.card>
</div>
@endsection
