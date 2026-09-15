@extends('layouts.app')

@push('head')
<style>
  .kp-reveal {
    opacity: 0;
    transform: translateY(40px);
    transition: opacity 700ms cubic-bezier(0.4,0,0.2,1),
                transform 700ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-reveal.is-visible { opacity: 1; transform: translateY(0); }

  .kp-collection-card { transition: transform 500ms cubic-bezier(0.4,0,0.2,1); }
  .kp-collection-card:hover { transform: translateY(-8px); }
  .kp-collection-card .kp-card-img { transition: transform 700ms cubic-bezier(0.4,0,0.2,1); }
  .kp-collection-card:hover .kp-card-img { transform: scale(1.08); }
  .kp-collection-card .kp-card-overlay { transition: opacity 400ms ease; }
  .kp-collection-card .kp-card-arrow { transition: transform 300ms ease, background 300ms ease; }
  .kp-collection-card:hover .kp-card-arrow { transform: translateX(4px); background: #059669; }
  .kp-collection-card .kp-card-badge { transition: transform 400ms cubic-bezier(0.4,0,0.2,1); }
  .kp-collection-card:hover .kp-card-badge { transform: scale(1.08); }

  @media (prefers-reduced-motion: no-preference) {
    .kp-hero-float { animation: kpBob 4s ease-in-out infinite; }
  }
  @keyframes kpBob {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-16px); }
  }

  .kp-stagger > * { opacity: 0; transform: translateY(30px); transition: opacity 600ms cubic-bezier(0.4,0,0.2,1), transform 600ms cubic-bezier(0.4,0,0.2,1); }
  .kp-stagger.is-visible > * { opacity: 1; transform: translateY(0); }
  .kp-stagger.is-visible > *:nth-child(1) { transition-delay: 0ms; }
  .kp-stagger.is-visible > *:nth-child(2) { transition-delay: 80ms; }
  .kp-stagger.is-visible > *:nth-child(3) { transition-delay: 160ms; }
  .kp-stagger.is-visible > *:nth-child(4) { transition-delay: 240ms; }

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal, .kp-stagger > * { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-hero-float { animation: none !important; }
    .kp-collection-card, .kp-collection-card .kp-card-img,
    .kp-collection-card .kp-card-arrow, .kp-collection-card .kp-card-badge { transition: none !important; }
  }
</style>
@endpush

