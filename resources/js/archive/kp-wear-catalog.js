/**
 * KP Wear catalog: dynamic featured hero, live-taxonomy pills + tiles with
 * URL state, merchandised product cards (swatches, sale, stock), variant-
 * aware quick-add, load-more pagination, search. Backend is source of truth.
 */
(() => {
    const root = document.querySelector('[data-kp-catalog]');
    if (!root) return;

    const $ = (sel, r = root) => r.querySelector(sel);
    const $$ = (sel, r = root) => [...r.querySelectorAll(sel)];
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const slugify = (s) => String(s ?? '').toLowerCase().replace(/\s+/g, '-');
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    const toast = (m, t) => window.KipanyaToast?.show(m, t);
    const loginNotice = () => toast('Favorites need a Kipanya account — account sign-in on web is coming soon.', 'info');

    const COLOR_HEX = {
        black: '#17130f', white: '#ffffff', navy: '#1f3350', blue: '#2456a6',
        red: '#b3261e', maroon: '#6b1f2a', green: '#166534', grey: '#8a8a8a',
        gray: '#8a8a8a', beige: '#d9c7a7', cream: '#f3ead7', brown: '#6b4a2f',
        pink: '#e8a0b4', yellow: '#d9a400', orange: '#d85a30',
    };
    const swatchColor = (name) => COLOR_HEX[String(name || '').toLowerCase()] || '#b9b2a8';

    const slider = $('[data-wear-slider]');
    const skeleton = $('[data-kp-skeleton]');
    const grid = $('[data-kp-products]');
    const count = $('[data-kp-product-count]');
    const pillsRow = $('[data-kp-categories]');
    const loadMoreWrap = $('[data-kp-load-more-wrap]');
    const loadMoreBtn = $('[data-kp-load-more]');
    const searchChip = $('[data-kp-search-chip]');
    const sectionTitle = $('#kp-featured-title');

    const setVisible = (showGrid) => {
        skeleton.hidden = showGrid;
        grid.hidden = !showGrid;
        skeleton.setAttribute('aria-hidden', showGrid ? 'true' : 'false');
    };

    async function fetchJson(url) {
        const res = await fetch(url, { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error(`Request failed: ${res.status}`);
        return res.json();
    }

    /* ================= Hero (featured products, static fallback) ================= */
    let slideIndex = 0;
    let slideTimer = null;
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;

    const slidesOf = () => $$('[data-wear-slide]', slider);
    const dotsOf = () => $$('[data-wear-dot]', slider);

    const showSlide = (next) => {
        const slides = slidesOf();
        const dots = dotsOf();
        if (!slides.length) return;
        slideIndex = (next + slides.length) % slides.length;
        slides.forEach((slide, i) => {
            const active = i === slideIndex;
            slide.classList.toggle('is-active', active);
            slide.setAttribute('aria-hidden', active ? 'false' : 'true');
            if (active) slide.removeAttribute('inert');
            else slide.setAttribute('inert', '');
        });
        dots.forEach((dot, i) => {
            const active = i === slideIndex;
            dot.classList.toggle('is-active', active);
            if (active) dot.setAttribute('aria-current', 'true');
            else dot.removeAttribute('aria-current');
        });
    };
    const restartSlider = () => {
        window.clearInterval(slideTimer);
        if (reduceMotion || !slidesOf().length) return;
        slideTimer = window.setInterval(() => showSlide(slideIndex + 1), 6500);
    };
    const wireSlider = () => {
        if (!slider || slider.dataset.wired) return;
        slider.dataset.wired = '1';
        $('[data-wear-next]', slider)?.addEventListener('click', () => { showSlide(slideIndex + 1); restartSlider(); });
        $('[data-wear-prev]', slider)?.addEventListener('click', () => { showSlide(slideIndex - 1); restartSlider(); });
        slider.addEventListener('click', (e) => {
            const dot = e.target.closest('[data-wear-dot]');
            if (dot) { showSlide(Number(dot.dataset.wearDot)); restartSlider(); }
        });
        slider.addEventListener('mouseenter', () => window.clearInterval(slideTimer));
        slider.addEventListener('mouseleave', restartSlider);
        slider.addEventListener('focusin', () => window.clearInterval(slideTimer));
        slider.addEventListener('focusout', restartSlider);
    };

    async function buildHero() {
        wireSlider();
        try {
            const json = await fetchJson('/api/v1/wear/products?featured=1&per_page=6');
            const featured = (json.data ?? []).filter((p) => p.image).slice(0, 3);
            if (featured.length < 2) { showSlide(0); restartSlider(); return; }
            const oldSlides = slidesOf();
            oldSlides.forEach((s) => s.remove());
            const dotsWrap = slider.querySelector('.kp-slider-dots');
            if (dotsWrap) dotsWrap.innerHTML = '';
            featured.forEach((p, i) => {
                const slide = document.createElement('article');
                slide.className = `kp-slide${i === 0 ? ' is-active' : ''}`;
                slide.dataset.wearSlide = '';
                slide.setAttribute('aria-hidden', i === 0 ? 'false' : 'true');
                if (i !== 0) slide.setAttribute('inert', '');
                slide.innerHTML = `
                    <div class="kp-slide-media" role="img" aria-label="${esc(p.name)}">
                        <img src="${esc(p.image)}" alt="" ${i === 0 ? 'loading="eager" fetchpriority="high"' : 'loading="lazy"'} />
                    </div>
                    <div class="kp-slide-copy">
                        <p class="kp-kicker">Featured · ${esc(p.category ?? 'Kipanya Wear')}</p>
                        ${i === 0 ? `<h1>${esc(p.name)}</h1>` : `<h2>${esc(p.name)}</h2>`}
                        <p class="kp-hero-price">${tzs(p.price)}</p>
                        <div class="kp-hero-ctas">
                            <a href="/wear/products/${encodeURIComponent(p.slug)}" class="kp-btn kp-btn-dark">View product</a>
                            <a href="#kp-catalog-grid" class="kp-btn kp-btn-outline">Shop all</a>
                        </div>
                    </div>`;
                slider.insertBefore(slide, slider.querySelector('.kp-slider-controls'));
                if (dotsWrap) {
                    const dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = `kp-slider-dot${i === 0 ? ' is-active' : ''}`;
                    dot.dataset.wearDot = String(i);
                    dot.setAttribute('aria-label', `Slide ${i + 1}: ${p.name}`);
                    if (i === 0) dot.setAttribute('aria-current', 'true');
                    dotsWrap.appendChild(dot);
                }
            });
        } catch (_) { /* static Blade slides remain */ }
        showSlide(0);
        restartSlider();
    }

    /* ================= Catalog state ================= */
    const state = {
        categories: [],
        activeSlug: 'all',
        search: '',
        sizeFilter: 'all',
        sort: 'featured',
        products: [],
        nextUrl: null,
        total: 0,
        loading: false,
    };

    const urlSlug = () => {
        try { return new URLSearchParams(window.location.search).get('category') || 'all'; }
        catch (_) { return 'all'; }
    };
    const setUrlSlug = (slug) => {
        try {
            const url = new URL(window.location.href);
            if (slug === 'all') url.searchParams.delete('category');
            else url.searchParams.set('category', slug);
            window.history.replaceState({}, '', url);
        } catch (_) {}
    };

    /* ================= Reveal-on-scroll stagger ================= */
    const revealIO = ('IntersectionObserver' in window) ? new IntersectionObserver((entries) => {
        entries.forEach((en) => {
            if (en.isIntersecting) { en.target.classList.add('is-in'); revealIO.unobserve(en.target); }
        });
    }, { threshold: 0.08, rootMargin: '0px 0px 40px 0px' }) : null;

    /* ================= Rendering ================= */
    const favActive = new Set();

    function renderCategories() {
        pillsRow.innerHTML = `<button type="button" class="kp-pill${state.activeSlug === 'all' ? ' is-active' : ''}"`
            + ` data-filter="all" aria-pressed="${state.activeSlug === 'all'}">All</button>`
            + state.categories.map((c) => {
                const on = state.activeSlug === c.slug;
                return `<button type="button" class="kp-pill${on ? ' is-active' : ''}"`
                    + ` data-filter="${esc(c.slug)}" aria-pressed="${on}">${esc(c.name)}</button>`;
            }).join('');
        $$('[data-filter]', pillsRow).forEach((pill) => pill.addEventListener('click', () => {
            setSearch('');
            selectCategory(pill.dataset.filter, true);
        }));
        if (sectionTitle) {
            sectionTitle.textContent = state.search
                ? 'Search results'
                : state.activeSlug === 'all' ? 'Featured Wear' : (categoryName(state.activeSlug) || 'Featured Wear');
        }
    }

    function stockInfo(p) {
        const variants = p.variants ?? [];
        const inStock = variants.filter((v) => v.in_stock);
        return { variants, inStock, out: variants.length > 0 && inStock.length === 0 };
    }

    function renderCard(p, i) {
        const { inStock, out } = stockInfo(p);
        const badge = p.badge ? `<span class="kp-badge-limited">${esc(p.badge)}</span>` : '';
        const onSale = p.compare_at_price && Number(p.compare_at_price) > Number(p.price);
        const stockLabel = out ? '<span class="kp-stock-label is-out">Sold out</span>' : '';
        const price = onSale
            ? `<p class="kp-product-price"><span>${tzs(p.price)}</span> <s class="kp-compare-price">${tzs(p.compare_at_price)}</s></p>`
            : `<p class="kp-product-price">${tzs(p.price)}</p>`;
        return `<article class="kp-product-card kp-editorial-card kp-reveal" style="--kp-reveal-delay:${(i % 8) * 45}ms" data-product-id="${p.id}">
            <div class="kp-product-media-wrap">
                <a href="/wear/products/${encodeURIComponent(p.slug)}" class="kp-product-photo-link" aria-label="View ${esc(p.name)}">
                    <span class="kp-product-photo"><img src="${esc(p.image)}" alt="${esc(p.name)}" loading="lazy" /></span>
                </a>
                ${badge}
                <button type="button" class="kp-fav-btn kp-fav-btn-editorial${favActive.has(p.id) ? ' is-active' : ''}" data-kp-favorite data-product-id="${p.id}" aria-label="Save ${esc(p.name)} to favorites" aria-pressed="${favActive.has(p.id)}"><i class="ti ${favActive.has(p.id) ? 'ti-heart-filled' : 'ti-heart'}" aria-hidden="true"></i></button>
                <button type="button" class="kp-qv-btn" data-kp-qv data-product-id="${p.id}">Quick view</button>
                ${stockLabel}
            </div>
            <div class="kp-product-info kp-editorial-product-info">
                <div class="kp-product-text"><p class="kp-product-name">${esc(p.name)}</p>${price}</div>
                <button type="button" class="kp-quick-add-btn${out ? ' is-disabled' : ''}" data-kp-quick-add data-product-id="${p.id}"`
                    + `${out ? ' disabled' : ''} aria-label="${out ? `Out of stock: ${esc(p.name)}` : `Add ${esc(p.name)} to bag`}"><i class="ti ${out ? 'ti-x' : 'ti-shopping-bag'}" aria-hidden="true"></i></button>
            </div>
        </article>`;
    }

    function paintGrid() {
        let items = [...state.products];
        if (state.search) {
            items = items.filter((p) => `${p.name} ${p.category} ${p.description ?? ''}`.toLowerCase().includes(state.search));
        }
        if (state.sizeFilter && state.sizeFilter !== 'all') {
            items = items.filter((p) => (p.variants ?? []).some((v) => v.in_stock && v.size === state.sizeFilter));
        }
        if (state.sort === 'price-asc') items.sort((a, b) => Number(a.price) - Number(b.price));
        else if (state.sort === 'price-desc') items.sort((a, b) => Number(b.price) - Number(a.price));
        else if (state.sort === 'name') items.sort((a, b) => String(a.name).localeCompare(String(b.name)));

        if (!items.length) {
            grid.innerHTML = `<p class="kp-empty-state">${state.search || state.sizeFilter !== 'all'
                ? 'No products match these filters.'
                : 'No products in this category yet.'}</p>`;
        } else {
            const cards = items.map(renderCard);
            // Mid-grid editorial banner on the unfiltered landing view.
            const unfiltered = !state.search && state.sizeFilter === 'all' && state.sort === 'featured';
            if (unfiltered && state.activeSlug === 'all' && cards.length > 6 && categoryName('t-shirts')) {
                cards.splice(4, 0, `<aside class="kp-editorial-banner" data-kp-banner>
                    <p class="kp-eyebrow">The new edit</p>
                    <p class="kp-editorial-banner-title">Everyday tees, cut to last</p>
                    <button type="button" class="kp-hero-cta" data-kp-banner-go>Shop T-Shirts</button>
                </aside>`);
            }
            grid.innerHTML = cards.join('');
            grid.querySelector('[data-kp-banner-go]')?.addEventListener('click', () => selectCategory('t-shirts', true));
        }
        if (revealIO) $$('.kp-reveal', grid).forEach((el) => revealIO.observe(el));
        else $$('.kp-reveal', grid).forEach((el) => el.classList.add('is-in'));
        wireCards();
        if (count) {
            count.textContent = state.search
                ? `${items.length} result${items.length === 1 ? '' : 's'}`
                : `${state.total} piece${state.total === 1 ? '' : 's'}`;
        }
        paintToolbar();
        renderCategories();
    }

    function paintLoadMore() {
        const pristine = !state.search && state.sizeFilter === 'all' && state.sort === 'featured';
        const show = !!state.nextUrl && pristine;
        loadMoreWrap.hidden = !show;
        if (loadMoreBtn) loadMoreBtn.disabled = state.loading;
    }

    function setSearch(q) {
        state.search = q.trim().toLowerCase();
        if (!state.search) {
            searchChip.hidden = true;
            searchChip.textContent = '';
        } else {
            searchChip.hidden = false;
            searchChip.innerHTML = `Results for “${esc(q.trim())}” <button type="button" data-kp-search-clear>Clear</button>`;
            $('[data-kp-search-clear]', searchChip)?.addEventListener('click', () => {
                try { sessionStorage.removeItem('kp-search'); } catch (_) {}
                setSearch('');
                paintGrid();
                paintLoadMore();
            });
        }
    }

    /* ================= Fetching ================= */
    async function loadCategories() {
        const json = await fetchJson('/api/v1/wear/categories');
        state.categories = (json.data ?? []).map((c) => (
            typeof c === 'string' ? { name: c, slug: slugify(c) } : { name: c.name, slug: c.slug }
        ));
    }

    function categoryName(slug) {
        return state.categories.find((c) => c.slug === slug)?.name ?? null;
    }

    async function loadProducts({ reset = true } = {}) {
        if (state.loading) return;
        state.loading = true;
        paintLoadMore();
        if (reset) setVisible(false);
        try {
            let url;
            if (state.search) {
                url = '/api/v1/wear/products?per_page=50';
            } else if (state.activeSlug === 'all') {
                url = '/api/v1/wear/products?per_page=24';
            } else {
                const name = categoryName(state.activeSlug);
                if (!name) throw new Error('unknown-category');
                url = `/api/v1/wear/products?per_page=24&category=${encodeURIComponent(name)}`;
            }
            const json = await fetchJson(url);
            state.products = json.data ?? [];
            state.nextUrl = json.links?.next ?? null;
            state.total = json.meta?.total ?? state.products.length;
            setVisible(true);
            paintGrid();
        } catch (err) {
            setVisible(true);
            grid.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">We could not load the collection. Check your connection and try again.</p>
                <button type="button" class="kp-secondary-btn" data-kp-retry>Retry</button>
            </div>`;
            $('[data-kp-retry]', grid)?.addEventListener('click', () => boot(false));
            if (count) count.textContent = '';
        } finally {
            state.loading = false;
            paintLoadMore();
        }
    }

    loadMoreBtn?.addEventListener('click', async () => {
        if (!state.nextUrl || state.loading) return;
        state.loading = true;
        paintLoadMore();
        try {
            const json = await fetchJson(state.nextUrl);
            const start = state.products.length;
            state.products = state.products.concat(json.data ?? []);
            state.nextUrl = json.links?.next ?? null;
            paintGrid();
            grid.children[start]?.querySelector('a')?.focus({ preventScroll: true });
        } catch (_) {
            toast('Could not load more products. Try again.', 'error');
        } finally {
            state.loading = false;
            paintLoadMore();
        }
    });

    async function selectCategory(slug, pushUrl) {
        state.activeSlug = slug;
        resetToolbar();
        if (pushUrl) setUrlSlug(slug);
        renderCategories();
        await loadProducts({ reset: true });
    }

    /* ============ Toolbar: size filter + sort (bounded client-side) ============ */
    const toolbar = $('[data-kp-toolbar]');
    const sizeFilterEl = $('[data-kp-size-filter]');
    const sortEl = $('[data-kp-sort]');
    let lastSizesKey = '';

    async function ensureAllLoaded() {
        // No backend sort/filter params exist — page through (bounded) so
        // filtering/sorting sees the whole catalog, not just page one.
        let pages = 0;
        while (state.nextUrl && pages < 4) {
            pages += 1;
            try {
                const json = await fetchJson(state.nextUrl);
                state.products = state.products.concat(json.data ?? []);
                state.nextUrl = json.links?.next ?? null;
            } catch (_) { break; }
        }
        paintLoadMore();
    }

    function paintToolbar() {
        if (!state.products.length) { toolbar.hidden = true; return; }
        toolbar.hidden = false;
        const sizes = [...new Set(state.products.flatMap((p) => (p.variants ?? []).filter((v) => v.in_stock).map((v) => v.size)))];
        const key = sizes.join('|');
        if (key !== lastSizesKey) {
            lastSizesKey = key;
            sizeFilterEl.innerHTML = `<button type="button" class="kp-size-chip${state.sizeFilter === 'all' ? ' is-active' : ''}" data-size-filter="all" aria-pressed="${state.sizeFilter === 'all'}">All sizes</button>`
                + sizes.map((s) => `<button type="button" class="kp-size-chip${state.sizeFilter === s ? ' is-active' : ''}" data-size-filter="${esc(s)}" aria-pressed="${state.sizeFilter === s}">${esc(s)}</button>`).join('');
            $$('[data-size-filter]', sizeFilterEl).forEach((chip) => chip.addEventListener('click', async () => {
                await ensureAllLoaded();
                state.sizeFilter = chip.dataset.sizeFilter;
                paintToolbar();
                paintGrid();
            }));
        } else {
            $$('[data-size-filter]', sizeFilterEl).forEach((chip) => {
                const on = chip.dataset.sizeFilter === state.sizeFilter;
                chip.classList.toggle('is-active', on);
                chip.setAttribute('aria-pressed', String(on));
            });
        }
    }

    sortEl?.addEventListener('change', async () => {
        await ensureAllLoaded();
        state.sort = sortEl.value;
        paintGrid();
    });

    function resetToolbar() {
        state.sizeFilter = 'all';
        state.sort = 'featured';
        if (sortEl) sortEl.value = 'featured';
        lastSizesKey = '';
    }

    /* ============ Quick view modal ============ */
    const qvModal = $('[data-kp-qv-modal]');
    const qvBody = $('[data-kp-qv-body]');
    let qvReturnFocus = null;

    function closeQuickView() {
        if (!qvModal || qvModal.hidden) return;
        qvModal.classList.remove('is-open');
        window.setTimeout(() => {
            qvModal.hidden = true;
            qvBody.innerHTML = '';
        }, 300);
        qvReturnFocus?.focus();
        qvReturnFocus = null;
    }

    function openQuickView(product, trigger) {
        if (!qvModal) return;
        qvReturnFocus = trigger || null;
        const { inStock } = stockInfo(product);
        const sizes = [...new Set(inStock.map((v) => v.size))];
        let size = sizes.length === 1 ? sizes[0] : null;
        const colorsFor = (s) => [...new Set(inStock.filter((v) => v.size === s).map((v) => v.color))];
        const price = product.compare_at_price && Number(product.compare_at_price) > Number(product.price)
            ? `<p class="kp-product-price"><span>${tzs(product.price)}</span> <s class="kp-compare-price">${tzs(product.compare_at_price)}</s></p>`
            : `<p class="kp-product-price">${tzs(product.price)}</p>`;

        const renderQv = () => {
            const colors = size ? colorsFor(size) : [];
            const validColor = colors.length === 1 ? colors[0]
                : (colors.includes(qvBody.dataset.color) ? qvBody.dataset.color : colors[0] || null);
            if (validColor) qvBody.dataset.color = validColor;
            const variant = size && validColor
                ? inStock.find((v) => v.size === size && v.color === validColor) : null;
            qvBody.innerHTML = `
                <div class="kp-qv-media"><img src="${esc(product.image)}" alt="${esc(product.name)}" /></div>
                <p class="kp-eyebrow">${esc(product.category ?? 'Kipanya Wear')}</p>
                <h3 id="kp-qv-title">${esc(product.name)}</h3>
                ${price}
                <p class="kp-qa-title">Size${sizes.length > 1 ? ' — choose' : ''}</p>
                <div class="kp-qa-row" role="group" aria-label="Size">
                    ${sizes.map((s) => `<button type="button" class="kp-qa-opt${s === size ? ' is-active' : ''}" data-qv-size="${esc(s)}" aria-pressed="${s === size}">${esc(s)}</button>`).join('') || '<span class="kp-hint-text">Out of stock</span>'}
                </div>
                ${colors.length > 1 ? `<p class="kp-qa-title">Color</p>
                <div class="kp-qa-row" role="group" aria-label="Color">
                    ${colors.map((c) => `<button type="button" class="kp-qa-opt${c === validColor ? ' is-active' : ''}" data-qv-color="${esc(c)}" aria-pressed="${c === validColor}">
                        <span class="kp-swatch" style="background:${swatchColor(c)}" aria-hidden="true"></span>${esc(c)}</button>`).join('')}
                </div>` : ''}
                <div class="kp-qv-actions">
                    <button type="button" class="kp-qa-add" data-qv-add ${variant ? '' : 'disabled'}>
                        ${variant ? `Add to bag — ${tzs(product.price)}` : 'Select options'}
                    </button>
                    <a href="/wear/products/${encodeURIComponent(product.slug)}" class="kp-secondary-btn">Full details</a>
                </div>`;
            $$('[data-qv-size]', qvBody).forEach((b) => b.addEventListener('click', () => {
                size = b.dataset.qvSize;
                delete qvBody.dataset.color;
                renderQv();
            }));
            $$('[data-qv-color]', qvBody).forEach((b) => b.addEventListener('click', () => {
                qvBody.dataset.color = b.dataset.qvColor;
                renderQv();
            }));
            $('[data-qv-add]', qvBody)?.addEventListener('click', async (e) => {
                if (!variant) return;
                const btn = e.currentTarget;
                btn.disabled = true;
                btn.textContent = 'Adding…';
                const result = await window.KipanyaCart.addItem(Number(variant.id), 1);
                if (result.ok) {
                    toast(`${product.name} (${variant.size}${variant.color ? ` · ${variant.color}` : ''}) added to bag.`, 'success');
                    document.dispatchEvent(new CustomEvent('kp:bag-bump'));
                    closeQuickView();
                } else {
                    btn.disabled = false;
                    btn.textContent = `Add to bag — ${tzs(product.price)}`;
                    window.KipanyaAnim?.shake(btn);
                    toast(result.data?.message || 'Could not add to bag. Try again.', 'error');
                }
            });
        };

        delete qvBody.dataset.color;
        qvModal.hidden = false;
        requestAnimationFrame(() => qvModal.classList.add('is-open'));
        renderQv();
        qvModal.querySelector('[data-kp-qv-close]')?.focus();
    }

    qvModal?.querySelector('[data-kp-qv-close]')?.addEventListener('click', closeQuickView);
    qvModal?.addEventListener('click', (e) => { if (e.target === qvModal) closeQuickView(); });

    /* ================= Card wiring ================= */
    const byId = (id) => state.products.find((p) => String(p.id) === String(id));

    /* One-click add: first in-stock variant. Full size/color choice
       lives in Quick view and on the product page. */
    async function quickAdd(btn, product) {
        const { inStock } = stockInfo(product);
        const variant = inStock[0];
        if (!variant) return;
        btn.disabled = true;
        const icon = btn.querySelector('i');
        const original = icon ? icon.className : '';
        if (icon) icon.className = 'ti ti-loader-2 kp-spin';
        const result = await window.KipanyaCart.addItem(Number(variant.id), 1);
        if (icon) icon.className = original;
        btn.disabled = false;
        if (result.ok) {
            toast(`${product.name} (${variant.size}${variant.color ? ` · ${variant.color}` : ''}) added to bag.`, 'success');
            document.dispatchEvent(new CustomEvent('kp:bag-bump'));
        } else {
            window.KipanyaAnim?.shake(btn);
            toast(result.data?.message || 'Could not add to bag. Try again.', 'error');
        }
    }

    function wireCards() {
        $$('[data-kp-qv]', grid).forEach((btn) => btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const product = byId(btn.dataset.productId);
            if (product) openQuickView(product, btn);
        }));
        $$('[data-kp-quick-add]', grid).forEach((btn) => btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const product = byId(btn.dataset.productId);
            if (product) quickAdd(btn, product);
        }));
        $$('[data-kp-favorite]', grid).forEach((btn) => btn.addEventListener('click', async () => {
            const id = Number(btn.dataset.productId);
            const active = btn.classList.contains('is-active');
            const result = await window.KipanyaCart.toggleFavorite(id, active);
            if (result.requiresLogin) { loginNotice(); return; }
            if (result.ok) {
                btn.classList.toggle('is-active', !active);
                btn.setAttribute('aria-pressed', String(!active));
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = `ti ${!active ? 'ti-heart-filled' : 'ti-heart'}`;
                    if (!active) window.KipanyaAnim?.pop(icon);
                }
                if (!active) favActive.add(id); else favActive.delete(id);
                toast(active ? 'Removed from favorites.' : 'Saved to favorites.', 'success');
            } else {
                toast('Could not update favorites. Try again.', 'error');
            }
        }));
    }

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeQuickView();
    });
    document.addEventListener('kp:apply-search', (e) => {
        resetToolbar();
        setSearch(String(e.detail || ''));
        loadProducts({ reset: true });
    });

    /* ================= Boot ================= */
    async function boot(first = true) {
        buildHero();
        try {
            await loadCategories();
        } catch (_) {
            setVisible(true);
            grid.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">We could not load categories.</p>
                <button type="button" class="kp-secondary-btn" data-kp-retry>Retry</button>
            </div>`;
            $('[data-kp-retry]', grid)?.addEventListener('click', () => boot(false));
            return;
        }
        renderCategories();

        let stored = '';
        try { stored = sessionStorage.getItem('kp-search') || ''; sessionStorage.removeItem('kp-search'); } catch (_) {}
        if (stored) setSearch(stored);

        let slug = urlSlug();
        if (slug !== 'all' && !categoryName(slug)) {
            toast(`Category “${slug}” does not exist — showing everything.`, 'info');
            slug = 'all';
            setUrlSlug('all');
        }
        state.activeSlug = slug;
        renderCategories();
        if (first) setVisible(false);
        await loadProducts({ reset: true });
    }

    document.addEventListener('DOMContentLoaded', () => boot(true));
})();
