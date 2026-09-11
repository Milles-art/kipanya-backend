@extends('layouts.wear')

@php
    $backUrl = $backUrl ?? url()->previous();
    $productData = [
        'id' => $product->id,
        'name' => $product->name,
        'price' => (float) $product->price,
        'compare_at_price' => $product->compare_at_price !== null ? (float) $product->compare_at_price : null,
        'variants' => $product->variants->map(fn($v) => [
            'id' => $v->id,
            'size' => $v->size,
            'color' => $v->color,
            'stock' => (int) $v->stock,
            'in_stock' => (int) $v->stock > 0,
        ])->values(),
    ];
    $catSlug = \Illuminate\Support\Str::slug($product->category ?? '');
@endphp

@section('content')
<div class="kp-pdp" data-product-page>
    <script type="application/json" data-kp-product-data>@json($productData)</script>

    <nav class="kp-crumbs" aria-label="Breadcrumb">
        <a href="{{ route('wear') }}">Home</a>
        <span aria-hidden="true">/</span>
        <a href="{{ route('wear') }}?category={{ $catSlug }}#wear-catalog">{{ $product->category }}</a>
        <span aria-hidden="true">/</span>
        <span aria-current="page">{{ $product->name }}</span>
    </nav>

    <div class="kp-pdp-grid">
        <div class="kp-gallery">
            <div class="kp-gallery-main">
                <img class="kp-gallery-img" src="{{ $product->image_url }}" alt="{{ $product->name }}" data-kp-gallery-main />
                @if(!empty($product->badge))
                    <span class="kp-flag">{{ $product->badge }}</span>
                @endif
                <div class="kp-gallery-actions">
                    <button type="button"
                            class="kp-heart {{ $product->is_favorited ?? false ? 'is-active' : '' }}"
                            data-kp-favorite
                            data-product-id="{{ $product->id }}"
                            aria-label="Save {{ $product->name }} to favorites"
                        aria-pressed="{{ ($product->is_favorited ?? false) ? 'true' : 'false' }}">
                    <i class="ti {{ ($product->is_favorited ?? false) ? 'ti-heart-filled' : 'ti-heart' }}" aria-hidden="true"></i>
                </button>
                    <button type="button" class="kp-share" data-kp-share aria-label="Share {{ $product->name }}">
                        <i class="ti ti-share" aria-hidden="true"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="kp-buybox">
            <p class="kp-kicker">{{ $product->category }}</p>
            <h1 class="kp-pdp-title">{{ $product->name }}</h1>
            <p class="kp-pdp-price" data-kp-price>
                TZS {{ number_format($product->price) }}
                @if($product->compare_at_price && $product->compare_at_price > $product->price)
                    <s class="kp-was">TZS {{ number_format($product->compare_at_price) }}</s>
                @endif
            </p>
            <p class="kp-stock-note" data-kp-stock-note hidden></p>

            <p class="kp-opt-label" id="kp-size-label">Size</p>
            <div class="kp-size-grid" data-kp-size-selector role="group" aria-labelledby="kp-size-label"></div>

            <div data-kp-color-wrap hidden>
                <p class="kp-opt-label" id="kp-color-label">Color</p>
                <div class="kp-size-grid" data-kp-color-selector role="group" aria-labelledby="kp-color-label"></div>
            </div>

            <div class="kp-fit-links">
                <button type="button" class="kp-link" data-kp-sizing-quiz-open>Find your size</button>
                <button type="button" class="kp-link" data-kp-chart-open>Size chart</button>
            </div>

            <div class="kp-qty-row">
                <span class="kp-opt-label" id="kp-qty-label" style="margin:0;">Quantity</span>
                <div class="kp-stepper" role="group" aria-labelledby="kp-qty-label">
                    <button type="button" data-kp-qty-dec aria-label="Decrease quantity">−</button>
                    <span data-kp-qty-value aria-live="polite">1</span>
                    <button type="button" data-kp-qty-inc aria-label="Increase quantity">+</button>
                </div>
            </div>

            <button type="button" class="kp-atc" data-kp-add-to-cart>
                <span data-kp-add-label>Choose a size to add</span>
            </button>

            <div class="kp-acc" data-kp-accordion>
                @if($product->description)
                    <div class="kp-acc-item kp-acc-open">
                        <button type="button" class="kp-acc-head" data-kp-acc-head aria-expanded="true">
                            <span>Description</span><i class="ti ti-chevron-down" aria-hidden="true"></i>
                        </button>
                        <div class="kp-acc-body"><p>{{ $product->description }}</p></div>
                    </div>
                @endif
                <div class="kp-acc-item">
                    <button type="button" class="kp-acc-head" data-kp-acc-head aria-expanded="false">
                        <span>Delivery &amp; returns</span><i class="ti ti-chevron-down" aria-hidden="true"></i>
                    </button>
                    <div class="kp-acc-body">
                        <p>Complimentary delivery across Kenya. International orders ship with duties calculated at checkout. Unworn items in original condition can be returned within 14 days — contact support to start a return.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="kp-stickybar" data-kp-sticky-atc hidden>
    <img class="kp-sticky-thumb" src="{{ $product->image_url }}" alt="" />
    <div class="kp-sticky-text">
        <p class="kp-sticky-name">{{ $product->name }}</p>
        <p class="kp-sticky-price">TZS {{ number_format($product->price) }}</p>
    </div>
    <button type="button" class="kp-sticky-btn" data-kp-sticky-add>Add to bag</button>
