{{-- resources/views/account/orders.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
/* ============================================================
   KP WEAR — My Orders
   ============================================================ */
.kpo {
  --ink: #0c110e; --text: #1a231e; --muted: #5a6660;
  --border: #e2e8e4; --bg: #f5f8f6; --card: #ffffff;
  --green: #1a7a52; --green-dark: #145f40; --green-faint: #eef8f3; --green-faint2: #f5fbf8;
  --amber: #b45309; --amber-faint: #fffbeb; --amber-border: #fde68a;
  --blue: #1d4ed8; --blue-faint: #eff6ff;
  --red: #dc2626; --red-faint: #fef2f2;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
  color: var(--ink);
  min-width: 0;
  max-width: 1024px;
}
.kpo * { box-sizing: border-box; }
.kpo [hidden] { display: none !important; }
.kpo a, .kpo button { transition: background .18s, color .18s, border-color .18s, transform .15s, box-shadow .18s; font: inherit; }
.kpo a:focus-visible, .kpo button:focus-visible { outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px; }

/* ── Header ── */
.kpo-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 16px; }
.kpo-overline { margin: 0; font-size: 11px; font-weight: 800; letter-spacing: .22em; text-transform: uppercase; color: var(--green); }
.kpo-title { margin: 8px 0 0; font-size: 26px; font-weight: 900; letter-spacing: -.035em; line-height: 1.1; color: var(--ink); }
.kpo-sub { margin: 6px 0 0; font-size: 14px; color: var(--muted); }
.kpo-shop {
  display: inline-flex; align-items: center; gap: 8px;
  height: 44px; padding: 0 18px; border-radius: 999px;
  background: var(--ink); color: #fff; font-size: 13px; font-weight: 700; text-decoration: none;
}
.kpo-shop:hover { background: var(--green-dark); transform: translateY(-1px); }

/* ── Filter chips ── */
.kpo-filters {
  margin-top: 24px; display: flex; gap: 8px; overflow-x: auto;
  padding-bottom: 4px; scrollbar-width: none;
}
.kpo-filters::-webkit-scrollbar { display: none; }
.kpo-chip {
  flex: none; display: inline-flex; align-items: center; gap: 8px;
  height: 38px; padding: 0 14px; border-radius: 999px;
  border: 1px solid var(--border); background: var(--card);
  font-size: 13px; font-weight: 600; color: var(--text); cursor: pointer;
}
.kpo-chip:hover { border-color: #b0c4b8; background: var(--green-faint2); }
.kpo-chip[aria-pressed="true"] { background: var(--ink); border-color: var(--ink); color: #fff; }
.kpo-chip-count {
  min-width: 20px; height: 20px; padding: 0 6px; border-radius: 999px;
  display: inline-grid; place-items: center;
  background: var(--bg); color: var(--muted); font-size: 11px; font-weight: 800;
}
.kpo-chip[aria-pressed="true"] .kpo-chip-count { background: rgba(255,255,255,.16); color: #fff; }

/* ── Order list ── */
.kpo-list { margin-top: 18px; display: grid; grid-template-columns: minmax(0,1fr); gap: 12px; }
@media (min-width: 768px) { .kpo-list { grid-template-columns: repeat(2, minmax(0,1fr)); } }
@media (min-width: 1200px) { .kpo-list { grid-template-columns: repeat(3, minmax(0,1fr)); } }
.kpo-card {
  position: relative; overflow: hidden;
  background: var(--card); border: 1px solid var(--border); border-radius: 18px;
  padding: 18px;
  box-shadow: 0 1px 3px rgba(10,16,12,.05);
  transition: border-color .18s, box-shadow .18s, transform .18s;
}
.kpo-card:hover { border-color: rgba(26,122,82,.22); box-shadow: 0 10px 30px -14px rgba(10,16,12,.18); transform: translateY(-1px); }
/* coloured status rail on the left edge */
.kpo-rail { position: absolute; inset: 0 auto 0 0; width: 3px; background: var(--border); }
.tone-amber .kpo-rail { background: #f59e0b; }
.tone-green .kpo-rail { background: var(--green); }
.tone-blue  .kpo-rail { background: #3b82f6; }
.tone-red   .kpo-rail { background: #f87171; }

.kpo-row { display: flex; flex-direction: column; gap: 12px; }

/* ── Product thumbnails ── */
.kpo-thumbs { display: flex; align-items: center; flex: none; }
.kpo-thumbs img {
  width: 52px; height: 52px; border-radius: 14px; object-fit: cover;
  border: 2px solid var(--card); box-shadow: 0 1px 3px rgba(10,16,12,.15);
  background: var(--bg);
}
.kpo-thumbs img + img { margin-left: -12px; }

.kpo-main { min-width: 0; }
.kpo-top { display: flex; align-items: center; justify-content: space-between; gap: 10px; }
.kpo-num {
  font-family: ui-monospace, SFMono-Regular, Menlo, monospace;
  font-size: 14px; font-weight: 800; letter-spacing: -.01em; color: var(--ink); text-decoration: none;
}
.kpo-num:hover { color: var(--green); }
.kpo-meta { margin: 4px 0 0; font-size: 11px; color: var(--muted); }
.kpo-meta span { margin: 0 5px; color: #c3cdc7; }

.kpo-badge {
  display: inline-flex; align-items: center; gap: 5px;
  height: 24px; padding: 0 10px; border-radius: 999px;
  font-size: 11px; font-weight: 700; white-space: nowrap;
  background: var(--bg); color: var(--text); flex: none;
}
.kpo-badge-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; }
.kpo-badge.tone-amber { background: var(--amber-faint); color: var(--amber); }
.kpo-badge.tone-green { background: var(--green-faint); color: var(--green); }
.kpo-badge.tone-blue  { background: var(--blue-faint);  color: var(--blue); }
.kpo-badge.tone-red   { background: var(--red-faint);   color: var(--red); }

.kpo-items {
  margin: 6px 0 0; font-size: 12px; font-weight: 500; color: var(--text); line-height: 1.5;
  display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}

.kpo-side {
  display: flex; flex-wrap: wrap; align-items: center; gap: 8px;
  border-top: 1px dashed var(--border); padding-top: 12px; margin-top: 2px;
}
.kpo-total { font-size: 16px; font-weight: 900; letter-spacing: -.02em; color: var(--ink); font-variant-numeric: tabular-nums; white-space: nowrap; margin-right: auto; }
.kpo-total small { font-size: 10px; font-weight: 700; color: var(--muted); margin-right: 3px; }
.kpo-link { font-size: 12px; font-weight: 700; color: var(--green); text-decoration: none; white-space: nowrap; }
.kpo-link:hover { color: var(--green-dark); }
.kpo-pay {
  display: inline-flex; align-items: center; gap: 5px;
  height: 30px; padding: 0 12px; border-radius: 9px;
  font-size: 12px; font-weight: 700; text-decoration: none; white-space: nowrap;
  background: #d97706; color: #fff;
}
.kpo-pay:hover { background: var(--amber); }

.kpo-alert {
  margin: 10px 0 0; padding: 8px 12px; border-radius: 10px;
  background: var(--red-faint); color: #991b1b; font-size: 12px; line-height: 1.5;
}

/* ── Empty ── */
.kpo-empty {
  margin-top: 24px; padding: 56px 24px; text-align: center;
  border: 1px dashed #cfd9d3; border-radius: 24px;
  background: radial-gradient(circle at 50% 0%, var(--green-faint) 0%, var(--card) 60%);
}
.kpo-empty-icon {
  width: 64px; height: 64px; margin: 0 auto 18px; border-radius: 20px;
  display: grid; place-items: center; background: var(--card); color: var(--green);
  border: 1px solid var(--border); box-shadow: 0 8px 24px -12px rgba(26,122,82,.4);
}
.kpo-empty h2 { margin: 0; font-size: 19px; font-weight: 900; letter-spacing: -.02em; }
.kpo-empty p { margin: 6px auto 20px; max-width: 340px; font-size: 13px; color: var(--muted); line-height: 1.6; }
.kpo-filter-empty { padding: 28px; text-align: center; font-size: 13px; color: var(--muted); border: 1px dashed var(--border); border-radius: 18px; }

.kpo-pagination { margin-top: 24px; }

@media (max-width: 600px) {
  .kpo-title { font-size: 26px; }
  .kpo-card { padding: 16px; border-radius: 16px; }
  .kpo-thumbs img { width: 44px; height: 44px; }
}
@media (prefers-reduced-motion: reduce) {
  .kpo *, .kpo *::before, .kpo *::after { transition: none !important; }
}
</style>
@endpush

@php
  // status value => [label, tone, filter group]. The badge carries the
  // state — no progress bars on list cards (full timeline lives on the
  // order detail page). No shipping step: we don't ship or track delivery.
  $statusMap = [
    'pending_payment' => ['Awaiting payment', 'amber', 'pay'],
    'paid'            => ['Paid',             'green', 'active'],
    'confirmed'       => ['Confirmed',        'green', 'active'],
    'processing'      => ['Preparing',        'green', 'active'],
    'shipped'         => ['Preparing',        'green', 'active'],
    'delivered'       => ['Completed',        'green', 'done'],
    'completed'       => ['Completed',        'green', 'done'],
    'failed'          => ['Payment failed',   'red',   'closed'],
    'cancelled'       => ['Cancelled',        'red',   'closed'],
    'refunded'        => ['Refunded',         'red',   'closed'],
  ];
  $filters = [
    'all'    => 'All orders',
    'pay'    => 'To pay',
    'active' => 'In progress',
    'done'   => 'Completed',
    'closed' => 'Cancelled',
  ];
@endphp

@section('content')
<div data-orders-page class="w-full px-4 pb-20 pt-10 sm:px-6 lg:px-8">
  <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('components.account.sidebar')

    <section class="kpo">
      {{-- ── Header ── --}}
      <div class="kpo-head">
        <div>
          <p class="kpo-overline">Your account</p>
          <h1 class="kpo-title">My Orders</h1>
          <p class="kpo-sub">Finish payments and view every purchase.</p>
        </div>
        <a href="{{ route('shop') }}" class="kpo-shop">
          Continue shopping
          <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
        </a>
      </div>

      @if(count($orders))
        {{-- ── Filters (counts filled in by the script below) ── --}}
        <div class="kpo-filters" role="toolbar" aria-label="Filter orders">
          @foreach($filters as $key => $label)
            <button type="button" class="kpo-chip" data-filter="{{ $key }}" aria-pressed="{{ $key === 'all' ? 'true' : 'false' }}">
              {{ $label }}
              <span class="kpo-chip-count" data-count>0</span>
            </button>
          @endforeach
        </div>
      @endif

      <div data-orders-list class="kpo-list">
        @forelse($orders as $order)
          @php
            $s = $order->status->value;
            [$label, $tone, $group] = $statusMap[$s] ?? [ucfirst(str_replace('_', ' ', $s)), 'neutral', 'active'];
            $isClosed = in_array($s, ['failed', 'cancelled', 'refunded'], true);
            $itemCount = (int) $order->items->sum('quantity');
          @endphp
          <article data-server-order="{{ $order->order_number }}" data-group="{{ $group }}" class="kpo-card tone-{{ $tone }}">
            <span class="kpo-rail" aria-hidden="true"></span>

            <div class="kpo-row">
              @php
                $thumbs = $order->items->map(fn ($it) => $it->product?->image_url)->filter()->unique()->take(3);
              @endphp
              @if($thumbs->isNotEmpty())
                <div class="kpo-thumbs" aria-hidden="true">
                  @foreach($thumbs as $src)
                    <img src="{{ $src }}" alt="" loading="lazy">
                  @endforeach
                </div>
              @endif

              <div class="kpo-main">
                <div class="kpo-top">
                  <a href="{{ route('account.order-detail', $order->order_number) }}" class="kpo-num">#{{ $order->order_number }}</a>
                  <span class="kpo-badge tone-{{ $tone }}">
                    <span class="kpo-badge-dot" aria-hidden="true"></span>
                    {{ $label }}
                  </span>
                </div>
                <p class="kpo-meta">
                  Placed {{ $order->placed_at?->format('j M Y') }}
                  <span>·</span>
                  {{ $itemCount }} {{ \Illuminate\Support\Str::plural('item', $itemCount) }}
                </p>
                <p class="kpo-items">{{ $order->items->pluck('product_name')->unique()->implode(', ') }}</p>
              </div>

              <div class="kpo-side">
                <span class="kpo-total"><small>TZS</small>{{ number_format($order->total) }}</span>
                <a href="{{ route('account.order-detail', $order->order_number) }}" class="kpo-link">View details</a>
                @if($s === 'pending_payment')
                  <a href="{{ route('order-status', $order->order_number) }}" class="kpo-pay">Continue payment</a>
                @endif
              </div>
            </div>

            @if($isClosed)
              <p class="kpo-alert">
                @if($s === 'refunded')
                  This order was refunded. The money has been returned to your payment method.
                @elseif($s === 'failed')
                  Payment didn't go through, so this order wasn't placed. No money was charged.
                @else
                  This order was cancelled. No further action is needed.
                @endif
              </p>
            @endif
          </article>
        @empty
          <div class="kpo-empty">
            <div class="kpo-empty-icon">
              <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
            </div>
            <h2>No orders yet</h2>
            <p>When you place an order it will show up here, so you can pay, track and review it anytime.</p>
            <a href="{{ route('shop') }}" class="kpo-shop">Start shopping</a>
          </div>
        @endforelse

        <p data-filter-empty class="kpo-filter-empty" hidden>No orders in this category.</p>
      </div>

      @if($orders instanceof \Illuminate\Contracts\Pagination\Paginator && $orders->hasPages())
        <div class="kpo-pagination">{{ $orders->links() }}</div>
      @endif
    </section>
  </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const root = document.querySelector('[data-orders-page]');
  if (!root) return;
  const cards = [...root.querySelectorAll('[data-server-order]')];
  const chips = [...root.querySelectorAll('[data-filter]')];
  const empty = root.querySelector('[data-filter-empty]');
  const inGroup = (card, group) => group === 'all' || card.dataset.group === group;

  chips.forEach(chip => {
    const group = chip.dataset.filter;
    const count = cards.filter(c => inGroup(c, group)).length;
    const badge = chip.querySelector('[data-count]');
    if (badge) badge.textContent = count;
    // Hide empty categories to keep the bar short (but never "All").
    if (group !== 'all' && count === 0) chip.hidden = true;

    chip.addEventListener('click', () => {
      chips.forEach(c => c.setAttribute('aria-pressed', c === chip ? 'true' : 'false'));
      let visible = 0;
      cards.forEach(c => {
        const show = inGroup(c, group);
        c.hidden = !show;
        if (show) visible++;
      });
      if (empty) empty.hidden = visible > 0;
    });
  });
})();
</script>
@endpush