@section('content')
<div class="overflow-hidden">

    {{-- ── Hero ─────────────────────────────────────────────────── --}}
    <section class="relative mx-auto w-full max-w-[1600px] px-4 pb-10 pt-8 sm:px-6 lg:px-10 lg:pb-16 lg:pt-10">
        <div class="relative min-h-[520px] overflow-hidden rounded-[2rem] bg-gray-950 shadow-[0_28px_80px_rgba(15,23,42,0.18)] sm:min-h-[600px]">
            <img src="{{ asset('assets/wear/catalog/generated/product-07.jpg') }}" alt="Kipanya Wear collection" class="absolute inset-0 h-full w-full object-cover object-center opacity-90">
            <div class="absolute inset-0 bg-gradient-to-r from-black/80 via-black/40 to-black/10"></div>

            {{-- Floating accent badge --}}
            <div class="kp-hero-float absolute right-8 top-8 z-20 hidden sm:block lg:right-12 lg:top-12">
                <div class="flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 backdrop-blur-md ring-1 ring-white/20">
                    <span class="h-2 w-2 rounded-full bg-emerald-400"></span>
                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-white/90">New Season</span>
                </div>
            </div>

            <div class="relative flex min-h-[520px] items-end px-7 py-10 sm:min-h-[600px] sm:px-12 sm:py-14 lg:px-16 lg:py-16">
                <div class="max-w-2xl text-white">
                    <p class="text-xs font-bold uppercase tracking-[0.28em] text-emerald-400">Kipanya Wear / Collections</p>
                    <h1 class="mt-5 max-w-xl text-5xl font-black leading-[0.92] tracking-[-0.045em] sm:text-6xl lg:text-7xl">
                        More than clothes.<br>
                        <span class="text-emerald-400">It's a lifestyle.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-sm leading-7 text-white/75 sm:text-base">
                        Discover curated Kipanya edits built around the way you dress, move and express yourself.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#collection-grid" class="inline-flex items-center gap-3 rounded-full bg-white px-6 py-3.5 text-sm font-semibold text-gray-950 transition hover:-translate-y-0.5 hover:bg-emerald-400">
                            Explore collections <span aria-hidden="true">→</span>
                        </a>
                        <a href="{{ route('shop') }}" class="inline-flex items-center gap-3 rounded-full border border-white/25 px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-white/10">
                            Shop all
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Collection grid ──────────────────────────────────────── --}}
    <section id="collection-grid" class="mx-auto w-full max-w-[1600px] px-4 pb-20 sm:px-6 lg:px-10 lg:pb-28" data-collections-page>
        <div class="kp-reveal flex flex-col gap-4 border-b border-gray-100 pb-7 sm:flex-row sm:items-end sm:justify-between">
            <div>
                <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">Find your style story</p>
                <h2 class="mt-2 text-3xl font-black tracking-[-0.03em] text-gray-950 sm:text-4xl">Shop by collection</h2>
            </div>
            <p class="max-w-md text-sm leading-6 text-gray-500">Curated edits make it easier to find the pieces that fit your mood, your wardrobe and your everyday.</p>
        </div>

        <div data-collections-grid class="mt-8 grid gap-5 sm:grid-cols-2 lg:grid-cols-12 lg:gap-6">
            @for($i = 0; $i < 7; $i++)
                <div class="animate-pulse rounded-[1.5rem] bg-gray-100 lg:col-span-4 aspect-[4/3]"></div>
            @endfor
        </div>

        {{-- ── Editorial split ──────────────────────────────────────── --}}
        <div class="kp-reveal mt-6 overflow-hidden rounded-[1.5rem] bg-gray-100 lg:grid lg:grid-cols-[1.15fr_.85fr]">
            <div class="relative min-h-[280px] sm:min-h-[360px]">
                <img src="{{ asset('assets/wear/editorial/everyday-edit.png') }}" alt="Kipanya in Tanzania" class="absolute inset-0 h-full w-full object-cover">
                <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            </div>
            <div class="flex items-center p-8 sm:p-12 lg:p-14">
                <div class="max-w-lg">
                    <p class="text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">Made for here</p>
                    <h3 class="mt-3 text-3xl font-black tracking-[-0.03em] text-gray-950 sm:text-4xl">Tanzania in every thread.</h3>
                    <p class="mt-5 text-sm leading-7 text-gray-600">Kipanya is built around confidence, movement and everyday expression — with a style story that starts in Tanzania and travels with you.</p>
                    <a href="{{ route('shop') }}" class="mt-7 inline-flex items-center gap-2 rounded-full bg-slate-950 px-5 py-3 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-600">
                        Shop the wear edit <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- ── Trust badges ─────────────────────────────────────────── --}}
    <section class="border-y border-gray-100 bg-gray-50/70">
        <div class="kp-stagger mx-auto grid w-full max-w-[1600px] gap-6 px-4 py-8 sm:grid-cols-2 sm:px-6 lg:grid-cols-4 lg:px-10">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 7h11v8H3z"/><path d="M14 10h4l3 3v2h-7"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-950">Fast & reliable delivery</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Delivered with care across Tanzania.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 3 4 6v6c0 5 3.5 8 8 9 4.5-1 8-4 8-9V6z"/><path d="m9 12 2 2 4-4"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-950">Secure payments</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">A secure checkout experience.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M3 6h18v12H3z"/><path d="M3 10h18"/><path d="M8 14h4"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-950">Easy returns</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Straightforward support when you need it.</p>
                </div>
            </div>
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-emerald-100 text-emerald-700">
                    <svg viewBox="0 0 24 24" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path d="M12 2v6m0 0 2.5-2.5M12 8 9.5 5.5"/><circle cx="12" cy="14" r="8"/><path d="M12 11v3l2 2"/></svg>
                </span>
                <div>
                    <p class="text-sm font-semibold text-gray-950">Thoughtful choices</p>
                    <p class="mt-1 text-xs leading-5 text-gray-500">Better products, considered with purpose.</p>
                </div>
            </div>
        </div>
    </section>

</div>
@endsection

@push('scripts')
<script>
(function () {
  const reveals  = document.querySelectorAll('.kp-reveal');
  const staggers = document.querySelectorAll('.kp-stagger');

  if (!('IntersectionObserver' in window)) {
    reveals.forEach(el => el.classList.add('is-visible'));
    staggers.forEach(el => el.classList.add('is-visible'));
    return;
  }

  const obs = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        entry.target.classList.add('is-visible');
        obs.unobserve(entry.target);
      }
    });
  }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

  reveals.forEach(el => obs.observe(el));
  staggers.forEach(el => obs.observe(el));
})();
</script>
@endpush
