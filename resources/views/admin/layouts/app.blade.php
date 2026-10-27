<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control Panel' }} — KP Wear</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="kp-admin-body min-h-screen antialiased">
<div class="flex min-h-screen bg-slate-50">
    <div data-admin-menu-backdrop class="fixed inset-0 z-40 hidden bg-black/50 lg:hidden"></div>
    @include('admin.layouts.sidebar')

    <div class="flex min-w-0 flex-1 flex-col lg:ml-64">
        <header class="kp-admin-topbar sticky top-0 z-30 border-b">
            <div class="flex h-16 items-center justify-between gap-4 px-5 sm:px-7">
                <div class="flex min-w-0 items-center gap-3">
                    <button type="button" data-admin-menu-toggle class="kp-admin-icon-button lg:hidden" aria-label="Open navigation" aria-expanded="false"><svg aria-hidden="true" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 7h16M4 12h16M4 17h16" /></svg></button>
                    <div class="flex min-w-0 items-center gap-2 text-sm">
                        <svg aria-hidden="true" class="h-[17px] w-[17px] shrink-0 text-emerald-600" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 9l2-5h14l2 5v2a2 2 0 0 1-2 2 2.4 2.4 0 0 1-2-1 2.4 2.4 0 0 1-4 0 2.4 2.4 0 0 1-4 0 2.4 2.4 0 0 1-2 1 2 2 0 0 1-2-2z" /><path d="M5 13v7h14v-7M9 20v-4h6v4" /></svg>
                        <span class="hidden text-slate-400 sm:inline">KP Wear Admin</span><span class="hidden text-slate-300 sm:inline">/</span>
                        <span class="truncate font-medium text-slate-950">{{ $heading ?? $title ?? 'Dashboard' }}</span>
                    </div>
                </div>

                <div class="flex shrink-0 items-center gap-3 sm:gap-4">
                    @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.orders.index'))
                        <form action="{{ route('admin.wear.orders.index') }}" method="get" class="kp-admin-search hidden h-10 w-56 items-center gap-2 rounded-xl px-3.5 sm:flex lg:w-64"><svg aria-hidden="true" class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="6" /><path d="m16 16 4 4" /></svg><input type="search" name="q" value="{{ request('q') }}" placeholder="Search…" class="min-w-0 flex-1 bg-transparent text-sm text-slate-700 outline-none placeholder:text-slate-400" aria-label="Search orders"></form>
                    @endif

                    @if(auth()->user()->hasPermission('commerce.manage') && Route::has('admin.wear.orders.index') && ($metrics['orders_pending_payment'] ?? 0) > 0)
                        <a href="{{ route('admin.wear.orders.index', ['status' => 'pending_payment']) }}" class="kp-admin-icon-button relative" aria-label="View orders awaiting payment"><svg aria-hidden="true" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5a2 2 0 1 1 4 0 7 7 0 0 1 4 6v3l2 3H4l2-3v-3a7 7 0 0 1 4-6M9 21h6" /></svg><span class="absolute right-1.5 top-1.5 h-2.5 w-2.5 rounded-full bg-emerald-500 ring-2 ring-white"></span></a>
                    @else
                        <span class="kp-admin-icon-button" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 5a2 2 0 1 1 4 0 7 7 0 0 1 4 6v3l2 3H4l2-3v-3a7 7 0 0 1 4-6M9 21h6" /></svg></span>
                    @endif

                    <div class="hidden h-7 w-px bg-slate-200 sm:block"></div>

                    <div class="flex items-center gap-2.5">
                        <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-[11px] font-bold text-white">{{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 2)) }}</span>
                        <div class="hidden leading-tight sm:block">
                            <p class="max-w-32 truncate text-sm font-medium text-slate-950">{{ auth()->user()->name }}</p>
                            <p class="max-w-32 truncate text-xs text-slate-400">{{ auth()->user()->roles?->pluck('name')->implode(', ') ?: 'Administrator' }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.logout') }}" class="hidden sm:block">@csrf<button aria-label="Sign out" class="kp-admin-icon-button"><svg aria-hidden="true" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 6V4a2 2 0 0 1 2-2h7a2 2 0 0 1 2 2v16a2 2 0 0 1-2 2h-7a2 2 0 0 1-2-2v-2" /><path d="m15 12-4-4m4 4-4 4m4-4H3" /></svg></button></form>
                    </div>
                </div>
            </div>
        </header>

        <main class="kp-admin-workspace w-full flex-1 px-5 py-5 sm:px-7 sm:py-7">@yield('content')</main>
    </div>
</div>
</body>
</html>
