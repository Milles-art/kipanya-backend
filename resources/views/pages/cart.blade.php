{{-- resources/views/pages/cart.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
/* ============================================================
   KP WEAR Cart — redesigned
   ============================================================ */
.kpc {
  --ink: #0c110e;
  --text: #1a231e;
  --muted: #5a6660;
  --border: #e2e8e4;
  --bg: #f5f8f6;
  --card: #ffffff;
  --green: #1a7a52;
  --green-dark: #145f40;
  --green-faint: #eef8f3;
  --green-faint2: #f5fbf8;
  --radius-card: 18px;
  --shadow-card: 0 1px 3px rgba(10,16,12,.06);
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
  background: var(--bg);
  color: var(--ink);
  min-height: 100vh;
}
.kpc * { box-sizing: border-box; }
.kpc button, .kpc input { font: inherit; }
.kpc button, .kpc a {
  transition: background .18s, border-color .18s, color .18s, opacity .18s, transform .15s;
}
.kpc button:focus-visible,
.kpc a:focus-visible {
  outline: 3px solid rgba(26,122,82,.22);
  outline-offset: 2px;
}

/* ── Header ─────────────────────────────────────────────── */
.kpc-header {
  position: sticky; top: 0; z-index: 40;
  background: rgba(255,255,255,.82);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 32px;
  height: 56px;
}
.kpc-brand {
  font-size: 17px; font-weight: 900;
  letter-spacing: -.03em;
  color: var(--ink); text-decoration: none;
}
.kpc-brand:hover { color: var(--green); }
.kpc-header-link {
  display: none;
  align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600;
  color: var(--green);
  text-decoration: none;
}
.kpc-header-link:hover { color: var(--green-dark); }
@media (min-width: 640px) { .kpc-header-link { display: flex; } }

/* ── Page inner ──────────────────────────────────────────── */
.kpc-inner {
  width: min(1280px, calc(100% - 64px));
  margin: 0 auto;
  padding: 32px 0 96px;
}

/* ── Page heading ────────────────────────────────────────── */
.kpc-heading {
  border-bottom: 1px solid var(--border);
  padding-bottom: 28px;
  display: flex; flex-direction: column; gap: 12px;
}
@media (min-width: 640px) {
  .kpc-heading { flex-direction: row; align-items: flex-end; justify-content: space-between; }
}
.kpc-eyebrow {
  font-size: 11px; font-weight: 800;
  letter-spacing: .22em; text-transform: uppercase;
  color: var(--green); margin: 0 0 8px;
}
.kpc-h1 {
  margin: 0;
  font-size: 34px; font-weight: 900;
  letter-spacing: -.03em; color: var(--ink);
}
@media (min-width: 640px) { .kpc-h1 { font-size: 42px; } }
.kpc-h1-sep { font-weight: 400; color: var(--muted); margin: 0 8px; }
.kpc-continue-link {
  display: none;
  align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600;
  color: var(--ink);
  text-decoration: underline;
  text-underline-offset: 4px;
}
.kpc-continue-link:hover { color: var(--green); }
@media (min-width: 640px) { .kpc-continue-link { display: flex; } }

/* ── Error banner ────────────────────────────────────────── */
.kpc-error {
  display: none;
  margin-top: 20px;
  padding: 12px 14px;
  border-radius: 12px;
  border: 1px solid #fecdd3;
  background: #fff1f2;
  color: #be123c;
  font-size: 13px;
}

/* ── Skeleton ────────────────────────────────────────────── */
.kpc-skeleton {
  background: linear-gradient(90deg, #edf1ee 25%, #e0e8e2 50%, #edf1ee 75%);
  background-size: 200% 100%;
  animation: kpc-shimmer 1.4s infinite;
  border-radius: 14px;
}
@keyframes kpc-shimmer {
  0%   { background-position: 200% 0; }
  100% { background-position: -200% 0; }
}

/* ── Empty state ─────────────────────────────────────────── */
.kpc-empty {
  display: none;
  flex-direction: column; align-items: center;
  padding: 112px 0; text-align: center;
}
.kpc-empty-icon {
  width: 80px; height: 80px;
  border-radius: 50%;
  background: var(--green-faint);
  display: grid; place-items: center;
  box-shadow: 0 0 0 12px rgba(238,248,243,.5);
  color: var(--green);
  margin-bottom: 24px;
}
.kpc-empty h2 {
  margin: 0; font-size: 20px; font-weight: 800; color: var(--ink);
}
.kpc-empty p {
  margin: 8px 0 0; font-size: 14px; color: var(--muted); max-width: 260px;
}
.kpc-empty-cta {
  margin-top: 28px;
  display: inline-flex; align-items: center; gap: 8px;
  height: 44px; padding: 0 24px;
  border-radius: 12px;
  background: var(--green-dark); color: #fff;
  font-size: 14px; font-weight: 800;
  text-decoration: none;
}
.kpc-empty-cta:hover { background: #0f4e33; transform: translateY(-1px); }

/* ── Grid layout ─────────────────────────────────────────── */
.kpc-grid {
  display: grid;
  grid-template-columns: 1fr;
  gap: 36px;
  margin-top: 32px;
}
@media (min-width: 1024px) {
  .kpc-grid { grid-template-columns: minmax(0,1fr) 400px; gap: 40px; }
}
@media (min-width: 1280px) {
  .kpc-grid { grid-template-columns: minmax(0,1fr) 430px; gap: 56px; }
}

/* ── Items header ────────────────────────────────────────── */
.kpc-items-header {
  display: flex; align-items: center; justify-content: space-between; gap: 16px;
  margin-bottom: 20px;
}
.kpc-item-count { font-size: 14px; color: var(--muted); }
.kpc-item-count strong { font-weight: 700; color: var(--ink); }
.kpc-clear-btn {
  display: flex; align-items: center; gap: 6px;
  background: none; border: 0; padding: 0;
  font-size: 13px; font-weight: 600;
  color: var(--muted); cursor: pointer;
}
.kpc-clear-btn:hover { color: #dc2626; }

/* ── Item cards ──────────────────────────────────────────── */
.kpc-items { display: flex; flex-direction: column; gap: 12px; }
.kpc-item {
  display: flex; gap: 16px;
  border: 1px solid var(--border);
  border-radius: var(--radius-card);
  background: var(--card);
  padding: 16px; box-shadow: var(--shadow-card);
  transition: border-color .2s, background .2s;
}
.kpc-item:hover {
  border-color: rgba(26,122,82,.2);
  background: rgba(238,248,243,.3);
}
@media (min-width: 640px) { .kpc-item { padding: 20px; } }
.kpc-item-img {
  width: 96px; height: 96px;
  border-radius: 14px;
  overflow: hidden; flex: none;
  border: 1px solid var(--border);
  background: #f3f6f4;
  display: grid; place-items: center;
}
@media (min-width: 640px) { .kpc-item-img { width: 112px; height: 112px; } }
.kpc-item-img img { width: 100%; height: 100%; object-fit: cover; }
.kpc-item-img span { font-size: 11px; font-weight: 900; color: var(--muted); }
.kpc-item-body { flex: 1; min-width: 0; display: flex; flex-direction: column; justify-content: space-between; }
.kpc-item-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; }
.kpc-item-name { font-size: 14px; font-weight: 700; color: var(--ink); line-height: 1.35; }
.kpc-item-meta { font-size: 12px; color: var(--muted); margin-top: 4px; }
.kpc-remove {
  background: none; border: 0; padding: 2px;
  color: var(--muted); cursor: pointer; flex: none; margin-top: 2px;
}
.kpc-remove:hover { color: #dc2626; }
.kpc-item-bottom { display: flex; align-items: flex-end; justify-content: space-between; margin-top: 12px; }
.kpc-qty {
  display: flex; align-items: center; gap: 8px;
  border: 1px solid var(--border);
  border-radius: 999px;
  padding: 4px 8px;
}
.kpc-qty-btn {
  width: 24px; height: 24px;
  border-radius: 50%;
  border: 0; background: none;
  color: var(--muted); cursor: pointer;
  display: grid; place-items: center;
  font-size: 14px;
}
.kpc-qty-btn:hover:not(:disabled) { background: var(--green-faint); color: var(--green-dark); }
.kpc-qty-btn:disabled { opacity: .3; cursor: not-allowed; }
.kpc-qty-val { width: 20px; text-align: center; font-size: 14px; font-weight: 600; color: var(--ink); }
.kpc-item-price { font-size: 14px; font-weight: 800; color: var(--ink); }

/* ── Order summary ───────────────────────────────────────── */
.kpc-summary {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius-card);
  padding: 20px; box-shadow: 0 16px 48px rgba(0,0,0,.05);
}
@media (min-width: 640px) { .kpc-summary { padding: 24px; } }
@media (min-width: 1280px) { .kpc-summary { padding: 28px; } }
@media (min-width: 1024px) { .kpc-summary { position: sticky; top: 72px; } }
.kpc-summary-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.kpc-summary-head h2 {
  margin: 0; font-size: 18px; font-weight: 900;
  letter-spacing: -.025em; color: var(--ink);
}
.kpc-secure-badge {
  display: inline-flex; align-items: center; gap: 4px;
  background: var(--green-faint);
  border-radius: 999px;
  padding: 4px 10px;
  font-size: 11px; font-weight: 700;
  color: var(--green);
}
.kpc-subtotal-row {
  display: flex; align-items: center; justify-content: space-between;
  border-bottom: 1px solid var(--border);
  padding-bottom: 20px; margin-top: 28px;
}
.kpc-subtotal-row span { font-size: 13px; color: var(--muted); }
.kpc-subtotal-row strong { font-size: 20px; font-weight: 900; color: var(--ink); }
.kpc-summary-meta { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; }
.kpc-summary-meta-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 12px; color: var(--muted);
}
.kpc-summary-meta-row span:first-child { display: flex; align-items: center; gap: 6px; }
.kpc-checkout-btn {
  margin-top: 24px;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  width: 100%; height: 48px;
  border-radius: 12px;
  background: var(--green-dark); color: #fff;
  font-size: 14px; font-weight: 800;
  text-decoration: none;
}
.kpc-checkout-btn:hover { background: #0f4e33; transform: translateY(-1px); }
.kpc-checkout-note { margin-top: 14px; text-align: center; font-size: 11px; color: var(--muted); }

/* ── Reassurance strip ───────────────────────────────────── */
.kpc-strip {
  border-top: 1px solid var(--border);
  border-bottom: 1px solid var(--border);
  padding: 24px 0; margin-top: 48px;
}
@media (min-width: 640px) { .kpc-strip { margin-top: 56px; padding: 28px 0; } }
.kpc-strip-grid { display: grid; gap: 20px; }
@media (min-width: 640px) { .kpc-strip-grid { grid-template-columns: 1fr 1fr; gap: 0; } }
.kpc-benefit {
  display: flex; align-items: center; gap: 16px;
  padding: 0;
}
@media (min-width: 640px) { .kpc-benefit { padding: 0 24px; } }
.kpc-benefit + .kpc-benefit {
  border-top: 1px solid rgba(26,122,82,.1);
  padding-top: 20px;
}
@media (min-width: 640px) {
  .kpc-benefit + .kpc-benefit {
    border-top: 0;
    border-left: 1px solid rgba(26,122,82,.1);
    padding-top: 0;
  }
}
.kpc-benefit-icon { color: var(--ink); flex: none; }
.kpc-benefit-title { font-size: 14px; font-weight: 700; color: var(--ink); }
.kpc-benefit-sub { font-size: 12px; color: var(--muted); margin-top: 4px; }

/* ── Mobile footer ───────────────────────────────────────── */
.kpc-mobile-footer { display: flex; justify-content: center; margin-top: 24px; }
@media (min-width: 640px) { .kpc-mobile-footer { display: none; } }
.kpc-mobile-footer a {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600; color: var(--muted);
  text-decoration: underline; text-underline-offset: 4px;
}

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 680px) {
  .kpc-inner { width: calc(100% - 24px); }
  .kpc-header { padding: 0 16px; }
}
@media (prefers-reduced-motion: reduce) {
  .kpc *, .kpc *::before, .kpc *::after {
    transition: none !important; animation: none !important;
  }
}
</style>
@endpush

@section('content')
<div data-cart-page data-cart-version="2" class="kpc">

  {{-- ── Header ── --}}
  <header class="kpc-header">
    <a href="{{ route('home') }}" class="kpc-brand">KP WEAR</a>
    <a href="{{ route('shop') }}" class="kpc-header-link">
      Continue shopping
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="M5 12h14m-7-7 7 7-7 7"/>
      </svg>
    </a>
  </header>

  <div class="kpc-inner">

    {{-- ── Page heading ── --}}
    <div class="kpc-heading">
      <div>
        <p class="kpc-eyebrow">KP Wear</p>
        <h1 class="kpc-h1">
          Your bag
          <span class="kpc-h1-sep" aria-hidden="true">·</span>
          <span data-cart-header-count>{{ $cartItems->count() }}</span>
        </h1>
      </div>
      <a href="{{ route('shop') }}" class="kpc-continue-link">
        Continue shopping
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14m-7-7 7 7-7 7"/>
        </svg>
      </a>
    </div>

    {{-- ── Error banner ── --}}
    <div data-cart-error class="kpc-error" role="alert" aria-live="polite"></div>

    {{-- ── Loading skeleton ── --}}
    <div data-cart-loading class="kpc-grid" aria-live="polite" aria-busy="true" style="margin-top:32px">
      <div style="display:flex;flex-direction:column;gap:12px">
        <div class="kpc-skeleton" style="height:112px"></div>
        <div class="kpc-skeleton" style="height:112px"></div>
        <div class="kpc-skeleton" style="height:96px"></div>
      </div>
      <div class="kpc-skeleton" style="height:280px"></div>
    </div>

    {{-- ── Empty state ── --}}
    <div data-cart-empty class="kpc-empty" style="{{ $cartItems->isEmpty() ? '' : 'display:none' }}">
      <div class="kpc-empty-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
      </div>
      <h2>Your bag is empty</h2>
      <p>Find something you'll reach for every day.</p>
      <a href="{{ route('shop') }}" class="kpc-empty-cta">
        Continue shopping
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14m-7-7 7 7-7 7"/>
        </svg>
      </a>
    </div>

    {{-- ── Cart content ── --}}
    @if($cartItems->isNotEmpty())
    <div data-cart-content class="kpc-grid">

      {{-- Items --}}
      <section aria-label="Cart items">
        <div class="kpc-items-header">
          <p class="kpc-item-count">
            <strong data-cart-item-count>{{ $cartItems->count() }}</strong>
            {{ $cartItems->count() === 1 ? 'item' : 'items' }} in your bag
          </p>
          <button type="button" data-cart-clear class="kpc-clear-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <polyline points="3 6 5 6 21 6"/>
              <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
            </svg>
            Clear bag
          </button>
        </div>

        <div data-cart-items class="kpc-items" aria-live="polite">
          @foreach($cartItems as $item)
          <div class="kpc-item" data-item-id="{{ $item->id }}" data-variant-id="{{ $item->wear_product_variant_id }}" data-unit-price="{{ (int) ($item->unit_price ?? 0) }}">
            <div class="kpc-item-img">
              @if($item->image_url ?? $item->product?->image ?? null)
                <img src="{{ $item->image_url ?? $item->product->image }}"
                     alt="{{ $item->name ?? $item->product?->name }}"
                     onerror="this.parentElement.innerHTML='<span>KP</span>'">
              @else
                <span>KP</span>
              @endif
            </div>
            <div class="kpc-item-body">
              <div class="kpc-item-top">
                <div>
                  <p class="kpc-item-name">{{ $item->name ?? $item->product?->name }}</p>
                  <p class="kpc-item-meta">
                    {{ collect([$item->size ? 'Size '.$item->size : null, $item->color ?? null])->filter()->implode('  ·  ') }}
                  </p>
                </div>
                <button type="button" class="kpc-remove" aria-label="Remove item"
                        data-remove-item="{{ $item->id }}">
                  <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                       stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                  </svg>
                </button>
              </div>
              <div class="kpc-item-bottom">
                <div class="kpc-qty">
                  <button type="button" class="kpc-qty-btn" aria-label="Decrease quantity"
                          data-qty-dec="{{ $item->id }}" {{ $item->quantity <= 1 ? 'disabled' : '' }}>
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                  <span class="kpc-qty-val" data-qty-val="{{ $item->id }}">{{ $item->quantity }}</span>
                  <button type="button" class="kpc-qty-btn" aria-label="Increase quantity"
                          data-qty-inc="{{ $item->id }}">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                         stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                      <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                  </button>
                </div>
                <strong class="kpc-item-price" data-item-total="{{ $item->id }}">
                  TZS {{ number_format(($item->unit_price ?? $item->price) * $item->quantity) }}
                </strong>
              </div>
            </div>
          </div>
          @endforeach
        </div>
      </section>

      {{-- Order summary --}}
      <aside class="kpc-summary" aria-label="Order summary">
        <div class="kpc-summary-head">
          <h2>Order summary</h2>
          <span class="kpc-secure-badge">
            <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
            </svg>
            Secure
          </span>
        </div>

        <div class="kpc-subtotal-row">
          <span>Subtotal</span>
          <strong data-cart-summary>TZS {{ number_format($subtotal ?? 0) }}</strong>
        </div>

        <div class="kpc-summary-meta">
          <div class="kpc-summary-meta-row">
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1-2-2h11a2 2 0 0 1 2 2v3"/>
                <rect x="9" y="11" width="14" height="10" rx="1"/>
              </svg>
              Delivery
            </span>
            <span>Calculated at checkout</span>
          </div>
          <div class="kpc-summary-meta-row">
            <span>
              <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
              </svg>
              Payment
            </span>
            <span>Secure checkout</span>
          </div>
        </div>

        <a href="{{ route('checkout') }}" class="kpc-checkout-btn">
          Proceed to checkout
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14m-7-7 7 7-7 7"/>
          </svg>
        </a>

        <p class="kpc-checkout-note">Taxes and delivery calculated at the next step</p>
      </aside>
    </div>
    @endif

    {{-- ── Reassurance strip ── --}}
    <div class="kpc-strip">
      <div class="kpc-strip-grid">
        <div class="kpc-benefit">
          <svg class="kpc-benefit-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
          </svg>
          <div>
            <p class="kpc-benefit-title">Secure payments</p>
            <p class="kpc-benefit-sub">Your information is protected</p>
          </div>
        </div>
        <div class="kpc-benefit">
          <svg class="kpc-benefit-icon" xmlns="http://www.w3.org/2000/svg" width="26" height="26"
               viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
               stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1-2-2h11a2 2 0 0 1 2 2v3"/>
            <rect x="9" y="11" width="14" height="10" rx="1"/>
            <circle cx="12" cy="16" r="1"/>
          </svg>
          <div>
            <p class="kpc-benefit-title">Fast delivery</p>
            <p class="kpc-benefit-sub">Across Tanzania</p>
          </div>
        </div>
      </div>
    </div>

    {{-- ── Mobile footer ── --}}
    <div class="kpc-mobile-footer">
      <a href="{{ route('shop') }}">
        Continue shopping
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <path d="M5 12h14m-7-7 7 7-7 7"/>
        </svg>
      </a>
    </div>

  </div>{{-- /inner --}}
</div>{{-- /kpc --}}
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };

  const api = async (path, opts = {}) => {
    const headers = new Headers(opts.headers || {});
    headers.set('Accept', 'application/json');
    const method = (opts.method || 'GET').toUpperCase();
    if (method !== 'GET') {
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
      if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
      }
    }
    // Guests identify their cart with a token stored by the storefront JS.
    const guestToken = localStorage.getItem('kp_guest_cart_token');
    if (guestToken) headers.set('X-Guest-Cart-Token', guestToken);
    const res = await fetch('/api/v1' + path, {
      ...opts, credentials: 'include', headers,
      body: opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)
        ? JSON.stringify(opts.body) : opts.body,
    });
    const raw = await res.text();
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch {}
    if (!res.ok) throw new Error(data?.message || `Error ${res.status}`);
    return data;
  };

  const toast = msg => window.dispatchEvent(new CustomEvent('kp:toast', { detail: msg }));

  const $ = s => document.querySelector(s);
  const $$ = s => [...document.querySelectorAll(s)];

  const errBox    = $('[data-cart-error]');
  const loadEl    = $('[data-cart-loading]');
  const emptyEl   = $('[data-cart-empty]');
  const contentEl = $('[data-cart-content]');

  const showError = msg => {
    if (!errBox) return;
    errBox.textContent = msg;
    errBox.style.display = 'flex';
  };

  // Hide skeleton once page data is server-rendered
  if (loadEl) loadEl.style.display = 'none';
  if (contentEl) contentEl.style.display = 'grid';

  /* ── Subtotal recalculation ── */
  const recalcSubtotal = () => {
    let total = 0;
    $$('[data-item-id]').forEach(row => {
      const id  = row.dataset.itemId;
      const qty = parseInt($(`[data-qty-val="${id}"]`)?.textContent || '1', 10);
      const unitPrice = parseInt(row.dataset.unitPrice || '0', 10);
      total += qty * unitPrice;
    });
    const el = $('[data-cart-summary]');
    if (el) el.textContent = 'TZS ' + total.toLocaleString();
    const countEl = $('[data-cart-header-count]');
    const itemCountEl = $('[data-cart-item-count]');
    const itemCount = $$('[data-item-id]').length;
    if (countEl) countEl.textContent = itemCount;
    if (itemCountEl) {
      itemCountEl.innerHTML = `<strong>${itemCount}</strong>`;
      itemCountEl.insertAdjacentText('beforeend', ' ' + (itemCount === 1 ? 'item' : 'items') + ' in your bag');
    }
    if (itemCount === 0 && emptyEl) {
      if (contentEl) contentEl.style.display = 'none';
      emptyEl.style.display = 'flex';
    }
  };

  /* ── Quantity controls ── */
  document.addEventListener('click', async e => {
    // Decrease
    const dec = e.target.closest('[data-qty-dec]');
    if (dec) {
      const id  = dec.dataset.qtyDec;
      const valEl = $(`[data-qty-val="${id}"]`);
      const row   = $(`[data-item-id="${id}"]`);
      const variantId = row?.dataset.variantId;
      let qty = parseInt(valEl?.textContent || '1', 10);
      if (qty <= 1) return;
      qty--;
      if (valEl) valEl.textContent = qty;
      if (qty <= 1) dec.disabled = true;
      // update line total
      const unitPrice = parseInt(row?.dataset.unitPrice || '0', 10);
      const totalEl = $(`[data-item-total="${id}"]`);
      if (totalEl) totalEl.textContent = 'TZS ' + (unitPrice * qty).toLocaleString();
      recalcSubtotal();
      try { await api(`/cart/items/${variantId}`, { method: 'PUT', body: { quantity: qty } }); }
      catch (err) { showError(err.message); }
      return;
    }

    // Increase
    const inc = e.target.closest('[data-qty-inc]');
    if (inc) {
      const id  = inc.dataset.qtyInc;
      const valEl = $(`[data-qty-val="${id}"]`);
      const row   = $(`[data-item-id="${id}"]`);
      const variantId = row?.dataset.variantId;
      let qty = parseInt(valEl?.textContent || '1', 10);
      if (qty >= 99) return;
      qty++;
      if (valEl) valEl.textContent = qty;
      const decBtn = $(`[data-qty-dec="${id}"]`);
      if (decBtn) decBtn.disabled = false;
      const unitPrice = parseInt(row?.dataset.unitPrice || '0', 10);
      const totalEl = $(`[data-item-total="${id}"]`);
      if (totalEl) totalEl.textContent = 'TZS ' + (unitPrice * qty).toLocaleString();
      recalcSubtotal();
      try { await api(`/cart/items/${variantId}`, { method: 'PUT', body: { quantity: qty } }); }
      catch (err) { showError(err.message); }
      return;
    }

    // Remove
    const rem = e.target.closest('[data-remove-item]');
    if (rem) {
      const id  = rem.dataset.removeItem;
      const row = $(`[data-item-id="${id}"]`);
      const variantId = row?.dataset.variantId;
      if (row) row.remove();
      recalcSubtotal();
      toast('Item removed from bag.');
      try { await api(`/cart/items/${variantId}`, { method: 'DELETE' }); }
      catch (err) { showError(err.message); }
      return;
    }

    // Clear
    if (e.target.closest('[data-cart-clear]')) {
      $$('[data-item-id]').forEach(row => row.remove());
      recalcSubtotal();
      toast('Bag cleared.');
      try { await api('/cart', { method: 'DELETE' }); }
      catch (err) { showError(err.message); }
    }
  });

  // Scroll-reveal
  const reveals = $$('.kpc-reveal');
  if (!('IntersectionObserver' in window)) {
    reveals.forEach(el => el.classList.add('is-visible'));
  } else {
    const obs = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) { entry.target.classList.add('is-visible'); obs.unobserve(entry.target); }
      });
    }, { threshold: 0.12 });
    reveals.forEach(el => obs.observe(el));
  }
})();
</script>
@endpush
