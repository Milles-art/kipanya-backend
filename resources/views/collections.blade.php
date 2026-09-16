@extends('layouts.app')

@push('head')
<style>
    .kp-collections-reveal{opacity:0;transform:translateY(24px);transition:opacity:.65s cubic-bezier(.4,0,.2,1),transform:.65s cubic-bezier(.4,0,.2,1)}
    .kp-collections-reveal.is-visible{opacity:1;transform:translateY(0)}
    .kp-collections-stagger>*{opacity:0;transform:translateY(18px);transition:opacity:.55s cubic-bezier(.4,0,.2,1),transform:.55s cubic-bezier(.4,0,.2,1)}
    .kp-collections-stagger.is-visible>*{opacity:1;transform:translateY(0)}
    .kp-collections-stagger.is-visible>*:nth-child(1){transition-delay:0ms}
    .kp-collections-stagger.is-visible>*:nth-child(2){transition-delay:70ms}
    .kp-collections-stagger.is-visible>*:nth-child(3){transition-delay:140ms}
    .kp-collections-stagger.is-visible>*:nth-child(4){transition-delay:210ms}
    .kp-collections-card{transition:transform .45s cubic-bezier(.4,0,.2,1),box-shadow .45s ease}
    .kp-collections-card:hover{transform:translateY(-5px);box-shadow:0 18px 40px rgba(15,23,42,.14)}
    .kp-collections-card img{transition:transform .75s cubic-bezier(.4,0,.2,1)}
    .kp-collections-card:hover img{transform:scale(1.055)}
    .kp-collections-arrow{transition:transform .3s ease,background-color .3s ease}
    .kp-collections-card:hover .kp-collections-arrow{transform:translateX(4px);background-color:rgba(52,211,153,.95)}
    .kp-collections-scroll{scrollbar-width:none;-ms-overflow-style:none}
    .kp-collections-scroll::-webkit-scrollbar{display:none}
    @media (prefers-reduced-motion:reduce){
        .kp-collections-reveal,.kp-collections-stagger>*,.kp-collections-card,.kp-collections-card img,.kp-collections-arrow{opacity:1!important;transform:none!important;transition:none!important}
    }
</style>
@endpush

@section('content')
@php($collectionAsset = 'assets/wear/collections/')

