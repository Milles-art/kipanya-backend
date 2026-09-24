@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
  .kp-slide {
    opacity: 0;
    transform: translateX(60px);
    transition: opacity 300ms cubic-bezier(0.4,0,0.2,1),
                transform 300ms cubic-bezier(0.4,0,0.2,1);
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

  .kp-dots-positioned {
    position: absolute;
    bottom: 2.5rem;
    left: 2.5rem;
    display: flex;
    gap: 8px;
    z-index: 30;
  }

  @media (max-width: 860px) {
    .kp-hero-section { min-height: 0; padding-top: .5rem; padding-bottom: 1rem; }
    .kp-hero-section > .mx-auto { border-radius: 1.25rem; }
    .kp-hero-section > .mx-auto > div:first-child { min-height: auto !important; align-items: flex-start !important; padding: 1rem 1rem 1.1rem !important; }
    .kp-hero-section > .mx-auto > div:first-child > div { width: 100%; }
    .kp-hero-section > .mx-auto > div:first-child .mt-7 { margin-top: 1rem !important; }
    #kp-hero-card { min-height: 455px; }
    .kp-slide-grid { grid-template-columns: 1fr !important; }
    .kp-slide-grid > div:first-child { justify-content: flex-start !important; padding: 1.5rem 1.25rem .5rem !important; }
    .kp-hero-visual { min-width: 0 !important; min-height: 300px; align-items: center !important; justify-content: center !important; padding: .25rem 1rem 2.75rem !important; }
    .kp-hero-img-wrap { margin: 0 !important; }
    .kp-hero-img { width: min(64vw, 270px) !important; max-width: 270px !important; height: 300px !important; aspect-ratio: 3 / 4; object-fit: contain !important; object-position: center !important; }
    .kp-hero-float { display: block !important; left: 1rem !important; bottom: 2.25rem !important; width: 68px !important; border-width: 3px !important; animation: none !important; }
    .kp-dots-positioned { position: absolute; bottom: .9rem; left: 50%; transform: translateX(-50%); margin-top: 0; justify-content: center; }
  }

  /* ── Scroll-reveal, site-wide ───────────────────────────────── */
  .kp-reveal {
    opacity: 0;
    transform: translateY(32px);
    transition: opacity 650ms cubic-bezier(0.4,0,0.2,1), transform 650ms cubic-bezier(0.4,0,0.2,1);
  }
  .kp-reveal.is-visible { opacity: 1; transform: none; }

  /* ── Marquee ticker ───────────────────────────────────────────── */
  .kp-marquee { overflow: hidden; }
  .kp-marquee-track {
    display: flex;
    width: max-content;
    animation: kpMarquee 22s linear infinite;
  }
  .kp-marquee:hover .kp-marquee-track { animation-play-state: paused; }
  @keyframes kpMarquee {
    from { transform: translateX(0); }
    to   { transform: translateX(-50%); }
  }

  /* ── 3D tilt (CSS + lightweight JS, no library) ─────────────── */
  .kp-tilt { transform-style: preserve-3d; transition: transform 200ms ease, box-shadow 300ms ease; will-change: transform; }
  .kp-tilt:hover { box-shadow: 0 24px 48px rgba(15,23,42,0.14); }

  /* ── Magnetic buttons ─────────────────────────────────────────── */
  .kp-magnetic { transition: transform 200ms cubic-bezier(0.34,1.56,0.64,1); }

  @media (prefers-reduced-motion: reduce) {
    .kp-reveal { opacity: 1 !important; transform: none !important; transition: none !important; }
    .kp-marquee-track { animation: none !important; }
    .kp-tilt, .kp-magnetic { transition: none !important; transform: none !important; }
  }
</style>
@endpush

@section('content')

@php
$slides = [
  [
    'eyebrow'   => 'KP Wear · New Drop',
    'heading'   => "Built for<br><span style='opacity:.75'>everyday.</span>",
    'body'      => 'Signature KP Wear layers designed for everyday street style.',
    'cta'       => 'Shop the Drop',
    'cta_url'   => route('shop'),
    'price'     => '68,000 TZS',
    'was'       => '72,000 TZS',
    'bg'        => 'linear-gradient(135deg,#f97316 0%,#fb923c 40%,#fdba74 80%,#fed7aa 100%)',
    'btn_bg'    => '#7c2d12',
    'img_main'  => asset('assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-front.webp'),
    'img_thumb' => asset('assets/wear/catalog/products/hoodies/kp-wear-kp-icon-navy-front.webp'),
    'side_words'=> ['HOODIE', 'BOLD'],
  ],
  [
    'eyebrow'   => 'Streetwear Collection',
    'heading'   => "Redefine<br><span style='opacity:.75'>your everyday.</span>",
    'body'      => 'Designed for the everyday hustle. Versatile, comfortable, built to be worn.',
    'cta'       => 'Explore the Edit',
    'cta_url'   => route('collections'),
    'price'     => '44,000 TZS',
    'was'       => '50,000 TZS',
    'bg'        => 'linear-gradient(135deg,#1e3a5f 0%,#1d4ed8 45%,#60a5fa 90%)',
    'btn_bg'    => '#1e3a5f',
    'img_main'  => asset('assets/wear/catalog/products/t-shirts/kp-wear-redefined-graffiti-white-front.webp'),
    'img_thumb' => asset('assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-front.webp'),
    'side_words'=> ['TEE', 'STREET'],
  ],
  [
    'eyebrow'   => 'KP Wear Essentials',
    'heading'   => "Style lives<br><span style='opacity:.75'>here.</span>",
    'body'      => 'Clean KP Wear essentials built for everyday confidence.',
    'cta'       => 'Add to Bag',
    'cta_url'   => route('shop'),
    'price'     => '52,000 TZS',
    'was'       => '58,000 TZS',
    'bg'        => 'linear-gradient(135deg,#14532d 0%,#16a34a 45%,#86efac 90%)',
    'btn_bg'    => '#14532d',
    'img_main'  => asset('assets/wear/catalog/products/polos/kp-wear-white-graffiti-front.webp'),
    'img_thumb' => asset('assets/wear/catalog/products/polos/kp-wear-sand-brown-minimal-front.webp'),
    'side_words'=> ['POLO', 'ESSENTIAL'],
  ],
];
@endphp

{{-- ── Hero carousel ──────────────────────────────────────────── --}}
<section class="kp-hero-section bg-white px-4 py-6 sm:px-6 lg:px-8">
  <div class="mx-auto grid w-full max-w-[1900px] overflow-hidden rounded-3xl bg-white shadow-[0_28px_80px_rgba(15,23,42,0.10)] lg:grid-cols-[0.95fr_1.05fr]">

    <div class="flex min-h-[540px] items-center bg-white px-4 py-10 sm:px-8 lg:px-10 xl:px-12">
      <div class="max-w-[520px]">
        <span data-storefront-hero-eyebrow class="mb-5 block text-xs font-bold uppercase tracking-[0.22em] text-emerald-600">
          KP Wear
        </span>

        <h1 class="text-[clamp(3rem,5vw,5.6rem)] font-black leading-[0.9] tracking-[-0.055em] text-slate-950">
          <span data-storefront-hero-title class="block">WEAR THE</span>
          <span class="block text-emerald-600">CULTURE.</span>
          <span class="block">LIVE THE</span>
          <span class="block text-emerald-600">STORY.</span>
        </h1>

        <p data-storefront-hero-description class="mt-7 max-w-[470px] text-base leading-7 text-slate-600 sm:text-lg">
          Premium quality. Bold designs.<br>
          Made for everyday legends.
        </p>

        <div class="mt-7 flex flex-wrap gap-3">
          <a
            data-storefront-hero-cta
            href="{{ route('shop') }}"
            class="kp-magnetic inline-flex items-center justify-center rounded-xl bg-slate-950 px-6 py-3.5 text-sm font-semibold text-white transition hover:-translate-y-0.5 hover:bg-emerald-600"
          >
            <span data-storefront-hero-cta-label>Shop Collection</span>
            <span class="ml-2 text-base">→</span>
          </a>

          <a
            href="{{ route('collections') }}"
            class="kp-magnetic inline-flex items-center justify-center rounded-xl border border-slate-200 bg-white px-6 py-3.5 text-sm font-semibold text-slate-950 transition hover:border-slate-300 hover:bg-slate-50"
          >
            Explore
          </a>
        </div>
      </div>
    </div>

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
                data-storefront-hero-image
                src="{{ $slide['img_main'] }}"
                alt="{{ $slide['eyebrow'] }}"
                class="kp-hero-img relative z-10 h-[330px] w-[220px] rounded-2xl object-contain object-center sm:h-[380px] sm:w-[250px] lg:h-[400px] lg:w-[265px]"
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

{{-- ── Marquee ticker ────────────────────────────────────────────── --}}
<section class="border-y border-gray-100 bg-slate-950 py-3">
  <div class="kp-marquee">
    <div class="kp-marquee-track">
      @for($r = 0; $r < 2; $r++)
        @foreach(['NEW DROP · AW26', 'MADE FOR EVERYDAY LEGENDS', 'SHOP THE FULL EDIT'] as $msg)
          <span class="mx-6 flex items-center gap-3 whitespace-nowrap text-xs font-bold uppercase tracking-[0.2em] text-white/80">
            <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
            {{ $msg }}
          </span>
        @endforeach
      @endfor
    </div>
  </div>
</section>

{{-- ── Categories ─────────────────────────────────────────────── --}}
<section class="mx-auto max-w-[1600px] px-6 py-14 sm:px-8 lg:px-10">
  <div class="kp-reveal mb-8 flex items-end justify-between">
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
      class="kp-magnetic inline-flex items-center gap-1.5 rounded-full border border-emerald-600 px-5 py-2 text-sm font-medium text-emerald-600 transition hover:bg-emerald-600 hover:text-white"
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


{{-- ── Collections ─────────────────────────────────────────────── --}}
<section class="bg-white py-14">
  <div class="mx-auto max-w-[1600px] px-6 sm:px-8 lg:px-10">
    <div class="kp-reveal mb-8 flex items-end justify-between">
      <div>
        <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">
          Curated
        </p>
        <h2 class="mt-2 text-3xl font-bold tracking-tight">
          Shop collections
        </h2>
      </div>

      <a
        href="{{ route('collections') }}"
        class="text-sm font-medium text-emerald-600"
      >
        All collections →
      </a>
    </div>

    <div data-home-collections class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3" aria-live="polite">
      @for($i = 0; $i < 3; $i++)
        <div class="animate-pulse">
          <div class="aspect-[4/3] rounded-2xl bg-gray-100"></div>
          <div class="mt-3 h-3 w-1/2 rounded bg-gray-100"></div>
        </div>
      @endfor
    </div>
  </div>
</section>


{{-- ── Featured pieces ────────────────────────────────────────── --}}
<section class="bg-gray-50 py-14">
  <div class="mx-auto max-w-[1600px] px-6 sm:px-8 lg:px-10">
    <div class="kp-reveal mb-8 flex items-end justify-between">
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

    {{-- kp-tilt-group flags these cards for the 3D tilt-on-hover script
         once JS replaces the skeletons with real product cards. --}}
    <div
      data-home-featured
      data-tilt-group
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

{{-- ── Styling / Inspiration ──────────────────────────────────── --}}
<section class="bg-gray-50 py-14">
  <div class="mx-auto max-w-[1600px] px-6 sm:px-8 lg:px-10">
    <div class="kp-reveal mb-10">
      <p class="text-sm font-semibold uppercase tracking-wider text-emerald-600">Style guide</p>
      <h2 class="mt-2 text-3xl font-bold tracking-tight text-slate-950">How to wear it</h2>
    </div>

    <div class="kp-reveal grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
      @foreach([
        [
          'The Hoodie',
          'Signature layers with KP Wear attitude.',
          asset('assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-front.webp'),
        ],
        [
          'The Tee',
          'Graphic essentials made for everyday movement.',
          asset('assets/wear/catalog/products/t-shirts/kp-wear-kp-signature-block-white-front.webp'),
        ],
        [
          'The Polo',
          'A clean finish for everyday confidence.',
          asset('assets/wear/catalog/products/polos/kp-wear-black-icon-front.webp'),
        ],
      ] as [$title, $desc, $img])
        <div class="kp-tilt group relative block overflow-hidden rounded-2xl bg-slate-100">
          <img src="{{ $img }}" alt="{{ $title }}" class="aspect-[4/5] w-full object-cover transition duration-300 group-hover:scale-105">
          <div class="absolute inset-0 flex flex-col items-start justify-end bg-gradient-to-t from-black/60 via-black/0 to-transparent p-6 text-white">
            <h3 class="text-lg font-bold">{{ $title }}</h3>
            <p class="mt-2 text-sm text-white/85">{{ $desc }}</p>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</section>

{{-- ── Editorial banner ───────────────────────────────────────── --}}
@include('components.editorial-banner')

@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(function () {
  const HOLD = 2000;
  const DUR  = 300;

  const card   = document.getElementById('kp-hero-card');
  const slides = document.querySelectorAll('[data-slide]');
  const dots   = document.querySelectorAll('.kp-dot');
  const bgs    = @json(array_column($slides, 'bg'));

  let current = 0;
  let locked  = false;

  const dotsEl = document.getElementById('kp-dots');
  card.appendChild(dotsEl);
  dotsEl.classList.add('kp-dots-positioned');

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

  dots.forEach(d => d.addEventListener('click', () => {
    goTo(+d.dataset.goto);
    resetTimer();
  }));

  function advance() { goTo((current + 1) % slides.length); }
  let timer = setInterval(advance, HOLD + DUR);
  function resetTimer() { clearInterval(timer); timer = setInterval(advance, HOLD + DUR); }

  card.addEventListener('mouseenter', () => clearInterval(timer));
  card.addEventListener('mouseleave', () => resetTimer());
  card.addEventListener('focusin', () => clearInterval(timer));
  card.addEventListener('focusout', () => resetTimer());

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

// ── Scroll-reveal (site-wide) ─────────────────────────────────────
(function () {
  const reveals  = document.querySelectorAll('.kp-reveal');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  if (reduceMotion || !('IntersectionObserver' in window)) {
    reveals.forEach(el => el.classList.add('is-visible'));
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
})();

// ── 3D tilt on hover (CSS transform + pointer tracking, no library) ─
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (window.matchMedia('(hover: none)').matches) return; // skip on touch devices

  function attachTilt(el) {
    const MAX_TILT = 8;
    el.addEventListener('mousemove', (e) => {
      const rect = el.getBoundingClientRect();
      const x = (e.clientX - rect.left) / rect.width - 0.5;
      const y = (e.clientY - rect.top) / rect.height - 0.5;
      el.style.transform = `perspective(800px) rotateY(${x * MAX_TILT}deg) rotateX(${-y * MAX_TILT}deg) translateZ(0)`;
    });
    el.addEventListener('mouseleave', () => {
      el.style.transform = 'perspective(800px) rotateY(0deg) rotateX(0deg)';
    });
  }

  // Attach tilt to static cards (brand story, coming soon, styling, payments)
  document.querySelectorAll('.kp-tilt:not([data-tilt-bound])').forEach(el => {
    el.dataset.tiltBound = '1';
    attachTilt(el);
  });

  // Featured pieces are JS-populated later — watch the container and
  // attach tilt to any new .kp-tilt cards that appear inside it.
  const tiltGroup = document.querySelector('[data-tilt-group]');
  if (tiltGroup && 'MutationObserver' in window) {
    new MutationObserver(() => {
      tiltGroup.querySelectorAll('.kp-tilt:not([data-tilt-bound])').forEach(el => {
        el.dataset.tiltBound = '1';
        attachTilt(el);
      });
    }).observe(tiltGroup, { childList: true });
  }
})();

// ── Magnetic buttons ─────────────────────────────────────────────
(function () {
  if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  if (window.matchMedia('(hover: none)').matches) return;

  document.querySelectorAll('.kp-magnetic').forEach((el) => {
    const STRENGTH = 0.25;
    el.addEventListener('mousemove', (e) => {
      const rect = el.getBoundingClientRect();
      const x = (e.clientX - rect.left - rect.width / 2) * STRENGTH;
      const y = (e.clientY - rect.top - rect.height / 2) * STRENGTH;
      el.style.transform = `translate(${x}px, ${y}px)`;
    });
    el.addEventListener('mouseleave', () => { el.style.transform = 'translate(0, 0)'; });
  });
})();

</script>
@endpush
