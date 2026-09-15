<header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur">
    <div class="flex h-16 w-full items-center justify-between px-4 sm:px-6 lg:h-20 lg:px-7">

        {{-- Logo --}}
        <a href="{{ route('home') }}"
           class="shrink-0 text-2xl font-black tracking-tight">
            KP<span class="text-emerald-600">.</span>
        </a>

        {{-- Main Navigation --}}
        <nav class="hidden items-center gap-2 md:flex">
            <a href="{{ route('home') }}"
               class="rounded-xl px-4 py-2.5 text-base font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950">
                Home
            </a>

            <a href="{{ route('shop') }}"
               class="rounded-xl px-4 py-2.5 text-base font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950">
                Shop
            </a>

            <a href="{{ route('collections') }}"
               class="rounded-xl px-4 py-2.5 text-base font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950">
                Collections
            </a>

            <a href="{{ route('about') }}"
               class="rounded-xl px-4 py-2.5 text-base font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950">
                About
            </a>

            <a href="{{ route('contact') }}"
               class="rounded-xl px-4 py-2.5 text-base font-medium text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950">
                Contact
            </a>
        </nav>

        {{-- Right Actions --}}
        <div class="flex items-center gap-1 md:gap-2">

            {{-- Search --}}
            <button
                data-search-toggle
                type="button"
                class="rounded-xl p-2.5 text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950"
                aria-label="Search">
                <x-tabler-search size="20" stroke-width="1.8" />
            </button>

            {{-- Wishlist --}}
            <a
                href="{{ route('wishlist') }}"
                class="rounded-xl p-2.5 text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950"
                aria-label="Wishlist">
                <x-tabler-heart size="20" stroke-width="1.8" />
            </a>

            {{-- Cart --}}
            <a
                href="{{ route('cart') }}"
                class="relative rounded-xl p-2.5 text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950"
                aria-label="Cart">
                <x-tabler-shopping-bag size="20" stroke-width="1.8" />
                <span data-cart-count class="badge hidden">0</span>
            </a>

            {{-- Account --}}
            <a
                href="{{ route('account') }}"
                class="rounded-xl p-2.5 text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950"
                aria-label="Account">
                <x-tabler-user size="20" stroke-width="1.8" />
            </a>

            {{-- Mobile Menu --}}
            <button
                data-menu-toggle
                type="button"
                class="rounded-xl p-2.5 text-gray-700 transition-all duration-200 hover:bg-gray-100 hover:text-gray-950 md:hidden"
                aria-label="Open menu">
                <x-tabler-menu-2 size="20" stroke-width="1.8" />
            </button>

        </div>
    </div>

    {{-- Search Panel --}}
    <div data-search-panel class="hidden border-t border-gray-100 bg-white px-4 py-4">
        <form action="{{ route('search') }}" class="mx-auto flex max-w-xl gap-2">
            <input
                name="q"
                class="field flex-1"
                placeholder="Search products..."
                autocomplete="off">

            <button type="submit" class="button-dark">
                <x-tabler-search size="18" />
                Search
            </button>
        </form>
    </div>

    {{-- Mobile Menu --}}
    <div data-mobile-menu class="hidden border-t border-gray-100 bg-white px-4 pb-5 pt-3 md:hidden">

        <a
            href="{{ route('home') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Home
        </a>

        <a
            href="{{ route('shop') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Shop
        </a>

        <a
            href="{{ route('collections') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Collections
        </a>

        <a
            href="{{ route('about') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            About
        </a>

        <a
            href="{{ route('contact') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Contact
        </a>

        <a
            href="{{ route('wishlist') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Wishlist
        </a>

        <a
            href="{{ route('account') }}"
            class="block rounded-xl px-3 py-3 text-lg font-medium transition hover:bg-gray-100">
            Account
        </a>

    </div>
</header>
