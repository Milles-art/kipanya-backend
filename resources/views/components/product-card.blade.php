@php
    $id = (string) ($product['id'] ?? '');
    $slug = $product['slug'] ?? '';
    $name = $product['name'] ?? 'Product';
    $category = $product['category'] ?? '';
    $price = (float) ($product['price'] ?? 0);
    $compare = $product['compare_at_price'] ?? null;
    $image = $product['image'] ?? asset('assets/wear/catalog/generated/product-01.jpg');
    $badge = $product['badge'] ?? null;
    $availability = $product['availability'] ?? 'available';
    $variants = $product['variants'] ?? [];
    $stock = collect($variants)->sum(fn ($variant) => (int) ($variant['stock'] ?? 0));
    $onSale = $compare !== null && (float) $compare > $price;
    $discount = $onSale ? (int) round((((float) $compare - $price) / (float) $compare) * 100) : 0;
    $colors = collect($variants)->pluck('color')->filter()->unique()->values();
    $colorMap = [
        'black' => '#141414', 'white' => '#f5f5f5', 'navy' => '#1f3556',
        'olive' => '#5b6847', 'burgundy' => '#6b2c2c', 'camel' => '#c4a882',
        'gray' => '#8a8a8a', 'sage' => '#9caf88', 'sand' => '#d7c4a3',
        'beige' => '#d9ccb7', 'cream' => '#efe6d6',
    ];
@endphp

<article class="group relative min-w-0" data-product-card data-product-id="{{ $id }}">
    <div class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-gray-100">
        <a href="{{ url('/product/'.$slug) }}" class="block h-full w-full">
            <img src="{{ $image }}" alt="{{ $name }}" loading="lazy" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-105">
        </a>

        <div class="pointer-events-none absolute left-3 top-3 flex flex-col gap-1.5">
            @if($onSale)
                <span class="w-fit rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">-{{ $discount }}%</span>
            @endif
            @if(strtolower((string) $badge) === 'new')
                <span class="w-fit rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">New</span>
            @elseif($badge && strtolower((string) $badge) !== 'new')
                <span class="w-fit rounded-full bg-gray-950 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">{{ $badge }}</span>
            @endif
        </div>

        @if($availability === 'out_of_stock')
            <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-gray-900 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">Sold out</span>
        @elseif($stock > 0 && $stock < 10)
            <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">Low stock</span>
        @endif

        <button type="button" data-wishlist-product="{{ $id }}" class="absolute bottom-3 left-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-rose-500 hover:text-white" aria-label="Add {{ $name }} to wishlist">
            <x-tabler-heart size="18" stroke-width="1.8" />
        </button>

        <button type="button" data-quick-add="{{ $id }}" @disabled($availability === 'out_of_stock') class="absolute bottom-3 right-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-emerald-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-50" aria-label="Add {{ $name }} to bag">
            <x-tabler-shopping-bag size="18" stroke-width="1.8" />
        </button>
    </div>

    <div>
        <p class="mb-1 text-xs text-gray-400">{{ $category }}</p>
        <a href="{{ url('/product/'.$slug) }}" class="line-clamp-1 text-sm font-medium text-gray-950 transition-colors hover:text-emerald-700">{{ $name }}</a>
        <div class="mt-1 flex items-center gap-2">
            <span class="text-sm font-semibold text-gray-950">{{ number_format($price, 0) }} TZS</span>
            @if($onSale)
                <span class="text-xs text-gray-400 line-through">{{ number_format((float) $compare, 0) }} TZS</span>
            @endif
        </div>

        @if($colors->count() > 0)
            <div class="mt-2.5 flex items-center gap-1.5">
                @foreach($colors->take(5) as $color)
                    <span class="h-3.5 w-3.5 rounded-full border border-gray-200 shadow-sm" style="background-color: {{ $colorMap[strtolower($color)] ?? '#d1d5db' }}" title="{{ $color }}"></span>
                @endforeach
                @if($colors->count() > 5)
                    <span class="text-[11px] text-gray-400">+{{ $colors->count() - 5 }}</span>
                @endif
            </div>
        @endif
    </div>
</article>
