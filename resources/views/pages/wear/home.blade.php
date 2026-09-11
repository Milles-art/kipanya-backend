@extends('layouts.wear-design2')

@section('title', 'Kipanya Wear — Wear the culture')
@section('meta_description', 'Discover KP Wear — premium everyday streetwear inspired by Kipanya culture, made for people who wear the story.')

@section('content')
<div data-d2-home>

  {{-- HERO --}}
  <section class="kp-d2-hero">
    <div class="kp-d2-shell kp-d2-hero-grid">
      <div>
        <span class="kp-d2-kicker"><span class="kp-d2-kicker-dot" aria-hidden="true"></span> Kipanya Wear</span>
        <h1>Wear the culture.<br><span>Live the story.</span></h1>
        <p class="kp-d2-hero-copy">Premium everyday streetwear built around the Kipanya universe. Clean silhouettes, bold character and pieces made to be worn often.</p>
        <div class="kp-d2-cta-row">
          <a href="{{ route('wear.shop') }}" class="kp-d2-btn kp-d2-btn-dark">
            Shop Collection <i class="ti ti-arrow-right" aria-hidden="true"></i>
          </a>
          <a href="{{ route('wear.about') }}" class="kp-d2-btn kp-d2-btn-light">Our Story</a>
        </div>
      </div>

      <div class="kp-d2-hero-media">
        <div class="col">
          <div class="kp-d2-media tall">
            <img src="{{ asset('assets/wear/home/hero.jpg') }}" alt="KP Wear campaign with models wearing Kipanya pieces" width="658" height="356" fetchpriority="high" decoding="async">
          </div>
          <div class="kp-d2-media square">
            <img src="{{ asset('assets/wear/home/lifestyle-rack.jpg') }}" alt="Kipanya garments ready to wear" loading="lazy" decoding="async">
          </div>
        </div>
        <div class="col">
          <div class="kp-d2-media square">
            <img src="{{ asset('assets/wear/home/lifestyle-street.jpg') }}" alt="KP Wear streetwear lifestyle" loading="lazy" decoding="async">
          </div>
          <div class="kp-d2-media tall">
            <img src="{{ asset('assets/wear/home/new-drop.jpg') }}" alt="KP Wear new drop campaign" loading="lazy" decoding="async">
          </div>
        </div>
      </div>
    </div>
  </section>

  {{-- BENEFITS --}}
  <section class="kp-d2-features">
    <div class="kp-d2-shell kp-d2-feature-grid">
      <div class="kp-d2-feature"><div class="kp-d2-feature-icon"><i class="ti ti-truck" aria-hidden="true"></i></div><div><strong>Fast Delivery</strong><span>Across Tanzania</span></div></div>
      <div class="kp-d2-feature"><div class="kp-d2-feature-icon"><i class="ti ti-refresh" aria-hidden="true"></i></div><div><strong>Easy Returns</strong><span>Simple return process</span></div></div>
      <div class="kp-d2-feature"><div class="kp-d2-feature-icon"><i class="ti ti-shield-check" aria-hidden="true"></i></div><div><strong>Secure Payment</strong><span>Protected checkout</span></div></div>
      <div class="kp-d2-feature"><div class="kp-d2-feature-icon"><i class="ti ti-headset" aria-hidden="true"></i></div><div><strong>Support</strong><span>We're here to help</span></div></div>
    </div>
  </section>

  {{-- CATEGORIES --}}
  <section class="kp-d2-section" data-d2-home-data>
    <div class="kp-d2-shell">
      <div class="kp-d2-section-head">
        <div>
          <h2>Shop by Category</h2>
          <p>Explore the current Kipanya Wear collection.</p>
        </div>
        <a class="kp-d2-link" href="{{ route('wear.shop') }}">View all <i class="ti ti-arrow-right" aria-hidden="true"></i></a>
      </div>
      <div class="kp-d2-category-grid" data-d2-home-categories>
        @for ($i = 0; $i < 4; $i++)
          <div class="kp-d2-category-card kp-d2-skeleton" style="min-height:220px"></div>
        @endfor
      </div>
    </div>
  </section>

  {{-- NEW ARRIVALS --}}
  <section class="kp-d2-section kp-d2-section-soft">
    <div class="kp-d2-shell">
      <div class="kp-d2-section-head">
        <div>
          <h2>New Arrivals</h2>
          <p>Fresh pieces for the next everyday rotation.</p>
        </div>
        <a class="kp-d2-link" href="{{ route('wear.shop') }}">Shop new <i class="ti ti-arrow-right" aria-hidden="true"></i></a>
      </div>
      <div class="kp-d2-grid" data-d2-home-new-arrivals>
        @for ($i = 0; $i < 6; $i++)
          <div class="kp-d2-skeleton" style="height:360px"></div>
        @endfor
      </div>
    </div>
  </section>

  {{-- FEATURED PRODUCTS --}}
  <section id="featured" class="kp-d2-section">
    <div class="kp-d2-shell">
      <div class="kp-d2-section-head">
        <div>
          <h2>Featured Products</h2>
          <p>Signature KP Wear pieces worth keeping close.</p>
        </div>
        <a class="kp-d2-link" href="{{ route('wear.shop') }}">View all <i class="ti ti-arrow-right" aria-hidden="true"></i></a>
      </div>
      <div class="kp-d2-grid" data-d2-home-featured-products>
        @for ($i = 0; $i < 6; $i++)
          <div class="kp-d2-skeleton" style="height:360px"></div>
        @endfor
      </div>
    </div>
  </section>

  {{-- EDITORIAL BANNER --}}
  <section class="kp-d2-editorial-banner">
    <div class="kp-d2-shell">
      <div class="kp-d2-editorial-banner-card">
        <img src="{{ asset('assets/wear/home/editorial-banner.jpg') }}" alt="KP Wear fresh styles editorial" loading="lazy">
        <div class="kp-d2-editorial-overlay">
          <p class="kp-d2-kicker">KP Wear campaign</p>
          <h2>Fresh styles.<br>Bold vibes.</h2>
          <p>Streetwear with personality, built around the culture.</p>
          <a href="{{ route('wear.shop') }}" class="kp-d2-btn kp-d2-btn-light">Explore Collection <i class="ti ti-arrow-right" aria-hidden="true"></i></a>
        </div>
      </div>
    </div>
  </section>

  {{-- TWO FINAL PRODUCT PICKS --}}
  <section class="kp-d2-section kp-d2-section-soft">
    <div class="kp-d2-shell">
      <div class="kp-d2-section-head">
        <div>
          <h2>Made for the culture</h2>
          <p>Two more pieces from the current KP Wear line.</p>
        </div>
      </div>
      <div class="kp-d2-grid kp-d2-grid-picks" data-d2-home-picks>
        <div class="kp-d2-skeleton" style="height:360px"></div>
        <div class="kp-d2-skeleton" style="height:360px"></div>
      </div>
    </div>
  </section>

</div>
@endsection
