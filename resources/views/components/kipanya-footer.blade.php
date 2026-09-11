@php
    // Minimal footer — every link resolves to a real route.
    $wearUrl = Route::has('wear') ? route('wear') : '/wear';
    $cartUrl = Route::has('wear.cart') ? route('wear.cart') : '/wear/cart';
    $wishUrl = Route::has('wear.wishlist') ? route('wear.wishlist') : '/wear/wishlist';
    $ordersUrl = Route::has('wear.orders') ? route('wear.orders') : '/wear/orders';
@endphp

<footer class="kp-footer">
    <div class="kp-footer-inner">
        <div class="kp-footer-brand">
            <img class="kp-footer-logo" src="{{ asset('assets/wear/brand/kp-wear-logo.jpg') }}" alt="Kipanya Wear" loading="lazy">
            <div>
                <p class="kp-footer-name">KIPANYA WEAR</p>
                <p class="kp-footer-tag">Everyday clothing, made to last.</p>
            </div>
        </div>
        <nav class="kp-footer-nav" aria-label="Footer">
            <a href="{{ $wearUrl }}">Shop all</a>
            <a href="{{ $cartUrl }}">Bag</a>
            <a href="{{ $wishUrl }}">Favorites</a>
            <a href="{{ $ordersUrl }}">Orders</a>
        </nav>
        <p class="kp-footer-note">Designed in Dar es Salaam · Prices in Tanzanian shillings. Duties calculated at checkout for international orders.</p>
    </div>
</footer>
