{{-- resources/views/checkout.blade.php --}}
@extends('layouts.app')

@section('content')

<div data-checkout-page data-google-maps-key="{{ config('services.google.maps_key') }}" class="ckp">
<style nonce="{{ Vite::cspNonce() }}">
/* ============================================================
   KP WEAR Checkout — redesigned
   ============================================================ */
.ckp {
  --ink: #0c110e;
  --text: #1a231e;
  --muted: #5a6660;
  --border: #e2e8e4;
  --border-strong: #c9d4ce;
  --bg: #f5f8f6;
  --card: #ffffff;
  --green: #1a7a52;
  --green-dark: #145f40;
  --green-faint: #eef8f3;
  --green-faint2: #f5fbf8;
  --amber-bg: #fff7e5;
  --amber: #a85b00;
  --radius-card: 18px;
  --radius-input: 12px;
  --radius-btn: 12px;
  --shadow-card: 0 1px 3px rgba(10,16,12,.06);
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
  background: var(--bg);
  color: var(--ink);
  min-height: 100vh;
}
.ckp * { box-sizing: border-box; }
.ckp button, .ckp input, .ckp select, .ckp textarea { font: inherit; }
.ckp button, .ckp a {
  transition: background .18s, border-color .18s, color .18s, opacity .18s, transform .15s;
}
.ckp button:focus-visible,
.ckp a:focus-visible,
.ckp input:focus-visible,
.ckp select:focus-visible,
.ckp textarea:focus-visible {
  outline: 3px solid rgba(26,122,82,.22);
  outline-offset: 2px;
}

