<article class="group min-w-0" data-product-card data-product-id="{{ $product['id'] ?? '' }}">
    <a href="{{ url('/product/'.($product['slug'] ?? '')) }}" class="relative mb-3 block aspect-[3/4] overflow-hidden rounded-2xl bg-gray-100">
        <img src="{{ $product['image'] ?? '' }}" alt="{{ $product['name'] ?? 'Product' }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy">
        @if(!empty($product['badge']))<span class="absolute left-3 top-3 rounded-full bg-gray-950 px-2.5 py-1 text-xs font-bold text-white">{{ $product['badge'] }}</span>@endif
        @if(($product['availability'] ?? '') === 'out_of_stock')<span class="absolute right-3 top-3 rounded-full bg-gray-800 px-2.5 py-1 text-xs font-bold text-white">Sold out</span>@endif
    </a>
    <p class="mb-1 text-xs text-gray-400">{{ $product['category'] ?? '' }}</p>
    <a href="{{ url('/product/'.($product['slug'] ?? '')) }}" class="line-clamp-1 text-sm font-medium text-gray-900 hover:text-emerald-600">{{ $product['name'] ?? '' }}</a>
    <div class="mt-1 flex items-center gap-2">
        <span class="text-sm font-semibold">{{ number_format((float) ($product['price'] ?? 0), 0) }} TZS</span>
        @if(($product['compare_at_price'] ?? null) !== null)<span class="text-xs text-gray-400 line-through">{{ number_format((float)$product['compare_at_price'], 0) }} TZS</span>@endif
    </div>
    <div class="mt-3 flex gap-2">
        <button data-wishlist-product="{{ $product['id'] ?? '' }}" class="rounded-full border border-gray-200 px-3 py-1 text-xs hover:border-rose-400 hover:text-rose-500">Save</button>
        <button data-quick-add="{{ $product['id'] ?? '' }}" @disabled(($product['availability'] ?? '') === 'out_of_stock') class="rounded-full bg-gray-900 px-3 py-1 text-xs text-white hover:bg-emerald-600 disabled:cursor-not-allowed disabled:opacity-40">Add to bag</button>
    </div>
</article>
