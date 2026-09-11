@extends('layouts.app')
@section('content')
<div class="mx-auto max-w-7xl px-4 pb-20 pt-12 sm:px-6 lg:px-8"><div class="mb-8"><p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">KP Wear</p><h1 class="mt-2 text-3xl font-bold tracking-tight">Shop</h1><p data-catalog-count class="mt-2 text-sm text-gray-500">Loading products…</p></div><div data-catalog-grid class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-6"></div><div data-catalog-empty class="hidden rounded-2xl border border-dashed border-gray-200 py-24 text-center"><p class="text-lg font-semibold">No products found</p><a href="{{ route('shop') }}" class="mt-5 inline-block text-sm font-semibold text-emerald-600">Browse all products →</a></div></div>
@endsection
