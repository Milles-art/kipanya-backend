<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'Kipanya Wear' }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/tabler-icons.css') }}">
    @vite([
        'resources/css/kp-wear-design2.css',
        'resources/js/kipanya-cart.js',
        'resources/js/kp-wear-design2.js'
    ])
</head>
<body data-kp-auth="{{ auth()->check() ? '1' : '0' }}">
<header class="kp-d2-header" data-kp-design2-nav>
    <div class="kp-d2-shell kp-d2-header-inner">
        <a href="{{ route('wear') }}" class="kp-d2-brand" aria-label="Kipanya Wear home">
            <img src="{{ asset('assets/wear/brand/kp-wear-logo.jpg') }}" alt="Kipanya Wear">
            <span class="kp-d2-brand-word">Kipanya Wear</span>
        </a>
        <nav class="kp-d2-nav" aria-label="Primary">
            <a href="{{ route('wear') }}" class="{{ request()->routeIs('wear') ? 'is-active' : '' }}">Home</a>
            <a href="{{ route('wear.shop') }}" class="{{ request()->routeIs('wear.shop') ? 'is-active' : '' }}">Shop</a>
            <a href="{{ route('wear.about') }}">About</a>
            <a href="{{ route('wear.contact') }}">Contact</a>
        </nav>
        <div class="kp-d2-actions">
            <button type="button" class="kp-d2-icon-btn" data-d2-search-toggle aria-label="Search"><i class="ti ti-search"></i></button>
            <a class="kp-d2-icon-btn" href="{{ route('wear.cart') }}" aria-label="Shopping bag"><span class="kp-d2-bag"><i class="ti ti-shopping-bag"></i><span class="kp-d2-badge" data-kp-cart-count hidden>0</span></span></a>
            <button type="button" class="kp-d2-icon-btn kp-d2-menu-toggle" data-d2-menu-toggle aria-label="Open menu"><i class="ti ti-menu-2"></i></button>
        </div>
    </div>
    <div class="kp-d2-shell kp-d2-search" data-d2-search hidden>
        <form class="kp-d2-search-form" action="{{ route('wear.shop') }}" method="GET">
            <i class="ti ti-search"></i>
            <input name="q" data-d2-search-input type="search" placeholder="Search products..." aria-label="Search products">
        </form>
    </div>
    <div class="kp-d2-shell kp-d2-mobile-menu" data-d2-mobile-menu hidden>
        <a href="{{ route('wear') }}">Home</a>
        <a href="{{ route('wear.shop') }}">Shop</a>
        <a href="{{ route('wear.about') }}">About</a>
        <a href="{{ route('wear.contact') }}">Contact</a>
        <a href="{{ route('wear.cart') }}">Shopping bag</a>
    </div>
</header>

<main class="kp-d2-main">@yield('content')</main>
@stack('scripts')

<footer class="kp-d2-footer">
    <div class="kp-d2-shell kp-d2-footer-inner">
        <div class="kp-d2-footer-grid">
            <div>
                <h3>Kipanya Wear</h3>
                <p>Premium everyday wear inspired by Kipanya stories, characters and culture.</p>
            </div>
            <div>
                <h4>Shop</h4>
                <ul class="kp-d2-footer-list">
                    <li><a href="{{ route('wear.shop') }}">Shop all</a></li>
                    <li><a href="{{ route('wear') }}#featured">Featured</a></li>
                    <li><a href="{{ route('wear.cart') }}">Bag</a></li>
                </ul>
            </div>
            <div>
                <h4>Help</h4>
                <ul class="kp-d2-footer-list">
                    <li><a href="{{ route('wear.contact') }}">Contact</a></li>
                    <li><a href="{{ route('wear.about') }}">Our story</a></li>
                    <li><a href="{{ route('wear.orders') }}">Orders</a></li>
                </ul>
            </div>
            <div>
                <h4>Kipanya</h4>
                <ul class="kp-d2-footer-list">
                    <li><a href="{{ route('wear') }}">Wear</a></li>
                    <li><a href="#">Instagram</a></li>
                    <li><a href="#">YouTube</a></li>
                </ul>
            </div>
        </div>
        <div class="kp-d2-footer-bottom"><span>© {{ date('Y') }} Kipanya Wear</span><span>Part of the Kipanya ecosystem</span></div>
    </div>
</footer>
</body>
</html>
