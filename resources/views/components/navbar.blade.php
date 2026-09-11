<header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur">
    <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:h-20 lg:px-8">
        <a href="{{ route('home') }}" class="text-2xl font-black tracking-tight">KP<span class="text-emerald-600">.</span></a>
        <nav class="hidden items-center gap-7 md:flex">
            <a href="{{ route('home') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600">Home</a>
            <a href="{{ route('shop') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600">Shop</a>
            <a href="{{ route('collections') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600">Collections</a>
            <a href="{{ route('wishlist') }}" class="text-sm font-medium text-gray-700 hover:text-emerald-600">Wishlist</a>
        </nav>
        <div class="flex items-center gap-1 md:gap-2">
            <button data-search-toggle class="rounded-xl p-2 text-gray-700 hover:bg-gray-100" aria-label="Search"><x-tabler-search size="20" stroke-width="1.8" /></button>
            <a href="{{ route('account') }}" class="hidden rounded-xl p-2 text-gray-700 hover:bg-gray-100 sm:block" aria-label="Account"><x-tabler-user size="20" stroke-width="1.8" /></a>
            <a href="{{ route('wishlist') }}" class="relative hidden rounded-xl p-2 text-gray-700 hover:bg-gray-100 sm:block" aria-label="Wishlist"><x-tabler-heart size="20" stroke-width="1.8" /><span data-wishlist-count class="badge hidden">0</span></a>
            <a href="{{ route('cart') }}" class="relative rounded-xl p-2 text-gray-700 hover:bg-gray-100" aria-label="Cart"><x-tabler-shopping-bag size="20" stroke-width="1.8" /><span data-cart-count class="badge hidden">0</span></a>
            <button data-menu-toggle class="rounded-xl p-2 text-gray-700 hover:bg-gray-100 md:hidden" aria-label="Open menu"><x-tabler-menu-2 size="20" stroke-width="1.8" /></button>
        </div>
    </div>
    <div data-search-panel class="hidden border-t border-gray-100 bg-white px-4 py-4">
        <form action="{{ route('search') }}" class="mx-auto flex max-w-xl gap-2">
            <input name="q" class="field flex-1" placeholder="Search products..." autocomplete="off">
            <button class="button-dark"><x-tabler-search size="18" /> Search</button>
        </form>
    </div>
    <div data-mobile-menu class="hidden border-t border-gray-100 bg-white px-4 pb-5 pt-3 md:hidden">
        <a href="{{ route('home') }}" class="block border-b border-gray-50 py-3 text-sm font-medium">Home</a>
        <a href="{{ route('shop') }}" class="block border-b border-gray-50 py-3 text-sm font-medium">Shop</a>
        <a href="{{ route('collections') }}" class="block border-b border-gray-50 py-3 text-sm font-medium">Collections</a>
        <a href="{{ route('wishlist') }}" class="block border-b border-gray-50 py-3 text-sm font-medium">Wishlist</a>
        <a href="{{ route('account') }}" class="block border-b border-gray-50 py-3 text-sm font-medium">Account</a>
    </div>
</header>
