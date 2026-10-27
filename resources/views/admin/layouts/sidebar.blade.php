<aside class="kp-admin-sidebar flex w-full shrink-0 flex-col text-white lg:sticky lg:top-0 lg:h-screen lg:w-[252px] lg:overflow-y-auto">
    <div class="flex items-center justify-between px-5 py-6">
        <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
            <span class="flex h-11 w-11 items-center justify-center rounded-2xl border border-emerald-400/30 bg-emerald-400/10 p-2">
                <img src="{{ asset('images/kp-wear-logo.png') }}" alt="KP Wear" class="h-full w-full object-contain">
            </span>
            <span class="min-w-0">
                <span class="block text-sm font-bold tracking-[.04em] text-white">KP Wear</span>
                <span class="mt-0.5 block text-[10px] font-medium tracking-[.12em] text-emerald-300">ADMIN CONSOLE</span>
            </span>
        </a>
    </div>

    <nav class="flex-1 px-3 pb-6">
        <p class="px-3 pb-2 pt-2 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Store operations</p>
        <div class="space-y-1">
            @php
                $storeLinks = [
                    ['admin.dashboard', 'Dashboard', 'dashboard', null],
                    ['admin.wear.products.index', 'Products', 'package', 'commerce.manage'],
                    ['admin.wear.categories.index', 'Categories', 'tag', 'commerce.manage'],
                    ['admin.wear.collections.index', 'Collections', 'stack-2', 'commerce.manage'],
                    ['admin.wear.inventory.index', 'Inventory', 'box', 'commerce.manage'],
                    ['admin.wear.orders.index', 'Orders', 'clipboard-list', 'commerce.manage'],
                    ['admin.wear.payments.index', 'Payments', 'credit-card', 'payments.manage'],
                    ['admin.wear.customers.index', 'Customers', 'users', 'commerce.manage'],
                    ['admin.wear.enquiries.index', 'Enquiries', 'message-circle-2', 'commerce.manage'],
                    ['admin.wear.returns.index', 'Returns', 'rotate-clockwise-2', 'commerce.manage'],
                ];
            @endphp
            @foreach($storeLinks as [$routeName, $label, $icon, $permission])
                @if(Route::has($routeName) && (!$permission || auth()->user()->hasPermission($permission)))
                    <a href="{{ route($routeName) }}" class="kp-admin-nav-link {{ request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName)) ? 'is-active' : '' }}">
                        <x-dynamic-component :component="'tabler-'.$icon" size="19" stroke-width="1.8" />
                        <span>{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>

        <p class="px-3 pb-2 pt-7 text-[10px] font-bold uppercase tracking-[0.18em] text-white/40">Administration</p>
        <div class="space-y-1">
            @php
                $adminLinks = [
                    ['admin.users.index', 'Admin Users', 'users', 'users.manage'],
                    ['admin.wear.analytics.index', 'Analytics', 'chart-bar', 'analytics.view'],
                    ['admin.storefront.index', 'Content', 'layout-dashboard', 'settings.manage'],
                    ['admin.settings.index', 'Settings', 'settings', 'settings.manage'],
                    ['admin.audit.index', 'Audit Log', 'shield-check', 'admin.dashboard.view'],
                    ['admin.security.two-factor.index', 'Two-Factor Auth', 'lock', null],
                ];
            @endphp
            @foreach($adminLinks as [$routeName, $label, $icon, $permission])
                @if(Route::has($routeName) && (!$permission || auth()->user()->hasPermission($permission)))
                    <a href="{{ route($routeName) }}" class="kp-admin-nav-link {{ request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName)) ? 'is-active' : '' }}">
                        <x-dynamic-component :component="'tabler-'.$icon" size="19" stroke-width="1.8" />
                        <span>{{ $label }}</span>
                    </a>
                @endif
            @endforeach
        </div>
    </nav>

    <div class="mt-auto border-t border-white/10 px-4 py-5">
        <a href="{{ route('home') }}" class="flex items-center gap-3 rounded-xl px-2 py-2.5 text-xs font-medium text-white/70 transition hover:bg-white/5 hover:text-white" target="_blank" rel="noopener">
            <x-tabler-building-store size="18" stroke-width="1.8" />
            <span>View Store</span>
            <x-tabler-external-link size="14" class="ml-auto" />
        </a>
        <p class="mt-3 px-2 text-[11px] text-white/40">Fashion for a Greater You</p>
    </div>
</aside>
