@extends('admin.layouts.app')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <p class="text-xs font-bold uppercase tracking-[0.18em] text-emerald-600">Kipanya Wear</p>
            <h2 class="mt-1 text-2xl font-black tracking-tight">Products</h2>
            <p class="mt-1 text-sm text-gray-500">Manage the products shown in the Wear storefront.</p>
        </div>
        <a href="{{ route('admin.wear.products.create') }}" class="button-dark">Add product</a>
    </div>

    @if(session('success'))
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-800">{{ session('success') }}</div>
    @endif
    @if($errors->has('product'))
        <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">{{ $errors->first('product') }}</div>
    @endif

    <form method="GET" class="grid gap-3 rounded-2xl border border-gray-200 bg-white p-4 sm:grid-cols-[1fr_180px_180px_auto]">
        <input class="field" name="q" value="{{ $search }}" placeholder="Search products...">
        <select class="field" name="category">
            <option value="all">All categories</option>
            @foreach($categories as $item)
                <option value="{{ $item }}" @selected($category === $item)>{{ $item }}</option>
            @endforeach
        </select>
        <select class="field" name="status">
            <option value="all" @selected($status === 'all')>All status</option>
            <option value="active" @selected($status === 'active')>Active</option>
            <option value="inactive" @selected($status === 'inactive')>Inactive</option>
        </select>
        <button class="rounded-xl border border-gray-200 px-4 py-3 text-sm font-semibold hover:bg-gray-50">Filter</button>
    </form>

    <div class="overflow-hidden rounded-2xl border border-gray-200 bg-white">
        <div class="overflow-x-auto">
            <table class="min-w-full text-left text-sm">
                <thead class="border-b border-gray-100 bg-gray-50 text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-5 py-4">Product</th>
                        <th class="px-5 py-4">Category</th>
                        <th class="px-5 py-4">Price</th>
                        <th class="px-5 py-4">Variants</th>
                        <th class="px-5 py-4">Status</th>
                        <th class="px-5 py-4 text-right">Action</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse($products as $product)
                    <tr class="hover:bg-gray-50/70">
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $product->image_url }}" alt="" class="h-12 w-12 rounded-xl object-cover">
                                <div>
                                    <p class="font-bold">{{ $product->name }}</p>
                                    <p class="text-xs text-gray-400">{{ $product->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-4 text-gray-600">{{ $product->category }}</td>
                        <td class="px-5 py-4 font-semibold">TZS {{ number_format((float) $product->price, 0) }}</td>
                        <td class="px-5 py-4 text-gray-600">{{ $product->variants_count }}</td>
                        <td class="px-5 py-4">
                            <span class="rounded-full px-2.5 py-1 text-xs font-bold {{ $product->is_active ? 'bg-emerald-50 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                                {{ $product->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                        <td class="px-5 py-4 text-right">
                            <a class="font-semibold text-gray-900 hover:text-emerald-700" href="{{ route('admin.wear.products.edit', $product) }}">Edit</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-500">No products found.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="border-t border-gray-100 px-5 py-4">{{ $products->links() }}</div>
    </div>
</div>
@endsection
