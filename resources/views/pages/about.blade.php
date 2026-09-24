@extends('layouts.app')

@section('content')
<div class="bg-black text-white">
    {{-- HERO --}}
    <section class="relative isolate min-h-[calc(100svh-80px)] overflow-hidden">
        <img
            src="{{ asset('assets/wear/about/hero.webp') }}"
            alt="KP Wear streetwear beside the Dar es Salaam waterfront"
            class="absolute inset-0 h-full w-full object-cover object-center"
        >
        <div class="absolute inset-0 bg-gradient-to-r from-black via-black/75 to-black/10"></div>
        <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-black/20"></div>

        <div class="relative z-10 mx-auto flex min-h-[calc(100svh-80px)] kp-content-wide items-center px-5 py-14 sm:px-8 lg:px-12 xl:px-14 2xl:px-16">
            <div class="max-w-[650px]">
                <p class="text-xs font-bold uppercase tracking-[0.38em] text-emerald-400">About</p>

                <h1 class="mt-4 text-[clamp(4rem,8vw,8rem)] font-black leading-[0.82] tracking-[-0.055em] text-white">
                    KP <span class="text-emerald-400">Wear.</span>
                </h1>

                <p class="mt-7 max-w-xl text-sm font-semibold uppercase leading-7 tracking-[0.18em] text-white sm:text-base">
                    From cartoons to clothing.<br class="hidden sm:block"> Built for creators.
                </p>

                <p class="mt-5 max-w-xl text-sm leading-7 text-white/80 sm:text-base sm:leading-8">
                    KP Wear started with a simple idea: the clothes you wear should reflect who you are. Born from the creative world of Masoud Kipanya—artist, cartoonist, storyteller—KP Wear brings that same creative energy into everyday pieces.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-5">
                    <a
                        href="{{ route('shop') }}"
                        class="inline-flex items-center justify-center rounded-xl bg-emerald-400 px-6 py-3.5 text-sm font-semibold text-black transition hover:bg-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-black"
                    >
                        Shop the collection
                        <span class="ml-2" aria-hidden="true">→</span>
                    </a>

                    <span class="hidden h-px w-14 bg-emerald-400 sm:block"></span>
                    <span class="text-[10px] font-bold uppercase tracking-[0.28em] text-white/75 sm:text-xs">
                        Designed for people who have something to say.
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- FOUR BRAND PILLARS --}}
    <section class="border-y border-white/10 bg-[#07110d]">
        <div class="mx-auto grid max-w-[1600px] grid-cols-1 md:grid-cols-2 xl:grid-cols-4">
            <article class="border-b border-white/10 px-6 py-10 sm:px-8 xl:border-b-0 xl:border-r">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-400 text-emerald-400">
                    <x-tabler-pencil size="22" stroke-width="1.8" />
                </div>
                <h2 class="mt-6 max-w-[220px] text-xl font-bold leading-tight">From cartoons to clothing</h2>
                <p class="mt-4 text-sm leading-6 text-white/65">
                    Masoud's creative foundation started in cartooning and art. That same energy—bold, expressive, unapologetic—is now woven into every KP Wear piece. Your clothes tell your story.
                </p>
            </article>

            <article class="border-b border-white/10 px-6 py-10 sm:px-8 md:border-r xl:border-b-0">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-400 text-emerald-400">
                    <x-tabler-world size="22" stroke-width="1.8" />
                </div>
                <h2 class="mt-6 max-w-[220px] text-xl font-bold leading-tight">Made in Dar es Salaam</h2>
                <p class="mt-4 text-sm leading-6 text-white/65">
                    Built in Tanzania, worn worldwide. KP Wear is rooted in Dar's creative energy and designed for people everywhere who value authenticity over trends.
                </p>
            </article>

            <article class="border-b border-white/10 px-6 py-10 sm:px-8 xl:border-b-0 xl:border-r">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-400 text-emerald-400">
                    <x-tabler-star size="22" stroke-width="1.8" />
                </div>
                <h2 class="mt-6 max-w-[220px] text-xl font-bold leading-tight">For creators & storytellers</h2>
                <p class="mt-4 text-sm leading-6 text-white/65">
                    You don't have to be famous to have something worth saying. KP Wear is made for people who think differently, create differently, and wear it with intent.
                </p>
            </article>

            <article class="px-6 py-10 sm:px-8">
                <div class="flex h-12 w-12 items-center justify-center rounded-full border border-emerald-400 text-emerald-400">
                    <x-tabler-telescope size="22" stroke-width="1.8" />
                </div>
                <h2 class="mt-6 max-w-[220px] text-xl font-bold leading-tight">One brand, many stories</h2>
                <p class="mt-4 text-sm leading-6 text-white/65">
                    KP Wear is part of a larger creative universe. Connected to cartoons, clothing, and more—it's all rooted in the same idea: creativity matters.
                </p>
            </article>
        </div>
    </section>

    {{-- OUR STORY --}}
    <section class="overflow-hidden bg-white text-black">
        <div class="mx-auto grid max-w-[1800px] lg:grid-cols-[1.05fr_.95fr]">
            <div class="relative min-h-[460px] overflow-hidden lg:min-h-[650px]">
                <img
                    src="{{ asset('assets/wear/about/story-artist.webp') }}"
                    alt="Artist drawing in a creative studio surrounded by sketches"
                    class="absolute inset-0 h-full w-full object-cover object-center"
                    loading="lazy"
                >
            </div>

            <div class="flex items-center px-6 py-14 sm:px-10 lg:px-14 xl:px-20">
                <div class="max-w-xl">
                    <p class="text-xs font-bold uppercase tracking-[0.35em] text-emerald-600">Our story</p>
                    <h2 class="mt-4 text-[clamp(2.5rem,5vw,5rem)] font-black leading-[0.9] tracking-[-0.045em]">
                        Same mindset.<br>
                        Different canvas.
                    </h2>
                    <p class="mt-7 text-base leading-7 text-black/65">
                        What started on paper—through cartoons, characters and stories—naturally evolved into something you can wear. KP Wear is the next chapter in a lifelong journey of creativity, expression and connection.
                    </p>

                    <div class="mt-9 border-t border-black/10 pt-6">
                        <p class="text-2xl font-medium tracking-[-0.02em]">Masoud Kipanya</p>
                        <p class="mt-2 text-[10px] font-bold uppercase tracking-[0.3em] text-black/45">
                            Artist · Cartoonist · Founder
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- PRODUCT / BRAND DETAIL --}}
    <section class="bg-[#f3f4f1] px-5 py-16 text-black sm:px-8 lg:px-12 xl:px-16">
        <div class="mx-auto grid max-w-[1600px] items-stretch gap-8 lg:grid-cols-[.8fr_1.2fr]">
            <div class="flex flex-col justify-center py-4 lg:pr-10">
                <p class="text-xs font-bold uppercase tracking-[0.35em] text-emerald-600">More than clothing</p>
                <h2 class="mt-4 text-[clamp(2.5rem,5vw,5rem)] font-black leading-[0.9] tracking-[-0.045em]">
                    Wear the idea.<br>
                    Tell the story.
                </h2>
                <p class="mt-6 max-w-lg text-base leading-7 text-black/65">
                    KP Wear brings the same creative energy from cartoons and art into everyday pieces. Bold, expressive and made for people who have something to say.
                </p>
                <a
                    href="{{ route('shop') }}"
                    class="mt-8 inline-flex w-fit items-center rounded-xl bg-black px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                >
                    Explore KP Wear
                    <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </div>

            <div class="overflow-hidden rounded-2xl bg-black shadow-2xl">
                <img
                    src="{{ asset('assets/wear/about/product-story.webp') }}"
                    alt="KP Wear apparel, artwork and brand materials arranged as an editorial flat lay"
                    class="h-full min-h-[380px] w-full object-cover object-center"
                    loading="lazy"
                >
            </div>
        </div>
    </section>

    {{-- CREATIVE UNIVERSE --}}
    <section class="relative isolate min-h-[600px] overflow-hidden">
        <img
            src="{{ asset('assets/wear/about/creative-universe.webp') }}"
            alt="KP Wear creative collage of artwork, culture and clothing"
            class="absolute inset-0 h-full w-full object-cover object-center"
            loading="lazy"
        >
        <div class="absolute inset-0 bg-black/45"></div>
        <div class="absolute inset-0 bg-gradient-to-r from-black/85 via-black/45 to-black/20"></div>

        <div class="relative z-10 mx-auto flex min-h-[600px] max-w-[1600px] items-end px-5 py-14 sm:px-8 lg:px-12 xl:px-16">
            <div class="flex w-full flex-col gap-8 lg:flex-row lg:items-end lg:justify-between">
                <div class="max-w-2xl">
                    <p class="text-xs font-bold uppercase tracking-[0.35em] text-emerald-400">A creative universe</p>
                    <h2 class="mt-4 text-[clamp(3rem,6vw,6rem)] font-black leading-[0.86] tracking-[-0.05em]">
                        More than<br>clothing.
                    </h2>
                    <p class="mt-6 max-w-xl text-sm leading-7 text-white/80 sm:text-base">
                        KP Wear is part of a larger creative universe. Connected to cartoons, clothing, and more—it's all rooted in the same idea: creativity matters.
                    </p>
                </div>

                <a
                    href="{{ route('shop') }}"
                    class="inline-flex w-fit shrink-0 items-center rounded-xl bg-emerald-400 px-6 py-3.5 text-sm font-semibold text-black transition hover:bg-emerald-300 focus:outline-none focus:ring-2 focus:ring-emerald-400 focus:ring-offset-2 focus:ring-offset-black"
                >
                    Join the story
                    <span class="ml-2" aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>
</div>
@endsection
