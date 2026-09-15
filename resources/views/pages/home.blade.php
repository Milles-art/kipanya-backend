@extends('layouts.app')

@push('head')
<style>
  .kp-slide {
    opacity: 0;
    transform: translateX(60px);
    transition: opacity 650ms cubic-bezier(0.4,0,0.2,1),
                transform 650ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-slide.is-entering { opacity: 0; transform: translateX(60px); }
  .kp-slide.is-visible  { opacity: 1; transform: translateX(0); }
  .kp-slide.is-exiting  { opacity: 0; transform: translateX(-60px); }

  @media (prefers-reduced-motion: no-preference) {
    .kp-hero-float { animation: kpBob 3.5s ease-in-out infinite; }
  }
  @keyframes kpBob {
    0%, 100% { transform: translateY(0); }
    50%       { transform: translateY(-18px); }
  }
  @media (prefers-reduced-motion: reduce) {
    .kp-slide { transition: none !important; opacity: 1 !important; transform: none !important; }
    .kp-hero-float { animation: none !important; }
  }

  .kp-dot {
    width: 8px; height: 8px;
    border-radius: 9999px;
    background: rgba(255,255,255,0.35);
    transition: width 300ms ease, background 300ms ease;
    border: none; cursor: pointer; padding: 0;
  }
  .kp-dot.active { width: 28px; background: #fff; }

  /* ── Hero shell (width increased, height kept close to original) ── */
  .kp-hero-section { min-height: 600px; }
  #kp-hero-card {
    min-height: 540px;
    box-shadow: 0 32px 80px rgba(0,0,0,0.18), 0 8px 24px rgba(0,0,0,0.10);
    transition: background 1300ms ease;
  }
  #kp-hero-card:focus-visible {
    outline: 3px solid rgba(16,185,129,0.6);
    outline-offset: -3px;
  }

  /* ── Dots, positioned by JS once moved out of slide 0 ── */
  .kp-dots-positioned {
    position: absolute;
    bottom: 2.5rem;
    left: 2.5rem;
    display: flex;
    gap: 8px;
    z-index: 30;
  }

  /* ── Mobile: stack the slide instead of forcing a 300px+ right column
       into whatever's left of the viewport, which was overflowing/crushing
       the copy on small screens. ── */
  @media (max-width: 860px) {
    .kp-hero-section { min-height: 0; padding-top: 1rem; padding-bottom: 1rem; }
    #kp-hero-card { min-height: 0; }

    .kp-slide-grid { grid-template-columns: 1fr !important; }

    .kp-hero-visual {
      min-width: 0 !important;
      align-items: center !important;
      padding: 0 1.25rem 1.5rem !important;
    }

    .kp-hero-img-wrap { margin: 0 !important; }
    .kp-hero-img { width: 62vw !important; max-width: 260px !important; height: auto !important; aspect-ratio: 3 / 4; }

    /* Bobbing alt-colorway thumbnail is a nice-to-have on desktop; on a
       narrow single-column layout it just floats over the main image. */
    .kp-hero-float { display: none !important; }

    .kp-dots-positioned {
      position: static;
      margin-top: 1.25rem;
      justify-content: center;
    }
  }
</style>
@endpush

@section('content')

