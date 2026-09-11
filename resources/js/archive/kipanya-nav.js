/**
 * Kipanya global navigation interactions.
 * Wires every control rendered by x-kipanya-nav:
 * mobile menu, search panel (live results), account menu, cart badge.
 */
(() => {
    const $ = (sel, root = document) => root.querySelector(sel);
    const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

    /* ---------------- Toasts (global, aria-live) ---------------- */
    const ensureToastHost = () => {
        let host = $('#kp-toasts');
        if (!host) {
            host = document.createElement('div');
            host.id = 'kp-toasts';
            host.className = 'kp-toasts';
            host.setAttribute('aria-live', 'polite');
            host.setAttribute('aria-atomic', 'false');
            document.body.appendChild(host);
        }
        return host;
    };

    window.KipanyaToast = {
        show(message, type = 'info') {
            const host = ensureToastHost();
            const el = document.createElement('div');
            el.className = `kp-toast kp-toast-${type}`;
            el.textContent = message;
            host.appendChild(el);
            window.setTimeout(() => {
                el.classList.add('is-leaving');
                window.setTimeout(() => el.remove(), 300);
            }, 3200);
            while (host.children.length > 3) host.firstChild.remove();
        },
    };

    /* One-shot icon micro-animations (respects reduced motion via CSS) */
    const oneshot = (el, cls) => {
        if (!el) return;
        el.classList.remove(cls);
        void el.offsetWidth;
        el.classList.add(cls);
        el.addEventListener('animationend', () => el.classList.remove(cls), { once: true });
    };

    window.KipanyaAnim = {
        pop: (el) => oneshot(el, 'kp-anim-pop'),
        bump: (el) => oneshot(el, 'kp-anim-bump'),
        shake: (el) => oneshot(el, 'kp-anim-shake'),
    };

    document.addEventListener('kp:bag-bump', () => {
        document.querySelectorAll('[data-kp-cart-link]').forEach((a) => window.KipanyaAnim.bump(a));
        document.querySelectorAll('.kp-tab[href*="/wear/cart"]').forEach((a) => window.KipanyaAnim.bump(a));
    });

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;

    /* ---------------- Mobile menu ---------------- */
    const menuBtn = $('[data-kp-menu-toggle]');
    const mobileMenu = $('[data-kp-mobile-menu]');
    if (menuBtn && mobileMenu) {
        let menuTimer = null;
        const setMenu = (open) => {
            window.clearTimeout(menuTimer);
            if (open) {
                mobileMenu.hidden = false;
                requestAnimationFrame(() => mobileMenu.classList.add('is-open'));
            } else {
                mobileMenu.classList.remove('is-open');
                menuTimer = window.setTimeout(() => { mobileMenu.hidden = true; }, 350);
            }
            menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
            menuBtn.setAttribute('aria-label', open ? 'Close Kipanya sections menu' : 'Open Kipanya sections menu');
        };
        menuBtn.addEventListener('click', () => setMenu(mobileMenu.hidden));
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !mobileMenu.hidden) { setMenu(false); menuBtn.focus(); }
        });
    }

    /* ---------------- Account menu ---------------- */
    const account = $('[data-kp-account]');
    if (account) {
        const toggle = $('[data-kp-account-toggle]', account);
        const menu = $('[data-kp-account-menu]', account);
        const setAccount = (open) => {
            menu.hidden = !open;
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };
        toggle.addEventListener('click', (e) => { e.stopPropagation(); setAccount(menu.hidden); });
        document.addEventListener('click', (e) => {
            if (!menu.hidden && !account.contains(e.target)) setAccount(false);
        });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !menu.hidden) { setAccount(false); toggle.focus(); }
        });
    }

    /* ---------------- Search (live, client-side over catalog API) ---------------- */
    const searchToggle = $('[data-kp-search-toggle]');
    const searchToggles = $$('[data-kp-search-toggle]');
    const searchPanel = $('[data-kp-search-panel]');
    const searchForm = $('[data-kp-search-form]');
    const searchInput = $('[data-kp-search-input]');
    const searchResults = $('[data-kp-search-results]');
    let productCache = null;
    let cacheFailed = false;

    const loadProducts = async () => {
        if (productCache || cacheFailed) return productCache || [];
        try {
            const res = await fetch('/api/v1/wear/products?per_page=50', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error('catalog unavailable');
            const json = await res.json();
            productCache = json.data ?? [];
        } catch (_) {
            cacheFailed = true;
            productCache = [];
        }
        return productCache;
    };

    const renderSearch = (items, query) => {
        if (!query) {
            searchResults.innerHTML = '<p class="kp-search-hint">Type at least 2 characters to search the Wear catalog.</p>';
            return;
        }
        if (!items.length) {
            searchResults.innerHTML = `<p class="kp-search-hint">No Wear products match “${esc(query)}”.</p>`;
            return;
        }
        const top = items.slice(0, 6);
        searchResults.innerHTML = top.map((p) => `
            <a class="kp-search-hit" href="/wear/products/${encodeURIComponent(p.slug)}">
                <img class="kp-search-thumb" src="${esc(p.image)}" alt="" loading="lazy" />
                <span class="kp-search-text">
                    <span class="kp-search-name">${esc(p.name)}</span>
                    <span class="kp-search-cat">${esc(p.category ?? '')}</span>
                </span>
                <span class="kp-search-price">${tzs(p.price)}</span>
            </a>`).join('')
            + (items.length > 6
                ? `<button type="button" class="kp-search-all" data-kp-search-all>See all ${items.length} results →</button>`
                : '');
        const allBtn = $('[data-kp-search-all]', searchResults);
        if (allBtn) {
            allBtn.addEventListener('click', () => {
                try { sessionStorage.setItem('kp-search', query); } catch (_) {}
                window.location.href = '/wear';
            });
        }
    };

    let searchTimer = null;
    const openSearch = () => setSearch(true);
    document.addEventListener('kp:open-search', openSearch);
    if (searchToggles.length && searchPanel) {
        let lastToggle = searchToggle;
        let searchTimer2 = null;
        const setSearch = (open, trigger) => {
            searchPanel.hidden = !open;
            if (trigger) lastToggle = trigger;
            searchToggles.forEach((t) => t.setAttribute('aria-expanded', open ? 'true' : 'false'));
            window.clearTimeout(searchTimer2);
            if (open) {
                searchPanel.hidden = false;
                requestAnimationFrame(() => searchPanel.classList.add('is-open'));
                renderSearch([], '');
                searchInput.value = '';
                window.setTimeout(() => searchInput.focus(), 60);
            } else {
                searchPanel.classList.remove('is-open');
                searchTimer2 = window.setTimeout(() => {
                    searchPanel.hidden = true;
                    searchInput.value = '';
                    searchResults.innerHTML = '';
                }, 350);
            }
        };
        searchToggles.forEach((t) => t.addEventListener('click', () => setSearch(searchPanel.hidden, t)));
        $('[data-kp-search-close]')?.addEventListener('click', () => { setSearch(false); lastToggle?.focus(); });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !searchPanel.hidden) { setSearch(false); lastToggle?.focus(); }
        });
        searchForm?.addEventListener('submit', (e) => {
            // Results render live below; never navigate away with a dead query.
            e.preventDefault();
            const q = searchInput.value.trim();
            if (q.length >= 2) {
                try { sessionStorage.setItem('kp-search', q); } catch (_) {}
                if (window.location.pathname === '/wear') {
                    document.dispatchEvent(new CustomEvent('kp:apply-search', { detail: q }));
                    setSearch(false);
                    lastToggle?.focus();
                } else {
                    window.location.href = '/wear';
                }
            }
        });
        searchInput?.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(async () => {
                const q = searchInput.value.trim().toLowerCase();
                if (q.length < 2) { renderSearch([], ''); return; }
                searchResults.innerHTML = '<p class="kp-search-hint">Searching…</p>';
                const all = await loadProducts();
                if (!document.body.contains(searchInput) || searchPanel.hidden) return;
                renderSearch(
                    all.filter((p) => `${p.name} ${p.category} ${p.description ?? ''}`.toLowerCase().includes(q)),
                    searchInput.value.trim(),
                );
            }, 200);
        });
    }

    /* ---------------- Bottom-nav search shortcut ---------------- */
    $$('[data-kp-bottom-search]').forEach((btn) => btn.addEventListener('click', () => {
        window.scrollTo({ top: 0, behavior: 'smooth' });
        document.dispatchEvent(new CustomEvent('kp:open-search'));
    }));

    /* ---------------- Cart badge (live from backend cart state) ---------------- */
    const paintBadge = (count) => {
        $$('[data-kp-cart-count]').forEach((badge) => {
            badge.textContent = count > 99 ? '99+' : String(count);
            badge.hidden = count < 1;
            const link = badge.closest('[data-kp-cart-link]') || badge.closest('a');
            if (link) link.setAttribute('aria-label', `Open shopping bag, ${count} ${count === 1 ? 'item' : 'items'}`);
        });
    };

    const refreshBadge = async () => {
        if (!window.KipanyaCart) return;
        try {
            const result = await window.KipanyaCart.getCart();
            if (result.ok) paintBadge(result.data?.item_count ?? 0);
        } catch (_) { /* badge keeps server-rendered value */ }
    };

    document.addEventListener('DOMContentLoaded', refreshBadge);

    /* Sticky-header elevation on scroll */
    const header = document.querySelector('[data-kp-redesign-nav]');
    if (header) {
        const onScroll = () => header.classList.toggle('is-scrolled', window.scrollY > 8);
        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    }
    window.KipanyaCartRefresh = refreshBadge;
})();
