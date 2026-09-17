@extends('layouts.app')

@section('content')
<section class="relative isolate -mb-16 min-h-[calc(100vh-80px)] overflow-hidden bg-white text-gray-950">
    <div class="absolute inset-0 z-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/wear/about/kp-wear-about-hero.png') }}');"></div>
    <div class="absolute inset-y-0 left-0 z-[1] w-[58%]" style="background: linear-gradient(90deg, rgba(255,255,255,.97) 0%, rgba(255,255,255,.93) 55%, rgba(255,255,255,.30) 88%, rgba(255,255,255,0) 100%);"></div>

    <div class="relative z-10 mx-auto flex min-h-[calc(100vh-80px)] max-w-[1600px] items-start px-5 py-8 sm:px-8 sm:py-10 lg:px-12 xl:px-14 2xl:px-16">
        <div class="w-full max-w-[650px] rounded-3xl border border-gray-100 bg-white/98 p-6 shadow-xl shadow-black/5 sm:p-8 lg:p-9">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-600">About</p>
                <h1 class="mt-3 text-[clamp(3rem,5.2vw,5.6rem)] font-black leading-[0.92] tracking-[-0.04em] text-gray-950">
                    KP <span class="text-emerald-600">Wear.</span>
                </h1>
            </div>

            <p class="mt-6 max-w-2xl text-sm font-semibold uppercase leading-6 tracking-[0.12em] text-gray-900 sm:mt-7 sm:text-base sm:leading-7 sm:tracking-[0.14em]">
                From cartoons to clothing. Built for creators.
            </p>

            <p class="mt-4 max-w-2xl text-sm leading-6 text-gray-600 sm:text-base sm:leading-7">
                KP Wear started with a simple idea: the clothes you wear should reflect who you are. Born from the creative world of Masoud Kipanya—artist, cartoonist, storyteller—KP Wear brings that same creative energy into everyday pieces. This isn't just fashion. It's a way for people with something to say to wear it with confidence.
            </p>

            <div class="mt-7 grid max-w-2xl gap-6 sm:mt-8 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-7">
                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-pencil size="22" stroke-width="1.8" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-emerald-700">From cartoons to clothing</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Masoud's creative foundation started in cartooning and art. That same energy—bold, expressive, unapologetic—is now woven into every KP Wear piece. Your clothes tell your story.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-world size="22" stroke-width="1.8" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-emerald-700">Made in Dar es Salaam</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">Built in Tanzania, worn worldwide. KP Wear is rooted in Dar's creative energy and designed for people everywhere who value authenticity over trends.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-star size="22" stroke-width="1.8" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-emerald-700">For creators & storytellers</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">You don't have to be famous to have something worth saying. KP Wear is made for people who think differently, create differently, and wear it with intent.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                        <x-tabler-telescope size="22" stroke-width="1.8" />
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-emerald-700">One brand, many stories</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-600">KP Wear is part of a larger creative universe. Connected to cartoons, clothing, and more—it's all rooted in the same idea: creativity matters.</p>
                    </div>
                </article>
            </div>

            <div class="mt-7 flex items-center gap-4 sm:mt-8">
                <span class="h-1.5 w-16 shrink-0 rounded-full bg-emerald-600 sm:w-20"></span>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-emerald-600 sm:text-xs sm:tracking-[0.28em]">Designed for people who have something to say.</p>
            </div>

            <div class="mt-8 sm:mt-10">
                <a href="{{ route('shop') }}" class="inline-flex items-center justify-center rounded-xl bg-emerald-600 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-emerald-700">
                    Shop the collection
                    <span class="ml-2">→</span>
                </a>
            </div>
        </div>
    </div>
</section>
@endsection
