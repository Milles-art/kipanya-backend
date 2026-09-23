@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear · {{ $product->name }}</p><h2 class="mt-1 text-2xl font-black tracking-tight">Edit variant</h2><p class="mt-1 font-mono text-xs text-black">{{ $variant->sku }}</p></div>
    <form method="POST" action="{{ route('admin.wear.products.variants.update', [$product, $variant]) }}" class="space-y-5 rounded-2xl border border-black bg-white p-6">@csrf @method('PUT') @include('admin.wear.variants.form', ['submitLabel' => 'Save changes'])</form>
    <form method="POST" action="{{ route('admin.wear.products.variants.destroy', [$product, $variant]) }}" data-confirm="Delete this variant? This cannot be undone.">@csrf @method('DELETE')<button class="rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-50">Delete variant</button></form>
</div>
@endsection
