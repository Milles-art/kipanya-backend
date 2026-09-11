/**
 * KIPANYA WEAR — ANIMATION ENHANCER
 * Drop-in JS file. Add AFTER your existing scripts in wear.blade.php:
 *
 *   <script src="{{ asset('js/kipanya-wear-animations.js') }}" defer></script>
 *
 * This file enhances your existing Blade/JS without touching it.
 * All features degrade gracefully if elements are missing.
 */

(function () {
  'use strict';

  /* ─── HELPERS ──────────────────────────────────────────── */
  const qs  = (sel, root = document) => root.querySelector(sel);
  const qsa = (sel, root = document) => [...root.querySelectorAll(sel)];

  /* ─── 1. HEADER: SCROLL SHADOW ─────────────────────────── */
  function initScrollHeader() {
    const header = qs('.kp-header');
    if (!header) return;

    const io = new IntersectionObserver(
      ([entry]) => header.classList.toggle('is-scrolled', !entry.isIntersecting),
      { rootMargin: '-1px 0px 0px 0px', threshold: 0 }
    );

    // Observe a sentinel just below the header
    const sentinel = document.createElement('div');
    sentinel.style.cssText = 'position:absolute;top:120px;left:0;height:1px;width:1px;pointer-events:none;';
    document.body.prepend(sentinel);
    io.observe(sentinel);
  }

  /* ─── 2. MOBILE MENU: SLIDE-IN ──────────────────────────── */
  function initMobileMenu() {
    const toggle  = qs('[data-kp-menu-toggle]');
    const menu    = qs('[data-kp-mobile-menu]');
    if (!toggle || !menu) return;

    function openMenu() {
      menu.classList.add('is-open');
      toggle.setAttribute('aria-expanded', 'true');
      document.body.style.overflow = 'hidden';
    }

    function closeMenu() {
      menu.classList.remove('is-open');
      toggle.setAttribute('aria-expanded', 'false');
      document.body.style.overflow = '';
    }

    toggle.addEventListener('click', () => {
      menu.classList.contains('is-open') ? closeMenu() : openMenu();
    });

    // Click scrim (the ::before overlay area)
    menu.addEventListener('click', (e) => {
      if (e.target === menu) closeMenu();
    });

    // Escape key
    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && menu.classList.contains('is-open')) closeMenu();
    });
  }

  /* ─── 3. SEARCH PANEL: SMOOTH SLIDE ────────────────────── */
  function initSearchPanel() {
    const toggleBtn = qs('[data-kp-search-toggle]');
    const panel     = qs('[data-kp-search-panel]');
    const closeBtn  = qs('[data-kp-search-close]');
    const input     = qs('[data-kp-search-input]');
    if (!toggleBtn || !panel) return;

    function openSearch() {
      panel.removeAttribute('hidden');
      requestAnimationFrame(() => {
        panel.classList.add('is-open');
        toggleBtn.setAttribute('aria-expanded', 'true');
        setTimeout(() => input?.focus(), 50);
      });
    }

    function closeSearch() {
      panel.classList.remove('is-open');
      toggleBtn.setAttribute('aria-expanded', 'false');
      setTimeout(() => panel.setAttribute('hidden', ''), 420);
    }

    toggleBtn.addEventListener('click', () => {
      panel.classList.contains('is-open') ? closeSearch() : openSearch();
    });

    closeBtn?.addEventListener('click', closeSearch);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && panel.classList.contains('is-open')) closeSearch();
    });
  }

  /* ─── 4. HERO SLIDER: CROSSFADE + TEXT REVEAL ───────────── */
  function initHeroSlider() {
    const slider   = qs('[data-wear-slider]');
    const slides   = qsa('[data-wear-slide]', slider || document);
    const dots     = qsa('[data-wear-dot]');
    const prevBtn  = qs('[data-wear-prev]');
    const nextBtn  = qs('[data-wear-next]');
    if (!slides.length) return;

    let current   = 0;
    let timer     = null;
    const DELAY   = 5500;

    function goTo(index) {
      // Remove active from current
      slides[current].classList.remove('is-active');
      slides[current].setAttribute('aria-hidden', 'true');
      slides[current].setAttribute('inert', '');
      dots[current]?.classList.remove('is-active');

      current = (index + slides.length) % slides.length;

      slides[current].classList.add('is-active');
      slides[current].setAttribute('aria-hidden', 'false');
      slides[current].removeAttribute('inert');
      dots[current]?.classList.add('is-active');
    }

    function next() { goTo(current + 1); }
    function prev() { goTo(current - 1); }

    function startTimer() {
      clearInterval(timer);
      timer = setInterval(next, DELAY);
    }

    function pauseTimer() { clearInterval(timer); }

    prevBtn?.addEventListener('click', () => { prev(); startTimer(); });
    nextBtn?.addEventListener('click', () => { next(); startTimer(); });

    dots.forEach(dot => {
      dot.addEventListener('click', () => {
        goTo(parseInt(dot.dataset.wearDot, 10));
        startTimer();
      });
    });

    // Pause on hover / touch
    slider?.addEventListener('mouseenter', pauseTimer);
    slider?.addEventListener('mouseleave', startTimer);
    slider?.addEventListener('touchstart', pauseTimer, { passive: true });
    slider?.addEventListener('touchend',   startTimer, { passive: true });

    // Swipe support
    let touchX = 0;
    slider?.addEventListener('touchstart', e => { touchX = e.touches[0].clientX; }, { passive: true });
    slider?.addEventListener('touchend', e => {
      const dx = e.changedTouches[0].clientX - touchX;
      if (Math.abs(dx) > 48) { dx < 0 ? next() : prev(); startTimer(); }
    }, { passive: true });

    startTimer();
  }

  /* ─── 5. PRODUCT CARDS: STAGGERED ENTRANCE ──────────────── */
  function initCardEntrance() {
    function staggerCards(container) {
      const cards = qsa('.kp-card', container);
      cards.forEach((card, i) => {
        card.style.animationDelay = `${i * 60}ms`;
      });
    }

    // Cards already in DOM
    staggerCards(document);

    // Cards added dynamically (catalog JS loads them)
    const productsGrid = qs('[data-kp-products]');
    if (productsGrid) {
      const mo = new MutationObserver(() => staggerCards(productsGrid));
      mo.observe(productsGrid, { childList: true });
    }
  }

  /* ─── 6. BAG DRAWER: SLIDE-IN ───────────────────────────── */
  function initBagDrawer() {
    const drawer  = qs('[data-kp-bag-drawer]');
    const scrim   = qs('[data-kp-bag-scrim]');
    const closeBtn = qs('[data-kp-bag-close]');
    const openBtns = qsa('[data-kp-bag-open]');
    if (!drawer) return;

    function openDrawer() {
      drawer.removeAttribute('hidden');
      scrim.removeAttribute('hidden');
      requestAnimationFrame(() => {
        drawer.classList.add('is-open');
        scrim.classList.add('is-open');
      });
      document.body.style.overflow = 'hidden';
      // Focus first focusable element
      setTimeout(() => closeBtn?.focus(), 50);
    }

    function closeDrawer() {
      drawer.classList.remove('is-open');
      scrim.classList.remove('is-open');
      document.body.style.overflow = '';
      setTimeout(() => {
        drawer.setAttribute('hidden', '');
        scrim.setAttribute('hidden', '');
      }, 420);
    }

    openBtns.forEach(btn => {
      btn.addEventListener('click', (e) => {
        e.preventDefault();
        openDrawer();
      });
    });

    closeBtn?.addEventListener('click', closeDrawer);
    scrim?.addEventListener('click', closeDrawer);

    document.addEventListener('keydown', (e) => {
      if (e.key === 'Escape' && drawer.classList.contains('is-open')) closeDrawer();
    });

    // Expose so existing bag JS can call it
    window.KipanyaDrawer = { open: openDrawer, close: closeDrawer };
  }

  /* ─── 7. STICKY ATC BAR: SLIDE UP ───────────────────────── */
  function initStickyAtc() {
    const bar     = qs('[data-kp-sticky-atc]');
    const mainBtn = qs('[data-kp-add-to-cart]');
    if (!bar || !mainBtn) return;

    bar.removeAttribute('hidden');

    const io = new IntersectionObserver(
      ([entry]) => bar.classList.toggle('is-visible', !entry.isIntersecting),
      { threshold: 0 }
    );
    io.observe(mainBtn);
  }

  /* ─── 8. ACCORDION: SMOOTH HEIGHT ───────────────────────── */
  function initAccordion() {
    const items = qsa('.kp-acc-item');
    items.forEach(item => {
      const head = qs('[data-kp-acc-head]', item);
      const body = qs('.kp-acc-body', item);
      if (!head || !body) return;

      // Initialise open state
      if (item.classList.contains('kp-acc-open')) {
        body.style.maxHeight = body.scrollHeight + 'px';
        body.style.opacity   = '1';
        body.removeAttribute('hidden');
      } else {
        body.style.maxHeight = '0';
        body.style.opacity   = '0';
      }

      head.addEventListener('click', () => {
        const isOpen = item.classList.contains('kp-acc-open');
        // Close all
        items.forEach(i => {
          i.classList.remove('kp-acc-open');
          const b = qs('.kp-acc-body', i);
          if (b) { b.style.maxHeight = '0'; b.style.opacity = '0'; }
          qs('[data-kp-acc-head]', i)?.setAttribute('aria-expanded', 'false');
        });
        // Open clicked if was closed
        if (!isOpen) {
          item.classList.add('kp-acc-open');
          body.style.maxHeight = body.scrollHeight + 'px';
          body.style.opacity   = '1';
          head.setAttribute('aria-expanded', 'true');
        }
      });
    });
  }

  /* ─── 9. MODALS: SMOOTH OPEN/CLOSE ──────────────────────── */
  function initModals() {
    function attachModal(openSel, modal, closeSels) {
      if (!modal) return;

      const closeBtns = closeSels.map(s => qs(s)).filter(Boolean);

      function openModal() {
        modal.removeAttribute('hidden');
        requestAnimationFrame(() => modal.classList.add('is-open'));
        document.body.style.overflow = 'hidden';
        qs('[data-kp-quiz-height]', modal)?.focus();
      }

      function closeModal() {
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
        setTimeout(() => modal.setAttribute('hidden', ''), 300);
      }

      qsa(openSel).forEach(btn => btn.addEventListener('click', openModal));
      closeBtns.forEach(btn => btn.addEventListener('click', closeModal));

      // Click outside card closes modal
      modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
      });

      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && modal.classList.contains('is-open')) closeModal();
      });
    }

    attachModal('[data-kp-sizing-quiz-open]', qs('[data-kp-sizing-modal]'), ['[data-kp-sizing-close]']);
    attachModal('[data-kp-chart-open]',       qs('[data-kp-chart-modal]'),  ['[data-kp-chart-close]']);

    // Quick view modal
    const qvModal = qs('[data-kp-qv-modal]');
    if (qvModal) {
      const qvClose = qs('[data-kp-qv-close]', qvModal);
      function closeQv() {
        qvModal.classList.remove('is-open');
        setTimeout(() => qvModal.setAttribute('hidden', ''), 300);
      }
      qvClose?.addEventListener('click', closeQv);
      qvModal.addEventListener('click', e => { if (e.target === qvModal) closeQv(); });
      document.addEventListener('keydown', e => {
        if (e.key === 'Escape' && qvModal.classList.contains('is-open')) closeQv();
      });

      // Expose for catalog JS
      window.KipanyaQV = {
        open() {
          qvModal.removeAttribute('hidden');
          requestAnimationFrame(() => qvModal.classList.add('is-open'));
          document.body.style.overflow = 'hidden';
        },
        close: closeQv,
      };
    }
  }

  /* ─── 10. BOTTOM NAV: ACTIVE INDICATOR ──────────────────── */
  function initTabBar() {
    const tabs = qsa('.kp-tab');
    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        if (tab.tagName === 'A') return; // handled by page load
        tabs.forEach(t => t.classList.remove('is-active'));
        tab.classList.add('is-active');
      });
    });
  }

  /* ─── 11. CART COUNT BADGE: POP ANIMATION ───────────────── */
  function initCartCountAnim() {
    let prevCount = null;

    function animateBadge(badge) {
      badge.style.animation = 'none';
      badge.offsetHeight; // reflow
      badge.style.animation = '';
    }

    // Watch for any cart count element updates
    const observer = new MutationObserver(() => {
      const badges = qsa('[data-kp-cart-count]');
      badges.forEach(badge => {
        const count = badge.textContent.trim();
        if (count !== prevCount) {
          animateBadge(badge);
          prevCount = count;
        }
      });
    });

    qsa('[data-kp-cart-count]').forEach(badge => {
      observer.observe(badge, { childList: true, characterData: true, subtree: true });
    });
  }

  /* ─── 12. SCROLL-REVEAL: FADE UP ────────────────────────── */
  function initScrollReveal() {
    const targets = qsa('.kp-order-card, .kp-listing-head, .kp-footer-brand');
    if (!targets.length) return;

    targets.forEach(el => {
      el.style.opacity = '0';
      el.style.transform = 'translateY(16px)';
      el.style.transition = 'opacity 0.45s ease, transform 0.45s ease';
    });

    const io = new IntersectionObserver(
      (entries) => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.style.opacity = '1';
            entry.target.style.transform = 'translateY(0)';
            io.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.12 }
    );

    targets.forEach(el => io.observe(el));
  }

  /* ─── 13. SIZE BUTTON: ANIMATED SELECT ──────────────────── */
  function initSizeSelector() {
    function attachSizeGroup(group) {
      const btns = qsa('.kp-size-btn', group);
      btns.forEach(btn => {
        btn.addEventListener('click', () => {
          btns.forEach(b => b.classList.remove('is-selected'));
          btn.classList.add('is-selected');
        });
      });
    }

    // Existing groups
    qsa('[data-kp-size-selector], [data-kp-color-selector]').forEach(attachSizeGroup);

    // Dynamically added groups (product JS renders them)
    const buybox = qs('.kp-buybox');
    if (buybox) {
      const mo = new MutationObserver(() => {
        qsa('[data-kp-size-selector], [data-kp-color-selector]', buybox).forEach(attachSizeGroup);
      });
      mo.observe(buybox, { childList: true, subtree: true });
    }
  }

  /* ─── 14. ORDER CONFIRMATION: SUCCESS ICON ──────────────── */
  function initOrderConfirmation() {
    const card = qs('[data-kp-order-card]');
    if (!card) return;

    // Wait for your existing JS to populate the card, then inject icon
    const mo = new MutationObserver(() => {
      if (!card.dataset.kpIconDone && card.textContent.trim() !== 'Loading order...') {
        card.dataset.kpIconDone = '1';

        const isSuccess = !qs('.kp-error', card) && card.textContent.includes('Order');

        if (isSuccess) {
          const icon = document.createElement('div');
          icon.className = 'kp-success-icon';
          icon.setAttribute('aria-hidden', 'true');
          icon.innerHTML = '<i class="ti ti-check"></i>';
          card.prepend(icon);
        }

        mo.disconnect();
      }
    });

    mo.observe(card, { childList: true, subtree: true });
  }

  /* ─── 15. SUBTOTAL TICKER ───────────────────────────────── */
  function initSubtotalTicker() {
    function ticker(el, newVal) {
      if (!el) return;
      const match = newVal.match(/[\d,]+/);
      if (!match) { el.textContent = newVal; return; }

      const target  = parseInt(match[0].replace(/,/g, ''), 10);
      const current = parseInt((el.textContent.match(/[\d,]+/) || ['0'])[0].replace(/,/g, ''), 10);
      if (target === current) return;

      const start    = performance.now();
      const duration = 500;
      const prefix   = newVal.replace(/[\d,]+.*/, '');
      const suffix   = newVal.replace(/.*[\d,]/, '');

      function step(now) {
        const t = Math.min((now - start) / duration, 1);
        const ease = 1 - Math.pow(1 - t, 3);
        const val  = Math.round(current + (target - current) * ease);
        el.textContent = prefix + val.toLocaleString() + suffix;
        if (t < 1) requestAnimationFrame(step);
      }

      requestAnimationFrame(step);
    }

    function watchSubtotal(selector) {
      const el = qs(selector);
      if (!el) return;

      const mo = new MutationObserver(() => {
        ticker(el, el.textContent);
      });
      mo.observe(el, { childList: true, characterData: true, subtree: true });
    }

    watchSubtotal('[data-kp-cart-subtotal]');
    watchSubtotal('[data-kp-bag-subtotal]');
    watchSubtotal('[data-kp-checkout-subtotal]');
  }

  /* ─── 16. FORM FIELD: FLOATING FOCUS RING ───────────────── */
  function initFieldEffects() {
    qsa('.kp-field').forEach(field => {
      // Wrap in relative container if not already
      const parent = field.parentElement;
      if (!parent) return;

      field.addEventListener('focus', () => {
        parent.style.position = 'relative';
      });
    });
  }

  /* ─── BOOT ─────────────────────────────────────────────────
     Single-owner rule: interactive behavior stays in the feature
     modules (nav/drawer/catalog/product); this file adds only
     additive animation. Inits that duplicate an owner are OFF —
     the owners sync the matching is-open/is-visible classes. */
  function boot() {
    initScrollHeader();   // compatible duplicate of nav.js shadow toggle
    // initMobileMenu();  // OFF — owned by kipanya-nav.js (hidden + is-open sync)
    // initSearchPanel(); // OFF — owned by kipanya-nav.js (live results + is-open sync)
    // initHeroSlider();  // OFF — owned by kp-wear-catalog.js (would double-advance)
    // initCardEntrance();// OFF — targets .kp-card, which does not exist
    // initBagDrawer();   // OFF — owned by kp-bag-drawer.js (render + is-open sync)
    // initStickyAtc();   // OFF — owned by kp-wear-product.js (is-visible sync)
    initAccordion();      // owns accordion motion (product.js handler removed)
    initModals();         // transition layer; open/close owned by product/catalog JS
    initTabBar();
    initCartCountAnim();
    initScrollReveal();
    // initSizeSelector();// OFF — targets .kp-size-btn, which does not exist
    // initOrderConfirmation(); // OFF — confirmation page already renders icons
    // initSubtotalTicker();    // OFF — observer reads post-mutation text (no-op)
    // initFieldEffects();      // OFF — visible no-op
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
