@extends('layouts.wear-design2')
@section('content')
<div class="kp-d2-cart-page kp-d2-shell" data-d2-cart>
<div class="kp-d2-section-head"><div><h2>Your Shopping Bag</h2><p data-d2-cart-count>Loading…</p></div><a class="kp-d2-link" href="{{ route('wear.shop') }}">Continue shopping <i class="ti ti-arrow-right"></i></a></div>
<div class="kp-d2-cart-grid"><div class="kp-d2-cart-list" data-d2-cart-list></div><aside class="kp-d2-summary"><h2>Summary</h2><div class="kp-d2-summary-row"><span>Subtotal</span><strong data-d2-subtotal>TZS 0</strong></div><div class="kp-d2-summary-row"><span>Delivery</span><span>Calculated at checkout</span></div><div class="kp-d2-summary-row total"><span>Total</span><span data-d2-subtotal>TZS 0</span></div><a class="kp-d2-btn kp-d2-btn-dark" href="{{ route('wear.checkout') }}">Checkout <i class="ti ti-arrow-right"></i></a></aside></div>
</div>
@endsection
