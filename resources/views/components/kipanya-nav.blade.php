@php
    // Centered-logo boutique header (Participle language). Left: shop links.
    // Center: brand. Right: search, favorites, bag. All links real.
    $cartCount = $cartCount ?? 0;
    $user = auth()->user() ?? null;

    $wearUrl = Route::has('wear') ? route('wear') : '/wear';
    $cartUrl = Route::has('wear.cart') ? route('wear.cart') : '/wear/cart';
    $ordersUrl = Route::has('wear.orders') ? route('wear.orders') : '/wear/orders';
    $wishUrl = Route::has('wear.wishlist') ? route('wear.wishlist') : '/wear/wishlist';
@endphp

<header class="kp-header" data-kp-redesign-nav>
    <div class="kp-utility-strip">
        <span>Complimentary delivery across Tanzania</span>
    </div>

    <div class="kp-mainbar">
        <button
            type="button"
            class="kp-icon-btn kp-menu-btn"
            data-kp-menu-toggle
            aria-controls="kp-mobile-menu"
            aria-expanded="false"
            aria-label="Open menu"
        >
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
            <span aria-hidden="true"></span>
        </button>

        <nav class="kp-shop-links" aria-label="Shop">
            <a href="{{ $wearUrl }}">New Arrivals</a>
            <a href="{{ $wearUrl }}#kp-catalog-grid">Shop</a>
            <a href="{{ $ordersUrl }}">Orders</a>
        </nav>

        <a class="kp-brand" href="{{ $wearUrl }}" aria-label="Kipanya Wear home">
            <span class="kp-brand-word">Kipanya Wear</span>
        </a>

        <div class="kp-mainbar-actions">
            <button type="button" class="kp-search-link" data-kp-search-toggle aria-expanded="false" aria-controls="kp-search-panel">
                <span>Search</span>
                <i class="ti ti-search" aria-hidden="true"></i>
            </button>

            <a class="kp-icon-btn" href="{{ $wishUrl }}" aria-label="Favorites">
                <i class="ti ti-heart" aria-hidden="true"></i>
            </a>

            <a class="kp-bag-link" data-kp-cart-link data-kp-bag-open href="{{ $cartUrl }}" aria-label="Open shopping bag, {{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }}">
                <span>Bag</span>
                <i class="ti ti-shopping-bag" aria-hidden="true"></i>
                <span class="kp-bag-count" data-kp-cart-count @if($cartCount < 1) hidden @endif>{{ $cartCount }}</span>
            </a>

            @if($user)
                <div class="kp-account" data-kp-account>
                    <button type="button" class="kp-avatar" data-kp-account-toggle aria-haspopup="menu" aria-expanded="false" aria-label="Open account menu">
                        {{ mb_strtoupper(mb_substr($user->name ?? 'K', 0, 1)) }}
                    </button>
                    <div class="kp-account-menu" data-kp-account-menu hidden role="menu" aria-label="Account">
                        <p class="kp-account-name" role="presentation">{{ $user->name ?? 'My account' }}</p>
                        <a href="{{ $ordersUrl }}" role="menuitem">Orders</a>
                        <a href="{{ $wishUrl }}" role="menuitem">Favorites</a>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <div class="kp-search-panel" id="kp-search-panel" data-kp-search-panel hidden>
        <form action="{{ $wearUrl }}" method="GET" role="search" data-kp-search-form>
            <label class="kp-visually-hidden" for="kp-search-input">Search Wear products</label>
            <i class="ti ti-search" aria-hidden="true"></i>
            <input id="kp-search-input" type="search" name="q" value="" placeholder="Search by product name or category…" autocomplete="off" data-kp-search-input>
            <button type="button" data-kp-search-close aria-label="Close site search"><i class="ti ti-x" aria-hidden="true"></i></button>
        </form>
        <div class="kp-search-results" data-kp-search-results aria-live="polite"></div>
    </div>

    <div class="kp-mobile-menu" id="kp-mobile-menu" data-kp-mobile-menu hidden>
        <nav aria-label="Mobile">
            <a href="{{ $wearUrl }}">New Arrivals</a>
            <a href="{{ $wearUrl }}#kp-catalog-grid">Shop</a>
            <a href="{{ $ordersUrl }}">Orders</a>
            <a href="{{ $wishUrl }}">Favorites</a>
        </nav>
    </div>
</header>
