<aside class="h-fit rounded-2xl border border-gray-200 bg-white p-4 shadow-sm lg:sticky lg:top-24">

    {{-- Profile summary --}}
    <div class="mb-4 flex items-center gap-3 border-b border-gray-100 pb-4">
        <div class="flex h-11 w-11 flex-shrink-0 items-center justify-center rounded-full bg-emerald-100 font-semibold text-emerald-700">
            KP
        </div>
        <div class="min-w-0">
            <p data-sidebar-name class="truncate text-sm font-semibold text-gray-900">KP Wear customer</p>
            <p data-sidebar-phone class="truncate text-xs text-gray-500">Account</p>
        </div>
    </div>

    {{-- Main navigation --}}
    <nav class="space-y-1">
        <a href="{{ route('account') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-layout-dashboard size="17" />
            Dashboard
        </a>
        <a href="{{ route('account.orders') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.orders') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-package size="17" />
            My Orders
        </a>
        <a href="{{ route('account.returns') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.returns') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-rotate size="17" />
            Returns &amp; Support
        </a>
        <a href="{{ route('wishlist') }}"
           class="flex items-center justify-between gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('wishlist') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <span class="flex items-center gap-2.5">
                <x-tabler-heart size="17" />
                Wishlist
            </span>
            @if(isset($wishlistCount) && $wishlistCount > 0)
                <span class="rounded-full bg-gray-100 px-2 py-0.5 text-xs font-semibold text-gray-600">{{ $wishlistCount }}</span>
            @endif
        </a>
        <a href="{{ route('account.addresses') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.addresses') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-map-pin size="17" />
            Addresses
        </a>
        <a href="{{ route('account.payment-methods') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.payment-methods') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-credit-card size="17" />
            Payment Methods
        </a>
        <a href="{{ route('account.loyalty') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.loyalty') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-gift size="17" />
            Loyalty &amp; Referrals
        </a>
    </nav>

    {{-- Settings group --}}
    <p class="mb-1.5 mt-5 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">Settings</p>
    <nav class="space-y-1">
        <a href="{{ route('account.profile') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.profile') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-user-circle size="17" />
            Profile
        </a>
        <a href="{{ route('account.size-profile') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.size-profile') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-ruler-2 size="17" />
            Size Profile
        </a>
        <a href="{{ route('account.notifications') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.notifications') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-bell size="17" />
            Notifications
        </a>
        <a href="{{ route('account.security') }}"
           class="flex items-center gap-2.5 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs('account.security') ? 'bg-emerald-50 text-emerald-700' : 'text-gray-600 hover:bg-gray-50 hover:text-emerald-600' }}">
            <x-tabler-shield-lock size="17" />
            Security &amp; Login
        </a>
    </nav>

    {{-- Sign out --}}
    <div class="mt-4 border-t border-gray-100 pt-4">
        <button data-logout class="flex w-full items-center gap-2.5 rounded-lg px-3 py-2.5 text-left text-sm font-medium text-rose-500 transition hover:bg-rose-50">
            <x-tabler-logout size="17" />
            Sign out
        </button>
    </div>
</aside>
