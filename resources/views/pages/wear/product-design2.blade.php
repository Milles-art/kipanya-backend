@extends('layouts.wear-design2')
@php
$productData = ['id'=>$product->id,'name'=>$product->name,'price'=>(float)$product->price,'compare_at_price'=>$product->compare_at_price !== null ? (float)$product->compare_at_price : null,'image'=>$product->image_url,'category'=>$product->category,'description'=>$product->description,'badge'=>$product->badge,'variants'=>$product->variants->map(fn($v)=>['id'=>$v->id,'size'=>$v->size,'color'=>$v->color,'stock'=>(int)$v->stock,'in_stock'=>(int)$v->stock>0])->values()];
@endphp
@section('content')
<div class="kp-d2-product kp-d2-shell" data-d2-product>
<script type="application/json" data-d2-product-data>@json($productData)</script>
<div class="kp-d2-breadcrumb"><a href="{{ route('wear') }}">Home</a><span>/</span><a href="{{ route('wear.shop', ['category'=>\Illuminate\Support\Str::slug($product->category ?? '')]) }}">{{ $product->category }}</a><span>/</span><span>{{ $product->name }}</span></div>
<div class="kp-d2-product-grid">
<div><div class="kp-d2-main-image"><img data-d2-main-image src="{{ $product->image_url }}" alt="{{ $product->name }}"></div></div>
<div>
<p class="kp-d2-eyebrow">{{ $product->category ?: 'KP Wear' }}</p>
<h1>{{ $product->name }}</h1>
<div class="kp-d2-detail-price"><strong>TZS {{ number_format($product->price) }}</strong>@if($product->compare_at_price && $product->compare_at_price > $product->price)<s>TZS {{ number_format($product->compare_at_price) }}</s><span class="kp-d2-save">Sale</span>@endif</div>
@if($product->description)<p class="kp-d2-description">{{ $product->description }}</p>@endif
@php($hasStock=$product->variants->contains(fn($v)=>(int)$v->stock>0))
<div class="kp-d2-stock {{ $hasStock ? 'ok':'out' }}"><i class="ti {{ $hasStock?'ti-circle-check':'ti-circle-x' }}"></i>{{ $hasStock ? 'In stock — select your size below.':'Currently sold out' }}</div>
<div class="kp-d2-option"><div class="kp-d2-option-label">Size</div><div class="kp-d2-option-list" data-d2-sizes></div></div>
<div class="kp-d2-option" data-d2-colors hidden><div class="kp-d2-option-label">Color</div><div class="kp-d2-option-list" data-d2-color-list></div></div>
<div class="kp-d2-buyrow"><div class="kp-d2-stepper"><button type="button" data-d2-minus aria-label="Decrease quantity">−</button><span data-d2-qty>1</span><button type="button" data-d2-plus aria-label="Increase quantity">+</button></div><button class="kp-d2-btn kp-d2-btn-dark kp-d2-buy" type="button" data-d2-add-product disabled>Select a size</button></div>
<div class="kp-d2-perks"><div class="kp-d2-perk"><i class="ti ti-truck kp-d2-perk-icon"></i><div><strong>Fast delivery</strong><span>Across Tanzania</span></div></div><div class="kp-d2-perk"><i class="ti ti-shield-check kp-d2-perk-icon"></i><div><strong>Secure payment</strong><span>Protected checkout</span></div></div><div class="kp-d2-perk"><i class="ti ti-refresh kp-d2-perk-icon"></i><div><strong>Easy returns</strong><span>Simple return process</span></div></div></div>
</div></div></div>
@endsection
