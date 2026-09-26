{{-- resources/views/account/notifications.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
.kpnf {
  --ink: #0c110e; --text: #1a231e; --muted: #5a6660;
  --border: #e2e8e4; --card: #ffffff;
  --green: #1a7a52; --green-dark: #145f40; --green-faint: #eef8f3;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
}
.kpnf * { box-sizing: border-box; }
.kpnf a:focus-visible, .kpnf button:focus-visible { outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px; }

/* header */
.kpnf-header { display: flex; align-items: flex-start; gap: 16px; border-bottom: 1px solid var(--border); padding-bottom: 28px; }
.kpnf-icon { width: 44px; height: 44px; flex: none; border-radius: 14px; background: var(--ink); color: #fff; display: grid; place-items: center; }
.kpnf-eyebrow { font-size: 10px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--green); display: flex; align-items: center; gap: 5px; margin: 0 0 4px; }
.kpnf-title  { margin: 0; font-size: 26px; font-weight: 900; letter-spacing: -.03em; color: var(--ink); }
@media (min-width: 640px) { .kpnf-title { font-size: 30px; } }
.kpnf-sub { margin: 6px 0 0; font-size: 13px; color: var(--muted); }

/* feedback */
.kpnf-feedback { display: none; margin-bottom: 18px; padding: 12px 16px; border-radius: 12px; border: 1px solid; font-size: 13px; }
.kpnf-feedback.ok  { background: var(--green-faint); border-color: #b5d9c8; color: var(--green-dark); }
.kpnf-feedback.err { background: #fff1f2; border-color: #fecdd3; color: #be123c; }

/* preference card */
.kpnf-card { border: 1px solid var(--border); border-radius: 18px; background: var(--card); box-shadow: 0 1px 3px rgba(10,16,12,.05); overflow: hidden; margin-top: 24px; }
.kpnf-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; padding: 18px 20px; }
.kpnf-row + .kpnf-row { border-top: 1px solid var(--border); }
.kpnf-row-icon { width: 36px; height: 36px; border-radius: 11px; background: var(--green-faint); color: var(--green); display: grid; place-items: center; flex: none; margin-top: 2px; }
.kpnf-row-label { font-size: 14px; font-weight: 700; color: var(--ink); }
.kpnf-row-sub { font-size: 12px; color: var(--muted); margin-top: 3px; line-height: 1.5; }
.kpnf-always { background: var(--green-faint); color: var(--green); border-radius: 999px; padding: 3px 10px; font-size: 11px; font-weight: 700; white-space: nowrap; flex: none; margin-top: 2px; }

/* toggle switch */
.kpnf-toggle { position: relative; display: inline-flex; align-items: center; cursor: pointer; flex: none; margin-top: 2px; }
.kpnf-toggle input { position: absolute; opacity: 0; width: 0; height: 0; }
.kpnf-track {
  display: block; width: 44px; height: 24px;
  border-radius: 999px;
  background: #d4dbd6;
  transition: background .2s;
}
.kpnf-toggle input:checked ~ .kpnf-track { background: var(--green); }
.kpnf-thumb {
  position: absolute; top: 2px; left: 2px;
  width: 20px; height: 20px;
  border-radius: 50%; background: #fff;
  box-shadow: 0 1px 4px rgba(0,0,0,.16);
  transition: transform .2s;
}
.kpnf-toggle input:checked ~ .kpnf-track ~ .kpnf-thumb { transform: translateX(20px); }

/* channel chips */
.kpnf-channels { border: 1px solid var(--border); border-radius: 18px; background: var(--card); padding: 18px 20px; box-shadow: 0 1px 3px rgba(10,16,12,.05); margin-top: 14px; }
.kpnf-channels-label { font-size: 14px; font-weight: 700; color: var(--ink); margin: 0 0 12px; }
.kpnf-chips { display: flex; flex-wrap: wrap; gap: 8px; }
.kpnf-chip { display: inline-flex; align-items: center; gap: 6px; border: 1px solid var(--border); border-radius: 999px; padding: 8px 16px; font-size: 13px; font-weight: 600; color: var(--ink); background: var(--card); cursor: pointer; transition: background .15s, border-color .15s, color .15s; }
.kpnf-chip:hover { background: var(--green-faint); border-color: #b0c4b8; }
.kpnf-chip input { display: none; }
.kpnf-chip:has(input:checked) { background: var(--ink); color: #fff; border-color: var(--ink); }

/* save */
.kpnf-save { display: inline-flex; align-items: center; gap: 8px; height: 44px; padding: 0 24px; border-radius: 12px; background: var(--green-dark); color: #fff; font: 700 14px/1 inherit; border: 0; cursor: pointer; transition: background .18s, transform .15s; }
.kpnf-save:hover:not(:disabled) { background: #0f4e33; transform: translateY(-1px); }
.kpnf-save:disabled { opacity: .5; cursor: not-allowed; }
.kpnf-note { display: flex; align-items: flex-start; gap: 6px; font-size: 11.5px; color: var(--muted); line-height: 1.55; margin-top: 14px; }
</style>
@endpush

@section('content')
<div class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
  <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('components.account.sidebar')

    <div data-notifications-page class="kpnf">

      <div data-notifications-feedback class="kpnf-feedback"></div>

      {{-- Header --}}
      <div class="kpnf-header">
        <div class="kpnf-icon">
          <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
          </svg>
        </div>
        <div>
          <p class="kpnf-eyebrow">
            <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/>
              <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
            </svg>
            Preferences
          </p>
          <h1 class="kpnf-title">Notifications</h1>
          <p class="kpnf-sub">Choose what you hear from us and how.</p>
        </div>
      </div>

      {{-- Preferences card --}}
      <div class="kpnf-card">

        {{-- Order updates (always on) --}}
        <div class="kpnf-row">
          <div style="display:flex;align-items:flex-start;gap:12px">
            <span class="kpnf-row-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m16.5 9.4-9-5.19M21 16V8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/>
                <polyline points="3.29 7 12 12 20.71 7"/><line x1="12" y1="22" x2="12" y2="12"/>
              </svg>
            </span>
            <div>
              <p class="kpnf-row-label">Order updates</p>
              <p class="kpnf-row-sub">Confirmation, shipping, and delivery status for your orders.</p>
            </div>
          </div>
          <span class="kpnf-always">Always on</span>
        </div>

        {{-- Promotions --}}
        <div class="kpnf-row">
          <div style="display:flex;align-items:flex-start;gap:12px">
            <span class="kpnf-row-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="m12 3-1.912 5.813a2 2 0 0 1-1.275 1.275L3 12l5.813 1.912a2 2 0 0 1 1.275 1.275L12 21l1.912-5.813a2 2 0 0 1 1.275-1.275L21 12l-5.813-1.912a2 2 0 0 1-1.275-1.275L12 3Z"/>
              </svg>
            </span>
            <div>
              <p class="kpnf-row-label">Promotions &amp; new drops</p>
              <p class="kpnf-row-sub">Sales, discount codes, and new collection launches.</p>
            </div>
          </div>
          <label class="kpnf-toggle" aria-label="Promotions notifications">
            <input type="checkbox" name="notify_promotions" {{ ($preferences->marketing_enabled ?? true) ? 'checked' : '' }}>
            <span class="kpnf-track"></span>
            <span class="kpnf-thumb"></span>
          </label>
        </div>

        {{-- Restock --}}
        <div class="kpnf-row">
          <div style="display:flex;align-items:flex-start;gap:12px">
            <span class="kpnf-row-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/>
              </svg>
            </span>
            <div>
              <p class="kpnf-row-label">Restock alerts</p>
              <p class="kpnf-row-sub">When a wishlist item you saved comes back in stock.</p>
            </div>
          </div>
          <label class="kpnf-toggle" aria-label="Restock alerts">
            <input type="checkbox" name="notify_restock" {{ ($preferences->restock_enabled ?? true) ? 'checked' : '' }}>
            <span class="kpnf-track"></span>
            <span class="kpnf-thumb"></span>
          </label>
        </div>

        {{-- Price drop --}}
        <div class="kpnf-row">
          <div style="display:flex;align-items:flex-start;gap:12px">
            <span class="kpnf-row-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
                   stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M12.586 2.586A2 2 0 0 0 11.172 2H4a2 2 0 0 0-2 2v7.172a2 2 0 0 0 .586 1.414l8.704 8.704a2.426 2.426 0 0 0 3.42 0l6.58-6.58a2.426 2.426 0 0 0 0-3.42z"/>
                <circle cx="7.5" cy="7.5" r=".5" fill="currentColor"/>
              </svg>
            </span>
            <div>
              <p class="kpnf-row-label">Price drop alerts</p>
              <p class="kpnf-row-sub">When a saved item's price is reduced.</p>
            </div>
          </div>
          <label class="kpnf-toggle" aria-label="Price drop alerts">
            <input type="checkbox" name="notify_price_drop" {{ ($preferences->price_drop_enabled ?? false) ? 'checked' : '' }}>
            <span class="kpnf-track"></span>
            <span class="kpnf-thumb"></span>
          </label>
        </div>
      </div>

      {{-- Delivery channels --}}
      <div class="kpnf-channels">
        <p class="kpnf-channels-label">Send notifications via</p>
        <div class="kpnf-chips">
          <label class="kpnf-chip">
            <input type="checkbox" name="channel_sms" {{ ($preferences->sms_enabled ?? true) ? 'checked' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/>
            </svg>
            SMS
          </label>
          <label class="kpnf-chip">
            <input type="checkbox" name="channel_email" {{ ($preferences->email_enabled ?? true) ? 'checked' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="16" x="2" y="4" rx="2"/><path d="m22 7-8.97 5.7a1.94 1.94 0 0 1-2.06 0L2 7"/>
            </svg>
            Email
          </label>
          <label class="kpnf-chip">
            <input type="checkbox" name="channel_push" {{ ($preferences->push_enabled ?? false) ? 'checked' : '' }}>
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none"
                 stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9"/>
              <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0"/>
              <path d="M4 2C2.8 3.7 2 5.7 2 8"/><path d="M22 8c0-2.3-.8-4.3-2-6"/>
            </svg>
            Push
          </label>
        </div>
      </div>

      {{-- Save --}}
      <div style="margin-top:22px;display:flex;flex-wrap:wrap;align-items:center;gap:14px">
        <button data-save-notifications type="button" class="kpnf-save">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
               stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15.2 3a2 2 0 0 1 1.4.6l3.8 3.8a2 2 0 0 1 .6 1.4V19a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2z"/>
            <path d="M17 21v-7a1 1 0 0 0-1-1H8a1 1 0 0 0-1 1v7"/><path d="M7 3v4a1 1 0 0 0 1 1h7"/>
          </svg>
          Save preferences
        </button>
      </div>

      <p class="kpnf-note">
        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="flex:none;margin-top:1px">
          <circle cx="12" cy="12" r="10"/><line x1="12" x2="12" y1="8" y2="12"/>
          <line x1="12" x2="12.01" y1="16" y2="16"/>
        </svg>
        Order confirmations and essential service messages remain enabled so you do not miss important updates.
      </p>

    </div>
  </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const page = document.querySelector('[data-notifications-page]');
  if (!page) return;

  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };
  const api = async (path, opts = {}) => {
    const headers = new Headers({ 'Accept': 'application/json', 'Content-Type': 'application/json', ...(opts.headers || {}) });
    const xsrf = getCookie('XSRF-TOKEN');
    if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
    const res = await fetch('/api/v1' + path, { ...opts, credentials: 'include', headers, body: opts.body });
    const data = await res.text().then(t => { try { return JSON.parse(t); } catch { return null; } });
    if (!res.ok) throw new Error(data?.message || `Error ${res.status}`);
    return data;
  };

  const feedback = page.querySelector('[data-notifications-feedback]');
  const btn = page.querySelector('[data-save-notifications]');
  const get = name => !!page.querySelector(`[name="${name}"]`)?.checked;

  const showFeedback = (msg, err) => {
    feedback.textContent = msg;
    feedback.className = 'kpnf-feedback ' + (err ? 'err' : 'ok');
    feedback.style.display = 'flex';
    setTimeout(() => { feedback.style.display = 'none'; }, 4000);
  };

  btn?.addEventListener('click', async () => {
    btn.disabled = true;
    btn.textContent = 'Saving…';
    try {
      await api('/account/preferences/notifications', {
        method: 'PUT',
        body: JSON.stringify({
          push_enabled:    get('channel_push'),
          email_enabled:   get('channel_email'),
          sms_enabled:     get('channel_sms'),
          marketing_enabled: get('notify_promotions'),
          restock_enabled: get('notify_restock'),
          price_drop_enabled: get('notify_price_drop'),
        }),
      });
      showFeedback('Notification preferences saved.');
    } catch (e) {
      showFeedback(e.message, true);
    } finally {
      btn.disabled = false;
      btn.textContent = 'Save preferences';
    }
  });
})();
</script>
@endpush
