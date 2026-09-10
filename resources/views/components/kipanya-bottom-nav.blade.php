@php
    // Mobile bottom bar: only destinations that work.
    $activeSection = $activeSection ?? 'wear';
    $cartCount = $cartCount ?? 0;

    $wearUrl = Route::has('wear') ? route('wear') : '/wear';
    $cartUrl = Route::has('wear.cart') ? route('wear.cart') : '/wear/cart';
    $wishUrl = Route::has('wear.wishlist') ? route('wear.wishlist') : '/wear/wishlist';
    $ordersUrl = Route::has('wear.orders') ? route('wear.orders') : '/wear/orders';
@endphp

<nav class="kp-tabbar" aria-label="Primary">
    <a href="{{ $wearUrl }}" class="kp-tab {{ $activeSection === 'wear' ? 'is-active' : '' }}" @if($activeSection === 'wear') aria-current="page" @endif>
        <i class="ti ti-shirt" aria-hidden="true"></i>
        <span>Wear</span>
    </a>
    <button type="button" class="kp-tab" data-kp-bottom-search aria-label="Search Wear products">
        <i class="ti ti-search" aria-hidden="true"></i>
        <span>Search</span>
    </button>
    <a href="{{ $cartUrl }}" class="kp-tab" aria-label="Open shopping bag, {{ $cartCount }} {{ $cartCount === 1 ? 'item' : 'items' }}">
        <i class="ti ti-shopping-bag" aria-hidden="true"></i>
        <span>Bag</span>
        <span class="kp-tab-count" data-kp-cart-count @if($cartCount < 1) hidden @endif>{{ $cartCount }}</span>
    </a>
    <a href="{{ $wishUrl }}" class="kp-tab {{ $activeSection === 'wishlist' ? 'is-active' : '' }}" @if($activeSection === 'wishlist') aria-current="page" @endif>
        <i class="ti ti-heart" aria-hidden="true"></i>
        <span>Saved</span>
    </a>
    <a href="{{ $ordersUrl }}" class="kp-tab {{ $activeSection === 'orders' ? 'is-active' : '' }}" @if($activeSection === 'orders') aria-current="page" @endif>
        <i class="ti ti-package" aria-hidden="true"></i>
        <span>Orders</span>
    </a>
</nav>
