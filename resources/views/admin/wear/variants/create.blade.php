@extends('admin.layouts.app')

@section('content')
<div class="mx-auto max-w-3xl space-y-6">
    <div><p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear · {{ $product->name }}</p><h2 class="mt-1 text-2xl font-black tracking-tight">Add variant</h2></div>
    <form method="POST" action="{{ route('admin.wear.products.variants.store', $product) }}" class="space-y-5 rounded-2xl border border-black bg-white p-6">@csrf @include('admin.wear.variants.form', ['submitLabel' => 'Create variant'])</form>
</div>
@endsection
