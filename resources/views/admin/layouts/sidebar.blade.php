<aside data-admin-sidebar class="kp-admin-sidebar fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col text-slate-300 transition-transform duration-200 lg:translate-x-0">
    <div class="flex h-16 items-center justify-between border-b border-white/10 px-6">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2" aria-label="KP Wear dashboard">
            <span class="font-display text-2xl uppercase text-white">KP<span class="text-emerald-500">.</span></span>
            <span class="mt-1 text-[10px] font-semibold uppercase tracking-[.25em] text-slate-500">Admin</span>
        </a>
        <button type="button" data-admin-menu-close class="text-slate-400 transition hover:text-white lg:hidden" aria-label="Close navigation"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M6 6l12 12M18 6 6 18" /></svg></button>
    </div>

    <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
        @php
            $navItems = [
                ['admin.dashboard', 'Dashboard', 'layout-dashboard', null],
                ['admin.wear.products.index', 'Products', 'package', 'commerce.manage'],
                ['admin.wear.inventory.index', 'Inventory', 'box', 'commerce.manage'],
                ['admin.wear.orders.index', 'Orders', 'shopping-cart', 'commerce.manage'],
                ['admin.wear.payments.index', 'Payments', 'credit-card', 'payments.manage'],
                ['admin.wear.customers.index', 'Customers', 'users', 'commerce.manage'],
                ['admin.wear.enquiries.index', 'Enquiries', 'message-circle', 'commerce.manage'],
                ['admin.wear.returns.index', 'Returns & Refunds', 'rotate-clockwise-2', 'commerce.manage'],
                ['admin.wear.analytics.index', 'Analytics', 'trending-up', 'analytics.view'],
                ['admin.discounts.index', 'Discounts', 'percentage', 'commerce.manage'],
                ['admin.users.index', 'Admin Users', 'shield', 'users.manage'],
                ['admin.settings.index', 'Settings', 'settings', 'settings.manage'],
            ];
        @endphp
        @foreach($navItems as [$routeName, $label, $icon, $permission])
            @if(Route::has($routeName) && (!$permission || auth()->user()->hasPermission($permission)))
                <a href="{{ route($routeName) }}" class="kp-admin-nav-link {{ request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName)) ? 'is-active' : '' }}"><x-dynamic-component :component="'tabler-'.$icon" size="18" stroke-width="1.8" /><span>{{ $label }}</span></a>
            @endif
        @endforeach
    </nav>

    <div class="border-t border-white/10 p-3">
        <a href="{{ route('home') }}" target="_blank" rel="noopener" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm text-slate-400 transition hover:bg-white/5 hover:text-white"><x-tabler-building-store size="18" stroke-width="1.8" /><span>Back to Store</span></a>
    </div>
</aside>
