{{-- resources/views/pages/order-status.blade.php --}}
@extends('layouts.app')

@section('content')
<style nonce="{{ Vite::cspNonce() }}">
/* ============================================================
   KP WEAR Order Status — 3-step payment tracker
   ============================================================ */
.os {
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
  --red: #dc2626;
  --red-faint: #fef2f2;
  --red-border: #fecdd3;
  --amber: #d97706;
  --amber-faint: #fffbeb;
  --amber-border: #fde68a;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
  background: var(--bg);
  color: var(--ink);
  min-height: 100vh;
  padding: 0 0 80px;
}
.os * { box-sizing: border-box; }
.os a, .os button { transition: background .18s, color .18s, border-color .18s, transform .15s; }
.os a:focus-visible, .os button:focus-visible {
  outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px;
}

/* ── Header ─────────────────────────────────────────────── */
.os-header {
  position: sticky; top: 0; z-index: 40;
  background: rgba(255,255,255,.82); backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
  display: flex; align-items: center; justify-content: space-between;
  padding: 0 32px; height: 56px;
}
.os-brand { font-size: 17px; font-weight: 900; letter-spacing: -.03em; color: var(--ink); text-decoration: none; }
.os-brand:hover { color: var(--green); }
.os-header-orders {
  display: flex; align-items: center; gap: 6px;
  font-size: 13px; font-weight: 600; color: var(--muted); text-decoration: none;
}
.os-header-orders:hover { color: var(--green); }

/* ── Inner wrapper ───────────────────────────────────────── */
.os-inner {
  width: min(520px, calc(100% - 32px));
  margin: 0 auto;
  padding: 40px 0 0;
}

