@extends('admin.layouts.app')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div>
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear / Products</p>
        <h2 class="mt-1 text-2xl font-black tracking-tight">Add product</h2>
    </div>
    @include('admin.wear.products.form', ['formAction' => route('admin.wear.products.store'), 'method' => 'POST'])
</div>
@endsection
