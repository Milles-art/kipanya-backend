@extends('layouts.app')
@section('content')
<section class="relative overflow-hidden bg-stone-100">
  <div class="mx-auto grid max-w-7xl items-center gap-10 px-4 py-14 sm:px-6 md:grid-cols-2 md:py-20 lg:px-8">
    <div><p class="mb-4 text-sm font-semibold uppercase tracking-[.25em] text-emerald-700">KP Wear · 2026</p><h1 class="max-w-xl text-5xl font-black leading-[1.02] tracking-tight md:text-7xl">Everyday fits.<br><span class="text-emerald-600">Made to move.</span></h1><p class="mt-6 max-w-md text-lg leading-8 text-gray-600">Clothes built around real days: comfortable, confident and easy to wear.</p><div class="mt-8 flex flex-wrap gap-3"><a href="{{ route('shop') }}" class="button-dark px-6 py-3">Shop now</a><a href="{{ route('collections') }}" class="rounded-xl border border-gray-300 px-6 py-3 text-sm font-semibold hover:bg-white">Explore collections</a></div></div>
    <div class="aspect-[4/5] overflow-hidden rounded-[2rem]"><img src="{{ asset('assets/wear/home/15_hero.jpg') }}" class="h-full w-full object-cover" alt="KP Wear collection"></div>
  </div>
</section>
<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"><div class="mb-8 flex items-end justify-between"><div><p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">Browse</p><h2 class="mt-2 text-3xl font-bold tracking-tight">Shop by category</h2></div><a href="{{ route('shop') }}" class="text-sm font-medium text-emerald-600">View all →</a></div><div data-home-categories class="grid grid-cols-2 gap-4 md:grid-cols-4"></div></section>
<section class="bg-gray-50 py-14"><div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8"><div class="mb-8 flex items-end justify-between"><div><p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">The edit</p><h2 class="mt-2 text-3xl font-bold tracking-tight">Featured pieces</h2></div><a href="{{ route('shop') }}" class="text-sm font-medium text-emerald-600">Shop all →</a></div><div data-home-featured class="grid grid-cols-2 gap-x-4 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-6"></div></div></section>
<section class="mx-auto max-w-7xl px-4 py-14 sm:px-6 lg:px-8"><div class="overflow-hidden rounded-3xl bg-gray-100"><img src="{{ asset('assets/wear/home/16_editorial_banner.jpg') }}" class="h-auto w-full object-cover" loading="lazy" alt="KP Wear editorial"></div></section>
@endsection
