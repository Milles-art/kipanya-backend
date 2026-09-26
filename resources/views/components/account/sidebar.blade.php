@props(['wishlistCount' => 0])

<style nonce="{{ Vite::cspNonce() }}">
.kpac {
  --ink: #0c110e;
  --text: #1a231e;
  --muted: #5a6660;
  --border: #e2e8e4;
  --card: #ffffff;
  --green: #1a7a52;
  --green-dark: #145f40;
  --green-faint: #eef8f3;
  --green-faint2: #f5fbf8;
  --radius-item: 12px;
  font-family: "Inter", ui-sans-serif, system-ui, sans-serif;
  -webkit-font-smoothing: antialiased;
}
.kpac * { box-sizing: border-box; }
.kpac a, .kpac button { transition: background .16s, color .16s; font: inherit; }
.kpac a:focus-visible, .kpac button:focus-visible {
  outline: 3px solid rgba(26,122,82,.22); outline-offset: 2px;
}

/* ── Card shell ── */
.kpac-aside {
  background: var(--card);
  border: 1px solid var(--border);
  border-radius: 18px;
  padding: 20px;
  box-shadow: 0 1px 3px rgba(10,16,12,.06);
}
@media (min-width: 1024px) { .kpac-aside { position: sticky; top: 96px; } }