</div>

<div class="kp-modal" data-kp-sizing-modal hidden>
    <div class="kp-modal-card" role="dialog" aria-modal="true" aria-labelledby="kp-quiz-title">
        <button type="button" class="kp-modal-x" data-kp-sizing-close aria-label="Close sizing quiz">
            <i class="ti ti-x" aria-hidden="true"></i>
        </button>
        <p class="kp-opt-label" id="kp-quiz-title" style="margin-top:0;">Find your size</p>

        <label class="kp-field-label" for="kp-quiz-height">Height (cm)</label>
        <input type="number" id="kp-quiz-height" class="kp-field" data-kp-quiz-height min="120" max="230" placeholder="e.g. 172">

        <label class="kp-field-label" for="kp-quiz-weight">Weight (kg)</label>
        <input type="number" id="kp-quiz-weight" class="kp-field" data-kp-quiz-weight min="30" max="250" placeholder="e.g. 68">

        <label class="kp-field-label" for="kp-quiz-fit">Fit preference</label>
        <select id="kp-quiz-fit" class="kp-field" data-kp-quiz-fit>
            <option value="slim">Slim</option>
            <option value="regular" selected>Regular</option>
            <option value="relaxed">Relaxed</option>
            <option value="oversized">Oversized</option>
        </select>

        <button type="button" class="kp-atc" data-kp-quiz-submit style="margin-top:12px;">Get my size</button>
        <p class="kp-quiz-result" data-kp-quiz-result aria-live="polite"></p>
        <p class="kp-fineprint" style="text-align:center;">Estimate from height + weight — not backed by garment measurements yet.</p>
    </div>
</div>

<div class="kp-modal" data-kp-chart-modal hidden>
    <div class="kp-modal-card" role="dialog" aria-modal="true" aria-labelledby="kp-chart-title">
        <button type="button" class="kp-modal-x" data-kp-chart-close aria-label="Close size chart">
            <i class="ti ti-x" aria-hidden="true"></i>
        </button>
        <p class="kp-opt-label" id="kp-chart-title" style="margin-top:0;">Size chart</p>
        <table class="kp-chart">
            <caption class="kp-visually-hidden">General alpha size ranges in centimetres</caption>
            <thead><tr><th scope="col">Size</th><th scope="col">Chest</th><th scope="col">Waist</th></tr></thead>
            <tbody>
                <tr><th scope="row">S</th><td>88–96</td><td>73–81</td></tr>
                <tr><th scope="row">M</th><td>96–104</td><td>81–89</td></tr>
                <tr><th scope="row">L</th><td>104–112</td><td>89–97</td></tr>
                <tr><th scope="row">XL</th><td>112–120</td><td>97–107</td></tr>
                <tr><th scope="row">XXL</th><td>120–128</td><td>107–117</td></tr>
            </tbody>
        </table>
        <p class="kp-fineprint" style="text-align:center;">General guide only — cut and fabric vary by garment.</p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-product.js')
@endpush
