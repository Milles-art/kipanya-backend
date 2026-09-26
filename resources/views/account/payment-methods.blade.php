{{-- resources/views/account/payment-methods.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
.kppm {
  --ink: #0c110e; --text: #1a231e; --muted: #5a6660;
  --border: #e2e8e4; --card: #ffffff;
  --green: #1a7a52; --green-dark: #145f40; --green-faint: #eef8f3; --green-faint2: #f5fbf8;
  --radius-card: 18px;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
}
.kppm * { box-sizing: border-box; }
.kppm a { transition: color .16s, opacity .16s; }
.kppm a:focus-visible, .kppm button:focus-visible { outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px; }

/* page header */
.kppm-header { display: flex; align-items: flex-start; gap: 16px; border-bottom: 1px solid var(--border); padding-bottom: 28px; }
.kppm-icon { width: 44px; height: 44px; flex: none; border-radius: 14px; background: var(--ink); color: #fff; display: grid; place-items: center; }
.kppm-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--green); margin: 0 0 4px; }
.kppm-title { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -.03em; color: var(--ink); }
@media (min-width: 640px) { .kppm-title { font-size: 30px; } }
.kppm-sub { margin: 6px 0 0; font-size: 13px; color: var(--muted); line-height: 1.55; }

/* section label */
.kppm-label-row { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.kppm-label { font-size: 10px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; color: var(--muted); }
.kppm-tag { display: inline-flex; align-items: center; border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
.kppm-tag.green  { background: var(--green-faint); color: var(--green); }
.kppm-tag.amber  { background: #fef9c3; color: #a16207; }

/* card grid */
.kppm-card-grid { display: grid; gap: 14px; }
@media (min-width: 768px) { .kppm-card-grid { grid-template-columns: 1fr 1fr; } }
.kppm-saved-card {
  background: var(--card); border: 1px solid var(--border);
  border-radius: var(--radius-card); padding: 20px;
  box-shadow: 0 1px 3px rgba(10,16,12,.05);
}
.kppm-card-top { display: flex; align-items: flex-start; justify-content: space-between; gap: 12px; margin-bottom: 20px; }
.kppm-card-logo { width: 56px; height: 36px; border-radius: 10px; background: #f5f7f5; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; padding: 6px; overflow: hidden; }
.kppm-card-logo img { width: 100%; height: 100%; object-fit: contain; }
.kppm-default-badge { background: var(--green-faint); color: var(--green); border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; }
.kppm-card-num { font-size: 16px; font-weight: 900; letter-spacing: .14em; color: var(--ink); }
.kppm-card-meta { font-size: 12px; color: var(--muted); margin-top: 4px; }
.kppm-set-default { margin-top: 14px; background: none; border: 0; padding: 0; font-size: 12px; font-weight: 800; color: var(--green); cursor: pointer; }
.kppm-set-default:hover { color: var(--green-dark); }
.kppm-set-default:disabled { opacity: .5; cursor: wait; }

/* provider card */
.kppm-info-card { background: var(--card); border: 1px solid var(--border); border-radius: var(--radius-card); padding: 20px; box-shadow: 0 1px 3px rgba(10,16,12,.05); }
.kppm-info-card-head { display: flex; align-items: center; gap: 12px; margin-bottom: 20px; }
.kppm-info-icon { width: 36px; height: 36px; border-radius: 11px; background: var(--green-faint); color: var(--green); display: grid; place-items: center; flex: none; }
.kppm-info-name { font-size: 14px; font-weight: 800; color: var(--ink); }
.kppm-info-sub { font-size: 12px; color: var(--muted); margin-top: 2px; }

/* provider logos grid */
.kppm-providers { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }
@media (min-width: 480px) { .kppm-providers { grid-template-columns: repeat(4,1fr); } }
.kppm-provider { border: 1px solid var(--border); border-radius: 14px; padding: 12px 8px; display: flex; flex-direction: column; align-items: center; gap: 8px; }
.kppm-provider-logo { width: 100%; height: 36px; display: flex; align-items: center; justify-content: center; }
.kppm-provider-logo img { width: 100%; height: 100%; object-fit: contain; }
.kppm-provider-name { font-size: 11px; font-weight: 600; color: var(--muted); text-align: center; }
.kppm-provider.mpesa   { background: rgba(254,242,242,.5); }
.kppm-provider.halo    { background: rgba(255,247,237,.5); }
.kppm-provider.airtel  { background: rgba(254,242,242,.5); }
.kppm-provider.yas     { background: rgba(240,253,244,.5); }

/* card brand logos row */
.kppm-brand-logos { display: flex; gap: 10px; margin-top: 16px; }
.kppm-brand-logo { width: 56px; height: 36px; border-radius: 10px; background: #fff; border: 1px solid var(--border); display: flex; align-items: center; justify-content: center; padding: 5px; overflow: hidden; }
.kppm-brand-logo img { width: 100%; height: 100%; object-fit: contain; }

/* security block */
.kppm-security {
  background: var(--ink); border-radius: var(--radius-card); padding: 20px 24px;
  display: flex; gap: 16px; align-items: flex-start; color: #fff;
}
.kppm-security-icon { width: 40px; height: 40px; border-radius: 12px; background: rgba(255,255,255,.1); display: grid; place-items: center; flex: none; }
.kppm-security h3 { margin: 0; font-size: 14px; font-weight: 800; }
.kppm-security p { margin: 6px 0 0; font-size: 12px; color: rgba(255,255,255,.72); line-height: 1.6; max-width: 480px; }

/* support strip */
.kppm-support {
  border: 1px solid var(--border); border-radius: var(--radius-card);
  background: var(--green-faint2); padding: 18px 22px;
  display: flex; flex-direction: column; gap: 12px;
}
@media (min-width: 640px) { .kppm-support { flex-direction: row; align-items: center; justify-content: space-between; } }
.kppm-support-title { font-size: 14px; font-weight: 800; color: var(--ink); }
.kppm-support-sub { font-size: 12px; color: var(--muted); margin-top: 3px; }
.kppm-support-btn {
  display: inline-flex; align-items: center; gap: 6px;
  height: 40px; padding: 0 18px; border-radius: 12px;
  border: 1px solid var(--border); background: #fff; color: var(--ink);
  font-size: 13px; font-weight: 700; text-decoration: none; flex: none;
  transition: background .16s, border-color .16s;
}
.kppm-support-btn:hover { background: var(--green-faint); border-color: #b0c4b8; }

/* section spacing */
.kppm-section { margin-top: 28px; }
</style>
@endpush

@section('content')
<div class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
  <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('components.account.sidebar')

    <section class="min-w-0 kppm" aria-labelledby="pm-title">

      {{-- ── Header ── --}}
      <div class="kppm-header">
        <div class="kppm-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="21" height="21" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
          </svg>
        </div>
        <div>
          <p class="kppm-eyebrow">Account</p>
          <h1 id="pm-title" class="kppm-title">Payment methods</h1>
          <p class="kppm-sub">Manage the payment details you use when shopping with KP Wear.</p>
        </div>
      </div>

      {{-- ── Saved cards ── --}}
      @if(isset($paymentMethods) && $paymentMethods->isNotEmpty())
      <div class="kppm-section">
        <div class="kppm-label-row">
          <span class="kppm-label">Your saved cards</span>
        </div>
        <div class="kppm-card-grid">
          @foreach($paymentMethods as $card)
          <div class="kppm-saved-card">
            <div class="kppm-card-top">
              <div class="kppm-card-logo">
                @if(strtolower($card->brand) === 'visa')
                  <img src="{{ asset('assets/wear/payments/visa.png') }}" alt="Visa">
                @elseif(str_contains(strtolower($card->brand), 'master'))
                  <img src="{{ asset('assets/wear/payments/mastercard.png') }}" alt="Mastercard">
                @else
                  <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
                @endif
              </div>
              @if($card->is_default)
                <span class="kppm-default-badge">Default</span>
              @endif
            </div>
            <p class="kppm-card-num">•••• •••• •••• {{ $card->last4 }}</p>
            <p class="kppm-card-meta">{{ $card->brand }} · Expires {{ $card->exp_month }}/{{ $card->exp_year }}</p>
            @if(!$card->is_default)
              <button type="button" class="kppm-set-default" data-set-default="{{ $card->id }}">Set as default</button>
            @endif
          </div>
          @endforeach
        </div>
      </div>
      @endif

      {{-- ── Mobile Money ── --}}
      <div class="kppm-section">
        <div class="kppm-label-row">
          <span class="kppm-label">Mobile money</span>
          <span class="kppm-tag amber">Used at checkout</span>
        </div>
        <div class="kppm-info-card">
          <div class="kppm-info-card-head">
            <span class="kppm-info-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/>
              </svg>
            </span>
            <div>
              <p class="kppm-info-name">Pay with mobile money</p>
              <p class="kppm-info-sub">Enter your number at checkout — no pre-saving required</p>
            </div>
          </div>
          <div class="kppm-providers">
            <div class="kppm-provider mpesa">
              <div class="kppm-provider-logo"><img src="{{ asset('assets/wear/payments/mpesa.png') }}" alt="M-Pesa"></div>
              <span class="kppm-provider-name">M-Pesa</span>
            </div>
            <div class="kppm-provider halo">
              <div class="kppm-provider-logo"><img src="{{ asset('assets/wear/payments/halopesa.png') }}" alt="HaloPesa"></div>
              <span class="kppm-provider-name">HaloPesa</span>
            </div>
            <div class="kppm-provider airtel">
              <div class="kppm-provider-logo"><img src="{{ asset('assets/wear/payments/airtel-money.png') }}" alt="Airtel Money"></div>
              <span class="kppm-provider-name">Airtel Money</span>
            </div>
            <div class="kppm-provider yas">
              <div class="kppm-provider-logo"><img src="{{ asset('assets/wear/payments/yas.png') }}" alt="Yas"></div>
              <span class="kppm-provider-name">Yas</span>
            </div>
          </div>
        </div>
      </div>

      {{-- ── Card Payments ── --}}
      <div class="kppm-section">
        <div class="kppm-label-row">
          <span class="kppm-label">Card payments</span>
          <span class="kppm-tag green">Accepted</span>
        </div>
        <div class="kppm-info-card">
          <div class="kppm-info-card-head">
            <span class="kppm-info-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/>
              </svg>
            </span>
            <div>
              <p class="kppm-info-name">Visa &amp; Mastercard</p>
              <p class="kppm-info-sub">Processed securely via Selcom</p>
            </div>
          </div>
          <div class="kppm-brand-logos">
            <div class="kppm-brand-logo"><img src="{{ asset('assets/wear/payments/visa.png') }}"       alt="Visa"></div>
            <div class="kppm-brand-logo"><img src="{{ asset('assets/wear/payments/mastercard.png') }}" alt="Mastercard"></div>
            <div class="kppm-brand-logo"><img src="{{ asset('assets/wear/payments/selcom.png') }}"     alt="Selcom"></div>
          </div>
        </div>
      </div>

      {{-- ── Security ── --}}
      <div class="kppm-section kppm-security">
        <div class="kppm-security-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><polyline points="9 12 11 14 15 10"/>
          </svg>
        </div>
        <div>
          <h3>Your payment security matters</h3>
          <p>KP Wear uses Selcom to process all payments. Full card details are never stored directly in this application — only the last 4 digits and expiry are saved for display.</p>
        </div>
      </div>

      {{-- ── Support ── --}}
      <div class="kppm-section kppm-support">
        <div>
          <p class="kppm-support-title">Need help with a payment?</p>
          <p class="kppm-support-sub">Our support team is here to help with any billing questions.</p>
        </div>
        <a href="{{ route('contact') }}" class="kppm-support-btn">
          <svg xmlns="http://www.w3.org/2000/svg" width="15" height="15" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M3 18v-6a9 9 0 0 1 18 0v6"/>
            <path d="M21 19a2 2 0 0 1-2 2h-1a2 2 0 0 1-2-2v-3a2 2 0 0 1 2-2h3z"/>
            <path d="M3 19a2 2 0 0 0 2 2h1a2 2 0 0 0 2-2v-3a2 2 0 0 0-2-2H3z"/>
          </svg>
          Contact support
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M5 12h14m-7-7 7 7-7 7"/>
          </svg>
        </a>
      </div>

    </section>
  </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };

  document.addEventListener('click', async e => {
    const btn = e.target.closest('[data-set-default]');
    if (!btn) return;

    const id = btn.dataset.setDefault;
    btn.disabled = true;
    const label = btn.textContent;
    btn.textContent = 'Saving…';

    try {
      const headers = new Headers({ Accept: 'application/json' });
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
      const res = await fetch(`/api/v1/payment-methods/${encodeURIComponent(id)}/set-default`, {
        method: 'PUT',
        credentials: 'include',
        headers,
      });
      if (!res.ok) {
        let msg = `Request failed (${res.status})`;
        try {
          const data = await res.json();
          msg = data?.message || msg;
        } catch {}
        throw new Error(msg);
      }
      window.location.reload();
    } catch (err) {
      btn.disabled = false;
      btn.textContent = label;
      window.dispatchEvent(new CustomEvent('kp:toast', { detail: err.message || 'Unable to update default.' }));
    }
  });
})();
</script>
@endpush
