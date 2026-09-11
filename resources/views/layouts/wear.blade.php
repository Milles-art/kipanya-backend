<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kipanya Wear' }}</title>
    <style>
        @font-face {
            font-family: "tabler-icons";
            font-style: normal;
            font-weight: 400;
            font-display: block;
            src: url("{{ asset('fonts/tabler-icons.woff2') }}") format("woff2"),
                 url("{{ asset('fonts/tabler-icons.woff') }}") format("woff");
        }
    </style>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    {{-- NOTE: kipanya-wear-upgraded.css is bundled INSIDE kipanya-wear-all.css
         (last @import = wins the cascade). Do NOT add it as a separate entry
         here: duplicate loading doubles every rule, and production builds
         would 500 on the missing manifest entry. --}}
    @vite([
        'resources/css/kipanya-wear-all.css',
        'resources/js/kipanya-cart.js',
        'resources/js/kipanya-nav.js',
        'resources/js/kipanya-wear-animations.js',
        'resources/js/kp-bag-drawer.js'
    ])
</head>
<body class="kp-body" data-kp-auth="{{ auth()->check() ? '1' : '0' }}">
    <a class="kp-skip-link" href="#kp-main">Skip to content</a>
    <x-kipanya-nav
        active-section="wear"
        :cart-count="$cartCount ?? 0"
    />

    <div class="kp-wear-layout">
        <main class="kp-wear-main" id="kp-main" tabindex="-1">@yield('content')</main>
    </div>

    <x-kipanya-bag-drawer />

    <x-kipanya-bottom-nav :active-section="match (Route::currentRouteName()) {
        'wear.wishlist' => 'wishlist',
        'wear.orders', 'wear.order-confirmation' => 'orders',
        'wear.cart', 'wear.checkout' => 'bag',
        default => 'wear',
    }" :cart-count="$cartCount ?? 0" />
    <x-kipanya-footer />
    @stack('scripts')
    @auth
    <script>
        document.addEventListener('DOMContentLoaded', () => window.KipanyaCart?.mergeGuestCartAfterLogin());
    </script>
    @endauth
</body>
</html>
