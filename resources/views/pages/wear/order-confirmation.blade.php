@extends('layouts.wear')

@php
    $orderNumberJs = $orderNumber ?? null;
@endphp

@section('content')
<div class="kp-result-page">
    <div class="kp-result-card" data-kp-order-card>
        <p class="kp-empty-state">Loading order...</p>
    </div>
</div>
@endsection

@push('scripts')
    <script type="application/json" data-kp-order-number>@json($orderNumberJs)</script>
    @vite('resources/js/kp-wear-confirmation.js')
@endpush