/* ── Header ─────────────────────────────────────────────── */
.ckp-header {
  position: sticky;
  top: 0;
  z-index: 40;
  background: rgba(255,255,255,.82);
  backdrop-filter: blur(10px);
  border-bottom: 1px solid var(--border);
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 32px;
  height: 60px;
}
.ckp-brand {
  display: flex;
  align-items: center;
  gap: 12px;
}
.ckp-back {
  width: 36px; height: 36px;
  border: 1px solid var(--border);
  border-radius: 10px;
  background: transparent;
  display: grid; place-items: center;
  color: var(--ink);
  text-decoration: none;
}
.ckp-back:hover { background: var(--green-faint); border-color: var(--border-strong); }
.ckp-brand-name {
  font-size: 17px;
  font-weight: 900;
  letter-spacing: -.03em;
  color: var(--ink);
  text-decoration: none;
}
.ckp-progress {
  display: flex;
  align-items: center;
  gap: 10px;
  font-size: 13px;
}
.ckp-step {
  display: flex;
  align-items: center;
  gap: 8px;
  color: var(--muted);
  white-space: nowrap;
}
.ckp-step-dot {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: #edf2ef;
  color: #7a8a82;
  display: grid; place-items: center;
  font-size: 12px;
  font-weight: 800;
  flex: none;
}
.ckp-step.active { color: var(--ink); }
.ckp-step.active .ckp-step-dot { background: var(--ink); color: #fff; }
.ckp-step.active span:last-child { font-weight: 700; }
.ckp-step.done .ckp-step-dot { background: var(--green); color: #fff; }
.ckp-step-sep { color: #b0bcb5; }
.ckp-secure {
  display: flex;
  align-items: center;
  gap: 7px;
  font-size: 13px;
  font-weight: 600;
  color: var(--muted);
}

/* ── Page wrapper & heading ──────────────────────────────── */
.ckp-inner {
  width: min(1280px, calc(100% - 64px));
  margin: 0 auto;
  padding: 36px 0 64px;
}
.ckp-eyebrow {
  font-size: 11px;
  font-weight: 900;
  letter-spacing: .22em;
  text-transform: uppercase;
  color: var(--green);
  margin: 0 0 10px;
}
.ckp-h1 {
  margin: 0;
  font-size: 48px;
  font-weight: 900;
  letter-spacing: -.055em;
  line-height: 1.04;
  color: var(--ink);
}
.ckp-subtitle {
  margin: 10px 0 0;
  font-size: 15px;
  color: var(--muted);
  max-width: 420px;
}

/* ── Two-column layout ───────────────────────────────────── */
.ckp-layout {
  display: grid;
  grid-template-columns: minmax(0,1fr) 420px;
  gap: 24px;
  align-items: start;
  margin-top: 28px;
}
.ckp-left { display: flex; flex-direction: column; gap: 16px; }

/* ── Card shell ──────────────────────────────────────────── */
.ckp-card {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius-card);
  padding: 22px;
  box-shadow: var(--shadow-card);
}

/* ── Icon circle ─────────────────────────────────────────── */
.ckp-icon {
  width: 40px; height: 40px;
  border-radius: 11px;
  background: var(--green-faint);
  color: var(--green);
  display: grid; place-items: center;
  flex: none;
}

/* ── Contact card ────────────────────────────────────────── */
.ckp-contact-row {
  display: flex;
  align-items: center;
  gap: 14px;
}
.ckp-contact-body { flex: 1; min-width: 0; }
.ckp-card-title {
  margin: 0;
  font-size: 15px;
  font-weight: 800;
  letter-spacing: -.015em;
  color: var(--ink);
}
.ckp-contact-name {
  display: block;
  margin-top: 3px;
  font-size: 14px;
  font-weight: 600;
  color: var(--text);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ckp-contact-meta {
  display: block;
  margin-top: 2px;
  font-size: 13px;
  color: var(--muted);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.ckp-contact-actions { display: flex; flex-direction: column; align-items: center; gap: 4px; flex: none; }
.ckp-check {
  width: 28px; height: 28px;
  border-radius: 50%;
  background: var(--green);
  color: #fff;
  display: grid; place-items: center;
}
.ckp-edit-btn {
  background: none; border: 0; padding: 0;
  font-size: 12px;
  font-weight: 800;
  color: var(--green);
  cursor: pointer;
}
.ckp-edit-btn:hover { color: var(--green-dark); }

/* ── Address card ────────────────────────────────────────── */
.ckp-addr-row {
  display: flex;
  align-items: flex-start;
  gap: 14px;
}
.ckp-addr-body { flex: 1; min-width: 0; }
.ckp-addr-title-row {
  display: flex;
  align-items: center;
  gap: 8px;
  flex-wrap: wrap;
}
.ckp-required {
  font-size: 10px;
  font-weight: 800;
  background: var(--amber-bg);
  color: var(--amber);
  border-radius: 999px;
  padding: 2px 8px;
}
.ckp-addr-text {
  margin: 5px 0 0;
  font-size: 14px;
  color: var(--muted);
  line-height: 1.5;
}
.ckp-addr-cta { display: flex; flex-direction: column; align-items: center; gap: 3px; }
.ckp-addr-actions {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 10px;
  margin-top: 16px;
}
.ckp-outline-btn {
  height: 44px;
  border: 1px solid var(--border-strong);
  border-radius: var(--radius-btn);
  background: var(--card);
  color: var(--ink);
  font-size: 13px;
  font-weight: 700;
  display: flex; align-items: center; justify-content: center; gap: 8px;
  cursor: pointer;
}
.ckp-outline-btn:hover { background: var(--green-faint2); border-color: #b0c4b8; }

/* ── Payment card ────────────────────────────────────────── */
.ckp-pay-header {
  display: flex; align-items: center; gap: 14px;
  margin-bottom: 18px;
}
.ckp-card-sub { margin: 2px 0 0; font-size: 13px; color: var(--muted); }
.ckp-pay-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.ckp-pay-option {
  position: relative;
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 16px;
  display: flex; flex-direction: column; gap: 12px;
  cursor: pointer;
  background: var(--card);
  transition: border-color .18s, background .18s, box-shadow .18s;
}
.ckp-pay-option:hover { border-color: #b0c4b8; }
.ckp-pay-option.active {
  border: 1.5px solid var(--green);
  background: var(--green-faint2);
  box-shadow: 0 2px 12px rgba(26,122,82,.1);
}
.ckp-pay-icon {
  width: 40px; height: 40px;
  border-radius: 11px;
  background: var(--green-faint);
  color: var(--green);
  display: grid; place-items: center;
  flex: none;
  transition: background .18s, color .18s;
}
.ckp-pay-option.active .ckp-pay-icon { background: var(--green); color: #fff; }
.ckp-pay-check {
  position: absolute; top: 12px; right: 12px;
  width: 20px; height: 20px;
  border-radius: 50%;
  background: var(--green);
  color: #fff;
  display: none; align-items: center; justify-content: center;
}
.ckp-pay-option.active .ckp-pay-check { display: flex; }
.ckp-pay-name { font-size: 14px; font-weight: 800; color: var(--ink); display: block; }
.ckp-pay-sub { font-size: 12px; color: var(--muted); display: block; margin-top: 3px; }

/* ── Mobile money panel ──────────────────────────────────── */
.ckp-mm-panel {
  margin-top: 14px;
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 16px;
  background: var(--green-faint2);
}
.ckp-provider-label,
.ckp-phone-label {
  display: block;
  font-size: 12px;
  font-weight: 800;
  color: #2a3d32;
  margin-bottom: 10px;
}
.ckp-provider-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 9px;
}
.ckp-provider-card {
  position: relative;
  display: flex; align-items: center; gap: 10px;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 11px 12px;
  cursor: pointer;
  background: var(--card);
}
.ckp-provider-card:hover { border-color: #b0c4b8; }
.ckp-provider-card.active {
  border: 1.5px solid var(--green);
  background: var(--green-faint2);
}
.ckp-provider-radio {
  width: 17px; height: 17px;
  border-radius: 50%;
  border: 1.5px solid #9aada5;
  display: grid; place-items: center;
  flex: none;
  transition: border-color .15s;
}
.ckp-provider-card.active .ckp-provider-radio { border-color: var(--green); }
.ckp-provider-card.active .ckp-provider-radio::after {
  content: '';
  width: 8px; height: 8px;
  border-radius: 50%;
  background: var(--green);
}
.ckp-logo {
  width: 36px; height: 24px;
  border-radius: 6px;
  display: grid; place-items: center;
  font-size: 8px;
  font-weight: 900;
  letter-spacing: .01em;
  flex: none;
}
.ckp-logo.mpesa   { background: #e72e34; color: #fff; }
.ckp-logo.tigo    { background: #ffd500; color: #111; }
.ckp-logo.halo    { background: #f5821f; color: #fff; }
.ckp-logo.airtel  { background: #e30613; color: #fff; }
.ckp-provider-name { font-size: 13px; font-weight: 700; color: var(--ink); }
.ckp-phone-wrap { margin-top: 16px; }
.ckp-phone {
  display: flex;
  border: 1px solid var(--border);
  border-radius: var(--radius-input);
  overflow: hidden;
  background: var(--card);
  transition: border-color .18s;
}
.ckp-phone:focus-within { border-color: var(--green); }
.ckp-phone-prefix {
  display: flex; align-items: center;
  padding: 0 13px;
  background: #f2f6f3;
  border-right: 1px solid var(--border);
  font-size: 13px;
  font-weight: 800;
  color: #2a3d32;
  flex: none;
}
.ckp-phone input {
  flex: 1; min-width: 0;
  height: 44px;
  border: 0; padding: 0 13px;
  font-size: 14px;
  color: var(--text);
  background: transparent;
}
.ckp-phone input:focus { outline: none; }

/* ── Card panel ──────────────────────────────────────────── */
.ckp-card-panel {
  margin-top: 14px;
  border: 1px solid var(--border);
  border-radius: 14px;
  padding: 16px;
  background: var(--green-faint2);
  display: flex; flex-direction: column; gap: 12px;
}
.ckp-card-row { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
.ckp-field span {
  display: block;
  font-size: 11px;
  font-weight: 800;
  color: #2a3d32;
  margin-bottom: 6px;
}
.ckp-field input {
  width: 100%;
  height: 44px;
  border: 1px solid var(--border);
  border-radius: var(--radius-input);
  padding: 0 12px;
  font-size: 14px;
  color: var(--text);
  background: var(--card);
  transition: border-color .18s;
}
.ckp-field input:focus { outline: none; border-color: var(--green); }
.ckp-field.full { grid-column: 1 / -1; }

/* ── Info bar ────────────────────────────────────────────── */
.ckp-info {
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  background: var(--card);
  border: 1px solid var(--border);
  color: #13684e;
  display: flex; gap: 8px; align-items: flex-start;
  font-size: 12px;
  line-height: 1.5;
}

/* ── Error bar ───────────────────────────────────────────── */
.ckp-error {
  display: none;
  margin-top: 12px;
  padding: 10px 12px;
  border-radius: 10px;
  background: #fff1f2;
  border: 1px solid #fecdd3;
  color: #be123c;
  font-size: 12px;
}

/* ── Place order button ──────────────────────────────────── */
.ckp-place {
  width: 100%;
  height: 54px;
  margin-top: 14px;
  border: 0;
  border-radius: var(--radius-btn);
  background: var(--green-dark);
  color: #fff;
  font-size: 16px;
  font-weight: 800;
  display: flex; align-items: center; justify-content: center; gap: 10px;
  cursor: pointer;
}
.ckp-place:hover:not(:disabled) { background: #0f4e33; transform: translateY(-1px); }
.ckp-place:disabled { background: #a5ada9; cursor: not-allowed; }
.ckp-secure-note {
  margin: 10px 0 0;
  display: flex; justify-content: center; align-items: center; gap: 6px;
  font-size: 11px;
  color: var(--muted);
}

/* ── Order summary ───────────────────────────────────────── */
.ckp-summary {
  position: sticky;
  top: 76px;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: var(--radius-card);
  padding: 22px;
  box-shadow: var(--shadow-card);
}
.ckp-summary-head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 14px;
}
.ckp-summary-head h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 900;
  letter-spacing: -.03em;
  color: var(--ink);
}
.ckp-edit-cart {
  font-size: 13px;
  font-weight: 800;
  color: var(--green);
  text-decoration: none;
}
.ckp-edit-cart:hover { color: var(--green-dark); }
.ckp-items { border-top: 1px solid var(--border); }
.ckp-item {
  display: grid;
  grid-template-columns: 72px minmax(0,1fr) auto;
  gap: 12px;
  padding: 12px 0;
  border-bottom: 1px solid #edf1ee;
}
.ckp-item:last-child { border-bottom: 0; }
.ckp-item-img {
  width: 72px; height: 72px;
  border-radius: 12px;
  background: #f5f7f5;
  border: 1px solid #e5eae6;
  overflow: hidden;
  display: grid; place-items: center;
}
.ckp-item-img img { width: 100%; height: 100%; object-fit: cover; }
.ckp-item-img span { font-size: 11px; font-weight: 900; color: var(--muted); }
.ckp-item-info { min-width: 0; }
.ckp-item-name { font-size: 13px; font-weight: 800; line-height: 1.35; color: var(--ink); }
.ckp-item-meta { font-size: 12px; color: var(--muted); margin-top: 4px; }
.ckp-item-unit { font-size: 11px; color: #7a8a82; margin-top: 4px; }
.ckp-item-price { font-size: 13px; font-weight: 800; color: var(--ink); white-space: nowrap; }
.ckp-totals { border-top: 1px solid var(--border); padding-top: 14px; margin-top: 4px; }
.ckp-total-row {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 13px;
  margin: 7px 0;
}
.ckp-total-row span { color: var(--muted); }
.ckp-grand {
  display: flex; align-items: center; justify-content: space-between;
  font-size: 19px;
  font-weight: 900;
  border-top: 1px solid var(--border);
  padding-top: 14px;
  margin-top: 12px;
  color: var(--ink);
}
.ckp-grand strong:last-child { color: var(--green-dark); }
.ckp-secure-box {
  margin-top: 18px;
  padding: 14px;
  border-radius: 12px;
  background: var(--green-faint);
  display: flex; gap: 10px;
  color: #13684e;
}
.ckp-secure-box strong { font-size: 13px; display: block; }
.ckp-secure-box p { margin: 4px 0 0; font-size: 11.5px; line-height: 1.5; }
.ckp-features {
  display: grid;
  grid-template-columns: repeat(3,1fr);
  text-align: center;
  border-top: 1px solid var(--border);
  margin-top: 16px;
  padding-top: 16px;
  gap: 6px;
}
.ckp-feature-icon { display: flex; justify-content: center; color: var(--ink); }
.ckp-feature-label { font-size: 11px; color: var(--muted); margin-top: 6px; }

/* ── Modals ──────────────────────────────────────────────── */
.ckp-overlay {
  position: fixed; inset: 0;
  z-index: 50;
  display: none; align-items: center; justify-content: center;
  background: rgba(10,16,12,.42);
  padding: 16px;
  backdrop-filter: blur(4px);
}
.ckp-overlay.open { display: flex; }
.ckp-modal {
  width: min(600px,100%);
  max-height: 90vh;
  overflow: auto;
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 20px;
  padding: 22px;
  box-shadow: 0 20px 60px rgba(10,16,12,.14);
}
.ckp-modal-sm { max-width: 480px; }
.ckp-modal-head {
  display: flex; align-items: center; justify-content: space-between;
  margin-bottom: 16px;
}
.ckp-modal-head h2 {
  margin: 0;
  font-size: 20px;
  font-weight: 900;
  letter-spacing: -.03em;
  color: var(--ink);
}
.ckp-modal-close {
  width: 36px; height: 36px;
  border: 1px solid var(--border);
  border-radius: 50%;
  background: var(--card);
  cursor: pointer;
  display: grid; place-items: center;
}
.ckp-modal-close:hover { background: var(--green-faint); }
.ckp-saved-list { display: flex; flex-direction: column; gap: 8px; }
.ckp-saved-addr {
  width: 100%; text-align: left;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px 14px;
  background: var(--card);
  cursor: pointer;
  display: flex; flex-direction: column; gap: 2px;
}
.ckp-saved-addr:hover { border-color: #b0c4b8; }
.ckp-saved-addr.active { border: 1.5px solid var(--green); background: var(--green-faint2); }
.ckp-saved-addr-name { font-size: 13px; font-weight: 600; color: var(--text); }
.ckp-saved-addr-meta { font-size: 12px; color: var(--muted); }
.ckp-tabs {
  display: flex; gap: 8px;
  margin: 16px 0;
}
.ckp-tab {
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 8px 14px;
  background: var(--card);
  color: var(--text);
  font-size: 12px;
  font-weight: 800;
  cursor: pointer;
}
.ckp-tab.active { background: var(--green); color: #fff; border-color: var(--green); }
.ckp-map-area {
  height: 200px;
  border-radius: 12px;
  border: 1px solid var(--border);
  background: var(--green-faint);
  overflow: hidden;
}
.ckp-map-search {
  width: 100%;
  height: 42px;
  border: 1px solid var(--border);
  border-radius: 10px;
  padding: 0 12px;
  font-size: 13px;
  margin-bottom: 10px;
}
.ckp-map-search:focus { outline: none; border-color: var(--green); }
.ckp-map-selected { font-size: 12.5px; color: var(--muted); margin-top: 8px; min-height: 16px; }
.ckp-map-actions {
  display: grid; grid-template-columns: 1fr 1fr; gap: 10px;
  margin-top: 10px;
}
.ckp-manual-grid {
  display: none;
  grid-template-columns: 1fr 1fr;
  gap: 12px;
}
.ckp-modal-submit {
  width: 100%; height: 44px;
  border: 0; border-radius: 10px;
  background: var(--green); color: #fff;
  font-size: 13px; font-weight: 800;
  cursor: pointer;
  display: flex; align-items: center; justify-content: center; gap: 8px;
}
.ckp-modal-submit:hover:not(:disabled) { background: var(--green-dark); }
.ckp-modal-submit:disabled { background: #a5ada9; cursor: not-allowed; }
.ckp-note-textarea {
  width: 100%; min-height: 120px;
  border: 1px solid var(--border);
  border-radius: 12px;
  padding: 12px;
  font-size: 14px;
  resize: vertical;
  color: var(--text);
}
.ckp-note-textarea:focus { outline: none; border-color: var(--green); }
.ckp-note-count { font-size: 11px; text-align: right; color: var(--muted); margin: 4px 0 10px; }
.ckp-map-error { color: #b91c1c; font-size: 12px; margin-top: 8px; }

/* ── Responsive ──────────────────────────────────────────── */
@media (max-width: 1100px) {
  .ckp-inner { width: min(100% - 40px, 760px); }
  .ckp-layout { grid-template-columns: 1fr; }
  .ckp-summary { position: static; }
}
@media (max-width: 680px) {
  .ckp-inner { width: calc(100% - 24px); padding-top: 16px; }
  .ckp-header { padding: 0 16px; }
  .ckp-progress { display: none; }
  .ckp-h1 { font-size: 34px; }
  .ckp-pay-grid,
  .ckp-provider-grid,
  .ckp-addr-actions,
  .ckp-map-actions { grid-template-columns: 1fr; }
  .ckp-item { grid-template-columns: 64px minmax(0,1fr); }
  .ckp-item-img { width: 64px; height: 64px; }
  .ckp-item-price { grid-column: 2; }
  .ckp-manual-grid { grid-template-columns: 1fr; }
  .ckp-field.full { grid-column: auto; }
}
@media (prefers-reduced-motion: reduce) {
  .ckp *, .ckp *::before, .ckp *::after {
    transition: none !important;
    animation: none !important;
  }
}
</style>


{{-- ══════════ HEADER ══════════ --}}
<header class="ckp-header">
  <div class="ckp-brand">
    <a href="{{ route('cart') }}" class="ckp-back" aria-label="Back to cart">
      <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="m15 18-6-6 6-6"/>
      </svg>
    </a>
    <a href="{{ route('home') }}" class="ckp-brand-name">KP WEAR</a>
  </div>

  <nav class="ckp-progress" aria-label="Checkout progress">
    <div class="ckp-step active" data-step="1">
      <span class="ckp-step-dot">1</span><span>Checkout</span>
    </div>
    <svg class="ckp-step-sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="m9 18 6-6-6-6"/>
    </svg>
    <div class="ckp-step" data-step="2">
      <span class="ckp-step-dot">2</span><span>Payment</span>
    </div>
    <svg class="ckp-step-sep" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24"
         fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <path d="m9 18 6-6-6-6"/>
    </svg>
    <div class="ckp-step" data-step="3">
      <span class="ckp-step-dot">3</span><span>Complete</span>
    </div>
  </nav>

  <div class="ckp-secure">
    <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
         stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
      <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
      <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
    </svg>
    <span>Secure checkout</span>
  </div>
</header>


{{-- ══════════ PAGE BODY ══════════ --}}
<div class="ckp-inner">

  <p class="ckp-eyebrow">Checkout</p>
  <h1 class="ckp-h1">Complete your order</h1>
  <p class="ckp-subtitle">Review your details and choose a payment method.</p>

  <div class="ckp-layout">

    {{-- ── LEFT COLUMN ── --}}
    <div class="ckp-left">

      {{-- Contact card --}}
      <section class="ckp-card">
        <div class="ckp-contact-row">
          <span class="ckp-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/>
            </svg>
          </span>
          <div class="ckp-contact-body">
            <h2 class="ckp-card-title">Contact information</h2>
            <span data-contact-name class="ckp-contact-name">{{ $user->name ?? 'Loading…' }}</span>
            <span data-contact-meta class="ckp-contact-meta">
              {{ implode('  ·  ', array_filter([$user->phone ?? '', $user->email ?? ''])) }}
            </span>
          </div>
          <div class="ckp-contact-actions">
            <span class="ckp-check">
              <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
            <button type="button" data-edit-contact class="ckp-edit-btn">Edit</button>
          </div>
        </div>
      </section>

      {{-- Address card --}}
      <section class="ckp-card">
        <div class="ckp-addr-row">
          <span class="ckp-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/>
            </svg>
          </span>
          <div class="ckp-addr-body">
            <div class="ckp-addr-title-row">
              <h2 class="ckp-card-title">Delivery address</h2>
              <span data-addr-badge class="ckp-required"
                    style="{{ isset($selectedAddress) && $selectedAddress ? 'display:none' : '' }}">Optional</span>
            </div>
            <p data-addr-summary class="ckp-addr-text">
              @if(isset($selectedAddress) && $selectedAddress)
                {{ $selectedAddress->street }}  —  {{ collect([$selectedAddress->ward, $selectedAddress->district, $selectedAddress->region])->filter()->implode(', ') }}
              @else
                No delivery address — continue without one or add one below.
              @endif
            </p>
          </div>
          <div class="ckp-addr-cta">
            <button type="button" data-open-addr-modal class="ckp-edit-btn">Edit</button>
          </div>
        </div>

        <div class="ckp-addr-actions">
          <button type="button" data-use-location class="ckp-outline-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
              <path d="M12 5a7 7 0 1 0 0 14A7 7 0 0 0 12 5z"/>
            </svg>
            Use my location
          </button>
          <button type="button" data-open-note-modal class="ckp-outline-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M14 2H6a2 2 0 0 0-2 2v16l4-4h10a2 2 0 0 0 2-2V8z"/>
            </svg>
            Add note
          </button>
        </div>
      </section>

      {{-- Payment card --}}
      <section class="ckp-card">
        <div class="ckp-pay-header">
          <span class="ckp-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="14" x="2" y="5" rx="2"/>
              <line x1="2" x2="22" y1="10" y2="10"/>
            </svg>
          </span>
          <div>
            <h2 class="ckp-card-title">Payment method</h2>
            <p class="ckp-card-sub">Choose how you want to pay.</p>
          </div>
        </div>

        <div class="ckp-pay-grid">
          {{-- Mobile Money --}}
          <label data-pay-option data-method="mobile_money" class="ckp-pay-option active">
            <input type="radio" name="payment_method" value="mobile_money" checked hidden>
            <span class="ckp-pay-check">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
            <span class="ckp-pay-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>
              </svg>
            </span>
            <span>
              <span class="ckp-pay-name">Mobile Money</span>
              <span class="ckp-pay-sub">M-Pesa or Tigopesa</span>
            </span>
          </label>

          {{-- Card --}}
          <label data-pay-option data-method="card" class="ckp-pay-option">
            <input type="radio" name="payment_method" value="card" hidden>
            <span class="ckp-pay-check">
              <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="20 6 9 17 4 12"/>
              </svg>
            </span>
            <span class="ckp-pay-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/>
                <line x1="2" x2="22" y1="10" y2="10"/>
              </svg>
            </span>
            <span>
              <span class="ckp-pay-name">Card</span>
              <span class="ckp-pay-sub">Debit or credit card</span>
            </span>
          </label>
        </div>

        {{-- Mobile money panel --}}
        <div data-method-panel="mobile_money" class="ckp-mm-panel">
          <span class="ckp-provider-label">Select provider</span>
          <div class="ckp-provider-grid">
            @foreach([
              ['mpesa',      'mpesa',  'M-PESA', 'M-Pesa',      true ],
              ['tigopesa',   'tigo',   'TIGO',   'Tigopesa',    false],
              ['halopesa',   'halo',   'HALO',   'HaloPesa',    false],
              ['airtelmoney','airtel', 'AIRTEL', 'Airtel Money',false],
            ] as [$val, $cls, $short, $name, $checked])
            <label data-provider-card class="ckp-provider-card {{ $checked ? 'active' : '' }}">
              <input type="radio" name="payment_provider" value="{{ $val }}" {{ $checked ? 'checked' : '' }} hidden>
              <span class="ckp-provider-radio"></span>
              <span class="ckp-logo {{ $cls }}">{{ $short }}</span>
              <span class="ckp-provider-name">{{ $name }}</span>
            </label>
            @endforeach
          </div>

          <div class="ckp-phone-wrap">
            <span class="ckp-phone-label">Mobile number</span>
            <div class="ckp-phone">
              <span class="ckp-phone-prefix">+255</span>
              <input type="tel" name="payment_phone" placeholder="624 643 714"
                     inputmode="numeric" autocomplete="tel" aria-label="Mobile money number">
            </div>
          </div>

          <div class="ckp-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="flex:none;margin-top:1px">
              <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/>
              <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span>After placing your order, you'll be redirected to complete your payment securely.</span>
          </div>
        </div>

        {{-- Card panel --}}
        <div data-method-panel="card" class="ckp-card-panel" style="display:none">
          <label class="ckp-field full">
            <span>Card number</span>
            <input name="card_number" inputmode="numeric" autocomplete="cc-number"
                   placeholder="1234 1234 1234 1234">
          </label>
          <div class="ckp-card-row">
            <label class="ckp-field">
              <span>Expiry</span>
              <input name="card_expiry" inputmode="numeric" autocomplete="cc-exp" placeholder="MM/YY">
            </label>
            <label class="ckp-field">
              <span>CVV</span>
              <input name="card_cvv" inputmode="numeric" autocomplete="cc-csc" placeholder="123">
            </label>
          </div>
          <label class="ckp-field full">
            <span>Name on card</span>
            <input name="card_name" autocomplete="cc-name" placeholder="As it appears on the card">
          </label>
          <div class="ckp-info">
            <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                 style="flex:none;margin-top:1px">
              <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/>
              <line x1="12" x2="12.01" y1="16" y2="16"/>
            </svg>
            <span>Your card is charged securely once you place the order.</span>
          </div>
        </div>

        <div data-checkout-error class="ckp-error" role="alert"></div>

        <button type="button" data-place-order class="ckp-place" disabled>
          <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
          </svg>
          <span data-place-label>Calculating total…</span>
        </button>

        <p class="ckp-secure-note">
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
            <polyline points="9 12 11 14 15 10"/>
          </svg>
          Your payment and details are encrypted and secure
        </p>
      </section>

    </div>{{-- /left --}}


    {{-- ── RIGHT: ORDER SUMMARY ── --}}
    <aside class="ckp-summary">
      <div class="ckp-summary-head">
        <h2>Order summary</h2>
        <a href="{{ route('cart') }}" class="ckp-edit-cart">Edit cart</a>
      </div>

      <div data-ckp-summary>
        @if(isset($cartItems) && $cartItems->count())
          <div class="ckp-items">
            @foreach($cartItems as $item)
            <div class="ckp-item">
              <div class="ckp-item-img">
                @if($item->image_url ?? $item->product?->image ?? null)
                  <img src="{{ $item->image_url ?? $item->product->image }}"
                       alt="{{ $item->name ?? $item->product?->name }}"
                       onerror="this.parentElement.innerHTML='<span>KP</span>'">
                @else
                  <span>KP</span>
                @endif
              </div>
              <div class="ckp-item-info">
                <div class="ckp-item-name">{{ $item->name ?? $item->product?->name }}</div>
                <div class="ckp-item-meta">
                  {{ collect([$item->size ? 'Size: '.$item->size : null, 'Qty: '.$item->quantity])->filter()->implode('  ·  ') }}
                </div>
                <div class="ckp-item-unit">TZS {{ number_format($item->unit_price ?? $item->price) }} × {{ $item->quantity }}</div>
              </div>
              <div class="ckp-item-price">TZS {{ number_format($item->line_total ?? ($item->unit_price * $item->quantity)) }}</div>
            </div>
            @endforeach
          </div>

          <div class="ckp-totals">
            <div class="ckp-total-row">
              <span>Subtotal</span>
              <strong>TZS {{ number_format($subtotal ?? 0) }}</strong>
            </div>
            <div class="ckp-total-row">
              <span>Delivery fee</span>
              <strong>TZS {{ number_format($deliveryFee ?? 0) }}</strong>
            </div>
            <div class="ckp-grand">
              <strong>Total</strong>
              <strong>TZS {{ number_format($total ?? 0) }}</strong>
            </div>
          </div>
        @else
          <div data-ckp-summary-loading style="padding:24px 0;font-size:13px;color:var(--muted)">
            Loading order summary…
          </div>
        @endif
      </div>

      <div class="ckp-secure-box">
        <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
        </svg>
        <div>
          <strong>Secure payment</strong>
          <p>Your payment is processed securely by Selcom.</p>
        </div>
      </div>

      <div class="ckp-features">
        <div>
          <div class="ckp-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 17H3a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11a2 2 0 0 1 2 2v3"/>
              <rect x="9" y="11" width="14" height="10" rx="1"/>
              <circle cx="12" cy="16" r="1"/>
            </svg>
          </div>
          <div class="ckp-feature-label">Fast delivery</div>
        </div>
        <div>
          <div class="ckp-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
            </svg>
          </div>
          <div class="ckp-feature-label">Secure payment</div>
        </div>
        <div>
          <div class="ckp-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
              <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/>
              <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
            </svg>
          </div>
          <div class="ckp-feature-label">Support 24/7</div>
        </div>
      </div>
    </aside>

  </div>{{-- /layout --}}
</div>{{-- /inner --}}


{{-- ══════════ ADDRESS MODAL ══════════ --}}
<div data-addr-modal class="ckp-overlay" role="dialog" aria-modal="true"
     aria-labelledby="ckp-addr-modal-title" aria-hidden="true">
  <div class="ckp-modal">
    <div class="ckp-modal-head">
      <h2 id="ckp-addr-modal-title">Choose your address</h2>
      <button type="button" data-close-addr class="ckp-modal-close" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>

    {{-- Saved addresses --}}
    <div data-saved-list class="ckp-saved-list">
      @forelse($addresses ?? [] as $addr)
        <button type="button" class="ckp-saved-addr{{ isset($selectedAddress) && $selectedAddress->id == $addr->id ? ' active' : '' }}"
                data-select-address="{{ $addr->id }}">
          <span class="ckp-saved-addr-name">{{ $addr->street }}</span>
          <span class="ckp-saved-addr-meta">{{ collect([$addr->ward, $addr->district, $addr->region])->filter()->implode(', ') }}</span>
        </button>
      @empty
        <p style="font-size:13px;color:var(--muted)">No saved addresses — use your location or enter one manually.</p>
      @endforelse
    </div>

    {{-- Tabs --}}
    <div class="ckp-tabs">
      <button type="button" data-tab="location" class="ckp-tab active">Use my location</button>
      <button type="button" data-tab="manual"   class="ckp-tab">Enter manually</button>
    </div>

    {{-- Location panel --}}
    <div data-panel="location">
      <input data-map-search class="ckp-map-search" type="text"
             placeholder="Search your area or street" autocomplete="off">
      <div data-google-map class="ckp-map-area"></div>
      <p data-map-selected class="ckp-map-selected"></p>
      <div class="ckp-map-actions">
        <button type="button" data-request-location class="ckp-outline-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <circle cx="12" cy="12" r="3"/><path d="M12 2v2m0 16v2M2 12h2m16 0h2"/>
          </svg>
          Current location
        </button>
        <button type="button" data-map-confirm class="ckp-modal-submit" disabled>
          Use this location
        </button>
      </div>
      <p data-map-error class="ckp-map-error"></p>
    </div>

    {{-- Manual panel --}}
    <form data-address-form data-panel="manual" class="ckp-manual-grid" style="display:none">
      @csrf
      <input type="hidden" name="type" value="shipping">
      <label class="ckp-field full"><span>Recipient name</span><input name="recipient_name" required></label>
      <label class="ckp-field"><span>Phone</span><input name="phone" required></label>
      <label class="ckp-field"><span>Region</span><input name="region" required></label>
      <label class="ckp-field"><span>District</span><input name="district" required></label>
      <label class="ckp-field"><span>Ward</span><input name="ward"></label>
      <label class="ckp-field full"><span>Street / landmark</span><input name="street" required></label>
      <div data-address-form-error class="ckp-error" role="alert"></div>
      <button type="submit" class="ckp-field full ckp-modal-submit">Save address</button>
    </form>
  </div>
</div>


{{-- ══════════ NOTE MODAL ══════════ --}}
<div data-note-modal class="ckp-overlay" role="dialog" aria-modal="true"
     aria-labelledby="ckp-note-modal-title" aria-hidden="true">
  <div class="ckp-modal ckp-modal-sm">
    <div class="ckp-modal-head">
      <h2 id="ckp-note-modal-title">Add order note</h2>
      <button type="button" data-close-note class="ckp-modal-close" aria-label="Close">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
          <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
        </svg>
      </button>
    </div>
    <form data-note-form>
      <textarea data-note-textarea maxlength="250" class="ckp-note-textarea"
                placeholder="e.g. Call when you arrive, gate code, landmark, etc."></textarea>
      <p class="ckp-note-count"><span data-note-count>0</span>/250</p>
      <button type="submit" class="ckp-modal-submit">Save note</button>
    </form>
  </div>
</div>


{{-- ══════════ SCRIPT ══════════ --}}
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  /* ── helpers ─────────────────────────────────────────── */
  const $ = (s, r = document) => r.querySelector(s);
  const $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = v => String(v ?? '').replace(/[&<>"']/g, c =>
    ({ '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;' }[c]));

  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };

  const api = async (path, opts = {}) => {
    const headers = new Headers(opts.headers || {});
    headers.set('Accept', 'application/json');
    const method = (opts.method || 'GET').toUpperCase();
    if (method !== 'GET' && method !== 'HEAD') {
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
      if (opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)) {
        headers.set('Content-Type', 'application/json');
      }
    }
    const res = await fetch('/api/v1' + path, {
      ...opts,
      credentials: 'include',
      headers,
      body: opts.body && typeof opts.body === 'object' && !(opts.body instanceof FormData)
        ? JSON.stringify(opts.body) : opts.body,
    });
    const raw = await res.text();
    let data = null;
    try { data = raw ? JSON.parse(raw) : null; } catch {}
    if (!res.ok) throw new Error(data?.message || Object.values(data?.errors || {})?.flat()?.[0] || `Error ${res.status}`);
    return data;
  };

  const toast = msg => window.dispatchEvent(new CustomEvent('kp:toast', { detail: msg }));

  /* ── modals ──────────────────────────────────────────── */
  const openModal  = m => { m.classList.add('open');    m.setAttribute('aria-hidden','false'); };
  const closeModal = m => { m.classList.remove('open'); m.setAttribute('aria-hidden','true');  };

  const addrModal = $('[data-addr-modal]');
  const noteModal = $('[data-note-modal]');

  $$('[data-open-addr-modal], [data-use-location]').forEach(b => b.addEventListener('click', () => openModal(addrModal)));
  $('[data-open-note-modal]')?.addEventListener('click', () => openModal(noteModal));
  $('[data-close-addr]')?.addEventListener('click', () => closeModal(addrModal));
  $('[data-close-note]')?.addEventListener('click', () => closeModal(noteModal));

  // close on backdrop click
  [addrModal, noteModal].forEach(m => m?.addEventListener('click', e => {
    if (e.target === m) closeModal(m);
  }));

  /* ── address modal tabs ──────────────────────────────── */
  const setTab = tab => {
    $$('[data-tab]').forEach(b => b.classList.toggle('active', b.dataset.tab === tab));
    $('[data-panel="location"]').style.display = tab === 'location' ? 'block' : 'none';
    const manual = $('[data-panel="manual"]');
    manual.style.display = tab === 'manual' ? 'grid' : 'none';
  };
  $$('[data-tab]').forEach(b => b.addEventListener('click', () => setTab(b.dataset.tab)));
  setTab('location');

  /* ── payment method toggle ───────────────────────────── */
  let selectedMethod = 'mobile_money';
  const syncPanels = () => {
    $$('[data-method-panel]').forEach(p => {
      p.style.display = p.dataset.methodPanel === selectedMethod ? 'block' : 'none';
    });
  };
  $$('[data-pay-option]').forEach(opt => {
    opt.addEventListener('click', () => {
      selectedMethod = opt.querySelector('input[name="payment_method"]')?.value || opt.dataset.method;
      $$('[data-pay-option]').forEach(o => o.classList.remove('active'));
      opt.classList.add('active');
      const radio = opt.querySelector('input[name="payment_method"]');
      if (radio) radio.checked = true;
      syncPanels();
      updateBtn();
    });
  });
  syncPanels();

  /* ── mobile provider toggle ──────────────────────────── */
  $$('[data-provider-card]').forEach(card => {
    card.addEventListener('click', () => {
      $$('[data-provider-card]').forEach(c => c.classList.remove('active'));
      card.classList.add('active');
      const r = card.querySelector('input[name="payment_provider"]');
      if (r) r.checked = true;
    });
  });

  /* ── address state ───────────────────────────────────── */
  let selectedAddressId = {{ isset($selectedAddress) && $selectedAddress?->id ? "'".$selectedAddress->id."'" : 'null' }};
  let addresses = @json($addresses ?? []);

  const updateAddressDisplay = () => {
    const a = addresses.find(x => String(x.id) === String(selectedAddressId));
    const summary = $('[data-addr-summary]');
    const badge   = $('[data-addr-badge]');
    if (summary) {
      summary.textContent = a
        ? [a.street, [a.ward, a.district, a.region].filter(Boolean).join(', ')].filter(Boolean).join('  —  ')
        : 'No delivery address — continue without one or add one below.';
    }
    if (badge) {
      if (a) { badge.style.display = 'none'; }
      else { badge.textContent = 'Optional'; badge.style.display = ''; }
    }
    updateBtn();
  };

  const renderSavedList = () => {
    const list = $('[data-saved-list]');
    if (!list) return;
    list.innerHTML = addresses.length
      ? addresses.map(a => `
          <button type="button" class="ckp-saved-addr${String(a.id) === String(selectedAddressId) ? ' active' : ''}"
                  data-select-address="${esc(a.id)}">
            <span class="ckp-saved-addr-name">${esc(a.street)}</span>
            <span class="ckp-saved-addr-meta">${esc([a.ward, a.district, a.region].filter(Boolean).join(', '))}</span>
          </button>`).join('')
      : '<p style="font-size:13px;color:var(--muted)">No saved addresses — use your location or enter one manually.</p>';
  };

  document.addEventListener('click', e => {
    const btn = e.target.closest('[data-select-address]');
    if (!btn) return;
    selectedAddressId = btn.dataset.selectAddress;
    updateAddressDisplay();
    renderSavedList();
    closeModal(addrModal);
  });

  /* ── total & place order button ──────────────────────── */
  let grandTotal = {{ $total ?? 0 }};
  const placeBtn   = $('[data-place-order]');
  const placeLabel = $('[data-place-label]');
  const errBox     = $('[data-checkout-error]');

  /* ── progress stepper: 1 Checkout → 2 Payment → 3 Complete ── */
  const setStep = n => {
    $$('.ckp-step[data-step]').forEach(el => {
      const i = Number(el.dataset.step);
      el.classList.toggle('done', i < n);
      el.classList.toggle('active', i === n);
    });
  };

  const updateBtn = () => {
    // Delivery address is optional: the button enables as soon as the bag
    // has a payable total — which also means details are done.
    const ready = grandTotal > 0;
    setStep(grandTotal > 0 ? 2 : 1);
    if (placeBtn) placeBtn.disabled = !ready;
    if (placeLabel) placeLabel.textContent = grandTotal > 0
      ? `Place Order — TZS ${grandTotal.toLocaleString()}`
      : 'Calculating total…';
  };
  updateBtn();

  /* ── summary renderer (preview payload → order summary) ── */
  const renderSummary = payload => {
    const box = $('[data-ckp-summary]');
    if (!box) return;
    const d = (payload && payload.data) || {};
    const items = Array.isArray(d.items) ? d.items : [];
    grandTotal = Number(d.total || 0);

    if (!items.length) {
      box.innerHTML = '<div style="padding:24px 0;font-size:13px;color:var(--muted)">Your bag is empty. <a href="/cart" style="color:var(--green);font-weight:700">Back to cart</a></div>';
      updateBtn();
      return;
    }

    const rows = items.map(item => {
      const name = item.name || 'Product';
      const qty = Number(item.quantity || 1);
      const unit = Number(item.unit_price || 0);
      const line = Number(item.line_total ?? unit * qty);
      const image = item.image_url || '';
      const meta = [item.size ? 'Size: ' + item.size : '', 'Qty: ' + qty].filter(Boolean).join('  ·  ');
      return '<div class="ckp-item">'
        + '<div class="ckp-item-img">' + (image ? '<img src="' + esc(image) + '" alt="' + esc(name) + '" onerror="this.parentElement.innerHTML=\'<span>KP</span>\'">' : '<span>KP</span>') + '</div>'
        + '<div class="ckp-item-info"><div class="ckp-item-name">' + esc(name) + '</div>'
        + '<div class="ckp-item-meta">' + esc(meta) + '</div>'
        + '<div class="ckp-item-unit">TZS ' + unit.toLocaleString() + ' × ' + qty + '</div></div>'
        + '<div class="ckp-item-price">TZS ' + line.toLocaleString() + '</div></div>';
    }).join('');

    const subtotal = Number(d.subtotal || 0);
    const delivery = Number(d.delivery_fee || 0);
    box.innerHTML = '<div class="ckp-items">' + rows + '</div>'
      + '<div class="ckp-totals"><div class="ckp-total-row"><span>Subtotal</span><strong>TZS ' + subtotal.toLocaleString() + '</strong></div>'
      + '<div class="ckp-total-row"><span>Delivery fee</span><strong>TZS ' + delivery.toLocaleString() + '</strong></div>'
      + '<div class="ckp-grand"><strong>Total</strong><strong>TZS ' + grandTotal.toLocaleString() + '</strong></div></div>';
    updateBtn();
  };

  /* ── boot: contact + addresses + preview ───────────────── */
  (async () => {
    try {
      const me = await api('/auth/me');
      const who = me?.data || {};
      const contactName = $('[data-contact-name]');
      const contactMeta = $('[data-contact-meta]');
      if (contactName) contactName.textContent = who.name || 'Signed-in customer';
      if (contactMeta) contactMeta.textContent = [who.phone, who.email].filter(Boolean).join('  ·  ');
    } catch (e) { /* contact card keeps its placeholder */ }

    try {
      const addr = await api('/addresses');
      addresses = Array.isArray(addr?.data) ? addr.data : [];
      const def = addresses.find(a => a.is_default) || addresses[0];
      selectedAddressId = def ? def.id : null;
      updateAddressDisplay();
      renderSavedList();
    } catch (e) { showError('Unable to load your addresses.'); }

    try {
      renderSummary(await api('/cart/checkout/preview'));
    } catch (e) {
      renderSummary(null);
      showError(e.message || 'Unable to load your order summary.');
    }
  })();

  const showError = msg => {
    if (!errBox) return;
    errBox.textContent = msg;
    errBox.style.display = 'flex';
  };
  const clearError = () => { if (errBox) errBox.style.display = 'none'; };

  /* ── place order ─────────────────────────────────────── */
  // The input shows a +255 prefix, so digits typed without a country code
  // belong to it. Mirror the server-side E.164 normalization so Selcom
  // always receives a valid MSISDN.
  const normalizePhone = raw => {
    const digits = String(raw || '').replace(/[\s().-]/g, '');
    if (!digits) return '';
    if (digits.startsWith('+')) return digits;
    if (digits.startsWith('00')) return '+' + digits.slice(2);
    if (digits.startsWith('0')) return '+255' + digits.slice(1);
    if (digits.startsWith('255')) return '+' + digits;
    return '+255' + digits;
  };
  placeBtn?.addEventListener('click', async () => {
    clearError();

    const phone      = normalizePhone($('input[name="payment_phone"]')?.value?.trim() || '');
    const provider   = $('input[name="payment_provider"]:checked')?.value || '';
    const cardNumber = $('input[name="card_number"]')?.value?.trim() || '';
    const cardExpiry = $('input[name="card_expiry"]')?.value?.trim() || '';
    const cardCvv    = $('input[name="card_cvv"]')?.value?.trim() || '';
    const cardName   = $('input[name="card_name"]')?.value?.trim() || '';

    if (selectedMethod === 'mobile_money' && !phone) {
      showError('Please enter your mobile number.'); return;
    }

    placeBtn.disabled = true;
    placeLabel.textContent = 'Placing order…';
    setStep(3);

    // The API requires an Idempotency-Key header: generate one per attempt so
    // double-clicks and retries collapse into a single order server-side.
    const idempotencyKey = 'kp-' + Date.now().toString(36) + '-'
      + Math.random().toString(36).slice(2, 14);

    try {
      const placed = await api('/checkout/place', {
        method: 'POST',
        headers: { 'Idempotency-Key': idempotencyKey },
        body: {
          address_id:      selectedAddressId,
          notes:           (typeof orderNote !== 'undefined' && orderNote) ? orderNote : null,
          payment_method:  selectedMethod,
          payment_provider: provider,
          payment_phone:   phone,
          card_number:     cardNumber,
          card_expiry:     cardExpiry,
          card_cvv:        cardCvv,
          card_name:       cardName,
        },
      });
      const placedNumber = placed?.data?.order_number;
      toast('Order placed!');
      // Land on this order's tracking page (falls back to the orders list
      // if the response shape ever changes).
      window.location.href = placedNumber ? '/orders/' + encodeURIComponent(placedNumber) : '/account/orders';
    } catch (e) {
      showError(e.message || 'Unable to place order. Please try again.');
      placeBtn.disabled = false;
      updateBtn();
    }
  });

  /* ── save address form ───────────────────────────────── */
  $('[data-address-form]')?.addEventListener('submit', async e => {
    e.preventDefault();
    const form      = e.currentTarget;
    const submitBtn = form.querySelector('button[type="submit"]');
    const formErr   = $('[data-address-form-error]');
    if (formErr) formErr.style.display = 'none';
    if (submitBtn) { submitBtn.disabled = true; submitBtn.textContent = 'Saving…'; }
    try {
      const result = await api('/addresses', { method: 'POST', body: Object.fromEntries(new FormData(form)) });
      const saved = result?.data || result;
      if (!saved?.id) throw new Error('Address saved but no data returned.');
      addresses.push(saved);
      selectedAddressId = saved.id;
      updateAddressDisplay();
      renderSavedList();
      form.reset();
      closeModal(addrModal);
      toast('Delivery address saved.');
    } catch (err) {
      if (formErr) { formErr.textContent = err.message || 'Unable to save address.'; formErr.style.display = 'flex'; }
      if (submitBtn) { submitBtn.disabled = false; submitBtn.textContent = 'Save address'; }
    }
  });

  /* ── note modal ──────────────────────────────────────── */
  // The note lives here until place-order sends it as `notes`. (It used to
  // only toast and drop the text — now it actually travels with the order.)
  let orderNote = '';
  const noteTextarea = $('[data-note-textarea]');
  const noteCount    = $('[data-note-count]');
  noteTextarea?.addEventListener('input', () => {
    if (noteCount) noteCount.textContent = noteTextarea.value.length;
  });
  $('[data-note-form]')?.addEventListener('submit', e => {
    e.preventDefault();
    orderNote = (noteTextarea?.value || '').trim();
    closeModal(noteModal);
    toast(orderNote ? 'Note added to your order.' : 'Note removed.');
  });

  /* ── contact edit ────────────────────────────────────── */
  $('[data-edit-contact]')?.addEventListener('click', () => {
    toast('Contact details are managed by your account settings.');
  });

  /* ── Google Maps ─────────────────────────────────────── */
  const MAPS_KEY = document.querySelector('[data-google-maps-key]')?.dataset?.googleMapsKey || '';
  const DAR = { lat: -6.7924, lng: 39.2083 };
  let map = null, marker = null, geocoder = null, mapsPromise = null;

  const loadMaps = () => {
    if (window.google?.maps) return Promise.resolve();
    if (mapsPromise) return mapsPromise;
    if (!MAPS_KEY) return Promise.reject(new Error('Map is not configured.'));
    mapsPromise = new Promise((res, rej) => {
      const s = document.createElement('script');
      s.src = `https://maps.googleapis.com/maps/api/js?key=${encodeURIComponent(MAPS_KEY)}&libraries=places`;
      s.async = true;
      s.onload = res;
      s.onerror = () => rej(new Error('Unable to load map. Please enter your address manually.'));
      document.head.appendChild(s);
    });
    return mapsPromise;
  };

  const placeMarker = async latLng => {
    marker.setPosition(latLng);
    map.panTo(latLng);
    $('[data-map-confirm]').disabled = false;
    const sel = $('[data-map-selected]');
    try {
      const { results } = await geocoder.geocode({ location: latLng });
      const place = results?.[0];
      marker.addrComponents = place?.address_components || [];
      marker.formattedAddr   = place?.formatted_address  || '';
      if (sel) sel.textContent = place?.formatted_address || `${latLng.lat().toFixed(5)}, ${latLng.lng().toFixed(5)}`;
    } catch {
      marker.addrComponents = [];
      marker.formattedAddr   = '';
      if (sel) sel.textContent = `${latLng.lat().toFixed(5)}, ${latLng.lng().toFixed(5)}`;
    }
  };

  let mapInit = null;
  const initMap = () => {
    if (mapInit) return mapInit;
    mapInit = (async () => {
      const mapErr = $('[data-map-error]');
      try { await loadMaps(); }
      catch (e) { if (mapErr) mapErr.textContent = e.message; throw e; }
      const container = $('[data-google-map]');
      map = new google.maps.Map(container, { center: DAR, zoom: 12, disableDefaultUI: true, zoomControl: true });
      geocoder = new google.maps.Geocoder();
      marker = new google.maps.Marker({ map, position: DAR, draggable: true });
      map.addListener('click', e => placeMarker(e.latLng));
      marker.addListener('dragend', () => placeMarker(marker.getPosition()));
      const searchEl = $('[data-map-search]');
      if (searchEl) {
        const ac = new google.maps.places.Autocomplete(searchEl, {
          fields: ['geometry','formatted_address','address_components'],
          componentRestrictions: { country: 'tz' },
        });
        ac.addListener('place_changed', () => {
          const p = ac.getPlace();
          if (!p.geometry) return;
          map.setZoom(16);
          placeMarker(p.geometry.location);
        });
      }
    })();
    return mapInit;
  };

  // No Maps API key: the location picker cannot work (no tiles, no
  // geocoder), so hide it up front and default to manual entry instead of
  // showing a dead map with an error message.
  if (!MAPS_KEY) {
    $$('[data-tab]').forEach(b => { if (b.dataset.tab === 'location') b.style.display = 'none'; });
    const locPanel = $('[data-panel="location"]');
    if (locPanel) locPanel.style.display = 'none';
    $$('[data-use-location]').forEach(b => { b.style.display = 'none'; });
    setTab('manual');
  }

  $$('[data-open-addr-modal], [data-use-location]').forEach(b =>
    b.addEventListener('click', () => initMap().catch(() => {}))
  );

  $('[data-request-location]')?.addEventListener('click', async () => {
    const mapErr = $('[data-map-error]');
    if (mapErr) mapErr.textContent = '';
    if (!navigator.geolocation) {
      if (mapErr) mapErr.textContent = 'Location services are not available on this device.';
      return;
    }
    try { await initMap(); } catch { return; }
    navigator.geolocation.getCurrentPosition(
      pos => { map.setZoom(16); placeMarker(new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude)); },
      err => {
        if (!mapErr) return;
        mapErr.textContent = err.code === err.PERMISSION_DENIED
          ? 'Location access was denied. Please enter your address manually.'
          : 'Unable to get your location. Please enter your address manually.';
      },
      { enableHighAccuracy: true, timeout: 10000 }
    );
  });

  const componentVal = (comps, type) => comps?.find(c => c.types.includes(type))?.long_name || '';

  $('[data-map-confirm]')?.addEventListener('click', () => {
    if (!marker) return;
    const form = $('[data-address-form]');
    if (form && marker.addrComponents?.length) {
      const r  = componentVal(marker.addrComponents, 'administrative_area_level_1');
      const d  = componentVal(marker.addrComponents, 'administrative_area_level_2') || componentVal(marker.addrComponents, 'locality');
      const w  = componentVal(marker.addrComponents, 'sublocality') || componentVal(marker.addrComponents, 'neighborhood');
      const st = [componentVal(marker.addrComponents, 'route'), componentVal(marker.addrComponents, 'street_number')].filter(Boolean).join(' ');
      if (r)  form.region.value   = r;
      if (d)  form.district.value = d;
      if (w)  form.ward.value     = w;
      if (st) form.street.value   = st;
      if (!form.street.value && marker.formattedAddr) form.street.value = marker.formattedAddr;
      const lat = document.createElement('input'); lat.type='hidden'; lat.name='latitude';  lat.value=marker.getPosition().lat(); form.append(lat);
      const lng = document.createElement('input'); lng.type='hidden'; lng.name='longitude'; lng.value=marker.getPosition().lng(); form.append(lng);
    }
    setTab('manual');
    toast('Location selected — please confirm the details below.');
  });

})();
</script>

</div>
@endsection
