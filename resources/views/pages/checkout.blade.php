@extends('layouts.app')

@section('content')

<div data-checkout-page data-google-maps-key="{{ config('services.google.maps_key') }}" class="checkout-reference-page">
<style nonce="{{ Vite::cspNonce() }}">
.checkout-reference-page{
  --ink:#090b0a;
  --text:#17201c;
  --muted:#66716c;
  --border:#e4e9e6;
  --border-strong:#d5ddd8;
  --green:#008f68;
  --green-dark:#007a59;
  --green-soft:#eef9f5;
  --green-soft-2:#f6fcfa;
  --amber-bg:#fff7e5;
  --amber:#a85b00;
  width:min(1360px,calc(100% - 64px));
  margin:0 auto;
  padding:18px 0 48px;
  color:var(--ink);
  font-family:inherit;
  -webkit-font-smoothing:antialiased;
}
.checkout-reference-page *{box-sizing:border-box}
.checkout-reference-page button,
.checkout-reference-page input,
.checkout-reference-page select,
.checkout-reference-page textarea{font:inherit}
.checkout-reference-page button,
.checkout-reference-page a{transition:border-color .18s ease,background-color .18s ease,color .18s ease,transform .18s ease}
.checkout-reference-page button:focus-visible,
.checkout-reference-page a:focus-visible,
.checkout-reference-page input:focus-visible,
.checkout-reference-page select:focus-visible,
.checkout-reference-page textarea:focus-visible{
  outline:3px solid rgba(0,143,104,.18);
  outline-offset:2px;
}

