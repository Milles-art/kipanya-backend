{{-- resources/views/account/order-detail.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
/* ============================================================
   KP WEAR — Order Detail
   ============================================================ */
.kpd {
  --ink: #0c110e; --text: #1a231e; --muted: #5a6660;
  --border: #e2e8e4; --bg: #f5f8f6; --card: #ffffff;
  --green: #1a7a52; --green-dark: #145f40; --green-faint: #eef8f3; --green-faint2: #f5fbf8;
  --amber: #b45309; --amber-faint: #fffbeb; --amber-border: #fde68a;
  --blue: #1d4ed8; --blue-faint: #eff6ff;
  --red: #dc2626; --red-faint: #fef2f2; --red-border: #fecdd3;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
  color: var(--ink);
}
.kpd * { box-sizing: border-box; }
.kpd a, .kpd button { transition: background .18s, color .18s, border-color .18s, transform .15s; font: inherit; }
.kpd a:focus-visible, .kpd button:focus-visible { outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px; }

/* ── Header ── */
.kpd-back { display: inline-flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: var(--muted); text-decoration: none; }
.kpd-back:hover { color: var(--green); }
.kpd-head { margin-top: 18px; display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
.kpd-overline { margin: 0; font-size: 11px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--green); }
.kpd-title-row { margin-top: 8px; display: flex; flex-wrap: wrap; align-items: center; gap: 12px; }
.kpd-title { margin: 0; font-size: 30px; font-weight: 900; letter-spacing: -.035em; line-height: 1.1; }
.kpd-title span { font-family: ui-monospace, SFMono-Regular, Menlo, monospace; letter-spacing: -.02em; }
.kpd-sub { margin: 6px 0 0; font-size: 13px; color: var(--muted); }
.kpd-head-actions { display: flex; flex-wrap: wrap; gap: 8px; }

.kpd-badge {
  display: inline-flex; align-items: center; gap: 6px;
  height: 30px; padding: 0 12px; border-radius: 999px;
  font-size: 12px; font-weight: 700; white-space: nowrap; background: var(--bg); color: var(--text);
}
.kpd-badge-dot { width: 7px; height: 7px; border-radius: 50%; background: currentColor; }
.tone-amber.kpd-badge { background: var(--amber-faint); color: var(--amber); }
.tone-green.kpd-badge { background: var(--green-faint); color: var(--green); }
.tone-blue.kpd-badge  { background: var(--blue-faint);  color: var(--blue); }
.tone-red.kpd-badge   { background: var(--red-faint);   color: var(--red); }

/* ── Buttons ── */
.kpd-btn {
  display: inline-flex; align-items: center; justify-content: center; gap: 8px;
  height: 44px; padding: 0 18px; border-radius: 12px; border: 0;
  font-size: 13px; font-weight: 700; text-decoration: none; cursor: pointer; white-space: nowrap;
}
.kpd-btn-dark { background: var(--ink); color: #fff; }
.kpd-btn-dark:hover { background: var(--green-dark); transform: translateY(-1px); }
.kpd-btn-soft { background: var(--card); color: var(--ink); border: 1px solid var(--border); }
.kpd-btn-soft:hover { background: var(--green-faint2); border-color: #b0c4b8; }
.kpd-btn-pay { background: #d97706; color: #fff; box-shadow: 0 8px 20px -10px rgba(217,119,6,.8); }
.kpd-btn-pay:hover { background: var(--amber); transform: translateY(-1px); }
.kpd-btn-danger { background: var(--card); color: var(--red); border: 1px solid var(--red-border); }
.kpd-btn-danger:hover { background: var(--red-faint); }
.kpd-btn[disabled] { opacity: .6; cursor: not-allowed; transform: none; }
.kpd-btn-block { width: 100%; }

/* ── Layout ── */
.kpd-grid { margin-top: 28px; display: grid; gap: 20px; }
@media (min-width: 1024px) { .kpd-grid { grid-template-columns: minmax(0,1fr) 360px; align-items: start; } }
.kpd-col { display: flex; flex-direction: column; gap: 20px; min-width: 0; }
.kpd-card {
  background: var(--card); border: 1px solid var(--border); border-radius: 22px;
  padding: 24px; box-shadow: 0 1px 3px rgba(10,16,12,.05);
}
.kpd-card-title { margin: 0 0 18px; display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.kpd-card-title h2 { margin: 0; font-size: 15px; font-weight: 900; letter-spacing: -.01em; }
.kpd-card-title small { font-size: 12px; font-weight: 600; color: var(--muted); }
@media (min-width: 1024px) { .kpd-sticky { position: sticky; top: 96px; } }

/* ── Pay banner ── */
.kpd-alert {
  display: flex; align-items: flex-start; gap: 14px;
  padding: 18px 20px; border-radius: 18px;
  background: var(--amber-faint); border: 1px solid var(--amber-border);
}
.kpd-alert-icon { width: 38px; height: 38px; flex: none; border-radius: 12px; background: #fff; color: #d97706; display: grid; place-items: center; }
.kpd-alert h3 { margin: 0; font-size: 14px; font-weight: 800; color: #78350f; }
.kpd-alert p { margin: 3px 0 0; font-size: 13px; color: #92400e; line-height: 1.55; }
.kpd-alert.red { background: var(--red-faint); border-color: var(--red-border); }
.kpd-alert.red .kpd-alert-icon { color: var(--red); }
.kpd-alert.red h3 { color: #7f1d1d; }
.kpd-alert.red p { color: #991b1b; }

/* ── Timeline ── */
.kpd-timeline { list-style: none; margin: 0; padding: 0; display: grid; grid-template-columns: repeat(2, 1fr); }
.kpd-tl { position: relative; display: flex; flex-direction: column; align-items: center; text-align: center; gap: 10px; }
.kpd-tl::before {
  content: ""; position: absolute; top: 17px; right: 50%; width: 100%; height: 3px;
  background: var(--border); z-index: 0;
}
.kpd-tl:first-child::before { display: none; }
.kpd-tl.done::before, .kpd-tl.current::before { background: var(--green); }
.kpd-tl-dot {
  position: relative; z-index: 1;
  width: 36px; height: 36px; border-radius: 50%;
  display: grid; place-items: center;
  background: var(--card); border: 2px solid var(--border); color: #9aa7a0;
}
.kpd-tl.done .kpd-tl-dot { background: var(--green); border-color: var(--green); color: #fff; }
.kpd-tl.current .kpd-tl-dot { background: var(--ink); border-color: var(--ink); color: #fff; box-shadow: 0 0 0 5px rgba(12,17,14,.08); }
.kpd-tl-label { font-size: 12px; font-weight: 700; color: var(--muted); }
.kpd-tl.done .kpd-tl-label, .kpd-tl.current .kpd-tl-label { color: var(--ink); }
.kpd-tl-date { margin-top: -6px; font-size: 11px; color: var(--muted); }

/* ── Items ── */
.kpd-items { list-style: none; margin: 0; padding: 0; }
.kpd-item { display: flex; align-items: center; gap: 14px; padding: 14px 0; border-top: 1px solid var(--border); }
.kpd-item:first-child { border-top: 0; padding-top: 0; }
.kpd-item:last-child { padding-bottom: 0; }
.kpd-thumb {
  width: 56px; height: 56px; flex: none; border-radius: 14px; overflow: hidden;
  background: linear-gradient(135deg, var(--green-faint), #dcefe5);
  color: var(--green); display: grid; place-items: center;
  font-size: 16px; font-weight: 900; letter-spacing: -.02em;
}
.kpd-thumb img { width: 100%; height: 100%; object-fit: cover; }
.kpd-item-body { flex: 1; min-width: 0; }
.kpd-item-name { margin: 0; font-size: 14px; font-weight: 700; color: var(--ink); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.kpd-tags { margin-top: 6px; display: flex; flex-wrap: wrap; gap: 6px; }
.kpd-tag { font-size: 11px; font-weight: 600; color: var(--text); background: var(--bg); border-radius: 6px; padding: 3px 8px; }
.kpd-item-sku { margin: 6px 0 0; font-size: 11px; color: var(--muted); font-family: ui-monospace, SFMono-Regular, Menlo, monospace; }
.kpd-item-price { text-align: right; flex: none; }
.kpd-item-price strong { display: block; font-size: 14px; font-weight: 800; font-variant-numeric: tabular-nums; }
.kpd-item-price span { display: block; margin-top: 2px; font-size: 11px; color: var(--muted); }
.kpd-empty { font-size: 13px; color: var(--muted); }

/* ── Info grid ── */
.kpd-info { display: grid; gap: 16px; grid-template-columns: repeat(2, minmax(0,1fr)); margin: 0; }
.kpd-info dt { font-size: 11px; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: var(--muted); }
.kpd-info dd { margin: 4px 0 0; font-size: 14px; font-weight: 700; color: var(--ink); overflow-wrap: anywhere; }
.kpd-copy { margin-left: 6px; padding: 2px 8px; border: 0; border-radius: 6px; background: var(--green-faint); color: var(--green); font-size: 11px; font-weight: 700; cursor: pointer; }
.kpd-copy:hover { background: #d7efe2; }

/* ── Summary ── */
.kpd-sum { margin: 0; }
.kpd-sum-row { display: flex; justify-content: space-between; gap: 12px; padding: 7px 0; font-size: 13px; color: var(--muted); }
.kpd-sum-row dd { margin: 0; font-weight: 600; color: var(--ink); font-variant-numeric: tabular-nums; }
.kpd-sum-row.grand { margin-top: 8px; padding-top: 16px; border-top: 1px dashed var(--border); align-items: baseline; }
.kpd-sum-row.grand dt { font-size: 14px; font-weight: 800; color: var(--ink); }
.kpd-sum-row.grand dd { font-size: 24px; font-weight: 900; letter-spacing: -.03em; }
.kpd-sum-row.grand dd small { font-size: 12px; font-weight: 700; color: var(--muted); margin-right: 4px; }
.kpd-free { color: var(--green) !important; }
.kpd-side-actions { margin-top: 20px; display: flex; flex-direction: column; gap: 10px; }

.kpd-help { display: flex; align-items: center; gap: 12px; }
.kpd-help-icon { width: 40px; height: 40px; flex: none; border-radius: 12px; background: var(--green-faint); color: var(--green); display: grid; place-items: center; }
.kpd-help p { margin: 0; font-size: 13px; color: var(--muted); line-height: 1.5; }
.kpd-help a { color: var(--green); font-weight: 700; text-decoration: none; }
.kpd-help a:hover { color: var(--green-dark); }

@media (max-width: 600px) {
  .kpd-title { font-size: 24px; }
  .kpd-card { padding: 18px; border-radius: 18px; }
  .kpd-head-actions { width: 100%; display: grid; grid-template-columns: 1fr 1fr; }
  .kpd-tl-label { font-size: 11px; }
  .kpd-tl-date { display: none; }
  .kpd-info { grid-template-columns: 1fr; }
}
@media (prefers-reduced-motion: reduce) {
  .kpd *, .kpd *::before, .kpd *::after { transition: none !important; }
}
</style>
@endpush

@php
  $statusMap = [
    'pending_payment' => ['Awaiting payment', 'amber', 0],
    'paid'            => ['Paid',             'green', 1],
    'confirmed'       => ['Confirmed',        'green', 1],
    'processing'      => ['Preparing',        'green', 1],
    'shipped'         => ['Preparing',        'green', 1],
    'delivered'       => ['Completed',        'green', 1],
    'completed'       => ['Completed',        'green', 1],
    'failed'          => ['Payment failed',   'red',  -1],
    'cancelled'       => ['Cancelled',        'red',  -1],
    'refunded'        => ['Refunded',         'red',  -1],
  ];
  $s = $order?->status->value;
  [$statusLabel, $tone, $step] = $statusMap[$s] ?? [ucfirst(str_replace('_', ' ', (string) $s)), 'neutral', 0];
  // No shipping or delivery steps: progress is Placed → Paid.
  $timeline = [
    ['label' => 'Placed',    'date' => $order?->placed_at],
    ['label' => 'Paid',      'date' => null],
  ];
  $itemCount = $order ? (int) $order->items->sum('quantity') : 0;
  $check = '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>';
@endphp

@section('content')
<div data-order-detail data-order-number="{{ request()->route('orderId') }}" class="kpd mx-auto max-w-6xl px-4 pb-20 pt-10 sm:px-6 lg:px-8">

  <a href="{{ route('account.orders') }}" class="kpd-back">
    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 12H5m7 7-7-7 7-7"/></svg>
    Back to orders
  </a>

  {{-- ── Header ── --}}
  <div class="kpd-head">
    <div>
      <p class="kpd-overline">Order details</p>
      <div class="kpd-title-row">
        <h1 class="kpd-title">Order <span>#{{ request()->route('orderId') }}</span></h1>
        @if($order)
          <span class="kpd-badge tone-{{ $tone }}"><span class="kpd-badge-dot" aria-hidden="true"></span>{{ $statusLabel }}</span>
        @endif
      </div>
      @if($order)
        <p class="kpd-sub">
          Placed {{ $order->placed_at?->format('j M Y, H:i') }} · {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
        </p>
      @endif
    </div>
    @if($order)
      <div class="kpd-head-actions">
        <a href="{{ route('shop') }}" class="kpd-btn kpd-btn-soft">Continue shopping</a>
        <a href="{{ route('order-status', $order->order_number) }}" class="kpd-btn kpd-btn-dark">
          Track order
          <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
        </a>
      </div>
    @endif
  </div>

  <div data-order-detail-content>
    @if($order)
    <div data-server-detail class="kpd-grid">

      {{-- ── Main column ── --}}
      <div class="kpd-col">

        @if($s === 'pending_payment')
          <div class="kpd-alert" role="status">
            <span class="kpd-alert-icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
            </span>
            <div>
              <h3>Waiting for your payment</h3>
              <p>We'll start preparing your order as soon as payment is confirmed. Pay with M-Pesa, Tigo, Airtel or Halo.</p>
            </div>
          </div>
        @elseif($step < 0)
          <div class="kpd-alert red" role="status">
            <span class="kpd-alert-icon" aria-hidden="true">
              <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
            </span>
            <div>
              <h3>{{ $statusLabel }}</h3>
              <p>
                @if($s === 'refunded') Your money has been returned to your original payment method.
                @elseif($s === 'failed') The payment didn't go through, so no money was charged.
                @else This order was cancelled and won't be shipped.
                @endif
              </p>
            </div>
          </div>
        @endif

        {{-- Progress --}}
        @if($step >= 0)
          <section class="kpd-card" aria-label="Order progress">
            <div class="kpd-card-title"><h2>Progress</h2></div>
            <ol class="kpd-timeline">
              @foreach($timeline as $i => $t)
                @php $state = $i <= $step ? 'done' : ($i === $step + 1 ? 'current' : ''); @endphp
                <li class="kpd-tl {{ $state }}">
                  <span class="kpd-tl-dot" aria-hidden="true">
                    @if($state === 'done') {!! $check !!} @else <strong style="font-size:12px">{{ $i + 1 }}</strong> @endif
                  </span>
                  <span class="kpd-tl-label">{{ $t['label'] }}</span>
                  @if($t['date'])<span class="kpd-tl-date">{{ $t['date']->format('j M') }}</span>@endif
                </li>
              @endforeach
            </ol>
          </section>
        @endif

        {{-- Items --}}
        <section class="kpd-card">
          <div class="kpd-card-title">
            <h2>Items</h2>
            <small>{{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}</small>
          </div>
          <ul class="kpd-items">
            @forelse($order->items as $item)
              @php
                $words = preg_split('/\s+/', trim((string) $item->product_name));
                $initials = strtoupper(substr($words[0] ?? 'K', 0, 1) . substr($words[1] ?? '', 0, 1));
                $image = $item->image_url ?? $item->product?->image_url ?? null;
              @endphp
              <li class="kpd-item">
                <div class="kpd-thumb" aria-hidden="true">
                  @if($image)<img src="{{ $image }}" alt="" loading="lazy">@else{{ $initials }}@endif
                </div>
                <div class="kpd-item-body">
                  <p class="kpd-item-name">{{ $item->product_name }}</p>
                  <div class="kpd-tags">
                    @if($item->size)<span class="kpd-tag">Size {{ $item->size }}</span>@endif
                    @if($item->color)<span class="kpd-tag">{{ $item->color }}</span>@endif
                    <span class="kpd-tag">Qty {{ $item->quantity }}</span>
                  </div>
                  @if($item->sku)<p class="kpd-item-sku">SKU {{ $item->sku }}</p>@endif
                </div>
                <div class="kpd-item-price">
                  <strong>TZS {{ number_format($item->line_total) }}</strong>
                  @if($item->quantity > 1)
                    <span>TZS {{ number_format($item->line_total / max(1, $item->quantity)) }} each</span>
                  @endif
                </div>
              </li>
            @empty
              <li class="kpd-empty">No items recorded for this order.</li>
            @endforelse
          </ul>
        </section>
      </div>

      {{-- ── Side column ── --}}
      <aside class="kpd-col kpd-sticky">
        <section class="kpd-card">
          <div class="kpd-card-title"><h2>Summary</h2></div>
          <dl class="kpd-sum">
            <div class="kpd-sum-row"><dt>Subtotal</dt><dd>TZS {{ number_format($order->subtotal) }}</dd></div>
            <div class="kpd-sum-row grand"><dt>Total</dt><dd><small>TZS</small>{{ number_format($order->total) }}</dd></div>
          </dl>

          @if($s === 'pending_payment')
            <div class="kpd-side-actions">
              <a href="{{ route('order-status', $order->order_number) }}" class="kpd-btn kpd-btn-pay kpd-btn-block">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="14" height="20" x="5" y="2" rx="2"/><path d="M12 18h.01"/></svg>
                Continue payment
              </a>
              <button type="button" data-cancel-order-detail data-order-number="{{ $order->order_number }}" class="kpd-btn kpd-btn-danger kpd-btn-block">Cancel order</button>
            </div>
          @endif
        </section>

        <section class="kpd-card kpd-help">
          <span class="kpd-help-icon" aria-hidden="true">
            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><path d="M12 17h.01"/></svg>
          </span>
          <p>Need help with this order? <a href="{{ route('account.returns') }}">Returns &amp; support</a></p>
        </section>
      </aside>
    </div>
    @endif
  </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const toast = msg => window.dispatchEvent(new CustomEvent('kp:toast', { detail: msg }));

  // Copy tracking number
  document.querySelectorAll('[data-copy]').forEach(b => {
    b.addEventListener('click', async () => {
      try {
        await navigator.clipboard.writeText(b.dataset.copy || '');
        b.textContent = 'Copied';
        setTimeout(() => { b.textContent = 'Copy'; }, 1600);
      } catch { toast('Could not copy tracking number.'); }
    });
  });

  // Cancel order (unchanged API flow)
  const btn = document.querySelector('[data-cancel-order-detail]');
  if (!btn) return;
  const getCookie = name => {
    const m = document.cookie.match(new RegExp('(?:^|;\\s*)' + name + '=([^;]*)'));
    return m ? decodeURIComponent(m[1]) : null;
  };
  btn.addEventListener('click', async () => {
    if (!(await window.kpConfirm?.('Cancel this order?', { title: 'Cancel this order?' }))) return;
    btn.disabled = true;
    btn.textContent = 'Cancelling…';
    try {
      const orderNumber = btn.dataset.orderNumber || window.location.pathname.split('/').pop();
      const headers = new Headers({ Accept: 'application/json' });
      const xsrf = getCookie('XSRF-TOKEN');
      if (xsrf) headers.set('X-XSRF-TOKEN', xsrf);
      const res = await fetch(`/api/v1/orders/${encodeURIComponent(orderNumber)}/cancel`, {
        method: 'POST', credentials: 'include', headers,
      });
      if (!res.ok) {
        let msg = `Request failed (${res.status})`;
        try { const d = await res.json(); msg = d?.message || msg; } catch {}
        throw new Error(msg);
      }
      window.location.reload();
    } catch (e) {
      btn.disabled = false;
      btn.textContent = 'Cancel order';
      toast(e.message || 'Unable to cancel order.');
    }
  });
})();
</script>
@endpush
