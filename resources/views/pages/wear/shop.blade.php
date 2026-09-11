@extends('layouts.wear-design2')
@section('content')
<div class="kp-d2-shop kp-d2-shell" data-d2-catalog>
  <div class="kp-d2-section-head"><div><h2>Shop All Products</h2><p>Browse the current Kipanya Wear collection.</p></div><span class="kp-d2-count" data-d2-count></span></div>
  <div class="kp-d2-toolbar">
    <div class="kp-d2-pills" data-d2-pills><button class="kp-d2-pill is-active" data-cat="all" aria-pressed="true">All</button></div>
    <select class="kp-d2-sort" data-d2-sort aria-label="Sort products"><option value="featured">Featured</option><option value="price-low">Price: Low to High</option><option value="price-high">Price: High to Low</option><option value="alpha">Alphabetical</option></select>
  </div>
  <div data-d2-status>
    <div class="kp-d2-grid" data-d2-product-grid><div class="kp-d2-skeleton" style="height:360px"></div><div class="kp-d2-skeleton" style="height:360px"></div><div class="kp-d2-skeleton" style="height:360px"></div><div class="kp-d2-skeleton" style="height:360px"></div></div>
  </div>
</div>
@endsection