@php
$slides = [
  [
    'eyebrow'   => 'New Drop · AW26',
    'heading'   => "Built for the<br><span style='opacity:.75'>coldest days.</span>",
    'body'      => 'The Alpine Puffer uses recycled 700-fill down and a weather-sealed shell — warm enough to earn the silence.',
    'cta'       => 'Shop the Drop',
    'cta_url'   => route('shop'),
    'price'     => '$279',
    'was'       => '$398',
    'bg'        => 'linear-gradient(135deg,#f97316 0%,#fb923c 40%,#fdba74 80%,#fed7aa 100%)',
    'btn_bg'    => '#7c2d12',
    'img_main'  => asset('assets/wear/catalog/generated/product-09.jpg'),
    'img_thumb' => asset('assets/wear/catalog/generated/product-05.jpg'),
    'side_words'=> ['DOWN', 'WARMTH'],
  ],
  [
    'eyebrow'   => 'Limited Edition',
    'heading'   => "Street-ready<br><span style='opacity:.75'>all winter.</span>",
    'body'      => 'The Urban Shell repels rain and locks heat — designed for city movement, finished for standing still.',
    'cta'       => 'Explore the Edit',
    'cta_url'   => route('collections'),
    'price'     => '$319',
    'was'       => '$420',
    'bg'        => 'linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 45%,#60a5fa 90%)',
    'btn_bg'    => '#1e3a5f',
    'img_main'  => asset('assets/wear/catalog/generated/product-02.jpg'),
    'img_thumb' => asset('assets/wear/catalog/generated/product-07.jpg'),
    'side_words'=> ['SHELL', 'RAIN'],
  ],
  [
    'eyebrow'   => 'Best Seller',
    'heading'   => "Wear the<br><span style='opacity:.75'>mountain home.</span>",
    'body'      => "Compression-packable, wind-resistant, and surprisingly light — the one jacket you'll reach for every time.",
    'cta'       => 'Add to Bag',
    'cta_url'   => route('shop'),
    'price'     => '$249',
    'was'       => '$360',
    'bg'        => 'linear-gradient(135deg,#14532d 0%,#16a34a 45%,#86efac 90%)',
    'btn_bg'    => '#14532d',
    'img_main'  => asset('assets/wear/catalog/generated/product-04.jpg'),
    'img_thumb' => asset('assets/wear/catalog/generated/product-01.jpg'),
    'side_words'=> ['LIGHT', 'TRAIL'],
  ],
];
@endphp

