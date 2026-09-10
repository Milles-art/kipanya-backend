@php
    // Slide-over bag drawer (template signature pattern). Opens on bag
    // click via JS; links degrade to real pages without JS.
    $cartUrl = Route::has('wear.cart') ? route('wear.cart') : '/wear/cart';
    $checkoutUrl = Route::has('wear.checkout') ? route('wear.checkout') : '/wear/checkout';
@endphp

<div class="kp-drawer-scrim" data-kp-bag-scrim hidden></div>
<aside class="kp-drawer" data-kp-bag-drawer hidden aria-label="Shopping bag" aria-modal="true" role="dialog">
    <div class="kp-drawer-head">
        <h2 data-kp-bag-title>Your bag</h2>
        <button type="button" class="kp-icon-btn" data-kp-bag-close aria-label="Close bag">
            <i class="ti ti-x" aria-hidden="true"></i>
        </button>
    </div>
    <div class="kp-drawer-items" data-kp-bag-items></div>
    <div class="kp-drawer-foot" data-kp-bag-foot hidden>
        <div class="kp-summary-row">
            <span>Subtotal</span>
            <strong data-kp-bag-subtotal>TZS 0</strong>
        </div>
        <p class="kp-fineprint">Shipping calculated at checkout.</p>
        <div class="kp-drawer-ctas">
            <a href="{{ $cartUrl }}" class="kp-btn-outline">View Bag</a>
            <a href="{{ $checkoutUrl }}" class="kp-atc">Go To Checkout</a>
        </div>
    </div>
</aside>