<div class="overflow-hidden bg-white text-slate-950">
    {{-- HERO --}}
    <section class="mx-auto w-full max-w-[1600px] px-4 pt-4 sm:px-6 lg:px-10">
        <div class="relative min-h-[520px] overflow-hidden rounded-[18px] bg-slate-950 sm:min-h-[590px] lg:min-h-[640px]">
            <img src="{{ asset($collectionAsset.'hero/hero.jpg') }}" alt="Kipanya Wear collections" class="absolute inset-0 h-full w-full object-cover object-center">
            <div class="absolute inset-0 bg-gradient-to-r from-black/[.9] via-black/[.55] to-black/[.08]"></div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/[.58] via-transparent to-transparent"></div>

            <div class="relative flex min-h-[520px] items-end px-6 py-8 sm:min-h-[590px] sm:px-10 sm:py-10 lg:min-h-[640px] lg:px-12 lg:py-12">
                <div class="max-w-[690px] text-white">
                    <p class="text-[10px] font-bold uppercase tracking-[.28em] text-emerald-400 sm:text-xs">Kipanya Wear / Collections</p>
                    <h1 class="mt-4 max-w-[610px] text-[42px] font-black leading-[.92] tracking-[-.045em] sm:text-[56px] lg:text-[66px]">
                        More than clothes.<br><span class="text-emerald-400">It’s a lifestyle.</span>
                    </h1>
                    <p class="mt-5 max-w-[520px] text-sm leading-6 text-white/90 sm:text-base sm:leading-7">
                        Discover collections inspired by culture, movement and everyday life. Designed for those who do more.
                    </p>
                    <a href="#featured-collections" class="mt-7 inline-flex items-center gap-3 rounded-lg bg-white px-6 py-3.5 text-xs font-extrabold uppercase tracking-wide text-slate-950 transition hover:-translate-y-0.5 hover:bg-emerald-400 sm:text-sm">
                        Explore collections <span class="text-lg leading-none">→</span>
                    </a>

                    <div class="mt-10 flex flex-wrap gap-x-7 gap-y-4 sm:gap-x-10">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-white/70" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.7"><path d="M3 7h11v10H3zM14 10h3l4 4v3h-7zM6 19a2 2 0 1 0 0 1M18 19a2 2 0 1 0 0 1M14 17h8"/><circle cx="6" cy="19" r="2"/><circle cx="18" cy="19" r="2"/></svg>
                            </span>
                            <span class="text-[11px] font-medium leading-4">Fast Delivery<br>Across Tanzania</span>
                        </div>
                        <div class="hidden h-9 w-px bg-white/20 sm:block"></div>
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-white/70" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.7"><path d="M12 3l8 3v5c0 5-3.3 8.7-8 10-4.7-1.3-8-5-8-10V6l8-3z"/><path d="m8.5 12 2.2 2.2 4.8-5"/></svg>
                            </span>
                            <span class="text-[11px] font-medium leading-4">Quality You<br>Can Trust</span>
                        </div>
                        <div class="hidden h-9 w-px bg-white/20 sm:block"></div>
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-9 w-9 items-center justify-center rounded-full border border-white/70" aria-hidden="true">
                                <svg viewBox="0 0 24 24" class="h-5 w-5 fill-none stroke-current" stroke-width="1.7"><path d="M12 21c0-5 1-8 6-13 0 6-2 10-6 10-4 0-6-3-6-7 4 0 6 2 6 5"/><path d="M12 21V9"/></svg>
                            </span>
                            <span class="text-[11px] font-medium leading-4">Wear a Better<br>Tomorrow</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURED COLLECTIONS --}}
    <section id="featured-collections" class="mx-auto w-full max-w-[1600px] px-4 py-10 sm:px-6 sm:py-12 lg:px-10 lg:py-14">
        <div class="kp-collections-reveal flex items-end justify-between gap-5">
            <div>
                <p class="text-[10px] font-extrabold uppercase tracking-[.24em] text-slate-500 sm:text-xs">Explore our</p>
                <h2 class="mt-1 text-[30px] font-black uppercase leading-none tracking-[-.035em] sm:text-[38px]">Featured collections</h2>
            </div>
            <a href="{{ route('shop') }}" class="hidden items-center gap-2 pb-1 text-xs font-bold sm:flex">View all collections <span class="text-base">→</span></a>
        </div>

        <div class="mt-6 grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4">
            @foreach([
                ['name'=>'Men','desc'=>'Bold pieces for everyday confidence.','image'=>'featured/men.jpg'],
                ['name'=>'Women','desc'=>'Modern style, made for real life.','image'=>'featured/women.jpg'],
                ['name'=>'Street','desc'=>'Inspired by the streets. Worn everywhere.','image'=>'featured/street.jpg'],
                ['name'=>'Outerwear','desc'=>'Layer up. Move freely.','image'=>'featured/outerwear.jpg'],
            ] as $collection)
                <a href="{{ route('shop') }}" class="kp-collections-card group relative aspect-[3/4] overflow-hidden rounded-xl bg-slate-100">
                    <img src="{{ asset($collectionAsset.$collection['image']) }}" alt="{{ $collection['name'] }} collection" class="h-full w-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/[.88] via-black/[.12] to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 p-4 text-white sm:p-5">
                        <h3 class="text-xl font-black uppercase tracking-[-.02em] sm:text-2xl">{{ $collection['name'] }}</h3>
                        <p class="mt-1 max-w-[200px] text-[11px] leading-4 text-white/90 sm:text-xs">{{ $collection['desc'] }}</p>
                        <span class="mt-4 inline-flex items-center gap-2 text-[11px] font-bold sm:text-xs">
                            Explore collection
                            <span class="kp-collections-arrow flex h-7 w-7 items-center justify-center rounded-full bg-white/15 text-base">→</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        <a href="{{ route('shop') }}" class="mt-5 flex items-center justify-center gap-2 text-xs font-bold sm:hidden">View all collections <span class="text-base">→</span></a>
    </section>

    {{-- EVERYDAY EDIT --}}
    <section class="mx-auto w-full max-w-[1600px] px-4 pb-10 sm:px-6 lg:px-10">
        <div class="grid overflow-hidden rounded-xl bg-slate-100 lg:grid-cols-[1.12fr_.88fr]">
            <div class="relative min-h-[330px] sm:min-h-[400px] lg:min-h-[455px]">
                <img src="{{ asset($collectionAsset.'editorial/everyday-edit.jpg') }}" alt="Kipanya everyday edit" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
                <div class="absolute inset-0 bg-gradient-to-r from-black/20 to-transparent"></div>
            </div>
            <div class="flex items-center p-7 sm:p-10 lg:p-12">
                <div class="max-w-[470px]">
                    <p class="text-[10px] font-extrabold uppercase tracking-[.24em] text-slate-500 sm:text-xs">The everyday edit</p>
                    <h2 class="mt-3 text-[38px] font-black leading-[.95] tracking-[-.04em] sm:text-[48px]">Style for<br>real life.</h2>
                    <p class="mt-5 text-sm leading-6 text-slate-600 sm:text-base sm:leading-7">Clean fits. Timeless pieces. Made for Tanzanian days and wherever they take you.</p>
                    <a href="{{ route('shop') }}" class="mt-7 inline-flex items-center gap-3 rounded-lg bg-slate-950 px-5 py-3 text-xs font-extrabold uppercase tracking-wide text-white transition hover:-translate-y-0.5 hover:bg-emerald-500">
                        Shop the edit <span class="text-lg">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ESSENTIALS --}}
    <section class="mx-auto w-full max-w-[1600px] px-4 pb-10 sm:px-6 lg:px-10">
        <div class="relative min-h-[300px] overflow-hidden rounded-xl bg-slate-900 sm:min-h-[360px]">
            <img src="{{ asset($collectionAsset.'editorial/essentials.jpg') }}" alt="Kipanya essentials" class="absolute inset-0 h-full w-full object-cover" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-r from-black/[.72] via-black/[.3] to-transparent"></div>
            <div class="relative flex min-h-[300px] items-center p-7 text-white sm:min-h-[360px] sm:p-10 lg:p-12">
                <div class="max-w-[520px]">
                    <p class="text-[10px] font-extrabold uppercase tracking-[.24em] text-emerald-400 sm:text-xs">Kipanya essentials</p>
                    <h2 class="mt-3 text-[38px] font-black leading-none tracking-[-.04em] sm:text-[50px]">Built different.</h2>
                    <p class="mt-4 text-sm text-white/85 sm:text-base">Everyday comfort. Lasting quality.</p>
                    <a href="{{ route('shop') }}" class="mt-6 inline-flex items-center gap-3 rounded-lg bg-white px-5 py-3 text-xs font-extrabold uppercase tracking-wide text-slate-950 transition hover:-translate-y-0.5 hover:bg-emerald-400">
                        Explore essentials <span class="text-lg">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- SHOP BY MOOD --}}
    <section class="mx-auto w-full max-w-[1600px] px-4 pb-12 sm:px-6 lg:px-10 lg:pb-14">
        <div class="flex items-end justify-between gap-4">
            <h2 class="text-[28px] font-black uppercase leading-none tracking-[-.035em] sm:text-[34px]">Shop by mood</h2>
            <a href="{{ route('shop') }}" class="flex items-center gap-2 text-xs font-bold">View all <span class="text-base">→</span></a>
        </div>
        <div class="kp-collections-stagger mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 sm:gap-4">
            @foreach([
                ['name'=>'New Drop','image'=>'mood/new-drop.jpg'],
                ['name'=>'Everyday','image'=>'mood/everyday.jpg'],
                ['name'=>'Street','image'=>'mood/street.jpg'],
                ['name'=>'Active','image'=>'mood/active.jpg'],
            ] as $mood)
                <a href="{{ route('shop') }}" class="kp-collections-card group relative aspect-[1.35/1] overflow-hidden rounded-xl bg-slate-100">
                    <img src="{{ asset($collectionAsset.$mood['image']) }}" alt="{{ $mood['name'] }}" class="h-full w-full object-cover" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/[.72] to-transparent"></div>
                    <div class="absolute inset-x-0 bottom-0 flex items-center justify-between p-4 text-white">
                        <span class="text-sm font-extrabold sm:text-base">{{ $mood['name'] }}</span>
                        <span class="kp-collections-arrow flex h-7 w-7 items-center justify-center rounded-full bg-white/15">→</span>
                    </div>
                </a>
            @endforeach
        </div>
    </section>

    {{-- TANZANIA CTA --}}
    <section class="relative overflow-hidden bg-[#063b2d] text-white">
        <img src="{{ asset($collectionAsset.'banner/tanzania-banner.jpg') }}" alt="" aria-hidden="true" class="absolute inset-0 h-full w-full object-cover opacity-30" loading="lazy">
        <div class="absolute inset-0 bg-[#063b2d]/65"></div>
        <div class="relative mx-auto flex w-full max-w-[1600px] flex-col gap-6 px-5 py-8 sm:px-8 lg:flex-row lg:items-center lg:justify-between lg:px-10 lg:py-9">
            <div class="flex items-center gap-5 sm:gap-8">
                <div class="shrink-0 text-center leading-none">
                    <div class="text-[25px] font-black tracking-[-.05em] sm:text-[30px]">KIPANYA</div>
                    <div class="mt-1 text-[8px] font-bold tracking-[.45em]">WEAR</div>
                </div>
                <div class="h-12 w-px bg-white/30"></div>
                <div>
                    <h2 class="text-xl font-black tracking-[-.02em] sm:text-2xl">Tanzania in every thread.</h2>
                    <p class="mt-1 text-xs text-white/75 sm:text-sm">More than fashion. A movement for a bolder, brighter tomorrow.</p>
                </div>
            </div>
            <a href="{{ route('shop') }}" class="inline-flex items-center justify-center gap-3 self-start rounded-lg bg-emerald-400 px-6 py-3 text-xs font-extrabold uppercase tracking-wide text-slate-950 transition hover:bg-white lg:self-auto">
                Shop now <span class="text-lg">→</span>
            </a>
        </div>
    </section>

    {{-- VALUES --}}
    <section class="border-t border-slate-100 bg-white">
        <div class="kp-collections-stagger mx-auto grid w-full max-w-[1600px] grid-cols-2 gap-y-6 px-5 py-8 sm:grid-cols-4 sm:px-8 lg:px-10">
            <div class="flex items-center gap-3 sm:justify-center">
                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 fill-none stroke-current" stroke-width="1.6"><path d="M12 21c0-5 1-8 6-13 0 6-2 10-6 10-4 0-6-3-6-7 4 0 6 2 6 5"/><path d="M12 21V9"/></svg>
                <span class="text-xs font-semibold sm:text-sm">Sustainable Choices</span>
            </div>
            <div class="flex items-center gap-3 sm:justify-center">
                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 fill-none stroke-current" stroke-width="1.6"><circle cx="8" cy="8" r="3"/><circle cx="16" cy="8" r="3"/><path d="M2 20c.5-3 2.4-5 6-5s5.5 2 6 5M10 20c.5-3 2.4-5 6-5s5.5 2 6 5"/></svg>
                <span class="text-xs font-semibold sm:text-sm">Support Local</span>
            </div>
            <div class="flex items-center gap-3 sm:justify-center">
                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 fill-none stroke-current" stroke-width="1.6"><path d="m12 3 8 5-8 13L4 8l8-5z"/><path d="m4 8 8 3 8-3M12 11v10"/></svg>
                <span class="text-xs font-semibold sm:text-sm">Quality Materials</span>
            </div>
            <div class="flex items-center gap-3 sm:justify-center">
                <svg viewBox="0 0 24 24" class="h-6 w-6 shrink-0 fill-none stroke-current" stroke-width="1.6"><path d="M20.8 8.7c0 5.2-8.8 10.3-8.8 10.3S3.2 13.9 3.2 8.7A4.7 4.7 0 0 1 12 6a4.7 4.7 0 0 1 8.8 2.7z"/></svg>
                <span class="text-xs font-semibold sm:text-sm">A Stronger Community</span>
            </div>
        </div>
    </section>
</div>
@endsection

@push('scripts')
<script>
(function () {
    const revealItems = document.querySelectorAll('.kp-collections-reveal, .kp-collections-stagger');
    if (!revealItems.length) return;

    if (!('IntersectionObserver' in window)) {
        revealItems.forEach((el) => el.classList.add('is-visible'));
        return;
    }

    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (!entry.isIntersecting) return;
            entry.target.classList.add('is-visible');
            observer.unobserve(entry.target);
        });
    }, { threshold: 0.12, rootMargin: '0px 0px -50px 0px' });

    revealItems.forEach((el) => observer.observe(el));
})();
</script>
@endpush
