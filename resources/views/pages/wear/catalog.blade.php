@extends('layouts.wear')

@section('content')
<div class="kp-catalog" data-kp-catalog>
    <section class="kp-hero" data-wear-slider aria-roledescription="carousel" aria-label="Featured products">
        @php
            $slides = [
                ['image' => 'assets/wear/catalog/pink-polo.jpg', 'kicker' => 'New season', 'title' => 'Everyday icons, refreshed.', 'copy' => 'Tees, polos and shirts built for daily wear.', 'cta' => 'Shop new in'],
                ['image' => 'assets/wear/catalog/palm-resort-shirt.jpg', 'kicker' => 'Shirts', 'title' => 'Easy shirts for warm days.', 'copy' => 'Relaxed cuts, breathable fabrics.', 'cta' => 'Shop shirts'],
                ['image' => 'assets/wear/catalog/heritage-border-tee.jpg', 'kicker' => 'T-Shirts', 'title' => 'The perfect tee, perfected.', 'copy' => 'Heavyweight cotton that keeps its shape.', 'cta' => 'Shop T-Shirts'],
            ];
        @endphp

        @foreach($slides as $index => $slide)
            <article
                class="kp-slide {{ $index === 0 ? 'is-active' : '' }}"
                data-wear-slide
                aria-hidden="{{ $index === 0 ? 'false' : 'true' }}"
                @if($index !== 0) inert @endif
            >
                <div class="kp-slide-media" role="img" aria-label="{{ $slide['title'] }}">
                    <img src="{{ url($slide['image']) }}" alt="" loading="{{ $index === 0 ? 'eager' : 'lazy' }}" @if($index === 0) fetchpriority="high" @endif />
                </div>
                <div class="kp-slide-copy">
                    <p class="kp-kicker">{{ $slide['kicker'] }}</p>
                    @if($index === 0)<h1>{{ $slide['title'] }}</h1>@else<h2>{{ $slide['title'] }}</h2>@endif
                    <p>{{ $slide['copy'] }}</p>
                    <div class="kp-hero-ctas">
                        <span class="kp-hero-price" data-kp-hero-price hidden></span>
                        <a href="#kp-catalog-grid" class="kp-btn kp-btn-dark" data-kp-hero-cta>{{ $slide['cta'] }}</a>
                    </div>
                </div>
            </article>
        @endforeach

        <div class="kp-slider-controls">
            <button type="button" class="kp-slider-arrow" data-wear-prev aria-label="Previous slide"><i class="ti ti-chevron-left" aria-hidden="true"></i></button>
            <div class="kp-slider-dots" role="group" aria-label="Choose slide">
                @foreach($slides as $index => $slide)
                    <button type="button" class="kp-slider-dot {{ $index === 0 ? 'is-active' : '' }}" data-wear-dot="{{ $index }}" aria-label="Slide {{ $index + 1 }}: {{ $slide['title'] }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}"></button>
                @endforeach
            </div>
            <button type="button" class="kp-slider-arrow" data-wear-next aria-label="Next slide"><i class="ti ti-chevron-right" aria-hidden="true"></i></button>
        </div>
    </section>

    <section class="kp-tiles" aria-labelledby="kp-tiles-title" data-kp-tiles hidden>
        <h2 id="kp-tiles-title">Shop by category</h2>
        <div class="kp-tiles-row" data-kp-tiles-row></div>
    </section>

    <section class="kp-listing-head" aria-labelledby="kp-listing-title">
        <div>
            <p class="kp-kicker">Current collection</p>
            <h2 id="kp-featured-title">New arrivals</h2>
        </div>
        <span class="kp-count" data-kp-product-count aria-live="polite"></span>
    </section>

    <div id="kp-catalog-grid" class="kp-pills" role="group" aria-label="Product categories" data-kp-categories tabindex="-1">
        <button type="button" class="kp-pill is-active" data-filter="all" aria-pressed="true">All</button>
    </div>

    <p class="kp-search-chip" data-kp-search-chip hidden></p>

    <div class="kp-toolbar" data-kp-toolbar hidden>
        <div class="kp-size-filter" data-kp-size-filter role="group" aria-label="Filter by size"></div>
        <label class="kp-sort">
            <span class="kp-visually-hidden">Sort products</span>
            <select data-kp-sort>
                <option value="featured">Featured</option>
                <option value="price-asc">Price: Low to High</option>
                <option value="price-desc">Price: High to Low</option>
                <option value="name">Alphabetical</option>
            </select>
        </label>
    </div>

    <div class="kp-grid" data-kp-skeleton aria-hidden="true">
        @for ($i = 0; $i < 8; $i++)
            <div class="kp-skel"><div class="kp-skel-img kp-pulse"></div><div class="kp-skel-line kp-pulse" style="width:70%"></div><div class="kp-skel-line kp-pulse" style="width:40%"></div></div>
        @endfor
    </div>
    <div class="kp-grid" data-kp-products hidden></div>

    <div class="kp-more-wrap" data-kp-load-more-wrap hidden>
        <button type="button" class="kp-btn kp-btn-outline" data-kp-load-more>Load more</button>
    </div>

    <div class="kp-qv" data-kp-qv-modal hidden>
        <div class="kp-qv-card" role="dialog" aria-modal="true" aria-labelledby="kp-qv-title">
            <button type="button" class="kp-qv-close" data-kp-qv-close aria-label="Close quick view">
                <i class="ti ti-x" aria-hidden="true"></i>
            </button>
            <div data-kp-qv-body></div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-catalog.js')
@endpush
