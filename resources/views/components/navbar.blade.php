<header class="sticky top-0 z-50 border-b border-emerald-950/10 bg-white/95 backdrop-blur">
    <div class="flex h-16 w-full items-center justify-between px-4 sm:px-6 lg:h-20 lg:px-7">

        {{-- Logo --}}
        <a href="{{ route('home') }}"
           class="shrink-0 text-2xl font-black tracking-tight">
            KP<span class="text-emerald-600">.</span>
        </a>

        {{-- Main Navigation --}}
        <nav class="hidden items-center gap-2 md:flex">
            <a href="{{ route('home') }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-base font-medium text-black transition-all duration-200 hover:bg-emerald-50/70',
                   'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('home'),
               ])
               @if(request()->routeIs('home')) aria-current="page" @endif>
                Home
            </a>

            <a href="{{ route('shop') }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-base font-medium text-black transition-all duration-200 hover:bg-emerald-50/70',
                   'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('shop'),
               ])
               @if(request()->routeIs('shop')) aria-current="page" @endif>
                Shop
            </a>

            <a href="{{ route('collections') }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-base font-medium text-black transition-all duration-200 hover:bg-emerald-50/70',
                   'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('collections'),
               ])
               @if(request()->routeIs('collections')) aria-current="page" @endif>
                Collections
            </a>

            <a href="{{ route('about') }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-base font-medium text-black transition-all duration-200 hover:bg-emerald-50/70',
                   'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('about'),
               ])
               @if(request()->routeIs('about')) aria-current="page" @endif>
                About
            </a>

            <a href="{{ route('contact') }}"
               @class([
                   'rounded-xl px-4 py-2.5 text-base font-medium text-black transition-all duration-200 hover:bg-emerald-50/70',
                   'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('contact'),
               ])
               @if(request()->routeIs('contact')) aria-current="page" @endif>
                Contact
            </a>
        </nav>

        {{-- Right Actions --}}
        <div class="flex items-center gap-1 md:gap-2">

            {{-- Search --}}
            <button
                data-search-toggle
                type="button"
                aria-expanded="false"
                class="hidden rounded-xl p-2.5 text-black transition-all duration-200 hover:bg-emerald-50/70 hover:text-black md:inline-flex"
                aria-label="Search">
                <x-tabler-search size="20" stroke-width="1.8" />
            </button>

            {{-- Wishlist --}}
            <a
                href="{{ route('wishlist') }}"
                class="relative hidden rounded-xl p-2.5 text-black transition-all duration-200 hover:bg-emerald-50/70 hover:text-black md:inline-flex"
                aria-label="Wishlist">
                <x-tabler-heart size="20" stroke-width="1.8" />
                <span data-wishlist-count class="badge hidden">0</span>
            </a>

            {{-- Cart --}}
            <a
                href="{{ route('cart') }}"
                class="relative rounded-xl p-2.5 text-black transition-all duration-200 hover:bg-emerald-50/70 hover:text-black"
                aria-label="Cart">
                <x-tabler-shopping-bag size="20" stroke-width="1.8" />
                <span data-cart-count class="badge hidden">0</span>
            </a>

            {{-- Account --}}
            <a
                href="{{ route('account') }}"
                @class([
                    'hidden rounded-xl p-2.5 text-black transition-all duration-200 hover:bg-emerald-50/70 md:inline-flex',
                    'bg-emerald-50 ring-1 ring-emerald-600/20 kp-signed-in' => auth()->check(),
                    'bg-emerald-50 ring-1 ring-emerald-600/20' => request()->routeIs('account*'),
                ])
                @if(request()->routeIs('account*')) aria-current="page" @endif
                aria-label="{{ auth()->check() ? 'Account (signed in)' : 'Account' }}">
                <x-tabler-user size="20" stroke-width="1.8" />
            </a>


        </div>
    </div>

    {{-- Search Panel --}}
    <div data-search-panel class="hidden border-t border-emerald-950/10 bg-white px-4 py-4">
        <form action="{{ route('search') }}" class="mx-auto flex max-w-xl gap-2">
            <label for="site-search" class="sr-only">Search products</label>
            <input
                id="site-search"
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

</header>

@unless(request()->routeIs('login', 'register', 'checkout'))
    <nav class="kp-mobile-bottom-nav" aria-label="Mobile navigation">
        <a href="{{ route('home') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('home')]) @if(request()->routeIs('home')) aria-current="page" @endif>
            <x-tabler-home-2 size="20" stroke-width="1.9" />
            <span>Home</span>
        </a>
        <a href="{{ route('shop') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('shop')]) @if(request()->routeIs('shop')) aria-current="page" @endif>
            <x-tabler-shopping-bag size="20" stroke-width="1.9" />
            <span>Shop</span>
        </a>
        <a href="{{ route('collections') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('collections')]) @if(request()->routeIs('collections')) aria-current="page" @endif>
            <x-tabler-sparkles size="20" stroke-width="1.9" />
            <span>Collections</span>
        </a>
        <a href="{{ route('account') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('account*')]) @if(request()->routeIs('account*')) aria-current="page" @endif>
            <x-tabler-user size="20" stroke-width="1.9" />
            <span>Account</span>
        </a>
        <a href="{{ route('contact') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('contact')]) @if(request()->routeIs('contact')) aria-current="page" @endif>
            <x-tabler-phone size="20" stroke-width="1.9" />
            <span>Contact</span>
        </a>
        <a href="{{ route('about') }}" @class(['kp-mobile-nav-item', 'is-active' => request()->routeIs('about')]) @if(request()->routeIs('about')) aria-current="page" @endif>
            <x-tabler-info-circle size="20" stroke-width="1.9" />
            <span>About</span>
        </a>
    </nav>
@endunless