/* Header */
.checkout-top{
  min-height:50px;
  display:grid;
  grid-template-columns:1fr auto 1fr;
  align-items:center;
  border-bottom:1px solid var(--border);
}
.checkout-brand{
  display:flex;
  align-items:center;
  gap:14px;
  font-size:16px;
  font-weight:900;
  letter-spacing:-.02em;
}
.checkout-brand a{
  color:var(--ink);
  text-decoration:none;
  display:flex;
  align-items:center;
}
.checkout-brand a:first-child{color:#252a27}
.checkout-progress{
  display:flex;
  align-items:center;
  justify-content:center;
  gap:10px;
  color:#66716c;
  font-size:13px;
}
.checkout-progress-step{
  display:flex;
  align-items:center;
  gap:8px;
  white-space:nowrap;
}
.checkout-progress-dot{
  width:28px;
  height:28px;
  border-radius:50%;
  display:grid;
  place-items:center;
  background:#f0f6f3;
  color:#52605a;
  font-size:12px;
  font-weight:800;
}
.checkout-progress-step.active{color:var(--ink)}
.checkout-progress-step.active .checkout-progress-dot{
  background:var(--ink);
  color:#fff;
}
.checkout-progress-step.active span:last-child{font-weight:800}
.checkout-top-secure{
  justify-self:end;
  display:flex;
  align-items:center;
  gap:8px;
  color:#27312d;
  font-size:13px;
  font-weight:700;
}

/* Heading */
.checkout-heading{padding-top:34px}
.checkout-eyebrow{
  margin:0;
  color:var(--green);
  font-size:11px;
  font-weight:900;
  letter-spacing:.24em;
  text-transform:uppercase;
}
.checkout-heading h1{
  margin:7px 0 0;
  font-size:44px;
  line-height:1.04;
  letter-spacing:-.055em;
  font-weight:900;
}
.checkout-heading p{
  margin:10px 0 0;
  color:#46514c;
  font-size:16px;
  line-height:1.5;
}

/* Main layout */
.checkout-layout{
  display:grid;
  grid-template-columns:minmax(0,1fr) 440px;
  gap:24px;
  align-items:start;
  margin-top:28px;
}
.checkout-left{min-width:0}
.checkout-stack{display:flex;flex-direction:column;gap:14px}

.checkout-card{
  border:1px solid var(--border);
  border-radius:14px;
  background:#fff;
  padding:20px;
  box-shadow:0 1px 2px rgba(9,11,10,.02);
}
.checkout-card-header,
.checkout-payment-header{
  display:flex;
  align-items:center;
  gap:13px;
}
.checkout-circle-icon{
  width:40px;
  height:40px;
  border-radius:11px;
  background:var(--green-soft);
  color:var(--green);
  display:grid;
  place-items:center;
  flex:none;
}
.checkout-card-main{min-width:0;flex:1}
.checkout-card-title{
  margin:0;
  font-size:16px;
  line-height:1.3;
  font-weight:850;
  letter-spacing:-.015em;
}
.checkout-contact-name{
  display:block;
  margin-top:4px;
  font-size:14px;
  line-height:1.3;
  color:var(--text);
  font-weight:600;
}
.checkout-contact-details{
  display:block;
  margin-top:3px;
  font-size:14px;
  line-height:1.4;
  color:var(--muted);
}
.checkout-check{
  width:29px;
  height:29px;
  border-radius:50%;
  background:#0bb47a;
  color:#fff;
  display:grid;
  place-items:center;
}
.checkout-contact-actions{
  display:flex;
  flex-direction:column;
  align-items:center;
  min-width:38px;
}
.checkout-edit{
  display:block;
  margin-top:3px;
  padding:0;
  color:var(--green);
  background:none;
  border:0;
  font-size:13px;
  font-weight:800;
  cursor:pointer;
}
.checkout-edit:hover{color:var(--green-dark)}

.checkout-address-row{
  display:flex;
  align-items:flex-start;
  gap:13px;
}
.checkout-address-main{min-width:0;flex:1}
.checkout-address-title-row{
  display:flex;
  align-items:center;
  gap:8px;
  flex-wrap:wrap;
}
.checkout-required{
  background:var(--amber-bg);
  color:var(--amber);
  border-radius:999px;
  padding:3px 8px;
  font-size:10px;
  font-weight:800;
}
.checkout-address-text{
  margin:5px 0 0;
  font-size:14px;
  line-height:1.55;
  color:#46514c;
}
.checkout-address-actions{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
  margin-top:16px;
}
.checkout-outline-btn{
  min-height:45px;
  border:1px solid var(--border-strong);
  border-radius:10px;
  background:#fff;
  color:var(--ink);
  font-size:13px;
  font-weight:800;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:9px;
  cursor:pointer;
}
.checkout-outline-btn:hover{
  border-color:#aebbb4;
  background:var(--green-soft-2);
}
.checkout-chevron-button{
  border:0;
  background:none;
  padding:5px 0 5px 7px;
  color:#222a26;
  cursor:pointer;
}

/* Payment */
.checkout-payment-header{margin-bottom:15px}
.checkout-card-text{
  margin:3px 0 0;
  color:var(--muted);
  font-size:13px;
}
.checkout-payment-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:12px;
}
.checkout-payment-option{
  position:relative;
  min-height:82px;
  border:1px solid var(--border);
  border-radius:13px;
  padding:16px;
  display:flex;
  flex-direction:column;
  gap:12px;
  cursor:pointer;
  background:#fff;
}
.checkout-payment-option:hover{
  border-color:#b9c6c0;
}
.checkout-payment-option.active{
  border:1.5px solid var(--green);
  background:var(--green-soft-2);
  box-shadow:0 2px 10px rgba(0,143,104,.08);
}
.checkout-payment-icon{
  width:42px;
  height:42px;
  border-radius:11px;
  background:var(--green-soft);
  color:var(--green);
  display:grid;
  place-items:center;
  flex:none;
}
.checkout-payment-option.active .checkout-payment-icon{background:var(--green);color:#fff}
.checkout-payment-name{font-size:14px;font-weight:850;letter-spacing:-.01em}
.checkout-payment-sub{
  margin-top:3px;
  font-size:12px;
  line-height:1.4;
  color:#53605a;
}
.checkout-payment-check{
  position:absolute;
  top:12px;
  right:12px;
  width:21px;
  height:21px;
  border-radius:50%;
  background:var(--green);
  color:#fff;
  display:none;
  align-items:center;
  justify-content:center;
}
.checkout-payment-option.active .checkout-payment-check{display:flex}

.checkout-mobile-panel{
  margin-top:12px;
  border:1px solid var(--border);
  border-radius:13px;
  padding:16px;
  background:var(--green-soft-2);
}
.checkout-provider-label,
.checkout-phone-label{
  display:block;
  font-size:12px;
  font-weight:850;
  color:#33413b;
  margin-bottom:9px;
}
.checkout-provider-grid{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:9px;
}
.checkout-provider-card{
  position:relative;
  display:flex;
  align-items:center;
  gap:10px;
  border:1px solid var(--border);
  border-radius:11px;
  padding:11px 12px;
  cursor:pointer;
  background:#fff;
}
.checkout-provider-card:hover{border-color:#b9c6c0}
.checkout-provider-card.active{
  border:1.5px solid var(--green);
  background:var(--green-soft-2);
}
.checkout-provider-radio{
  width:17px;
  height:17px;
  border-radius:50%;
  border:1.5px solid #9aa59f;
  display:grid;
  place-items:center;
  flex:none;
}
.checkout-provider-card.active .checkout-provider-radio{border-color:var(--green)}
.checkout-provider-card.active .checkout-provider-radio:after{
  content:"";
  width:8px;
  height:8px;
  border-radius:50%;
  background:var(--green);
}
.checkout-provider-logo{
  width:34px;
  height:24px;
  border-radius:6px;
  display:grid;
  place-items:center;
  font-size:8px;
  font-weight:900;
  letter-spacing:.01em;
  flex:none;
}
.checkout-provider-logo.mpesa{background:#e72e34;color:#fff}
.checkout-provider-logo.tigo{background:#ffd500;color:#111}
.checkout-provider-logo.halopesa{background:#f5821f;color:#fff}
.checkout-provider-logo.airtel{background:#e30613;color:#fff}
.checkout-provider-name{font-size:13px;font-weight:800}
.checkout-phone-group{margin-top:16px}
.checkout-phone{
  display:flex;
  border:1px solid var(--border);
  border-radius:11px;
  overflow:hidden;
  background:#fff;
}
.checkout-phone:focus-within{border-color:var(--green)}
.checkout-phone-prefix{
  display:flex;
  align-items:center;
  padding:0 13px;
  background:#f4f7f5;
  border-right:1px solid var(--border);
  font-size:13px;
  font-weight:850;
  color:#33413b;
  flex:none;
}
.checkout-phone input{
  min-width:0;
  flex:1;
  height:44px;
  border:0;
  padding:0 13px;
  font-size:14px;
  color:#17201c;
  background:transparent;
}
.checkout-phone input:focus{outline:none}
.checkout-info{
  margin-top:13px;
  padding:10px 12px;
  border-radius:9px;
  background:#fff;
  border:1px solid var(--border);
  color:#12684f;
  display:flex;
  gap:8px;
  align-items:flex-start;
  font-size:11.5px;
  line-height:1.45;
}
.checkout-card-panel{
  margin-top:12px;
  border:1px solid var(--border);
  border-radius:13px;
  padding:16px;
  background:var(--green-soft-2);
  display:flex;
  flex-direction:column;
  gap:11px;
}
.checkout-card-panel-row{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:11px;
}
.checkout-error{
  display:none;
  margin-top:11px;
  padding:10px 11px;
  border-radius:9px;
  background:#fff1f2;
  border:1px solid #fecdd3;
  color:#be123c;
  font-size:12px;
}
.checkout-place{
  width:100%;
  min-height:54px;
  margin-top:11px;
  border:0;
  border-radius:10px;
  background:var(--green-dark);
  color:#fff;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:9px;
  font-size:16px;
  font-weight:850;
  cursor:pointer;
}
.checkout-place:hover:not(:disabled){background:#006f51;transform:translateY(-1px)}
.checkout-place:disabled{background:#a5ada9;cursor:not-allowed}
.checkout-secure-note{
  margin:10px 0 0;
  display:flex;
  justify-content:center;
  align-items:center;
  gap:7px;
  color:#46514c;
  font-size:11px;
}

/* Summary */
.checkout-summary{
  position:sticky;
  top:18px;
  border:1px solid var(--border);
  border-radius:14px;
  background:#fff;
  padding:20px;
  box-shadow:0 1px 2px rgba(9,11,10,.02);
}
.checkout-summary-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap:15px;
}
.checkout-summary-head h2{
  margin:0;
  font-size:20px;
  line-height:1.2;
  font-weight:850;
  letter-spacing:-.025em;
}
.checkout-edit-cart{
  color:var(--green);
  text-decoration:none;
  font-size:13px;
  font-weight:800;
}
.checkout-edit-cart:hover{color:var(--green-dark)}
.checkout-products{margin-top:12px}
.checkout-product{
  display:grid;
  grid-template-columns:76px minmax(0,1fr) auto;
  gap:12px;
  padding:11px 0;
  border-bottom:1px solid #edf0ee;
}
.checkout-product:first-child{padding-top:6px}
.checkout-product:last-child{border-bottom:0}
.checkout-product-image{
  width:76px;
  height:76px;
  border-radius:10px;
  background:#f7f8f7;
  border:1px solid #e8ece9;
  overflow:hidden;
  display:grid;
  place-items:center;
}
.checkout-product-image img{
  width:100%;
  height:100%;
  object-fit:contain;
  padding:5px;
}
.checkout-product-image span{font-weight:900}
.checkout-product-info{min-width:0}
.checkout-product-name{
  font-size:13px;
  line-height:1.35;
  font-weight:850;
}
.checkout-product-meta{
  margin-top:4px;
  font-size:12px;
  color:#56625c;
}
.checkout-product-unit{
  margin-top:6px;
  font-size:11px;
  color:#7a8580;
}
.checkout-product-price{
  font-size:13px;
  font-weight:850;
  white-space:nowrap;
}
.checkout-totals{
  border-top:1px solid var(--border);
  padding-top:12px;
  margin-top:2px;
}
.checkout-total-row{
  display:flex;
  align-items:center;
  justify-content:space-between;
  font-size:13px;
  margin:8px 0;
}
.checkout-total-row span{color:#56625c}
.checkout-grand-total{
  border-top:1px solid var(--border);
  padding-top:14px;
  margin-top:12px;
  font-size:18px;
}
.checkout-grand-total strong:last-child{color:var(--green-dark)}
.checkout-secure-box{
  margin-top:18px;
  padding:13px;
  border-radius:10px;
  background:var(--green-soft);
  display:flex;
  gap:9px;
  color:#12684f;
}
.checkout-secure-box strong{font-size:13px}
.checkout-secure-box p{
  margin:3px 0 0;
  font-size:11px;
  line-height:1.45;
}
.checkout-features{
  display:grid;
  grid-template-columns:repeat(3,1fr);
  border-top:1px solid var(--border);
  margin-top:16px;
  padding-top:15px;
  text-align:center;
}
.checkout-feature-icon{
  display:flex;
  justify-content:center;
  color:#111815;
}
.checkout-feature-label{
  margin-top:8px;
  font-size:11px;
  color:#3e4944;
}
.checkout-empty-summary{padding:28px 0;font-size:13px;color:#66716c}
.checkout-summary-error{padding:28px 0;color:#b91c1c;font-size:13px}

/* Modals */
.checkout-modal-tabs{display:flex;gap:7px;margin-top:17px}
.checkout-tab{
  border:1px solid var(--border);
  background:#fff;
  color:var(--text);
  border-radius:9px;
  padding:9px 13px;
  font-size:12px;
  font-weight:800;
  cursor:pointer;
}
.checkout-tab.active{
  background:var(--green);
  color:#fff;
  border-color:var(--green);
}
.checkout-saved-list{
  display:flex;
  flex-direction:column;
  gap:8px;
  margin-top:17px;
}
.checkout-saved-address{
  display:flex;
  flex-direction:column;
  align-items:flex-start;
  gap:2px;
  width:100%;
  text-align:left;
  border:1px solid var(--border);
  border-radius:10px;
  padding:11px 13px;
  background:#fff;
  font-size:13px;
  cursor:pointer;
}
.checkout-saved-address:hover{border-color:#b9c6c0}
.checkout-saved-address.active{border:1.5px solid var(--green);background:var(--green-soft-2)}
.checkout-saved-address-meta{color:var(--muted);font-size:12px}
.checkout-no-saved{margin-top:17px;font-size:13px;color:var(--muted)}
.checkout-map-panel{margin-top:17px}
.checkout-map-search{
  width:100%;
  height:42px;
  border:1px solid var(--border);
  border-radius:9px;
  padding:0 12px;
  font-size:13px;
}
.checkout-map{
  height:220px;
  margin-top:10px;
  border-radius:11px;
  border:1px solid var(--border);
  background:var(--green-soft);
  overflow:hidden;
}
.checkout-map-selected{
  margin:10px 0 0;
  font-size:12.5px;
  color:#46514c;
  min-height:16px;
}
.checkout-map-actions{
  display:grid;
  grid-template-columns:1fr 1fr;
  gap:10px;
  margin-top:10px;
}
.checkout-map-actions .checkout-modal-submit{margin-top:0}
.checkout-manual{
  display:none;
  margin-top:17px;
  grid-template-columns:1fr 1fr;
  gap:11px;
}
.checkout-field span{
  display:block;
  font-size:11px;
  font-weight:800;
  margin-bottom:5px;
}
.checkout-field input{
  width:100%;
  height:41px;
  border:1px solid var(--border);
  border-radius:9px;
  padding:0 10px;
}
.checkout-field.full{grid-column:1/-1}
.checkout-map-error{color:#b91c1c;font-size:12px;margin-top:9px}
.checkout-modal-submit{
  width:100%;
  height:43px;
  margin-top:10px;
  border:0;
  border-radius:9px;
  background:var(--green);
  color:#fff;
  font-weight:800;
  font-size:13px;
  cursor:pointer;
  display:flex;
  align-items:center;
  justify-content:center;
  gap:8px;
}
.checkout-modal-submit:hover{background:var(--green-dark)}
.checkout-modal-submit:disabled{background:#a5ada9;cursor:not-allowed}
.checkout-address-modal{
  position:fixed;
  inset:0;
  z-index:100;
  display:none;
  align-items:center;
  justify-content:center;
  background:rgba(9,11,10,.38);
  padding:16px;
  backdrop-filter:blur(3px);
}
.checkout-modal{
  width:min(680px,100%);
  max-height:92vh;
  overflow:auto;
  border:1px solid var(--border);
  border-radius:15px;
  background:#fff;
  padding:21px;
  box-shadow:0 20px 60px rgba(9,11,10,.14);
}
.checkout-modal-head{
  display:flex;
  align-items:center;
  justify-content:space-between;
}
.checkout-modal-head h2{
  margin:0;
  font-size:20px;
  letter-spacing:-.025em;
}
.checkout-modal-close{
  width:35px;
  height:35px;
  border:1px solid var(--border);
  border-radius:50%;
  background:#fff;
  cursor:pointer;
}
.checkout-modal-close:hover{background:#f6f8f7}
.checkout-note-modal{max-width:520px}
.checkout-note-form{margin-top:17px}
.checkout-note-textarea{
  width:100%;
  min-height:125px;
  border:1px solid var(--border);
  border-radius:10px;
  padding:11px;
  resize:vertical;
}
.checkout-note-count{
  font-size:11px;
  text-align:right;
  color:#74807a;
}

/* Responsive */
@media(max-width:1080px){
  .checkout-reference-page{width:min(100% - 40px,780px)}
  .checkout-layout{grid-template-columns:1fr}
  .checkout-summary{position:static}
}
@media(max-width:700px){
  .checkout-reference-page{width:calc(100% - 24px);padding-top:9px}
  .checkout-top{grid-template-columns:1fr auto;min-height:47px}
  .checkout-progress{display:none}
  .checkout-top-secure{font-size:12px}
  .checkout-heading{padding-top:27px}
  .checkout-heading h1{font-size:34px}
  .checkout-heading p{font-size:15px}
  .checkout-card,.checkout-summary{padding:16px}
  .checkout-payment-grid,
  .checkout-mobile-grid,
  .checkout-address-actions{grid-template-columns:1fr}
  .checkout-product{grid-template-columns:68px minmax(0,1fr)}
  .checkout-product-image{width:68px;height:68px}
  .checkout-product-price{grid-column:2}
  .checkout-manual{grid-template-columns:1fr}
  .checkout-field.full{grid-column:auto}
}
@media(prefers-reduced-motion:reduce){
  .checkout-reference-page *{scroll-behavior:auto!important;transition:none!important}
}
</style>


<header class="checkout-top">
  <div class="checkout-brand">
    <a href="{{ route('cart') }}" aria-label="Back to cart">
      <x-tabler-arrow-left size="21" stroke-width="1.8"/>
    </a>
    <a href="{{ route('home') }}">KP WEAR</a>
  </div>

  <div class="checkout-progress" aria-label="Checkout progress">
    <div class="checkout-progress-step active">
      <span class="checkout-progress-dot">1</span><span>Checkout</span>
    </div>
    <x-tabler-chevron-right size="16"/>
    <div class="checkout-progress-step">
      <span class="checkout-progress-dot">2</span><span>Payment</span>
    </div>
    <x-tabler-chevron-right size="16"/>
    <div class="checkout-progress-step">
      <span class="checkout-progress-dot">3</span><span>Complete</span>
    </div>
  </div>

  <div class="checkout-top-secure">
    <x-tabler-lock size="18" stroke-width="1.8"/>
    <span>Secure checkout</span>
  </div>
</header>

<section class="checkout-heading">
  <p class="checkout-eyebrow">Checkout</p>
  <h1>Complete your order</h1>
  <p>Review your details and choose a payment method.</p>
</section>

<div class="checkout-layout">
  <div class="checkout-left">
    <div class="checkout-stack">

      <section class="checkout-card">
        <div class="checkout-card-header">
          <span class="checkout-circle-icon"><x-tabler-user size="21"/></span>
          <div class="checkout-card-main">
            <h2 class="checkout-card-title">Contact information</h2>
            <p data-checkout-contact class="checkout-contact-details">Loading...</p>
          </div>
          <div class="checkout-contact-actions">
            <span class="checkout-check"><x-tabler-check size="18"/></span>
            <button type="button" data-edit-contact class="checkout-edit">Edit</button>
          </div>
        </div>
      </section>

      <section class="checkout-card">
        <div class="checkout-address-row">
          <span class="checkout-circle-icon"><x-tabler-map-pin size="21"/></span>
          <div class="checkout-address-main">
            <div class="checkout-address-title-row">
              <h2 class="checkout-card-title">Delivery address</h2>
              <span data-address-required-badge class="checkout-required">Required</span>
            </div>
            <p data-checkout-address-summary class="checkout-address-text">Add a delivery address or use your current location.</p>
          </div>
          <div>
            <button type="button" data-open-address-modal class="checkout-edit">Edit</button>
          </div>
          <button type="button" data-open-address-modal aria-label="Edit delivery address" class="checkout-chevron-button">
            <x-tabler-chevron-right size="21"/>
          </button>
        </div>

        <div class="checkout-address-actions">
          <button type="button" data-use-current-location class="checkout-outline-btn">
            <x-tabler-current-location size="19"/> Use my current location
          </button>
          <button type="button" data-open-note-modal class="checkout-outline-btn">
            <x-tabler-file-description size="19"/> Add order note
          </button>
        </div>
      </section>

      <section class="checkout-card">
        <div class="checkout-payment-header">
          <span class="checkout-circle-icon"><x-tabler-credit-card size="21"/></span>
          <div>
            <h2 class="checkout-card-title">Payment method</h2>
            <p class="checkout-card-text">Choose how you want to pay.</p>
          </div>
        </div>

        <div data-checkout-payment-methods class="checkout-payment-grid">
          <label data-payment-card data-method="mobile_money" class="checkout-payment-option active">
            <input type="radio" name="payment_method" value="mobile_money" checked hidden>
            <span class="checkout-payment-check"><x-tabler-check size="13" stroke-width="3"/></span>
            <span class="checkout-payment-icon"><x-tabler-device-mobile size="22"/></span>
            <span>
              <span class="checkout-payment-name">Mobile Money</span>
              <span class="checkout-payment-sub">M-Pesa or Tigopesa</span>
            </span>
          </label>

          <label data-payment-card data-method="card" class="checkout-payment-option">
            <input type="radio" name="payment_method" value="card" hidden>
            <span class="checkout-payment-check"><x-tabler-check size="13" stroke-width="3"/></span>
            <span class="checkout-payment-icon"><x-tabler-credit-card size="22"/></span>
            <span>
              <span class="checkout-payment-name">Card</span>
              <span class="checkout-payment-sub">Debit or credit card</span>
            </span>
          </label>
        </div>

        <div data-method-panel="mobile_money" class="checkout-mobile-panel">
          <div class="checkout-provider-label">Select provider</div>
          <div data-provider-cards class="checkout-provider-grid">
            <label data-provider-card class="checkout-provider-card active">
              <input type="radio" name="payment_provider" value="mpesa" checked hidden>
              <span class="checkout-provider-radio"></span>
              <span class="checkout-provider-logo mpesa">M-PESA</span>
              <span class="checkout-provider-name">M-Pesa</span>
            </label>
            <label data-provider-card class="checkout-provider-card">
              <input type="radio" name="payment_provider" value="tigopesa" hidden>
              <span class="checkout-provider-radio"></span>
              <span class="checkout-provider-logo tigo">TIGO</span>
              <span class="checkout-provider-name">Tigopesa</span>
            </label>
            <label data-provider-card class="checkout-provider-card">
              <input type="radio" name="payment_provider" value="halopesa" hidden>
              <span class="checkout-provider-radio"></span>
              <span class="checkout-provider-logo halopesa">HALO</span>
              <span class="checkout-provider-name">HaloPesa</span>
            </label>
            <label data-provider-card class="checkout-provider-card">
              <input type="radio" name="payment_provider" value="airtelmoney" hidden>
              <span class="checkout-provider-radio"></span>
              <span class="checkout-provider-logo airtel">AIRTEL</span>
              <span class="checkout-provider-name">Airtel Money</span>
            </label>
          </div>

          <div class="checkout-phone-group">
            <span class="checkout-phone-label">Mobile number</span>
            <div class="checkout-phone">
              <span class="checkout-phone-prefix">+255</span>
              <input type="tel" name="payment_phone" placeholder="624 643 714" autocomplete="tel" inputmode="numeric" aria-label="Mobile money number">
            </div>
          </div>

          <div class="checkout-info">
            <x-tabler-info-circle size="17"/>
            <span>You will receive a prompt on your phone to complete the payment after placing the order.</span>
          </div>
        </div>

        <div data-method-panel="card" class="checkout-card-panel" style="display:none">
          <label class="checkout-field full">
            <span>Card number</span>
            <input name="card_number" inputmode="numeric" autocomplete="cc-number" placeholder="1234 1234 1234 1234">
          </label>
          <div class="checkout-card-panel-row">
            <label class="checkout-field">
              <span>Expiry</span>
              <input name="card_expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY">
            </label>
            <label class="checkout-field">
              <span>CVV</span>
              <input name="card_cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="123">
            </label>
          </div>
          <label class="checkout-field full">
            <span>Name on card</span>
            <input name="card_name" autocomplete="cc-name" placeholder="As it appears on the card">
          </label>

          <div class="checkout-info">
            <x-tabler-info-circle size="17"/>
            <span>Your card is charged securely once you place the order.</span>
          </div>
        </div>

        <div data-checkout-error class="checkout-error" role="alert"></div>

        <button type="button" data-place-order class="checkout-place" disabled>
          <x-tabler-lock size="19"/>
          <span data-place-order-label>Calculating total...</span>
        </button>

        <p class="checkout-secure-note">
          <x-tabler-shield-check size="17"/>
          Your payment and details are encrypted and secure
        </p>
      </section>

    </div>
  </div>

  <aside class="checkout-summary">
    <div class="checkout-summary-head">
      <h2>Order summary</h2>
      <a href="{{ route('cart') }}" class="checkout-edit-cart">Edit cart</a>
    </div>

    <div data-checkout-summary>
      <div class="checkout-empty-summary">Loading order summary...</div>
    </div>

    <div class="checkout-secure-box">
      <x-tabler-shield-check size="24"/>
      <div>
        <strong>Secure payment</strong>
        <p>Your payment is processed securely by Selcom.</p>
      </div>
    </div>

    <div class="checkout-features">
      <div>
        <div class="checkout-feature-icon"><x-tabler-truck-delivery size="24"/></div>
        <div class="checkout-feature-label">Fast delivery</div>
      </div>
      <div>
        <div class="checkout-feature-icon"><x-tabler-shield-check size="24"/></div>
        <div class="checkout-feature-label">Secure payment</div>
      </div>
      <div>
        <div class="checkout-feature-icon"><x-tabler-headphones size="24"/></div>
        <div class="checkout-feature-label">Support 24/7</div>
      </div>
    </div>
  </aside>
</div>

{{-- Address modal --}}
<div data-address-choice-modal class="checkout-address-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-address-modal-title" aria-hidden="true">
  <div class="checkout-modal">
    <div class="checkout-modal-head">
      <h2 id="checkout-address-modal-title">Choose your address</h2>
      <button type="button" data-close-address-choice class="checkout-modal-close" aria-label="Close"><x-tabler-x size="18"/></button>
    </div>

    <div data-saved-addresses class="checkout-saved-list"></div>

    <div class="checkout-modal-tabs">
      <button type="button" data-address-tab="location" class="checkout-tab active">Use my location</button>
      <button type="button" data-address-tab="manual" class="checkout-tab">Enter manually</button>
    </div>

    <div data-address-panel="location" class="checkout-map-panel">
      <input data-map-search class="checkout-map-search" placeholder="Search your area or street" type="text" autocomplete="off">
      <div data-google-map class="checkout-map"></div>
      <p data-map-selected-address class="checkout-map-selected"></p>
      <div class="checkout-map-actions">
        <button type="button" data-request-location class="checkout-outline-btn">
          <x-tabler-current-location size="18"/> Current location
        </button>
        <button type="button" data-map-confirm class="checkout-modal-submit" disabled>Use this location</button>
      </div>
      <p data-map-error class="checkout-map-error"></p>
    </div>

    <form data-address-form data-address-panel="manual" class="checkout-manual">
      <input type="hidden" name="type" value="shipping">
      <label class="checkout-field full"><span>Recipient name</span><input name="recipient_name" required></label>
      <label class="checkout-field"><span>Phone</span><input name="phone" required></label>
      <label class="checkout-field"><span>Region</span><input name="region" required></label>
      <label class="checkout-field"><span>District</span><input name="district" required></label>
      <label class="checkout-field"><span>Ward</span><input name="ward"></label>
      <label class="checkout-field full"><span>Street / landmark</span><input name="street" required></label>
      <div data-address-form-error class="checkout-error" role="alert"></div>
      <button type="submit" class="checkout-field full checkout-modal-submit">Save address</button>
    </form>
  </div>
</div>

{{-- Note modal --}}
<div data-note-modal class="checkout-address-modal" role="dialog" aria-modal="true" aria-labelledby="checkout-note-modal-title" aria-hidden="true">
  <div class="checkout-modal checkout-note-modal">
    <div class="checkout-modal-head">
      <h2 id="checkout-note-modal-title">Add order note</h2>
      <button type="button" data-close-note-modal class="checkout-modal-close" aria-label="Close"><x-tabler-x size="18"/></button>
    </div>
    <form data-note-form class="checkout-note-form">
      <textarea data-note-textarea maxlength="250" class="checkout-note-textarea" placeholder="e.g. Call when you arrive, gate code, landmark, etc."></textarea>
      <p class="checkout-note-count"><span data-note-count>0</span>/250</p>
      <button type="submit" class="checkout-modal-submit">Save note</button>
    </form>
  </div>
</div>

<script nonce="{{ Vite::cspNonce() }}">

(() => {
  const page = document.querySelector('[data-checkout-page]');
  if (!page || page.dataset.booted === '1') return;
  page.dataset.booted = '1';

  if (document.querySelector('meta[name="kp-signed-in"]')?.getAttribute('content') !== '1') { location.href = '/login'; return; }

  const API = '/api/v1';
  const $ = (s, r=page) => r.querySelector(s);
  const $$ = (s, r=page) => [...r.querySelectorAll(s)];
  const esc = v => String(v ?? '').replace(/[&<>"']/g, c => ({
    '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'
  }[c]));

  const getCookie = name => {
    const match = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return match ? decodeURIComponent(match[1]) : null;
  };

  const fallbackApi = async (path, options={}) => {
    const headers = new Headers(options.headers || {});
    headers.set('Accept','application/json');
    if (options.body && typeof options.body === 'object' && !(options.body instanceof FormData)) {
      headers.set('Content-Type','application/json');
    }
    const method = (options.method || 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD') {
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
    }
    const response = await fetch(API + path, {
      ...options,
      credentials:'include',
      headers,
      body: options.body && typeof options.body === 'object' && !(options.body instanceof FormData)
        ? JSON.stringify(options.body) : options.body
    });
    const raw = await response.text();
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch {}
    if (!response.ok) throw new Error(data?.message || Object.values(data?.errors || {})?.flat?.()?.[0] || `Request failed (${response.status})`);
    return data;
  };

  const api = async (path, options={}) => fallbackApi(path, options);

  const toast = message => window.dispatchEvent(new CustomEvent('kp:toast',{detail:message}));

  const contact = $('[data-checkout-contact]');
  const addressSummary = $('[data-checkout-address-summary]');
  const addressBadge = $('[data-address-required-badge]');
  const summary = $('[data-checkout-summary]');
  const errorBox = $('[data-checkout-error]');
  const place = $('[data-place-order]');
  const placeLabel = $('[data-place-order-label]');

  let user = null;
  let addresses = [];
  let selectedAddress = null;
  let selectedPayment = $('input[name="payment_method"]:checked')?.value || 'mobile_money';
  let selectedProvider = $('input[name="payment_provider"]:checked')?.value || 'mpesa';
  let total = 0;

  const error = msg => {
    if (!errorBox) return;
    errorBox.textContent = msg;
    errorBox.style.display = 'flex';
  };
  const clearError = () => { if (errorBox) errorBox.style.display='none'; };

  const updateButton = () => {
    const ready = !!selectedAddress && !!selectedPayment && total > 0;
    if (place) place.disabled = !ready;
    if (placeLabel) placeLabel.textContent = total > 0 ? `Place Order — TZS ${total.toLocaleString()}` : 'Calculating total…';
  };

  const renderSummary = payload => {
    const data = payload?.data || payload || {};
    const items = Array.isArray(data.items) ? data.items : [];
    const subtotal = Number(data.subtotal ?? 0);
    const delivery = Number(data.delivery_fee ?? data.shipping_total ?? data.shipping ?? 0);
    total = Number(data.total ?? (subtotal + delivery));

    if (!summary) return;

    if (!items.length) {
      summary.innerHTML = `
        <div class="checkout-empty-summary">
          Your cart is empty.
        </div>`;
      updateButton();
      return;
    }

    summary.innerHTML = `
      <div class="checkout-products">
        ${items.map(item => {
          const image = item.image_url || item.image || item.product?.image || '';
          const name = item.name || item.product_name || item.product?.name || 'Product';
          const qty = Number(item.quantity || 1);
          const unit = Number(item.unit_price ?? item.price ?? item.product?.price ?? 0);
          const line = Number(item.line_total ?? item.total ?? (unit * qty));
          const meta = [
            item.size ? `Size: ${esc(item.size)}` : '',
            `Qty: ${qty}`
          ].filter(Boolean).join('  ·  ');

          return `
            <div class="checkout-product">
              <div class="checkout-product-image">
                ${image
                  ? `<img src="${esc(image)}" alt="${esc(name)}">`
                  : `<span>KP</span>`}
              </div>
              <div class="checkout-product-info">
                <div class="checkout-product-name">${esc(name)}</div>
                <div class="checkout-product-meta">${meta}</div>
                <div class="checkout-product-unit">TZS ${unit.toLocaleString()} × ${qty}</div>
              </div>
              <div class="checkout-product-price">TZS ${line.toLocaleString()}</div>
            </div>`;
        }).join('')}
      </div>

      <div class="checkout-totals">
        <div class="checkout-total-row">
          <span>Subtotal</span>
          <strong>TZS ${subtotal.toLocaleString()}</strong>
        </div>
        <div class="checkout-total-row">
          <span>Delivery fee</span>
          <strong>TZS ${delivery.toLocaleString()}</strong>
        </div>
        <div class="checkout-total-row checkout-grand-total">
          <strong>Total</strong>
          <strong>TZS ${total.toLocaleString()}</strong>
        </div>
      </div>`;
    updateButton();
  };

  // Broken product images fall back to the "KP" placeholder instead of a broken-image icon.
  // (error events don't bubble, so this listener is attached with capture:true)
  summary?.addEventListener('error', event => {
    const img = event.target;
    if (img?.tagName === 'IMG' && img.closest('.checkout-product-image')) {
      img.replaceWith(Object.assign(document.createElement('span'), { textContent: 'KP' }));
    }
  }, true);

  const setContact = () => {
    if (!contact) return;
    contact.innerHTML = `
      <span class="checkout-contact-name">${esc(user?.name || 'Signed-in customer')}</span>
      <span class="checkout-contact-details">
        ${esc(user?.phone || '')}${user?.phone && user?.email ? '  ·  ' : ''}${esc(user?.email || '')}
      </span>`;
  };

  const updateAddress = () => {
    const a = addresses.find(x => String(x.id) === String(selectedAddress));
    if (!a) {
      addressSummary.textContent = 'Add a delivery address or use your current location.';
      addressBadge.style.display = '';
    } else {
      addressSummary.textContent = [
        a.street,
        [a.ward, a.district, a.region].filter(Boolean).join(', ')
      ].filter(Boolean).join('  ');
      addressBadge.style.display = 'none';
    }
    updateButton();
  };

  const renderSavedAddresses = () => {
    const list = $('[data-saved-addresses]');
    if (!list) return;
    if (!addresses.length) {
      list.innerHTML = '<p class="checkout-no-saved">No saved addresses yet — use your current location or enter one manually below.</p>';
      return;
    }
    list.innerHTML = addresses.map(a => `
      <button type="button" class="checkout-saved-address${String(a.id)===String(selectedAddress) ? ' active' : ''}" data-select-address="${esc(a.id)}">
        <span>${esc(a.street)}</span>
        <span class="checkout-saved-address-meta">${esc([a.ward, a.district, a.region].filter(Boolean).join(', '))}</span>
      </button>`).join('');
  };

  $('[data-saved-addresses]')?.addEventListener('click', event => {
    const button = event.target.closest('[data-select-address]');
    if (!button) return;
    selectedAddress = button.dataset.selectAddress;
    updateAddress();
    renderSavedAddresses();
    clearError();
    close(addressModal);
  });

  const load = async () => {
    try {
      const [me, addr, preview] = await Promise.all([
        api('/auth/me'),
        api('/addresses'),
        api('/cart/checkout/preview')
      ]);

      user = me?.data || me?.user || me || {};
      setContact();

      addresses = Array.isArray(addr?.data) ? addr.data : [];
      const def = addresses.find(a => a.is_default) || addresses[0];
      if (def) selectedAddress = def.id;
      updateAddress();
      renderSavedAddresses();

      renderSummary(preview);
    } catch (e) {
      error(e.message || 'Unable to load checkout.');
      if (summary) summary.innerHTML = '<div class="checkout-summary-error">Unable to load your order summary. Please refresh the page.</div>';
      updateButton();
    }
  };

  const togglePaymentPanels = () => {
    $$('[data-method-panel]').forEach(panel => {
      panel.style.display = panel.dataset.methodPanel === selectedPayment ? 'flex' : 'none';
    });
  };

  $$('[data-payment-card]').forEach(card => {
    card.addEventListener('click', () => {
      selectedPayment = $('input[name="payment_method"]',card)?.value || card.dataset.method || '';
      $$('[data-payment-card]').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      const radio = $('input[name="payment_method"]',card);
      if (radio) radio.checked = true;
      togglePaymentPanels();
      updateButton();
    });
  });
  togglePaymentPanels();

  $$('[data-provider-card]').forEach(card => {
    card.addEventListener('click', () => {
      selectedProvider = $('input[name="payment_provider"]',card)?.value || selectedProvider;
      $$('[data-provider-card]').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      const radio = $('input[name="payment_provider"]',card);
      if (radio) radio.checked = true;
    });
  });

  const addressModal = $('[data-address-choice-modal]');
  const noteModal = $('[data-note-modal]');
  const open = m => { if (m) { m.style.display='flex'; m.setAttribute('aria-hidden','false'); } };
  const close = m => { if (m) { m.style.display='none'; m.setAttribute('aria-hidden','true'); } };

  $('[data-edit-contact]')?.addEventListener('click', () => {
    toast('Contact details are managed by your account.');
  });

  $$('[data-open-address-modal]').forEach(button => button.addEventListener('click', () => open(addressModal)));
  $('[data-open-note-modal]')?.addEventListener('click', () => open(noteModal));
  $('[data-close-address-choice]')?.addEventListener('click', () => close(addressModal));
  $$('[data-close-note-modal]').forEach(b => b.addEventListener('click', () => close(noteModal)));

  // Address modal tabs: keep the saved/location/manual panels in sync.
  const setAddressTab = tab => {
    $$('[data-address-tab]').forEach(button => {
      button.classList.toggle('active', button.dataset.addressTab === tab);
    });
    $$('[data-address-panel]').forEach(panel => {
      const isActive = panel.dataset.addressPanel === tab;
      panel.style.display = isActive ? (tab === 'manual' ? 'grid' : 'block') : 'none';
    });
  };

  $$('[data-address-tab]').forEach(button => {
    button.addEventListener('click', () => setAddressTab(button.dataset.addressTab));
  });

  setAddressTab('location');

  // "Use my current location" on the page itself jumps straight to that flow.
  $('[data-use-current-location]')?.addEventListener('click', () => {
    open(addressModal);
    setAddressTab('location');
  });

  // --- Google Maps address picker ---------------------------------------
  // Loaded lazily (only once the address modal is opened) so the page
  // doesn't pay for the Maps SDK until it's actually needed.
  const scriptNonce = document.currentScript?.nonce || $('script[nonce]', document)?.nonce || '';
  let googleMapsPromise = null;
  const loadGoogleMaps = () => {
    if (window.google?.maps) return Promise.resolve();
    if (googleMapsPromise) return googleMapsPromise;
    const key = page.dataset.googleMapsKey;
    if (!key) return Promise.reject(new Error('Map is not configured yet.'));
    googleMapsPromise = new Promise((resolve, reject) => {
      const script = document.createElement('script');
      script.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(key)}&libraries=places`;
      if (scriptNonce) script.nonce = scriptNonce;
      script.async = true;
      script.onload = resolve;
      script.onerror = () => reject(new Error('Unable to load the map. Please enter your address manually.'));
      document.head.appendChild(script);
    });
    return googleMapsPromise;
  };

  const DAR_ES_SALAAM = { lat: -6.7924, lng: 39.2083 };
  let map = null, marker = null, geocoder = null, mapInitPromise = null;

  const componentValue = (components, type) =>
    components?.find(c => c.types.includes(type))?.long_name || '';

  const fillFromComponents = components => {
    const form = $('[data-address-form]');
    if (!form || !components?.length) return;
    const region = componentValue(components, 'administrative_area_level_1');
    const district = componentValue(components, 'administrative_area_level_2') || componentValue(components, 'locality');
    const ward = componentValue(components, 'sublocality') || componentValue(components, 'sublocality_level_1') || componentValue(components, 'neighborhood');
    const street = [componentValue(components, 'route'), componentValue(components, 'street_number')].filter(Boolean).join(' ');
    if (region) form.region.value = region;
    if (district) form.district.value = district;
    if (ward) form.ward.value = ward;
    if (street) form.street.value = street;
  };

  const setCoordFields = latLng => {
    const form = $('[data-address-form]');
    if (!form) return;
    form.querySelector('input[name="latitude"]')?.remove();
    form.querySelector('input[name="longitude"]')?.remove();
    const lat = document.createElement('input');
    lat.type = 'hidden'; lat.name = 'latitude'; lat.value = latLng.lat();
    const lng = document.createElement('input');
    lng.type = 'hidden'; lng.name = 'longitude'; lng.value = latLng.lng();
    form.append(lat, lng);
  };

  const placeMarker = async latLng => {
    marker.setPosition(latLng);
    map.panTo(latLng);
    setCoordFields(latLng);
    $('[data-map-confirm]').disabled = false;
    const selected = $('[data-map-selected-address]');
    try {
      const { results } = await geocoder.geocode({ location: latLng });
      const place = results?.[0];
      marker.addressComponents = place?.address_components || [];
      marker.formattedAddress = place?.formatted_address || '';
      if (selected) selected.textContent = place?.formatted_address || `${latLng.lat().toFixed(5)}, ${latLng.lng().toFixed(5)}`;
    } catch {
      marker.addressComponents = [];
      marker.formattedAddress = '';
      if (selected) selected.textContent = `${latLng.lat().toFixed(5)}, ${latLng.lng().toFixed(5)}`;
    }
  };

  const initMap = () => {
    if (mapInitPromise) return mapInitPromise;
    mapInitPromise = (async () => {
      const mapError = $('[data-map-error]');
      try {
        await loadGoogleMaps();
      } catch (e) {
        if (mapError) mapError.textContent = e.message;
        throw e;
      }
      const container = $('[data-google-map]');
      map = new google.maps.Map(container, { center: DAR_ES_SALAAM, zoom: 12, disableDefaultUI: true, zoomControl: true });
      geocoder = new google.maps.Geocoder();
      marker = new google.maps.Marker({ map, position: DAR_ES_SALAAM, draggable: true });

      map.addListener('click', e => placeMarker(e.latLng));
      marker.addListener('dragend', () => placeMarker(marker.getPosition()));

      const searchInput = $('[data-map-search]');
      if (searchInput) {
        const autocomplete = new google.maps.places.Autocomplete(searchInput, {
          fields: ['geometry', 'formatted_address', 'address_components'],
          componentRestrictions: { country: 'tz' }
        });
        autocomplete.addListener('place_changed', () => {
          const place = autocomplete.getPlace();
          if (!place.geometry) return;
          map.setZoom(16);
          placeMarker(place.geometry.location);
        });
      }
    })();
    return mapInitPromise;
  };

  // Load the map the first time the address modal is actually opened.
  $$('[data-open-address-modal]').forEach(button => button.addEventListener('click', () => initMap()));
  $('[data-use-current-location]')?.addEventListener('click', () => initMap());

  $('[data-map-confirm]')?.addEventListener('click', () => {
    if (!marker) return;
    fillFromComponents(marker.addressComponents);
    const form = $('[data-address-form]');
    if (form && marker.formattedAddress && !form.street.value) form.street.value = marker.formattedAddress;
    setAddressTab('manual');
    toast('Location selected — please confirm the details below.');
  });

  $('[data-request-location]')?.addEventListener('click', async () => {
    const mapError = $('[data-map-error]');
    if (mapError) mapError.textContent = '';
    if (!navigator.geolocation) {
      if (mapError) mapError.textContent = 'Location services are not available on this device.';
      return;
    }
    try {
      await initMap();
    } catch {
      return; // loadGoogleMaps already set the error message
    }
    navigator.geolocation.getCurrentPosition(
      position => {
        map.setZoom(16);
        placeMarker(new google.maps.LatLng(position.coords.latitude, position.coords.longitude));
      },
      err => {
        if (!mapError) return;
        mapError.textContent = err.code === err.PERMISSION_DENIED
          ? 'Location access was denied. Please enter your address manually.'
          : 'Unable to get your location. Please enter your address manually.';
      },
      { enableHighAccuracy:true, timeout:10000 }
    );
  });

  // Save a manually entered address via the real /addresses endpoint.
  $('[data-address-form]')?.addEventListener('submit', async event => {
    event.preventDefault();
    const form = event.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    const formError = $('[data-address-form-error]', form);
    if (formError) formError.style.display = 'none';

    const body = Object.fromEntries(new FormData(form).entries());
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }

    try {
      const result = await api('/addresses', { method:'POST', body });
      const saved = result?.data || result;
      if (!saved?.id) throw new Error('Address was saved but no address was returned.');
      addresses.push(saved);
      selectedAddress = saved.id;
      updateAddress();
      renderSavedAddresses();
      form.reset();
      close(addressModal);
      toast('Delivery address saved.');
    } catch (e) {
      if (formError) {
        formError.textContent = e.message || 'Unable to save address.';
        formError.style.display = 'flex';
      }
    } finally {
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save address'; }
    }
  });

  // Close modals when clicking their backdrop.
  [addressModal, noteModal].forEach(modal => {
    modal?.addEventListener('click', event => {
      if (event.target === modal) close(modal);
    });
  });

  // Escape closes whichever checkout modal is open.
  document.addEventListener('keydown', event => {
    if (event.key !== 'Escape') return;
    close(addressModal);
    close(noteModal);
  });

  // Order-note character counter.
  const noteTextarea = $('[data-note-textarea]');
  const noteCount = $('[data-note-count]');
  noteTextarea?.addEventListener('input', () => {
    if (noteCount) noteCount.textContent = String(noteTextarea.value.length);
  });

  $('[data-note-form]')?.addEventListener('submit', event => {
    event.preventDefault();
    close(noteModal);
    toast('Order note saved.');
  });

  place?.addEventListener('click', async () => {
    clearError();
    if (!selectedAddress) {
      error('Please add a delivery address before placing your order.');
      open(addressModal);
      return;
    }
    if (!total) { error('Your order total is not ready yet.'); return; }

    place.disabled = true;
    placeLabel.textContent = 'Creating your order…';

    try {
      const key = `kp-${Date.now()}-${crypto.randomUUID?.() || Math.random().toString(36).slice(2)}`.replace(/[^A-Za-z0-9._-]/g,'');
      const result = await api('/checkout',{
        method:'POST',
        headers:{'Idempotency-Key':key},
        body:{
          address_id:selectedAddress,
          notes:$('[data-note-textarea]')?.value?.trim() || null,
          payment_method:selectedPayment,
          payment_provider:selectedProvider,
          payment_phone:$('input[name="payment_phone"]')?.value?.replace(/[\s-]/g,'') || null
        }
      });
      const orderNumber = result?.data?.order_number;
      if (!orderNumber) throw new Error('Order was created but no order number was returned.');
      const gatewayUrl = (result?.data?.payment || []).find(p => p && typeof p.payment_gateway_url === 'string' && p.payment_gateway_url)?.payment_gateway_url;
      if (gatewayUrl) { window.location.href = gatewayUrl; return; }
      location.href = `/orders/${encodeURIComponent(orderNumber)}`;
    } catch(e) {
      place.disabled = false;
      updateButton();
      error(e.message || 'Unable to place order.');
    }
  });

  load();
})();

</script>
</div>

@endsection
