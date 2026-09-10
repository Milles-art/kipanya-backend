@extends('layouts.wear')

@section('content')
<div class="kp-cart-page">
    <h1 class="kp-page-title">Shopping bag</h1>

    <div class="kp-cart-items" data-kp-cart-items>
        <p class="kp-empty-state" data-kp-cart-loading>Loading your bag...</p>
    </div>

    <div class="kp-cart-summary" data-kp-cart-summary hidden>
        <div class="kp-summary-row">
            <span>Subtotal</span>
            <span data-kp-cart-subtotal>TZS 0</span>
        </div>
        <p class="kp-fineprint">Delivery fees are confirmed at checkout.</p>
    </div>

    <a href="{{ route('wear.checkout') }}" class="kp-atc kp-block-link" data-kp-checkout-link hidden>Proceed to checkout</a>
    <a href="{{ route('wear') }}" class="kp-btn-outline kp-block-link" data-kp-continue-shopping hidden>Continue shopping</a>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-cart.js')
@endpush
