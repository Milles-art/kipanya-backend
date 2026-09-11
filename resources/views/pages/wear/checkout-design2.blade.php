@extends('layouts.wear-design2')
@section('content')
<div class="kp-d2-checkout-page kp-d2-shell" data-kp-checkout>
<div class="kp-d2-section-head"><div><h2>Checkout</h2><p>Securely complete your Kipanya Wear order.</p></div></div>
<div data-kp-checkout-loading class="kp-d2-empty">Loading checkout…</div>
<div data-kp-checkout-guest hidden class="kp-d2-empty"><strong>Sign in required</strong><p>Checkout is available to signed-in customers. Your guest bag will remain saved.</p><a class="kp-d2-btn kp-d2-btn-dark" href="/account/login">Sign in</a></div>
<div class="kp-d2-checkout-grid" data-kp-checkout-main hidden>
<div>
<section class="kp-d2-check-card"><h2>Delivery address</h2><div data-kp-address-list></div><button type="button" class="kp-d2-btn kp-d2-btn-light" data-kp-address-toggle aria-expanded="false">Add a new address</button><p class="kp-d2-error" data-kp-addr-error hidden></p><form data-kp-address-form hidden><div class="kp-d2-field-grid"><div class="kp-d2-field"><label>Name</label><input data-kp-addr-name required></div><div class="kp-d2-field"><label>Phone</label><input data-kp-addr-phone required></div><div class="kp-d2-field"><label>Region</label><input data-kp-addr-region required></div><div class="kp-d2-field"><label>District</label><input data-kp-addr-district required></div><div class="kp-d2-field"><label>Ward</label><input data-kp-addr-ward></div><div class="kp-d2-field"><label>Street</label><input data-kp-addr-street required></div><div class="kp-d2-field full"><label>Notes</label><textarea data-kp-addr-notes></textarea></div></div><button type="submit" class="kp-d2-btn kp-d2-btn-dark" data-kp-addr-submit>Save address</button></form></section>
<section class="kp-d2-check-card"><h2>Order notes</h2><div class="kp-d2-field"><label for="kp-checkout-notes">Notes (optional)</label><textarea id="kp-checkout-notes" data-kp-checkout-notes maxlength="1000" placeholder="Anything we should know about this order?"></textarea></div></section>
<section class="kp-d2-check-card"><h2>Your items</h2><div data-kp-checkout-items></div></section>
</div>
<aside class="kp-d2-summary"><h2>Order summary</h2><div class="kp-d2-summary-row"><span>Subtotal</span><strong data-kp-checkout-subtotal>TZS 0</strong></div><div class="kp-d2-summary-row total"><span>Total</span><span data-kp-checkout-subtotal>TZS 0</span></div><button type="button" class="kp-d2-btn kp-d2-btn-dark" data-kp-place-order><span data-kp-place-order-label>Place order</span></button><p class="kp-d2-error" data-kp-checkout-error role="alert" hidden></p></aside>
</div>
</div>
@endsection
@push('scripts') @vite('resources/js/kp-wear-checkout.js') @endpush