{{-- ── Hero carousel ──────────────────────────────────────────── --}}
<section class="kp-hero-section bg-white px-4 py-6 sm:px-6 lg:px-8">
  {{-- Same 540px hero height, widened to 1280px for a fuller desktop presentation --}}
  <div class="mx-auto grid w-full max-w-[1900px] overflow-hidden rounded-3xl bg-white shadow-[0_28px_80px_rgba(15,23,42,0.10)] lg:grid-cols-[0.95fr_1.05fr]">

    {{-- LEFT: restored word-led hero treatment --}}
    <div class="flex min-h-[540px] items-center bg-white px-4 py-10 sm:px-8 lg:px-10 xl:px-12">
      <div class="max-w-[520px]">
        <span class="mb-5 block text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">
          KP Wear
        </span>

        <h1 class="text-[clamp(3rem,5vw,5.6rem)] font-black leading-[0.9] tracking-[-0.055em] text-slate-950">
          <span class="block">WEAR THE</span>
          <span class="block text-emerald-600">CULTURE.</span>
          <span class="block">LIVE THE</span>
          <span class="block text-emerald-600">STORY.</span>
        </h1>

        <p class="mt-7 max-w-[470px] text-base leading-7 text-slate-600 sm:text-lg">
          Premium quality. Bold designs.<br>
          Made for everyday legends.
        </p>

        <div class="mt-7 flex flex-wrap gap-3">
          <a
            href="{{ route('shop') }}"
            class="inline-flex items-center justify-center rounded-xl bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-600"
          >
            Shop Collection
            <span class="ml-2 text-base">→</span>
          </a>

          <a
            href="{{ route('collections') }}"
            class="inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-950 transition hover:border-slate-300 hover:bg-slate-50"
          >
            Explore
          </a>
        </div>
      </div>
    </div>

    {{-- RIGHT: existing visual carousel stays here --}}
    <div class="relative min-h-[540px] bg-stone-100">
      <div id="kp-hero-card" class="relative h-full min-h-[540px] overflow-hidden" tabindex="0" role="region" aria-label="Featured product carousel — use left and right arrow keys to navigate">

        @foreach($slides as $i => $slide)
        <div class="kp-slide kp-slide-grid {{ $i === 0 ? '' : 'hidden' }} absolute inset-0 grid"
             data-slide="{{ $i }}"
             style="grid-template-columns:1fr auto;">

          <div class="flex flex-col justify-center p-7 pr-2 sm:p-9 lg:p-10">
            <div class="max-w-[270px]">
              <span class="text-white/65 text-xs font-semibold tracking-widest uppercase">
                {{ $slide['eyebrow'] }}
              </span>

              <h2 class="mt-4 text-white font-black leading-[0.98] tracking-tight"
                  style="font-size:clamp(2rem,3.4vw,3.55rem)">
                {!! $slide['heading'] !!}
              </h2>

              <p class="mt-4 max-w-[255px] text-sm leading-6 text-white/75">
                {{ $slide['body'] }}
              </p>

              <a
                href="{{ $slide['cta_url'] }}"
                class="mt-6 inline-flex items-center rounded-full px-5 py-2.5 text-sm font-semibold text-white transition hover:opacity-85 active:scale-95"
                style="background:{{ $slide['btn_bg'] }};box-shadow:0 8px 24px rgba(0,0,0,0.22)"
              >
                {{ $slide['cta'] }}
                <span class="ml-2">→</span>
              </a>
            </div>
          </div>

          <div class="kp-hero-visual relative flex min-w-[220px] items-center justify-end pr-5 py-7 sm:min-w-[260px] sm:pr-7 lg:min-w-[275px] lg:pr-8">
            <div class="kp-hero-img-wrap relative z-10">
              <div
                class="absolute bottom-4 left-1/2 h-8 w-36 -translate-x-1/2 rounded-full blur-2xl"
                style="background:rgba(0,0,0,0.22)"
              ></div>

              <img
                src="{{ $slide['img_main'] }}"
                alt="{{ $slide['eyebrow'] }}"
                class="kp-hero-img relative z-10 h-[330px] w-[220px] rounded-2xl object-cover object-top sm:h-[380px] sm:w-[250px] lg:h-[400px] lg:w-[265px]"
                style="filter:drop-shadow(0 20px 40px rgba(0,0,0,0.28))"
              >
            </div>

            <div
              class="kp-hero-float absolute bottom-7 left-4 z-20 w-20 overflow-hidden rounded-2xl shadow-lg sm:left-5 sm:w-24"
              style="border:4px solid rgba(255,255,255,0.68)"
            >
              <img
                src="{{ $slide['img_thumb'] }}"
                alt="Alternate colorway"
                class="aspect-[3/4] w-full object-cover object-top"
              >
              <div class="absolute inset-x-0 bottom-1.5 flex justify-center">
                <span class="rounded-full bg-black/40 px-1.5 py-0.5 text-[8px] font-semibold text-white">
                  +2 colors
                </span>
              </div>
            </div>
          </div>
        </div>
        @endforeach

        @if(count($slides) > 1)
        <div id="kp-dots" class="kp-dots-positioned">
          @foreach($slides as $d => $_)
          <button
            class="kp-dot {{ $d === 0 ? 'active' : '' }}"
            data-goto="{{ $d }}"
            aria-label="Go to slide {{ $d + 1 }}"
          ></button>
          @endforeach
        </div>
        @endif
      </div>
    </div>
  </div>
</section>

{{-- ── Categories ─────────────────────────────────────────────── --}}
<section class="mx-auto max-w-[1600px] px-6 py-14 sm:px-8 lg:px-10">
  <div class="mb-8 flex items-end justify-between">
    <div>
      <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">
        Browse
      </p>

      <h2 class="mt-2 text-3xl font-bold tracking-tight">
        Shop by category
      </h2>
    </div>

    <a
      href="{{ route('shop') }}"
      class="inline-flex items-center gap-1.5 rounded-full border border-emerald-600 px-5 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-600 hover:text-white"
    >
      View all <span aria-hidden="true">→</span>
    </a>
  </div>

  <div data-home-categories class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-5" aria-live="polite">
    {{-- Skeleton shown until JS replaces this content --}}
    @for($i = 0; $i < 5; $i++)
      <div class="animate-pulse">
        <div class="aspect-square rounded-2xl bg-gray-100"></div>
        <div class="mt-2 h-3 w-2/3 rounded bg-gray-100"></div>
      </div>
    @endfor
  </div>