/* ── Step tracker ────────────────────────────────────────── */
.os-tracker {
  display: flex; align-items: center;
  margin-bottom: 32px;
}
.os-step-wrap {
  display: flex; flex-direction: column; align-items: center; gap: 6px; flex: none;
}
.os-step-dot {
  width: 34px; height: 34px; border-radius: 50%;
  background: #e2e8e4; color: #7a8a82;
  display: grid; place-items: center;
  font-size: 12px; font-weight: 800;
  transition: background .35s, color .35s, box-shadow .35s;
}
.os-step-label {
  font-size: 11px; font-weight: 600; color: var(--muted);
  white-space: nowrap; transition: color .35s;
}
.os-step-wrap.done .os-step-dot { background: var(--green); color: #fff; }
.os-step-wrap.done .os-step-label { color: var(--green); }
.os-step-wrap.active .os-step-dot {
  background: var(--ink); color: #fff;
  box-shadow: 0 0 0 4px rgba(12,17,14,.1);
}
.os-step-wrap.active .os-step-label { color: var(--ink); font-weight: 700; }
.os-step-wrap.success .os-step-dot { background: var(--green); color: #fff; box-shadow: 0 0 0 4px rgba(26,122,82,.15); }
.os-step-wrap.success .os-step-label { color: var(--green); font-weight: 700; }

.os-connector {
  flex: 1; height: 2px;
  background: #e2e8e4; border-radius: 2px;
  margin: 0 6px 18px;
  transition: background .4s;
}
.os-connector.done { background: var(--green); }

/* ── Status card ─────────────────────────────────────────── */
.os-card {
  background: var(--card); border: 1px solid var(--border);
  border-radius: 24px; padding: 32px 28px;
  box-shadow: 0 1px 3px rgba(10,16,12,.06);
  text-align: center;
  transition: border-color .4s;
}
.os-card.state-success { border-color: rgba(26,122,82,.3); }
.os-card.state-failed  { border-color: rgba(220,38,38,.25); }

/* ── Status icon ─────────────────────────────────────────── */
.os-status-icon {
  width: 72px; height: 72px; border-radius: 50%;
  display: grid; place-items: center;
  margin: 0 auto 20px;
  transition: background .4s, box-shadow .4s, color .4s;
}
.os-status-icon.pending {
  background: var(--amber-faint); color: var(--amber);
  box-shadow: 0 0 0 10px rgba(217,119,6,.07);
}
.os-status-icon.success {
  background: var(--green-faint); color: var(--green);
  box-shadow: 0 0 0 10px rgba(26,122,82,.09);
}
.os-status-icon.refunded {
  background: var(--green-faint); color: var(--green);
  box-shadow: 0 0 0 10px rgba(26,122,82,.09);
}
.os-status-icon.failed {
  background: var(--red-faint); color: var(--red);
  box-shadow: 0 0 0 10px rgba(220,38,38,.07);
}

/* ── Pulsing dot for pending ─────────────────────────────── */
.os-pulse-row {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  font-size: 13px; font-weight: 600; color: var(--amber); margin-bottom: 18px;
}
.os-pulse-dot {
  width: 8px; height: 8px; border-radius: 50%; background: var(--amber);
  animation: os-pulse 1.6s ease-in-out infinite;
}
@keyframes os-pulse {
  0%, 100% { opacity: 1; transform: scale(1); }
  50%       { opacity: .35; transform: scale(.75); }
}

.os-title {
  margin: 0 0 4px; font-size: 26px; font-weight: 900;
  letter-spacing: -.04em; color: var(--ink);
}
.os-order-ref {
  font-size: 13px; font-family: ui-monospace, monospace;
  color: var(--muted); margin: 0 0 20px;
}
.os-divider { border: 0; border-top: 1px solid var(--border); margin: 20px 0; }

/* ── Pending: step-by-step instructions ─────────────────── */
.os-instructions {
  list-style: none; padding: 0; margin: 0;
  display: flex; flex-direction: column; gap: 12px; text-align: left;
}
.os-instruction {
  display: flex; align-items: flex-start; gap: 12px;
}
.os-instruction-num {
  width: 22px; height: 22px; border-radius: 50%; flex: none; margin-top: 1px;
  background: var(--green-faint); color: var(--green);
  display: grid; place-items: center; font-size: 11px; font-weight: 900;
}
.os-instruction-text {
  font-size: 13px; color: var(--text); line-height: 1.55;
}
.os-note {
  margin: 14px 0 0; padding: 12px 14px;
  background: var(--bg); border: 1px solid var(--border); border-radius: 10px;
  font-size: 12px; color: var(--muted); line-height: 1.6; text-align: left;
}

/* ── Order summary (what did I buy?) ─────────────────────── */
.os-summary {
  margin-top: 16px;
  background: var(--card); border: 1px solid var(--border);
  border-radius: 24px; padding: 24px;
  box-shadow: 0 1px 3px rgba(10,16,12,.06);
  text-align: left;
}
.os-summary-title {
  margin: 0; font-size: 11px; font-weight: 800;
  letter-spacing: .18em; text-transform: uppercase; color: var(--green);
}
.os-items {
  list-style: none; padding: 0; margin: 8px 0 0;
  display: flex; flex-direction: column;
}
.os-item {
  display: flex; align-items: baseline; justify-content: space-between; gap: 12px;
  padding: 10px 0; border-top: 1px solid var(--border);
}
.os-item:first-child { border-top: 0; }
.os-item-name { margin: 0; font-size: 13px; font-weight: 700; color: var(--ink); line-height: 1.4; }
.os-item-meta { margin: 2px 0 0; font-size: 11px; color: var(--muted); }
.os-item-total { font-size: 13px; font-weight: 800; color: var(--ink); white-space: nowrap; }
.os-totals { margin: 4px 0 0; padding: 0; }
.os-total-row {
  display: flex; align-items: center; justify-content: space-between;
  padding: 6px 0; font-size: 13px; color: var(--muted);
}
.os-total-row dd { margin: 0; font-weight: 600; color: var(--ink); }
.os-total-row.grand {
  border-top: 1px solid var(--border); margin-top: 6px; padding-top: 12px;
}
.os-total-row.grand dt { font-weight: 800; color: var(--ink); }
.os-total-row.grand dd { font-size: 17px; font-weight: 900; }
.os-address {
  margin: 12px 0 0; padding: 12px 14px;
  background: var(--bg); border: 1px solid var(--border); border-radius: 10px;
  font-size: 12px; color: var(--muted); line-height: 1.6;
}
.os-address strong { color: var(--text); }

/* ── Success / failed messages ───────────────────────────── */
.os-success-msg {
  font-size: 15px; color: var(--green); font-weight: 700; margin: 0 0 6px;
}
.os-body-msg { font-size: 13px; color: var(--muted); line-height: 1.6; margin: 0; }

/* ── Actions ─────────────────────────────────────────────── */
.os-actions {
  margin-top: 20px;
  display: flex; flex-direction: column; gap: 10px;
}
.os-btn-primary {
  display: flex; align-items: center; justify-content: center; gap: 8px;
  height: 52px; border-radius: 14px; border: 0;
  background: var(--green-dark); color: #fff;
  font-size: 15px; font-weight: 800;
  text-decoration: none; cursor: pointer; width: 100%;
}
.os-btn-primary:hover { background: #0f4e33; transform: translateY(-1px); }
.os-btn-primary.amber { background: var(--amber); }
.os-btn-primary.amber:hover { background: #b45309; }
.os-btn-row {
  display: flex; gap: 10px;
}
.os-btn-row .os-btn-secondary { flex: 1; }
.os-btn-secondary {
  display: flex; align-items: center; justify-content: center; gap: 7px;
  height: 44px; border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--card); color: var(--ink);
  font-size: 13px; font-weight: 600;
  text-decoration: none; cursor: pointer; width: 100%;
}
.os-btn-secondary:hover { background: var(--green-faint2); border-color: #b0c4b8; }

/* ── Help link ───────────────────────────────────────────── */
.os-help {
  margin-top: 28px; text-align: center;
  font-size: 12px; color: var(--muted);
}
.os-help a { color: var(--green); font-weight: 600; text-decoration: none; }
.os-help a:hover { color: var(--green-dark); }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 600px) {
  .os-header { padding: 0 16px; }
  .os-card { padding: 24px 18px; }
  .os-btn-row { flex-direction: column; }
  .os-btn-row .os-btn-secondary { flex: none; }
}
@media (prefers-reduced-motion: reduce) {
  .os *, .os *::before, .os *::after { transition: none !important; animation: none !important; }
}
</style>

@php
  $orderNumber = $orderNumber ?? request()->route('orderNumber');
  $paymentUrl  = $paymentGatewayUrl ?? null;
@endphp

<div data-order-status
     data-order-number="{{ $orderNumber }}"
     data-payment-url="{{ $paymentUrl }}"
     class="os">

  {{-- ── Header ── --}}
  <header class="os-header">
    <a href="{{ route('home') }}" class="os-brand">KP WEAR</a>
    <a href="{{ route('account.orders') }}" class="os-header-orders">
      <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
        <rect x="9" y="3" width="6" height="4" rx="1"/>
      </svg>
      My orders
    </a>
  </header>

  <div class="os-inner">

    {{-- ── Step tracker ── --}}
    <div class="os-tracker" role="list" aria-label="Order progress">
      {{-- Step 1: Checkout (always done) --}}
      <div class="os-step-wrap done" data-os-step="1" role="listitem">
        <div class="os-step-dot" aria-label="Checkout complete">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
            <polyline points="20 6 9 17 4 12"/>
          </svg>
        </div>
        <span class="os-step-label">Checkout</span>
      </div>

      <div class="os-connector done" data-os-connector="1" aria-hidden="true"></div>

      {{-- Step 2: Payment --}}
      <div class="os-step-wrap active" data-os-step="2" role="listitem">
        <div class="os-step-dot" aria-label="Payment in progress">2</div>
        <span class="os-step-label">Payment</span>
      </div>

      <div class="os-connector" data-os-connector="2" aria-hidden="true"></div>

      {{-- Step 3: Complete --}}
      <div class="os-step-wrap" data-os-step="3" role="listitem">
        <div class="os-step-dot" aria-label="Order complete">3</div>
        <span class="os-step-label">Complete</span>
      </div>
    </div>

    {{-- ── Status card ── --}}
    <div data-os-card class="os-card" role="region" aria-live="polite">

      {{-- Icon (swapped by JS) --}}
      <div data-os-icon class="os-status-icon pending" aria-hidden="true">
        <svg data-icon="pending" xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round">
          <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        <svg data-icon="success" xmlns="http://www.w3.org/2000/svg" width="36" height="36" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
          <polyline points="20 6 9 17 4 12"/>
        </svg>
        <svg data-icon="failed" xmlns="http://www.w3.org/2000/svg" width="34" height="34" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
             style="display:none">
          <circle cx="12" cy="12" r="10"/>
          <line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>
        </svg>
      </div>

      <h1 data-os-title class="os-title">Awaiting payment</h1>
      <p class="os-order-ref">#{{ $orderNumber }}</p>

      {{-- ── PENDING panel ── --}}
      <div data-os-panel="pending">
        <div class="os-pulse-row" aria-live="polite">
          <span class="os-pulse-dot" aria-hidden="true"></span>
          Waiting for payment confirmation
        </div>
        <hr class="os-divider">
        <ol class="os-instructions" aria-label="How to complete payment">
          <li class="os-instruction">
            <span class="os-instruction-num" aria-hidden="true">1</span>
            <span class="os-instruction-text">
              Tap <strong>Pay now</strong> below to open the secure Selcom payment page.
            </span>
          </li>
          <li class="os-instruction">
            <span class="os-instruction-num" aria-hidden="true">2</span>
            <span class="os-instruction-text">
              Choose M-Pesa, Tigo, Airtel, or Halo and approve the payment on your phone.
            </span>
          </li>
          <li class="os-instruction">
            <span class="os-instruction-num" aria-hidden="true">3</span>
            <span class="os-instruction-text">
              Come back here — this page updates automatically once your payment arrives.
            </span>
          </li>
        </ol>
        <p class="os-note">
          Your order is only confirmed once payment is received. You can safely leave this page and find it later under <strong>My orders</strong>.
        </p>
      </div>

      {{-- ── SUCCESS panel ── --}}
      <div data-os-panel="success" style="display:none">
        <p class="os-success-msg">Payment confirmed!</p>
        <p class="os-body-msg">
          Your order is confirmed and being prepared. You'll receive updates by SMS to your mobile number.
        </p>
      </div>

      {{-- ── FAILED panel ── --}}
      <div data-os-panel="failed" style="display:none">
        <p class="os-body-msg">
          Your payment didn't go through. No money was charged — you can try again safely with the same or a different method.
        </p>
      </div>

      {{-- ── REFUNDED panel ── --}}
      <div data-os-panel="refunded" style="display:none">
        <p class="os-success-msg">Payment refunded</p>
        <p class="os-body-msg">
          The money was returned to your original payment method. It usually arrives within a few days.
        </p>
      </div>

    </div>{{-- /os-card --}}

    {{-- ── Order summary (filled in from the status API; stays hidden for guests) ── --}}
    <section data-os-summary class="os-summary" style="display:none" aria-label="Order summary">
      <h2 class="os-summary-title">Your order</h2>
      <ul data-os-items class="os-items"></ul>
      <dl class="os-totals">
        <div class="os-total-row"><dt>Subtotal</dt><dd data-os-subtotal>—</dd></div>
        <div class="os-total-row grand"><dt>Total</dt><dd data-os-total>—</dd></div>
      </dl>
      <p data-os-address class="os-address" style="display:none"></p>
    </section>

    {{-- ── Actions (swapped by JS) ── --}}

    {{-- Pending actions --}}
    <div data-os-ctas="pending" class="os-actions">
      <a data-pay-btn
         href="{{ $paymentUrl ?: '#' }}"
         class="os-btn-primary amber"
         style="{{ $paymentUrl ? '' : 'display:none' }}"
         aria-label="Open secure payment page">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>
        </svg>
        Pay now
      </a>
      <div class="os-btn-row">
        <a href="{{ route('account.orders') }}" class="os-btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 5H7a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
          </svg>
          View my orders
        </a>
        <a href="{{ route('shop') }}" class="os-btn-secondary">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
            <line x1="3" y1="6" x2="21" y2="6"/>
            <path d="M16 10a4 4 0 0 1-8 0"/>
          </svg>
          Continue shopping
        </a>
      </div>
    </div>

    {{-- Success actions --}}
    <div data-os-ctas="success" class="os-actions" style="display:none">
      <a href="{{ route('shop') }}" class="os-btn-primary">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/>
          <line x1="3" y1="6" x2="21" y2="6"/>
          <path d="M16 10a4 4 0 0 1-8 0"/>
        </svg>
        Continue shopping
      </a>
      <a href="{{ route('account.orders') }}" class="os-btn-secondary">
        View my orders
      </a>
    </div>

    {{-- Failed actions --}}
    <div data-os-ctas="failed" class="os-actions" style="display:none">
      <button data-retry-btn type="button" class="os-btn-primary amber">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M3 12a9 9 0 1 0 9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
          <path d="M3 3v5h5"/>
        </svg>
        Try again
      </button>
      <div class="os-btn-row">
        <a href="{{ route('account.orders') }}" class="os-btn-secondary">View my orders</a>
        <a href="{{ route('shop') }}" class="os-btn-secondary">Continue shopping</a>
      </div>
    </div>

    {{-- Refunded actions --}}
    <div data-os-ctas="refunded" class="os-actions" style="display:none">
      <a href="{{ route('shop') }}" class="os-btn-primary">
        Continue shopping
      </a>
      <a href="{{ route('account.orders') }}" class="os-btn-secondary">
        View my orders
      </a>
    </div>

    {{-- Help --}}
    <p class="os-help">
      Having trouble? <a href="{{ route('contact') }}">Contact support</a>
    </p>

  </div>{{-- /os-inner --}}
</div>{{-- /os --}}

<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const el  = sel => document.querySelector(sel);
  const els = sel => [...document.querySelectorAll(sel)];

  const root        = el('[data-order-status]');
  const orderNumber = root?.dataset?.orderNumber || '';
  // FIX: only https payment URLs are trusted (no javascript:, no http, no simulator).
  const isSafePayUrl = u => typeof u === 'string' && u.startsWith('https://');
  let paymentUrl = isSafePayUrl(root?.dataset?.paymentUrl) ? root.dataset.paymentUrl : '';

  /* ── State machine ───────────────────────────────────── */
  // Transitions: pending → (paid|failed)
  // pending: steps 1 done, 2 active, 3 idle
  // paid:    steps 1,2,3 done
  // failed:  steps 1 done, 2 active (amber), 3 idle

  const ICONS = {
    pending: `<circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>`,
    success: `<polyline points="20 6 9 17 4 12"/>`,
    failed:  `<circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/>`,
    refunded: `<polyline points="20 6 9 17 4 12"/>`,
  };

  const applyState = state => {
    const card     = el('[data-os-card]');
    const iconWrap = el('[data-os-icon]');
    const titleEl  = el('[data-os-title]');

    // card border
    card.className = 'os-card' + (state === 'success' ? ' state-success' : state === 'failed' ? ' state-failed' : '');

    // icon
    if (iconWrap) {
      iconWrap.className = `os-status-icon ${state === 'paid' ? 'success' : state}`;
      iconWrap.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="${state === 'success' ? 36 : 34}" height="${state === 'success' ? 36 : 34}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="${state === 'success' ? 2.2 : 1.8}" stroke-linecap="round" stroke-linejoin="round">${ICONS[state === 'paid' ? 'success' : state] || ICONS.pending}</svg>`;
    }

    // title
    const titles = { pending: 'Awaiting payment', paid: 'Payment confirmed!', failed: 'Payment failed', refunded: 'Payment refunded' };
    if (titleEl) titleEl.textContent = titles[state] || 'Awaiting payment';

    // panels
    const panelState = state === 'paid' ? 'success' : state;
    els('[data-os-panel]').forEach(p => {
      p.style.display = p.dataset.osPanel === panelState ? '' : 'none';
    });

    // CTAs
    els('[data-os-ctas]').forEach(c => {
      c.style.display = c.dataset.osCtas === panelState ? '' : 'none';
    });

    // steps
    if (state === 'paid') {
      // All 3 done
      els('[data-os-step]').forEach(s => {
        s.className = 'os-step-wrap success';
        if (s.dataset.osStep === '2' || s.dataset.osStep === '3') {
          s.querySelector('.os-step-dot').innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>`;
        }
      });
      els('[data-os-connector]').forEach(c => c.classList.add('done'));
    } else if (state === 'failed') {
      // Step 2 becomes "failed" visually (red)
      const step2 = el('[data-os-step="2"]');
      if (step2) {
        step2.className = 'os-step-wrap active';
        const dot = step2.querySelector('.os-step-dot');
        if (dot) {
          dot.style.background = '#dc2626';
          dot.style.color = '#fff';
          dot.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>`;
        }
      }
    }
    // FIX: update "Pay now" href from latest payment URL (https only, single place).
    if (isSafePayUrl(paymentUrl)) {
      const payBtn = el('[data-pay-btn]');
      if (payBtn) { payBtn.href = paymentUrl; payBtn.style.display = ''; }
    }
  };

  /* ── Order summary (what did I buy?) ─────────────────── */
  // Built with textContent only — server strings can never become markup.
  const money = v => {
    const n = Number(v);
    if (!Number.isFinite(n)) return '—';
    return 'TZS ' + Math.round(n).toLocaleString('en-US');
  };

  const renderSummary = data => {
    const box = el('[data-os-summary]');
    if (!box) return;
    const items = Array.isArray(data.items) ? data.items : [];
    if (!items.length && data.subtotal == null && data.total == null) return;

    const list = el('[data-os-items]');
    if (list && items.length && !list.dataset.done) {
      list.dataset.done = '1';
      const frag = document.createDocumentFragment();
      items.forEach(it => {
        const qty = Math.max(1, parseInt(it.quantity || '1', 10));
        const li = document.createElement('li');
        li.className = 'os-item';
        const left = document.createElement('div');
        const name = document.createElement('p');
        name.className = 'os-item-name';
        name.textContent = (it.name || 'Item') + (qty > 1 ? ' × ' + qty : '');
        left.appendChild(name);
        const meta = [it.size ? 'Size ' + it.size : null, it.color || null].filter(Boolean).join(' · ');
        if (meta) {
          const m = document.createElement('p');
          m.className = 'os-item-meta';
          m.textContent = meta;
          left.appendChild(m);
        }
        const total = document.createElement('span');
        total.className = 'os-item-total';
        total.textContent = money(it.line_total ?? (Number(it.unit_price || 0) * qty));
        li.appendChild(left);
        li.appendChild(total);
        frag.appendChild(li);
      });
      list.appendChild(frag);
    }

    const set = (sel, val) => { const n = el(sel); if (n) n.textContent = val; };
    if (data.subtotal != null) set('[data-os-subtotal]', money(data.subtotal));
    if (data.total != null) set('[data-os-total]', money(data.total));

    const addr = el('[data-os-address]');
    if (addr) {
      const c = data.customer || {};
      const d = data.delivery || {};
      const line1 = [c.name, c.phone].filter(Boolean).join(' · ');
      const line2 = [d.address, d.city].filter(Boolean).join(', ');
      if (line1 || line2) {
        addr.innerHTML = '';
        if (line1) {
          const s = document.createElement('strong');
          s.textContent = line1;
          addr.appendChild(s);
        }
        if (line1 && line2) addr.appendChild(document.createElement('br'));
        if (line2) addr.appendChild(document.createTextNode(line2));
        addr.style.display = '';
      }
    }

    box.style.display = '';
  };

  /* ── Polling ─────────────────────────────────────────── */
  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };

  const fetchStatus = async () => {
    const headers = new Headers({ 'Accept': 'application/json' });
    const xsrf = getCookie('XSRF-TOKEN');
    if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
    const res = await fetch(`/api/v1/orders/${encodeURIComponent(orderNumber)}`, {
      credentials: 'include', headers,
    });
    if (!res.ok) throw new Error(`HTTP ${res.status}`);
    return res.json();
  };

  const TERMINAL = new Set(['paid', 'completed', 'delivered', 'failed', 'cancelled', 'refunded']);
  const SUCCESS  = new Set(['paid', 'completed', 'delivered']);
  const FAILED   = new Set(['failed', 'cancelled']);

  let pollCount = 0;
  const MAX_POLLS = 72; // ~6 minutes at 5s intervals

  const poll = async () => {
    if (!orderNumber) return;
    try {
      const json = await fetchStatus();
      const data   = json?.data || json || {};
      // Normalise: backends use different field names
      const status = (data.payment_status || data.status || '').toLowerCase();
      // Grab the gateway URL if the API returns it (https only)
      const payments = Array.isArray(data.payment) ? data.payment : [];
      const freshUrl = payments.find(p => p?.payment_gateway_url)?.payment_gateway_url;
      if (isSafePayUrl(freshUrl)) {
        paymentUrl = freshUrl;
        const payBtn = document.querySelector('[data-pay-btn]');
        if (payBtn) { payBtn.href = paymentUrl; payBtn.style.display = ''; }
      }

      // Fill the order summary on every successful poll (items, totals, address).
      renderSummary(data);

      if (SUCCESS.has(status)) {
        applyState('paid');
        return; // done — no more polling
      }
      if (status === 'refunded') {
        applyState('refunded');
        return;
      }
      if (FAILED.has(status)) {
        applyState('failed');
        return;
      }
    } catch {
      // network error — keep polling silently
    }

    pollCount++;
    if (pollCount < MAX_POLLS) {
      // Adaptive interval: faster at first, then slow down
      const delay = pollCount < 10 ? 4000 : pollCount < 30 ? 7000 : 15000;
      setTimeout(poll, delay);
    } else {
      // Nudge instead of silence: the order still settles via webhook/SMS
      // even if this tab never sees it, so point at My orders.
      const pulseRow = el('[data-os-panel="pending"] .os-pulse-row');
      if (pulseRow) {
        pulseRow.innerHTML = '';
        const msg = document.createElement('span');
        msg.textContent = 'Still waiting? You will get an SMS once it confirms — see My orders below.';
        pulseRow.appendChild(msg);
      }
    }
  };

  /* ── Init ────────────────────────────────────────────── */
  applyState('pending');

  if (orderNumber) {
    // First poll after 3s (let page settle), then continue
    setTimeout(poll, 3000);
  }

  // "Try again" mints a fresh payment token — the old Selcom link may have
  // expired. Falls back to the last known URL if repay fails (https only).
  el('[data-retry-btn]')?.addEventListener('click', async () => {
    const btn = el('[data-retry-btn]');
    if (btn) btn.disabled = true;
    try {
      const headers = new Headers({ 'Accept': 'application/json', 'Content-Type': 'application/json' });
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
      const res = await fetch(`/api/v1/orders/${encodeURIComponent(orderNumber)}/repay`, {
        method: 'POST', credentials: 'include', headers,
      });
      if (res.ok) {
        const json = await res.json();
        const pays = Array.isArray(json?.data?.payment) ? json.data.payment : [];
        const url = pays.find(p => p?.payment_gateway_url)?.payment_gateway_url;
        if (isSafePayUrl(url)) {
          paymentUrl = url;
          window.location.href = url;
          return;
        }
      }
      throw new Error('repay failed');
    } catch {
      if (btn) btn.disabled = false;
      if (isSafePayUrl(paymentUrl)) window.location.href = paymentUrl;
    }
  });

})();
</script>
@endsection
