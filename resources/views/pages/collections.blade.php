@extends('layouts.app')

@section('content')
<div class="overflow-hidden">
    <section class="relative mx-auto w-full max-w-[1600px] px-4 pb-10 pt-8 sm:px-6 lg:px-10 lg:pb-16 lg:pt-10">
        <div class="relative min-h-[520px] overflow-hidden rounded-[2rem] bg-gray-950 sm:min-h-[600px]">
            <img src="{{ asset('assets/wear/catalog/generated/product-07.jpg') }}" alt="Kipanya Wear collection" class="absolute inset-0 h-full w-full object-cover object-center opacity-90">
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/35 to-black/10"></div>
            <div class="relative flex min-h-[520px] items-end px-7 py-10 sm:min-h-[600px] sm:px-12 sm:py-14 lg:px-16 lg:py-16">
                <div class="max-w-2xl text-white">
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-white/70">Kipanya Wear / Collections</p>
                    <h1 class="mt-5 max-w-xl text-5xl font-semibold leading-[0.98] tracking-[-0.045em] sm:text-6xl lg:text-7xl">More than clothes. It’s a lifestyle.</h1>
                    <p class="mt-6 max-w-lg text-sm leading-7 text-white/75 sm:text-base">Discover curated Kipanya edits built around the way you dress, move and express yourself.</p>
                    <a href="#collection-grid" class="mt-8 inline-flex items-center gap-3 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-gray-950 transition hover:-translate-y-0.5 hover:bg-gray-100">Explore collections <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section id="collection-grid" class="mx-auto w-full max-w-[1600px] px-4 pb-20 sm:px-6 lg:px-10 lg:pb-28" data-collections-page>
        <div class="flex flex-col gap-4 border-b border-gray-100 pb-7 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700">Find your style story</p>
                <h2 class="mt-2 text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl">Shop by collection</h2>
            </div>
            <p class="max-w-md text-sm leading-6 text-gray-500">Curated edits make it easier to find the pieces that fit your mood, your wardrobe and your everyday.</p>
        </div>

        <div data-collections-grid class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-12 lg:gap-6">
            @for($i = 0; $i < 7; $i++)
                <div class="animate-pulse rounded-[1.5rem] bg-gray-100 lg:col-span-4 aspect-[4/3]"></div>
            @endfor
        </div>

        <div class="mt-6 overflow-hidden rounded-[1.5rem] bg-gray-100 lg:grid lg:grid-cols-[1.15fr_.85fr]">
            <div class="relative min-h-[280px] sm:min-h-[360px]">
                <img src="{{ asset('assets/wear/editorial/everyday-edit.png') }}" alt="Kipanya in Tanzania" class="absolute inset-0 h-full w-full object-cover">
            </div>
            <div class="flex items-center p-8 sm:p-12 lg:p-14">
                <div class="max-w-lg">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-700">Made for here</p>
                    <h3 class="mt-3 text-3xl font-semibold tracking-tight text-gray-950 sm:text-4xl">Tanzania in every thread.</h3>
                    <p class="mt-5 text-sm leading-7 text-gray-600">Kipanya is built around confidence, movement and everyday expression — with a style story that starts in Tanzania and travels with you.</p>
                    <a href="{{ route('shop') }}" class="mt-7 inline-flex items-center gap-2 text-sm font-semibold text-gray-950 transition hover:text-emerald-700">Shop the wear edit <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </div>
    </section>

    <section class="border-y border-gray-100 bg-gray-50/70">
        <div class="mx-auto grid w-full max-w-[1600px] gap-6 px-4 py-8 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-10">
            <div><p class="text-sm font-semibold text-gray-950">Fast & reliable delivery</p><p class="mt-1 text-xs leading-5 text-gray-500">Delivered with care across Tanzania.</p></div>
            <div><p class="text-sm font-semibold text-gray-950">Secure payments</p><p class="mt-1 text-xs leading-5 text-gray-500">A secure checkout experience.</p></div>
            <div><p class="text-sm font-semibold text-gray-950">Easy returns</p><p class="mt-1 text-xs leading-5 text-gray-500">Straightforward support when you need it.</p></div>
            <div><p class="text-sm font-semibold text-gray-950">Thoughtful choices</p><p class="mt-1 text-xs leading-5 text-gray-500">Better products, considered with purpose.</p></div>
        </div>
    </section>
</div>
@endsection
