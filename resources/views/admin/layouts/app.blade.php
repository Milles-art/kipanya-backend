<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control Panel' }} — KP Wear</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kp-admin-body min-h-screen antialiased">
<div class="min-h-screen lg:flex">
    @include('admin.layouts.sidebar')
    <main class="min-w-0 flex-1 lg:ml-0">
        <header class="kp-admin-topbar sticky top-0 z-30 border-b backdrop-blur">
            <div class="flex min-h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                <div class="hidden min-w-0 flex-1 md:block">
                    @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.orders.index'))
                        <form action="{{ route('admin.wear.orders.index') }}" method="get" class="kp-admin-search flex h-11 max-w-xl items-center gap-3 rounded-xl border px-3.5 text-sm text-black/45 transition">
                            <x-tabler-search size="19" stroke-width="1.8" />
                            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search orders, customers..." class="min-w-0 flex-1 bg-transparent outline-none placeholder:text-black/35" aria-label="Search orders and customers">
                        </form>
                    @endif
                </div>
                <div class="ml-auto flex items-center gap-3 sm:gap-4">
                    @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.orders.index') && ($metrics['orders_pending_payment'] ?? 0) > 0)
                        <a href="{{ route('admin.wear.orders.index', ['status' => 'pending_payment']) }}" class="kp-admin-icon-button relative" aria-label="View orders awaiting payment">
                            <x-tabler-bell size="20" stroke-width="1.8" />
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{{ min(9, (int) $metrics['orders_pending_payment']) }}</span>
                        </a>
                    @else
                        <div class="kp-admin-icon-button relative" aria-hidden="true"><x-tabler-bell size="20" stroke-width="1.8" /></div>
                    @endif
                    <div class="h-8 w-px bg-black/10"></div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-emerald-600 text-xs font-bold text-white ring-2 ring-emerald-100">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
                        <div class="hidden leading-tight sm:block">
                            <p class="text-sm font-bold text-black">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] font-medium text-black/50">{{ auth()->user()->roles?->pluck('name')->implode(', ') ?: 'Administrator' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}" class="hidden sm:block">@csrf<button aria-label="Sign out" class="kp-admin-icon-button"><x-tabler-logout size="17" /></button></form>
                    </div>
                </div>
            </div>
        </header>
        <div class="px-4 py-6 sm:px-6 lg:px-8 lg:py-8">@yield('content')</div>
    </main>
</div>
</body>
</html>
