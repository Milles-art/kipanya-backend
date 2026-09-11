(() => {
  const nav = document.querySelector('[data-wear-nav]');
  if (nav) {
    const searchToggle = nav.querySelector('[data-wear-search-toggle]');
    const search = nav.querySelector('[data-wear-search]');
    const searchInput = nav.querySelector('[data-wear-search-input]');
    const menuToggle = nav.querySelector('[data-wear-menu-toggle]');
    const mobileMenu = nav.querySelector('[data-wear-mobile-menu]');
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive: true }); onScroll();
    searchToggle?.addEventListener('click', () => { const open = search.hidden; search.hidden = !open; searchToggle.setAttribute('aria-expanded', String(open)); if(open) searchInput?.focus(); });
    menuToggle?.addEventListener('click', () => { const open = mobileMenu.hidden; mobileMenu.hidden = !open; menuToggle.setAttribute('aria-expanded', String(open)); nav.classList.toggle('menu-open', open); });
    document.addEventListener('keydown', e => { if(e.key !== 'Escape') return; if(search && !search.hidden){search.hidden=true;searchToggle?.setAttribute('aria-expanded','false')} if(mobileMenu && !mobileMenu.hidden){mobileMenu.hidden=true;menuToggle?.setAttribute('aria-expanded','false')} });
    searchInput?.addEventListener('keydown', e => { if(e.key==='Escape'){search.hidden=true;searchToggle?.setAttribute('aria-expanded','false');searchToggle?.focus()} });
  }

  const cards = [...document.querySelectorAll('[data-wear-quick-add]')];
  cards.forEach(btn => btn.addEventListener('click', async e => {
    e.preventDefault();
    e.stopPropagation();
    const slug = btn.dataset.productSlug;
    if (!slug || !window.KipanyaCart) return;
    btn.disabled = true;
    const original = btn.innerHTML;
    try {
      const res = await fetch(`/api/v1/wear/products/${encodeURIComponent(slug)}`, { headers: { Accept: 'application/json' } });
      const json = await res.json(); const product = json?.data ?? json;
      const variants = (product.variants ?? []).filter(v => v.in_stock);
      if (variants.length !== 1) {
        window.location.href = `/wear/products/${encodeURIComponent(slug)}`;
        return;
      }
      const result = await window.KipanyaCart.addItem(Number(variants[0].id), 1);
      if (!result.ok) throw new Error(result.raw?.message || 'Could not add to bag.');
      btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="m5 12 4 4L19 6"></path></svg>';
      setTimeout(() => { btn.innerHTML = original; }, 900);
    } catch (err) {
      btn.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6 18 18M18 6 6 18"></path></svg>';
      setTimeout(() => { btn.innerHTML = original; }, 900);
    } finally { btn.disabled = false; }
  }));
})();
