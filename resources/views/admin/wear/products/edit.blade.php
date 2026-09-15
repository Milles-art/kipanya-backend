@extends('admin.layouts.app')
@section('content')
<div class="mx-auto max-w-4xl space-y-6">
    <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear / Products</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Edit product</h2>
        </div>
        @if(!$product->variants()->exists())
            <form method="POST" action="{{ route('admin.wear.products.destroy', $product) }}" onsubmit="return confirm('Delete this product permanently?')">
                @csrf @method('DELETE')
                <button class="rounded-xl border border-red-200 px-4 py-3 text-sm font-semibold text-red-700 hover:bg-red-50">Delete</button>
            </form>
        @endif
    </div>
    @include('admin.wear.products.form', ['formAction' => route('admin.wear.products.update', $product), 'method' => 'PUT'])
</div>
@endsection
