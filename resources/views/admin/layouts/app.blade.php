<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'Control Panel' }} — Kipanya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gray-50 text-gray-950 antialiased">
<div class="min-h-screen lg:flex">
    @include('admin.layouts.sidebar')
    <main class="min-w-0 flex-1">
        <header class="sticky top-0 z-20 border-b border-gray-200 bg-white/95 backdrop-blur">
            <div class="flex h-16 items-center justify-between px-4 sm:px-6 lg:px-8">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-gray-400">Kipanya</p>
                    <h1 class="text-lg font-bold">{{ $heading ?? 'Control Panel' }}</h1>
                </div>
                <div class="flex items-center gap-3">
                    <span class="hidden text-sm text-gray-500 sm:inline">{{ auth()->user()->name }}</span>
                    <form method="POST" action="{{ route('admin.logout') }}">@csrf<button class="rounded-xl border border-gray-200 px-3 py-2 text-sm font-semibold hover:bg-gray-50">Sign out</button></form>
                </div>
            </div>
        </header>
        <div class="p-4 sm:p-6 lg:p-8">@yield('content')</div>
    </main>
</div>
</body>
</html>
