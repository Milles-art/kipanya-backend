@extends('layouts.wear')

@section('content')
<div class="kp-checkout" data-kp-checkout>
    <h1 class="kp-page-title">Checkout</h1>

    <ol class="kp-steps" aria-label="Checkout progress">
        <li class="kp-step is-done"><a href="{{ route('wear.cart') }}"><span aria-hidden="true">✓</span> Bag</a></li>
        <li class="kp-step is-current" aria-current="step"><span>2</span> Details</li>
        <li class="kp-step"><span>3</span> Done</li>
    </ol>

    <div data-kp-checkout-loading>
        <p class="kp-empty-state">Loading your order...</p>
    </div>

    <div class="kp-state-block" data-kp-checkout-guest hidden>
        <p class="kp-empty-state">Checkout needs a Kipanya account, and account sign-in on web is coming soon.</p>
        <p class="kp-fineprint" style="text-align:center;">Your bag is saved on this device — come back once sign-in launches to complete your order.</p>
        <a href="{{ route('wear.cart') }}" class="kp-btn-outline">Back to bag</a>
    </div>

    <div class="kp-co-grid" data-kp-checkout-main hidden>
        <div class="kp-co-main">
            <section aria-labelledby="kp-co-items-t">
                <h2 class="kp-section-title" id="kp-co-items-t">Items</h2>
                <div class="kp-cart-items kp-checkout-items" data-kp-checkout-items></div>
            </section>

            <section aria-labelledby="kp-co-addr-t">
                <h2 class="kp-section-title" id="kp-co-addr-t">Delivery address</h2>
                <div data-kp-address-list></div>

                <button type="button" class="kp-btn-outline" data-kp-address-toggle aria-expanded="false">
                    Add a new address
                </button>

                <form data-kp-address-form hidden novalidate>
                    <div class="kp-form-grid">
                        <div>
                            <label class="kp-field-label" for="kp-addr-name">Recipient name</label>
                            <input id="kp-addr-name" type="text" class="kp-field" data-kp-addr-name autocomplete="name" required>
                        </div>
                        <div>
                            <label class="kp-field-label" for="kp-addr-phone">Phone</label>
                            <input id="kp-addr-phone" type="tel" class="kp-field" data-kp-addr-phone autocomplete="tel" placeholder="+255 6xx xxx xxx" required>
                        </div>
                        <div>
                            <label class="kp-field-label" for="kp-addr-region">Region</label>
                            <input id="kp-addr-region" type="text" class="kp-field" data-kp-addr-region required>
                        </div>
                        <div>
                            <label class="kp-field-label" for="kp-addr-district">District</label>
                            <input id="kp-addr-district" type="text" class="kp-field" data-kp-addr-district required>
                        </div>
                        <div>
                            <label class="kp-field-label" for="kp-addr-ward">Ward (optional)</label>
                            <input id="kp-addr-ward" type="text" class="kp-field" data-kp-addr-ward>
                        </div>
                        <div>
                            <label class="kp-field-label" for="kp-addr-street">Street / area</label>
                            <input id="kp-addr-street" type="text" class="kp-field" data-kp-addr-street required>
                        </div>
                    </div>
                    <label class="kp-field-label" for="kp-addr-notes">Delivery notes (optional)</label>
                    <textarea id="kp-addr-notes" class="kp-field" data-kp-addr-notes rows="2"></textarea>
                    <p class="kp-form-error" data-kp-addr-error hidden></p>
                    <button type="submit" class="kp-atc" data-kp-addr-submit>Save address</button>
                </form>
            </section>

            <section aria-labelledby="kp-co-notes-t">
                <h2 class="kp-section-title" id="kp-co-notes-t">Order notes (optional)</h2>
                <label class="kp-visually-hidden" for="kp-order-notes">Order notes</label>
                <textarea id="kp-order-notes" class="kp-field" data-kp-checkout-notes rows="2" placeholder="Anything we should know?"></textarea>
            </section>
        </div>

        <aside class="kp-co-side" aria-label="Order summary">
            <div class="kp-co-card">
                <h2 class="kp-section-title" style="margin-top:0;">Summary</h2>
                <div class="kp-cart-summary" data-kp-checkout-summary hidden>
                    <div class="kp-summary-row">
                        <span>Subtotal</span>
                        <span data-kp-checkout-subtotal>TZS 0</span>
                    </div>
                    <p class="kp-fineprint">Final total with delivery is confirmed when your order is created.</p>
                </div>
                <button type="button" class="kp-atc" data-kp-place-order>
                    <span data-kp-place-order-label>Place order</span>
                </button>
                <p class="kp-form-error" data-kp-checkout-error role="alert" hidden></p>
                <p class="kp-fineprint" style="text-align:center;">Secure checkout · Prices in TZS</p>
            </div>
        </aside>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-checkout.js')
@endpush
