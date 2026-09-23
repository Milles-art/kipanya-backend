
(function () {
  const reveals = document.querySelectorAll('.kp-reveal');
  if (!('IntersectionObserver' in window)) {
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
  }, { threshold: 0.12 });
  reveals.forEach(el => obs.observe(el));

  // Editorial campaign break after the first 12 products.
  // renderProductGrid creates the mount after product #12, so this re-runs
  // safely whenever filters, search or pagination re-render the catalog.
  let editorialTimer = null;
  let editorialMount = null;

  const initEditorialSlider = () => {
    if (editorialTimer) {
      window.clearInterval(editorialTimer);
      editorialTimer = null;
    }

    editorialMount = document.querySelector('[data-catalog-editorial-slider]');
    if (!editorialMount) return;

    const cards = Array.from(document.querySelectorAll('[data-catalog-grid] [data-product-card]'));
    if (cards.length < 12) return;

    const productImages = cards
      .slice(0, 4)
      .map((card) => card.querySelector('img'))
      .filter(Boolean)
      .map((img) => img.currentSrc || img.src);

    if (!productImages.length) return;

    const slides = [
      {
        eyebrow: 'KP Wear',
        title: 'Wear Your Story',
        copy: 'Everyday pieces. Distinctly KP.',
        action: 'Shop Now',
      },
      {
        eyebrow: 'Made for movement',
        title: 'Move in KP',
        copy: 'Clean silhouettes, bold details and everyday comfort.',
        action: 'Explore Collection',
      },
      {
        eyebrow: 'Everyday essentials',
        title: 'Built for Your Rotation',
        copy: 'Pieces designed to stay with you from day to day.',
        action: 'Discover More',
      },
    ];

    editorialMount.innerHTML = `
      <section class="relative isolate min-h-[235px] overflow-hidden rounded-[1.75rem] bg-emerald-950 text-white shadow-sm sm:min-h-[255px] lg:min-h-[275px]" aria-label="KP Wear campaign slider">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_72%_40%,rgba(16,185,129,.24),transparent_38%),linear-gradient(110deg,#022c22_0%,#064e3b_52%,#022c22_100%)]" aria-hidden="true"></div>
        <div class="absolute -left-20 -top-28 h-72 w-72 rounded-full border border-white/10" aria-hidden="true"></div>
        <div class="absolute -right-20 -bottom-40 h-96 w-96 rounded-full border border-emerald-300/10" aria-hidden="true"></div>
        <div class="absolute inset-y-0 left-0 w-1/3 bg-gradient-to-r from-black/20 to-transparent" aria-hidden="true"></div>

        <div class="relative grid min-h-[235px] items-center gap-5 px-5 py-7 sm:min-h-[255px] sm:px-8 lg:min-h-[275px] lg:grid-cols-[1fr_1.15fr_.55fr] lg:px-10">
          <div class="relative z-20 max-w-md">
            <p data-editorial-eyebrow class="text-[10px] font-bold uppercase tracking-[.24em] text-emerald-300"></p>
            <h2 data-editorial-title class="mt-2 text-3xl font-black leading-none tracking-[-.04em] sm:text-4xl lg:text-5xl"></h2>
            <p data-editorial-copy class="mt-3 max-w-sm text-sm leading-5 text-white/70 sm:text-[15px]"></p>
            <a href="/shop" class="mt-5 inline-flex items-center gap-2 rounded-full bg-emerald-500 px-5 py-2.5 text-xs font-bold text-black transition hover:bg-emerald-400 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300 focus-visible:ring-offset-2 focus-visible:ring-offset-emerald-950">
              <span data-editorial-action-label></span>
              <x-tabler-arrow-right size="15" stroke-width="2" />
            </a>
          </div>

          <div class="relative hidden h-full min-h-[205px] items-end justify-center sm:flex">
            <div class="absolute bottom-2 h-40 w-40 rounded-full bg-emerald-400/10 blur-2xl" aria-hidden="true"></div>
            <img data-editorial-image-main class="relative z-10 h-[205px] w-[150px] object-contain drop-shadow-[0_22px_30px_rgba(0,0,0,.45)] transition-opacity duration-300 lg:h-[235px] lg:w-[175px]" alt="KP Wear featured product" />
            <img data-editorial-image-secondary class="absolute bottom-3 left-1/2 z-0 h-32 w-24 -translate-x-[115%] rotate-[-7deg] object-contain opacity-80 drop-shadow-xl" alt="KP Wear featured product" />
          </div>

          <div class="hidden text-right lg:block">
            <p class="text-[10px] font-semibold uppercase tracking-[.34em] text-white/45">KP Wear</p>
            <p class="mt-3 text-sm font-semibold uppercase leading-7 tracking-[.18em] text-white/80">Made<br>for<br>movement</p>
          </div>
        </div>

        <div class="absolute bottom-4 left-5 right-5 z-30 flex items-center justify-between sm:left-8 sm:right-8 lg:left-10 lg:right-10">
          <div class="flex items-center gap-1.5" data-editorial-dots aria-label="Campaign slides"></div>
          <div class="flex items-center gap-2">
            <button type="button" data-editorial-prev class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow-sm transition hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300" aria-label="Previous campaign slide">
              <x-tabler-chevron-left size="17" stroke-width="1.8" />
            </button>
            <button type="button" data-editorial-next class="flex h-9 w-9 items-center justify-center rounded-full bg-white text-black shadow-sm transition hover:bg-emerald-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-300" aria-label="Next campaign slide">
              <x-tabler-chevron-right size="17" stroke-width="1.8" />
            </button>
          </div>
        </div>
      </section>`;

    const title = editorialMount.querySelector('[data-editorial-title]');
    const eyebrow = editorialMount.querySelector('[data-editorial-eyebrow]');
    const copy = editorialMount.querySelector('[data-editorial-copy]');
    const actionLabel = editorialMount.querySelector('[data-editorial-action-label]');
    const imageMain = editorialMount.querySelector('[data-editorial-image-main]');
    const imageSecondary = editorialMount.querySelector('[data-editorial-image-secondary]');
    const dots = editorialMount.querySelector('[data-editorial-dots]');
    let index = 0;

    const render = () => {
      const slide = slides[index];
      eyebrow.textContent = slide.eyebrow;
      title.textContent = slide.title;
      copy.textContent = slide.copy;
      actionLabel.textContent = slide.action;
      imageMain.src = productImages[index % productImages.length];
      imageSecondary.src = productImages[(index + 1) % productImages.length];

      Array.from(dots.children).forEach((dot, i) => {
        dot.classList.toggle('w-7', i === index);
        dot.classList.toggle('w-2', i !== index);
        dot.classList.toggle('bg-emerald-300', i === index);
        dot.classList.toggle('bg-white/30', i !== index);
      });
    };

    slides.forEach((_, i) => {
      const dot = document.createElement('button');
      dot.type = 'button';
      dot.className = 'h-1.5 rounded-full transition-all duration-200';
      dot.setAttribute('aria-label', `Go to campaign slide ${i + 1}`);
      dot.addEventListener('click', () => {
        index = i;
        render();
        restart();
      });
      dots.appendChild(dot);
    });

    const next = () => {
      index = (index + 1) % slides.length;
      render();
    };

    const prev = () => {
      index = (index - 1 + slides.length) % slides.length;
      render();
    };

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const restart = () => {
      window.clearInterval(editorialTimer);
      editorialTimer = null;
      if (!reduceMotion) editorialTimer = window.setInterval(next, 5000);
    };

    editorialMount.querySelector('[data-editorial-next]').addEventListener('click', () => {
      next();
      restart();
    });
    editorialMount.querySelector('[data-editorial-prev]').addEventListener('click', () => {
      prev();
      restart();
    });
    // Keep autoplay running while the pointer is over the campaign.
    editorialMount.addEventListener('focusin', () => window.clearInterval(editorialTimer));
    editorialMount.addEventListener('focusout', restart);

    render();
    restart();
  };

  document.addEventListener('kp:grid-rendered', initEditorialSlider);
  initEditorialSlider();
})();