/* ── Mobile accordion ── */
.kpac-mobile { display: block; margin-bottom: 14px; }
@media (min-width: 1024px) { .kpac-mobile { display: none; } }
.kpac-mobile-toggle {
  width: 100%;
  display: flex; align-items: center; justify-content: space-between;
  background: var(--green-faint); border: 0; border-radius: var(--radius-item);
  padding: 12px 16px;
  font-size: 13px; font-weight: 700; color: var(--ink);
  cursor: pointer;
}
.kpac-mobile-toggle[aria-expanded="true"] svg { transform: rotate(180deg); }
.kpac-mobile-toggle svg { transition: transform .2s; }
.kpac-mobile-list {
  margin-top: 8px;
  border: 1px solid var(--border);
  border-radius: var(--radius-item);
  padding: 8px;
  display: none;
}
.kpac-mobile-list.open { display: block; }
.kpac-mobile-list a,
.kpac-mobile-list button {
  display: block; width: 100%;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 13px; text-decoration: none;
  color: var(--text); background: none; border: 0; text-align: left; cursor: pointer;
}
.kpac-mobile-list a:hover { background: var(--green-faint2); color: var(--green-dark); }
.kpac-mobile-list a.active { background: var(--green-faint); color: var(--green); font-weight: 700; }
.kpac-mobile-list .kpac-signout { color: #e11d48; }
.kpac-mobile-list .kpac-signout:hover { background: #fff1f2; }

/* ── Profile summary ── */
.kpac-profile {
  display: flex; align-items: center; gap: 12px;
  border-bottom: 1px solid var(--border);
  padding-bottom: 14px; margin-bottom: 14px;
}
.kpac-avatar {
  width: 52px; height: 52px; flex: none;
  border-radius: 50%;
  background: var(--green-faint);
  color: var(--green);
  font-size: 15px; font-weight: 800;
  display: flex; align-items: center; justify-content: center;
}
.kpac-profile-name {
  font-size: 14px; font-weight: 700; color: var(--ink);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
.kpac-profile-sub {
  font-size: 13px; color: var(--muted);
  overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
  margin-top: 2px;
}

/* ── Desktop nav ── */
.kpac-desktop { display: none; }
@media (min-width: 1024px) { .kpac-desktop { display: block; } }

.kpac-nav { display: flex; flex-direction: column; gap: 2px; }
.kpac-nav-item {
  display: flex; align-items: center; gap: 12px;
  padding: 12px 14px;
  border-radius: var(--radius-item);
  font-size: 14px; font-weight: 500; color: var(--text);
  text-decoration: none; background: none; border: 0; text-align: left; cursor: pointer;
  width: 100%;
}
.kpac-nav-item:hover { background: var(--green-faint2); color: var(--green-dark); }
.kpac-nav-item.active { background: var(--green-faint); color: var(--green); font-weight: 700; }
.kpac-nav-item.active .kpac-icon { color: var(--green); }
.kpac-icon { color: var(--muted); flex: none; }
.kpac-label { flex: 1; }
.kpac-badge {
  background: var(--green-faint);
  color: var(--green);
  border-radius: 999px;
  padding: 1px 8px;
  font-size: 11px; font-weight: 800;
}
.kpac-section-label {
  margin: 18px 0 4px 12px;
  font-size: 10px; font-weight: 800;
  letter-spacing: .2em; text-transform: uppercase;
  color: var(--muted);
}
.kpac-divider { border: 0; border-top: 1px solid var(--border); margin: 14px 0; }
.kpac-nav-item.danger { color: #e11d48; }
.kpac-nav-item.danger .kpac-icon { color: #e11d48; }
.kpac-nav-item.danger:hover { background: #fff1f2; color: #be123c; }
</style>

<aside class="kpac-aside kpac">

  {{-- ── Mobile accordion ── --}}
  <div class="kpac-mobile">
    <button type="button" class="kpac-mobile-toggle" data-kpac-toggle
            aria-expanded="false" aria-controls="kpac-mobile-list">
      Account menu
      <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
           stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <path d="m6 9 6 6 6-6"/>
      </svg>
    </button>
    <div id="kpac-mobile-list" class="kpac-mobile-list">
      @foreach([
        ['route' => 'account',              'label' => 'Dashboard'],
        ['route' => 'account.orders',       'label' => 'My Orders'],
        ['route' => 'account.returns',      'label' => 'Returns & Support'],
        ['route' => 'wishlist',             'label' => 'Wishlist'],
        ['route' => 'account.addresses',    'label' => 'Addresses'],
        ['route' => 'account.payment-methods','label'=> 'Payment Methods'],
        ['route' => 'account.loyalty',      'label' => 'Loyalty & Referrals'],
        ['route' => 'account.profile',      'label' => 'Profile'],
        ['route' => 'account.size-profile', 'label' => 'Size Profile'],
        ['route' => 'account.notifications','label' => 'Notifications'],
        ['route' => 'account.security',     'label' => 'Security & Login'],
      ] as $item)
        <a href="{{ route($item['route']) }}"
           class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
          {{ $item['label'] }}
        </a>
      @endforeach
      <button type="button" data-logout class="kpac-signout">Sign out</button>
    </div>
  </div>

  {{-- ── Profile summary ── --}}
  <div class="kpac-profile">
    <div class="kpac-avatar" data-sidebar-initials>
      {{ strtoupper(substr($user->name ?? 'KP', 0, 1)) }}{{ strtoupper(substr(explode(' ', $user->name ?? 'KP Wear')[1] ?? '', 0, 1)) }}
    </div>
    <div style="min-width:0">
      <p class="kpac-profile-name" data-sidebar-name>{{ $user->name ?? 'KP Wear customer' }}</p>
      <p class="kpac-profile-sub" data-sidebar-phone>{{ $user->phone ?? $user->email ?? 'Account' }}</p>
    </div>
  </div>

  {{-- ── Desktop nav ── --}}
  <div class="kpac-desktop">

    <nav class="kpac-nav" aria-label="Account navigation">
      @foreach([
        ['route' => 'account',               'label' => 'Dashboard',         'icon' => 'grid'],
        ['route' => 'account.orders',        'label' => 'My Orders',         'icon' => 'package'],
        ['route' => 'account.returns',       'label' => 'Returns & Support', 'icon' => 'rotate'],
        ['route' => 'wishlist',              'label' => 'Wishlist',          'icon' => 'heart',  'badge' => true],
        ['route' => 'account.addresses',     'label' => 'Addresses',         'icon' => 'map-pin'],
        ['route' => 'account.payment-methods','label'=> 'Payment Methods',   'icon' => 'credit-card'],
        ['route' => 'account.loyalty',       'label' => 'Loyalty & Referrals','icon'=> 'gift'],
      ] as $item)
      <a href="{{ route($item['route']) }}"
         class="kpac-nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <span class="kpac-icon">
          @include('components.account._icons.' . $item['icon'])
        </span>
        <span class="kpac-label">{{ $item['label'] }}</span>
        @if(($item['badge'] ?? false) && isset($wishlistCount) && $wishlistCount > 0)
          <span class="kpac-badge">{{ $wishlistCount }}</span>
        @endif
      </a>
      @endforeach
    </nav>

    <p class="kpac-section-label">Settings</p>

    <nav class="kpac-nav" aria-label="Settings navigation">
      @foreach([
        ['route' => 'account.profile',       'label' => 'Profile',           'icon' => 'user-circle'],
        ['route' => 'account.size-profile',  'label' => 'Size Profile',      'icon' => 'ruler'],
        ['route' => 'account.notifications', 'label' => 'Notifications',     'icon' => 'bell'],
        ['route' => 'account.security',      'label' => 'Security & Login',  'icon' => 'shield'],
      ] as $item)
      <a href="{{ route($item['route']) }}"
         class="kpac-nav-item {{ request()->routeIs($item['route']) ? 'active' : '' }}">
        <span class="kpac-icon">
          @include('components.account._icons.' . $item['icon'])
        </span>
        <span class="kpac-label">{{ $item['label'] }}</span>
      </a>
      @endforeach
    </nav>

    <hr class="kpac-divider">

    <button type="button" data-logout class="kpac-nav-item danger">
      <span class="kpac-icon">
        <svg xmlns="http://www.w3.org/2000/svg" width="17" height="17" viewBox="0 0 24 24" fill="none"
             stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
          <polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
      </span>
      <span class="kpac-label">Sign out</span>
    </button>

  </div>
</aside>

<script nonce="{{ Vite::cspNonce() }}">
(() => {
  const btn = document.querySelector('[data-kpac-toggle]');
  const list = document.getElementById('kpac-mobile-list');
  if (!btn || !list) return;
  btn.addEventListener('click', () => {
    const open = btn.getAttribute('aria-expanded') === 'true';
    btn.setAttribute('aria-expanded', open ? 'false' : 'true');
    list.classList.toggle('open', !open);
  });
})();
</script>
