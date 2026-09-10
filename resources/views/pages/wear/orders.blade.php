@extends('layouts.wear')

@section('content')
<div class="kp-orders-page">
    <h1 class="kp-page-title">Your orders</h1>
    <div class="kp-orders-list" data-kp-orders-list>
        <p class="kp-empty-state">Loading your orders...</p>
    </div>
</div>
@endsection

@push('scripts')
    @vite('resources/js/kp-wear-orders.js')
@endpush
