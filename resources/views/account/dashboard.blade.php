{{-- resources/views/account/dashboard.blade.php --}}
@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
/* ── KP WEAR Account Dashboard ── */
.kpad {
  --ink: #0c110e; --text: #1a231e; --muted: #5a6660;
  --border: #e2e8e4; --bg: #f5f8f6; --card: #ffffff;
  --green: #1a7a52; --green-dark: #145f40; --green-faint: #eef8f3; --green-faint2: #f5fbf8;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
}
.kpad * { box-sizing: border-box; }
.kpad a { transition: color .16s, background .16s, opacity .16s; }
.kpad a:focus-visible { outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px; }

/* stat cards */
.kpad-stats { display: grid; grid-template-columns: repeat(2,1fr); gap: 12px; }
@media (min-width: 640px) { .kpad-stats { grid-template-columns: repeat(4,1fr); } }
.kpad-stat {
  background: var(--card); border: 1px solid var(--border);
  border-radius: 18px; padding: 20px;
  box-shadow: 0 1px 3px rgba(10,16,12,.05);
  text-decoration: none; display: flex; flex-direction: column;
  transition: border-color .18s, background .18s;
}
.kpad-stat:hover { border-color: rgba(26,122,82,.2); background: rgba(238,248,243,.3); }
.kpad-stat-top { display: flex; align-items: flex-start; justify-content: space-between; }
.kpad-stat-icon { color: var(--muted); }
.kpad-stat-arrow { color: rgba(90,102,96,.3); }
.kpad-stat-val { margin-top: 16px; font-size: 26px; font-weight: 900; letter-spacing: -.03em; color: var(--ink); }
.kpad-stat-label { margin-top: 4px; font-size: 12px; color: var(--muted); font-weight: 500; }

/* section header */
.kpad-section-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
.kpad-section-head h2 { margin: 0; font-size: 18px; font-weight: 900; letter-spacing: -.025em; color: var(--ink); }
.kpad-view-all { font-size: 13px; font-weight: 700; color: var(--green); text-decoration: none; display: inline-flex; align-items: center; gap: 4px; }
.kpad-view-all:hover { color: var(--green-dark); }

