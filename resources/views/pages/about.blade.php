@extends('layouts.app')

@section('content')
<section class="relative isolate -mb-16 min-h-[calc(100vh-80px)] overflow-hidden bg-[#f7f4ed] text-gray-950">
    <div class="absolute inset-0 z-0 bg-cover bg-center" style="background-image: url('{{ asset('assets/wear/about/kp-wear-about-hero.png') }}');"></div>
    <div class="absolute inset-y-0 left-0 z-[1] w-[58%]" style="background: linear-gradient(90deg, rgba(247,244,237,.96) 0%, rgba(247,244,237,.92) 55%, rgba(247,244,237,.28) 88%, rgba(247,244,237,0) 100%);"></div>

    <div class="relative z-10 mx-auto flex min-h-[calc(100vh-80px)] max-w-[1600px] items-start px-5 py-8 sm:px-8 sm:py-10 lg:px-12 xl:px-14 2xl:px-16">
        <div class="w-full max-w-[650px] rounded-[1.75rem] border border-white/80 bg-[#f7f4ed]/98 p-6 shadow-xl shadow-black/10 sm:p-8 lg:p-9">
            <div>
                <p class="text-[clamp(2.75rem,5vw,5.5rem)] font-extrabold leading-[0.9] tracking-[-0.04em] text-gray-950">About</p>
                <h1 class="mt-1 text-[clamp(3.5rem,6vw,6.5rem)] font-extrabold leading-[0.9] tracking-[-0.05em] text-amber-400">KP Wear</h1>
            </div>

            <p class="mt-6 max-w-2xl text-sm font-semibold uppercase leading-6 tracking-[0.12em] text-gray-950 sm:mt-7 sm:text-base sm:leading-7 sm:tracking-[0.14em]">
                A Tanzanian fashion brand built around creativity, individuality, and confidence.
            </p>

            <p class="mt-4 max-w-2xl text-sm leading-6 text-gray-700 sm:text-base sm:leading-7">
                KP Wear brings the spirit of art and self-expression into everyday clothing. Born from the creative world of Masoud Kipanya, the brand represents a simple idea: what we wear can be more than fashion — it can be a reflection of who we are.
            </p>

            <div class="mt-7 grid max-w-2xl gap-6 sm:mt-8 sm:grid-cols-2 sm:gap-x-8 sm:gap-y-7">
                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-amber-400 bg-white/70 text-amber-600 backdrop-blur-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 20c4.5-.7 8.5-3.1 11.5-7.1L20 4l-8.9 4.5C7.1 11.5 4.7 15.5 4 20Z"/><path d="m9 15 4 4M7.5 16.5l-1 2.5M11.5 12.5l2 2"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-amber-600">Our identity</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-700">KP Wear is rooted in Tanzanian creativity and culture, while looking beyond borders. Our designs are made for people who value individuality, creativity, and authenticity.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-amber-400 bg-white/70 text-amber-600 backdrop-blur-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="m12 3 2.4 4.8 5.3.8-3.8 3.7.9 5.2L12 15l-4.8 2.5.9-5.2-3.8-3.7 5.3-.8L12 3Z"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-amber-600">Our philosophy</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-700">We believe fashion should give people the confidence to be themselves. KP Wear is about creating pieces with character that allow the person wearing them to make their own statement.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-amber-400 bg-white/70 text-amber-600 backdrop-blur-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="2.5"/><circle cx="16.5" cy="9" r="2"/><path d="M4.5 18c.4-3 2.2-4.7 4.5-4.7s4.1 1.7 4.5 4.7M14 17.5c.3-2.2 1.5-3.4 3.2-3.4 1.5 0 2.6 1 3 3.4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-amber-600">The KP story</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-700">The name KP comes from Kipanya, the creative identity associated with Masoud Kipanya and his work as an artist and cartoonist. That creative foundation continues to influence the spirit of the brand.</p>
                    </div>
                </article>

                <article class="flex gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border-2 border-amber-400 bg-white/70 text-amber-600 backdrop-blur-sm" aria-hidden="true">
                        <svg viewBox="0 0 24 24" class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M4 17 9 12l3 3 7-8"/><path d="M15 7h4v4"/></svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold uppercase tracking-[0.08em] text-amber-600">Our vision</h2>
                        <p class="mt-1 text-sm leading-6 text-gray-700">To build KP Wear into a distinctive African fashion brand recognized for its creativity, identity, and authentic approach to modern clothing.</p>
                    </div>
                </article>
            </div>

            <div class="mt-7 flex items-center gap-4 sm:mt-8">
                <span class="h-1.5 w-16 shrink-0 bg-amber-400 sm:w-20"></span>
                <p class="text-[10px] font-bold uppercase tracking-[0.24em] text-amber-600 sm:text-xs sm:tracking-[0.28em]">Create. Express. Wear it with confidence.</p>
            </div>
        </div>
    </div>
</section>
@endsection
