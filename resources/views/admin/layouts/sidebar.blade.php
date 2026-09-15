<aside class="w-full shrink-0 border-b border-gray-200 bg-white lg:min-h-screen lg:w-72 lg:border-b-0 lg:border-r">
    <div class="px-5 py-6 lg:px-6">
        <a href="{{ route('admin.dashboard') }}" class="block text-xl font-black tracking-tight">
            KIPANYA <span class="text-emerald-600">WEAR</span>
        </a>
        <p class="mt-1 text-xs font-medium text-gray-400">Store Administration</p>
    </div>

    <nav class="px-3 pb-4 lg:px-4">
        <p class="px-2 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Store</p>

        <div class="space-y-1">
            <a href="{{ route('admin.dashboard') }}"
               class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.dashboard') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                Dashboard
            </a>

            @if(Route::has('admin.wear.products.index'))
                <a href="{{ route('admin.wear.products.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.products.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Products
                </a>
            @endif

            @if(Route::has('admin.wear.categories.index'))
                <a href="{{ route('admin.wear.categories.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.categories.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Categories
                </a>
            @endif

            @if(Route::has('admin.wear.inventory.index'))
                <a href="{{ route('admin.wear.inventory.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.inventory.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Inventory
                </a>
            @endif

            @if(Route::has('admin.wear.orders.index'))
                <a href="{{ route('admin.wear.orders.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.orders.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Orders
                </a>
            @endif

            @if(Route::has('admin.wear.payments.index'))
                <a href="{{ route('admin.wear.payments.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.payments.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Payments
                </a>
            @endif

            @if(Route::has('admin.wear.customers.index'))
                <a href="{{ route('admin.wear.customers.index') }}"
                   class="block rounded-xl px-3 py-3 text-sm font-semibold {{ request()->routeIs('admin.wear.customers.*') ? 'bg-gray-950 text-white' : 'text-gray-700 hover:bg-gray-50' }}">
                    Customers
                </a>
            @endif
        </div>

        <p class="mt-6 px-2 pb-2 text-[11px] font-bold uppercase tracking-widest text-gray-400">Administration</p>
        <div class="space-y-1">
            @if(auth()->user()->hasPermission('users.manage'))
                <span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Admin users</span>
            @endif
            @if(auth()->user()->hasPermission('analytics.view'))
                <span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Analytics</span>
            @endif
            @if(auth()->user()->hasPermission('settings.manage'))
                <span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Settings</span>
            @endif
            @if(auth()->user()->hasPermission('admin.dashboard.view'))
                <span class="block rounded-xl px-3 py-2 text-sm text-gray-500">Audit Log</span>
            @endif
        </div>
    </nav>
</aside>
