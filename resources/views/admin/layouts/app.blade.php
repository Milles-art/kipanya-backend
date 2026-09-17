<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control Panel' }} — Kipanya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kp-admin-body min-h-screen bg-slate-50 text-slate-950 antialiased">
<div class="min-h-screen lg:flex">
    @include('admin.layouts.sidebar')
    <main class="min-w-0 flex-1 lg:ml-0">
        <header class="kp-admin-topbar sticky top-0 z-30 border-b border-slate-200/80 bg-white/95 backdrop-blur">
            <div class="flex min-h-16 items-center gap-4 px-4 sm:px-6 lg:px-8">
                <div class="hidden min-w-0 flex-1 md:block">
                    <div class="kp-admin-search flex h-11 max-w-xl items-center gap-3 rounded-xl border border-slate-200 bg-slate-50 px-3.5 text-sm text-slate-400">
                        <x-tabler-search size="19" stroke-width="1.8" />
                        <span>Search products, orders, customers...</span>
                    </div>
                </div>
                <div class="ml-auto flex items-center gap-3">
                    <button type="button" class="kp-admin-icon-button relative" aria-label="Notifications">
                        <x-tabler-bell size="20" stroke-width="1.8" />
                        @if(($metrics['orders_pending_payment'] ?? 0) > 0)
                            <span class="absolute -right-0.5 -top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-red-500 px-1 text-[9px] font-bold text-white">{{ min(9, (int) $metrics['orders_pending_payment']) }}</span>
                        @endif
                    </button>
                    <div class="h-8 w-px bg-slate-200"></div>
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-blue-600 text-xs font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</div>
                        <div class="hidden leading-tight sm:block">
                            <p class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</p>
                            <p class="text-[11px] font-medium text-slate-400">Super Admin</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}" class="hidden sm:block">@csrf<button aria-label="Sign out" class="text-slate-400 hover:text-slate-900"><x-tabler-chevron-down size="17" /></button></form>
                    </div>
                </div>
            </div>
        </header>
        <div class="px-4 py-5 sm:px-6 lg:px-8 lg:py-7">@yield('content')</div>
    </main>
</div>
</body>
</html>