</section>


{{-- ── Featured pieces ────────────────────────────────────────── --}}
<section class="bg-gray-50 py-14">
  <div class="mx-auto max-w-[1600px] px-6 sm:px-8 lg:px-10">
    <div class="mb-8 flex items-end justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">
          The edit
        </p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight">
          Featured pieces
        </h2>
      </div>

      <a
        href="{{ route('shop') }}"
        class="text-sm font-medium text-emerald-600"
      >
        Shop all →
      </a>
    </div>

    <div
      data-home-featured
      class="grid grid-cols-2 gap-x-6 gap-y-10 md:grid-cols-3 lg:grid-cols-4 lg:gap-8"
    >
      {{-- Skeleton shown until JS replaces this content --}}
      @for($i = 0; $i < 8; $i++)
        <div class="animate-pulse">
          <div class="aspect-[3/4] rounded-2xl bg-gray-100"></div>
          <div class="mt-3 h-3 w-3/4 rounded bg-gray-100"></div>
          <div class="mt-2 h-3 w-1/3 rounded bg-gray-100"></div>
        </div>
      @endfor
    </div>
  </div>
</section>

{{-- ── Editorial banner ───────────────────────────────────────── --}}
@include('components.editorial-banner')

@endsection

@push('scripts')
<script>
(function () {
  const HOLD = 4000;
  const DUR  = 650;

  const card   = document.getElementById('kp-hero-card');
  const slides = document.querySelectorAll('[data-slide]');
  const dots   = document.querySelectorAll('.kp-dot');
  const bgs    = @json(array_column($slides, 'bg'));

  let current = 0;
  let locked  = false;

  // Move dots out of slide 0 so they survive slide transitions.
  // Positioning now lives in the .kp-dots-positioned CSS class (with its
  // own mobile override) instead of an inline cssText, so it responds to
  // the viewport instead of always being absolute bottom-left.
  const dotsEl = document.getElementById('kp-dots');
  card.appendChild(dotsEl);
  dotsEl.classList.add('kp-dots-positioned');

  // Boot first slide
  slides[0].classList.add('is-visible');
  card.style.background = bgs[0];

  function goTo(next) {
    if (locked || next === current) return;
    locked = true;

    const cur = slides[current];
    const nxt = slides[next];

    cur.classList.remove('is-visible');
    cur.classList.add('is-exiting');

    nxt.classList.remove('hidden');
    nxt.classList.add('is-entering');

    requestAnimationFrame(() => requestAnimationFrame(() => {
      nxt.classList.remove('is-entering');
      nxt.classList.add('is-visible');
      card.style.background = bgs[next];
      dots.forEach((d, i) => d.classList.toggle('active', i === next));
    }));

    setTimeout(() => {
      cur.classList.remove('is-exiting');
      cur.classList.add('hidden', 'is-entering');
      current = next;
      locked  = false;
    }, DUR);
  }

  // Dot clicks
  dots.forEach(d => d.addEventListener('click', () => {
    goTo(+d.dataset.goto);
    resetTimer();
  }));

  // Auto-advance
  function advance() { goTo((current + 1) % slides.length); }
  let timer = setInterval(advance, HOLD + DUR);
  function resetTimer() { clearInterval(timer); timer = setInterval(advance, HOLD + DUR); }

  // Pause auto-advance while the user is hovering or focused on the carousel
  card.addEventListener('mouseenter', () => clearInterval(timer));
  card.addEventListener('mouseleave', () => resetTimer());
  card.addEventListener('focusin', () => clearInterval(timer));
  card.addEventListener('focusout', () => resetTimer());

  // Keyboard navigation (left/right arrows) when the carousel is focused
  card.addEventListener('keydown', (e) => {
    if (e.key === 'ArrowRight') {
      goTo((current + 1) % slides.length);
      resetTimer();
    } else if (e.key === 'ArrowLeft') {
      goTo((current - 1 + slides.length) % slides.length);
      resetTimer();
    }
  });
})();
</script>
@endpush
