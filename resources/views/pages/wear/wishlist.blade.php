@extends('layouts.wear')

@section('content')
<div class="kp-wishlist-page">
    <h1 class="kp-page-title">Your favorites</h1>
    <div class="kp-product-grid" data-kp-wishlist-grid>
        <p class="kp-empty-state">Loading your favorites...</p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-wishlist.js')
@endpush
