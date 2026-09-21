<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="kp-signed-in" content="{{ Auth::guard('sanctum')->check() ? '1' : '0' }}">
    <title>{{ $title ?? 'KP Wear' }}</title>
    <meta name="description" content="KP Wear — everyday fashion made for movement, comfort and confidence.">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="bg-white text-gray-950 antialiased">
    @include('components.navbar')
    <main class="min-h-[70vh]">@yield('content')</main>
    @include('components.footer')
    <div id="toast" class="fixed bottom-5 left-1/2 z-[80] hidden -translate-x-1/2 rounded-full bg-gray-950 px-5 py-3 text-sm font-medium text-white shadow-xl"></div>
    @stack('scripts')
</body>
</html>
