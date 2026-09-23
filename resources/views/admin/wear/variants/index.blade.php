@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Variants & SKUs</h2>
            <p class="mt-1 text-sm text-black">{{ $product->name }} · {{ $variants->total() }} variant(s)</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.wear.products.edit', $product) }}" class="rounded-xl border border-black px-4 py-3 text-sm font-semibold hover:bg-white">Product</a>
            <a href="{{ route('admin.wear.products.variants.create', $product) }}" class="button-dark">Add variant</a>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('variant'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $errors->first('variant') }}</div>
    @endif

    <div class="overflow-hidden rounded-2xl border border-black bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-black bg-white text-xs uppercase tracking-wide text-black">
                    <tr><th class="px-5 py-4">SKU</th><th class="px-5 py-4">Size</th><th class="px-5 py-4">Color</th><th class="px-5 py-4">Stock</th><th class="px-5 py-4 text-right">Action</th></tr>
                </thead>
                <tbody class="divide-y divide-black">
                @forelse($variants as $variant)
                    <tr class="hover:bg-white/70">
                        <td class="px-5 py-4 font-mono text-xs font-bold">{{ $variant->sku }}</td>
                        <td class="px-5 py-4 font-semibold">{{ $variant->size }}</td>
                        <td class="px-5 py-4 text-black">{{ $variant->color }}</td>
                        <td class="px-5 py-4"><span class="font-bold">{{ number_format($variant->stock) }}</span></td>
                        <td class="px-5 py-4 text-right"><a class="font-semibold text-black hover:text-emerald-700" href="{{ route('admin.wear.products.variants.edit', [$product, $variant]) }}">Edit</a></td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-5 py-12 text-center text-black">No variants yet.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-black px-5 py-4">{{ $variants->links() }}</div>
    </div>
</div>
@endsection