/* order rows */
.kpad-orders { display: flex; flex-direction: column; gap: 10px; }
.kpad-order {
  display: flex; align-items: flex-start; justify-content: space-between; gap: 16px;
  border: 1px solid var(--border); border-radius: 18px;
  background: var(--card); padding: 16px 20px;
  box-shadow: 0 1px 3px rgba(10,16,12,.05);
  text-decoration: none;
  transition: border-color .18s, background .18s;
}
.kpad-order:hover { border-color: rgba(26,122,82,.2); background: rgba(238,248,243,.2); }
.kpad-order-left { min-width: 0; flex: 1; }
.kpad-order-top { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.kpad-order-num { font-size: 14px; font-weight: 800; color: var(--ink); }
.kpad-badge { display: inline-flex; align-items: center; gap: 4px; border-radius: 999px; padding: 2px 8px; font-size: 11px; font-weight: 700; }
.kpad-badge.shipped   { background: #eff6ff; color: #1d4ed8; }
.kpad-badge.delivered { background: var(--green-faint); color: var(--green); }
.kpad-badge.confirmed { background: var(--green-faint); color: var(--green); }
.kpad-badge.pending   { background: #fef9c3; color: #a16207; }
.kpad-order-items { margin-top: 4px; font-size: 12px; color: var(--muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; max-width: 280px; }
.kpad-order-date  { margin-top: 6px; font-size: 11px; color: var(--muted); }
.kpad-order-right { text-align: right; flex: none; }
.kpad-order-total { font-size: 14px; font-weight: 900; color: var(--ink); }

/* address + payment cards */
.kpad-2col { display: grid; grid-template-columns: 1fr; gap: 12px; }
@media (min-width: 768px) { .kpad-2col { grid-template-columns: 1fr 1fr; } }
.kpad-infocard {
  background: var(--card); border: 1px solid var(--border);
  border-radius: 18px; padding: 20px;
  box-shadow: 0 1px 3px rgba(10,16,12,.05);
}
.kpad-infocard-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 16px; }
.kpad-infocard-title { display: flex; align-items: center; gap: 10px; }
.kpad-infocard-icon {
  width: 32px; height: 32px; border-radius: 10px;
  background: var(--green-faint); color: var(--green);
  display: grid; place-items: center; flex: none;
}
.kpad-infocard-head h3 { margin: 0; font-size: 14px; font-weight: 800; color: var(--ink); }
.kpad-edit { font-size: 12px; font-weight: 800; color: var(--green); text-decoration: none; }
.kpad-edit:hover { color: var(--green-dark); }
.kpad-addr-name { font-size: 13px; font-weight: 700; color: var(--ink); }
.kpad-addr-meta { margin-top: 4px; font-size: 12px; color: var(--muted); line-height: 1.65; }
.kpad-card-chip {
  width: 40px; height: 28px; border-radius: 6px;
  background: var(--ink); display: grid; place-items: center; flex: none;
}
.kpad-card-chip span { font-size: 9px; font-weight: 900; color: var(--card); letter-spacing: .02em; }
.kpad-card-row { display: flex; align-items: center; gap: 12px; }
.kpad-card-num { font-size: 14px; font-weight: 700; color: var(--ink); letter-spacing: .12em; }
.kpad-card-exp { font-size: 12px; color: var(--muted); margin-top: 3px; }

/* quick actions */
.kpad-actions { display: grid; grid-template-columns: repeat(2,1fr); gap: 10px; }
@media (min-width: 640px) { .kpad-actions { grid-template-columns: repeat(4,1fr); } }
.kpad-action {
  display: flex; flex-direction: column; align-items: center; gap: 10px;
  border: 1px solid var(--border); border-radius: 18px;
  background: var(--card); padding: 18px 12px; text-align: center;
  text-decoration: none;
  transition: border-color .18s, background .18s;
}
.kpad-action:hover { border-color: rgba(26,122,82,.2); background: rgba(238,248,243,.3); }
.kpad-action-icon { color: var(--muted); }
.kpad-action:hover .kpad-action-icon { color: var(--green); }
.kpad-action-label { font-size: 12px; font-weight: 600; color: var(--ink); }

/* section spacing */
.kpad-section { margin-top: 28px; }

/* section label */
.kpad-overline { font-size: 10px; font-weight: 800; letter-spacing: .2em; text-transform: uppercase; color: var(--muted); margin: 0 0 12px; }
</style>
@endpush

@section('content')
<div class="w-full px-4 pb-20 pt-12 sm:px-6 lg:px-8">
  <div class="grid gap-8 lg:grid-cols-[300px_minmax(0,1fr)]">
    @include('components.account.sidebar')

    <div class="kpad">

      {{-- ── Welcome ── --}}
      <div>
        <p class="text-[11px] font-bold uppercase tracking-[.22em]" style="color:var(--green)">My account</p>
        <h1 data-account-name class="mt-2 text-3xl font-black tracking-tight" style="color:var(--ink);letter-spacing:-.03em">
          Welcome back, {{ explode(' ', $user->name ?? 'Customer')[0] }}
        </h1>
        <p class="mt-1 text-sm" style="color:var(--muted)">
          {{ $user->phone }}
          <span style="margin:0 8px;color:var(--border)">·</span>
          {{ $user->email }}
        </p>
      </div>

      {{-- ── Stats ── --}}
      <div class="kpad-stats kpad-section">
        @foreach([
          ['label' => 'Total orders',    'value' => $totalOrders,         'route' => 'account.orders',    'icon' => 'package'],
          ['label' => 'Wishlist',        'value' => $wishlistCount ?? 0,  'route' => 'wishlist',          'icon' => 'heart'],
          ['label' => 'Loyalty points',  'value' => number_format($loyalty->points ?? 0), 'route' => 'account.loyalty', 'icon' => 'star'],
          ['label' => 'Saved addresses', 'value' => $addressCount ?? 0,   'route' => 'account.addresses', 'icon' => 'map-pin'],
        ] as $stat)
        <a href="{{ route($stat['route']) }}" class="kpad-stat">
          <div class="kpad-stat-top">
            <span class="kpad-stat-icon">
              @include('components.account._icons.' . $stat['icon'], ['size' => 18])
            </span>
            <svg class="kpad-stat-arrow" xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
          </div>
          <p class="kpad-stat-val">{{ $stat['value'] }}</p>
          <p class="kpad-stat-label">{{ $stat['label'] }}</p>
        </a>
        @endforeach
      </div>

      {{-- ── Recent orders ── --}}
      @if(isset($recentOrders) && $recentOrders->isNotEmpty())
      <section class="kpad-section">
        <div class="kpad-section-head">
          <h2>Recent orders</h2>
          <a href="{{ route('account.orders') }}" class="kpad-view-all">
            View all
            <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14m-7-7 7 7-7 7"/></svg>
          </a>
        </div>
        <div class="kpad-orders">
          @foreach($recentOrders as $order)
          @php $s = $order->status->value; @endphp
          <a href="{{ route('account.order-detail', $order->order_number) }}" class="kpad-order">
            <div class="kpad-order-left">
              <div class="kpad-order-top">
                <span class="kpad-order-num">{{ $order->order_number }}</span>
                <span class="kpad-badge {{ $s }}">
                  {{ ucfirst(str_replace('_', ' ', $s)) }}
                </span>
              </div>
              <p class="kpad-order-items">{{ $order->items->pluck('product_name')->unique()->implode(', ') }}</p>
              <p class="kpad-order-date">{{ $order->created_at->format('d M Y') }}</p>
            </div>
            <div class="kpad-order-right">
              <p class="kpad-order-total">TZS {{ number_format($order->total) }}</p>
            </div>
          </a>
          @endforeach
        </div>
      </section>
      @endif

      {{-- ── Address + Payment ── --}}
      <div class="kpad-2col kpad-section">
        @if(isset($defaultAddress))
        <section class="kpad-infocard">
          <div class="kpad-infocard-head">
            <div class="kpad-infocard-title">
              <span class="kpad-infocard-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12S4 16 4 10a8 8 0 1 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg>
              </span>
              <h3>Default address</h3>
            </div>
            <a href="{{ route('account.addresses') }}" class="kpad-edit">Edit</a>
          </div>
          <p class="kpad-addr-name">{{ $defaultAddress->recipient_name }}</p>
          <p class="kpad-addr-meta">
            {{ $defaultAddress->phone }}<br>
            {{ $defaultAddress->street }}<br>
            {{ collect([$defaultAddress->ward, $defaultAddress->district, $defaultAddress->region])->filter()->implode(', ') }}
          </p>
        </section>
        @endif

        @if(isset($defaultPayment))
        <section class="kpad-infocard">
          <div class="kpad-infocard-head">
            <div class="kpad-infocard-title">
              <span class="kpad-infocard-icon">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="5" rx="2"/><line x1="2" x2="22" y1="10" y2="10"/></svg>
              </span>
              <h3>Default payment</h3>
            </div>
            <a href="{{ route('account.payment-methods') }}" class="kpad-edit">Edit</a>
          </div>
          <div class="kpad-card-row">
            <div class="kpad-card-chip"><span>{{ strtoupper($defaultPayment->brand) }}</span></div>
            <div>
              <p class="kpad-card-num">•••• •••• •••• {{ $defaultPayment->last4 }}</p>
              <p class="kpad-card-exp">Expires {{ $defaultPayment->exp_month }}/{{ $defaultPayment->exp_year }}</p>
            </div>
          </div>
        </section>
        @endif
      </div>

      {{-- ── Quick actions ── --}}
      <section class="kpad-section">
        <p class="kpad-overline">Quick actions</p>
        <div class="kpad-actions">
          @foreach([
            ['label' => 'Shop now',       'route' => 'shop',            'icon' => 'bag'],
            ['label' => 'My wishlist',    'route' => 'wishlist',        'icon' => 'heart'],
            ['label' => 'Loyalty points', 'route' => 'account.loyalty', 'icon' => 'gift'],
            ['label' => 'Track orders',   'route' => 'account.orders',  'icon' => 'package'],
          ] as $action)
          <a href="{{ route($action['route']) }}" class="kpad-action">
            <span class="kpad-action-icon">
              @include('components.account._icons.' . $action['icon'], ['size' => 20])
            </span>
            <span class="kpad-action-label">{{ $action['label'] }}</span>
          </a>
          @endforeach
        </div>
      </section>

    </div>{{-- /kpad --}}
  </div>
</div>
@endsection
