@extends('admin.layouts.app', ['title' => 'Variants', 'heading' => 'Variants'])

@section('content')
<div>
    <x-admin.page-header title="Variants & SKUs" subtitle="{{ $product->name }} · {{ $variants->total() }} variant(s)">
        <x-slot name="action">
            <a href="{{ route('admin.wear.products.edit', $product) }}" class="kp-admin-secondary-button">Product</a>
            <a href="{{ route('admin.wear.products.variants.create', $product) }}" class="kp-admin-primary-button"><x-tabler-plus size="17" stroke-width="2" />Add variant</a>
        </x-slot>
    </x-admin.page-header>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('variant'))
        <div class="mb-6 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm font-medium text-rose-800">{{ $errors->first('variant') }}</div>
    @endif

    <x-admin.card class="overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px] text-sm">
                <thead>
                    <tr class="text-xs uppercase tracking-wider text-slate-400">
                        <th class="px-6 py-3.5 text-left font-medium">SKU</th>
                        <th class="px-3 py-3.5 text-left font-medium">Size</th>
                        <th class="px-3 py-3.5 text-left font-medium">Color</th>
                        <th class="px-3 py-3.5 text-right font-medium">Stock</th>
                        <th class="px-6 py-3.5 text-right font-medium">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($variants as $variant)
                        <tr class="border-b border-slate-50 transition-colors last:border-0 hover:bg-slate-50/60">
                            <td class="px-6 py-3.5 font-mono text-xs font-medium text-slate-900">{{ $variant->sku }}</td>
                            <td class="px-3 py-3.5 font-medium text-slate-700">{{ $variant->size }}</td>
                            <td class="px-3 py-3.5 text-slate-600">{{ $variant->color }}</td>
                            <td class="px-3 py-3.5 text-right">
                                <span class="font-medium text-slate-900">{{ number_format($variant->stock) }}</span>
                            </td>
                            <td class="px-6 py-3.5">
                                <div class="flex items-center justify-end">
                                    <a href="{{ route('admin.wear.products.variants.edit', [$product, $variant]) }}" class="kp-admin-icon-button" aria-label="Edit variant {{ $variant->sku }}"><x-tabler-pencil size="15" stroke-width="1.8" /></a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><x-admin.empty message="No variants yet." /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($variants->hasPages())
            <div class="border-t border-slate-100 px-6 py-4">{{ $variants->links() }}</div>
        @endif
    </x-admin.card>
</div>
@endsection
