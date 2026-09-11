/**
 * KP Wear wishlist page. Favorites live on the backend and require auth;
 * guests get an honest notice (there is no web sign-in route yet).
 */
(() => {
    const grid = document.querySelector('[data-kp-wishlist-grid]');
    if (!grid) return;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    const toast = (m, t) => window.KipanyaToast?.show(m, t);

    async function load() {
        if (document.body.dataset.kpAuth !== '1') {
            grid.innerHTML = `<div class="kp-state-block kp-fav-guest">
                <i class="ti ti-heart" aria-hidden="true"></i>
                <p class="kp-fav-title">Hi, keep track of your favorites!</p>
                <p class="kp-empty-state" style="padding:0;">Please sign in — account sign-in on web is coming soon.</p>
                <a href="/wear" class="kp-btn-outline">Browse Wear</a>
            </div>`;
            return;
        }
        try {
            const res = await fetch('/api/v1/wishlist', { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'request failed');
            const entries = json.data || [];
            if (!entries.length) {
                grid.innerHTML = '<p class="kp-empty-state">No favorites yet — tap the heart on a product to save it here.</p>';
                return;
            }
            grid.innerHTML = entries.map(({ product }) => {
                const img = product.image_url ?? product.image;
                const price = product.compare_at_price && Number(product.compare_at_price) > Number(product.price)
                    ? `<p class="kp-product-price"><span>${tzs(product.price)}</span> <s class="kp-compare-price">${tzs(product.compare_at_price)}</s></p>`
                    : `<p class="kp-product-price">${tzs(product.price)}</p>`;
                return `<article class="kp-product-card" data-product-id="${product.id}">
                    <a href="/wear/products/${encodeURIComponent(product.slug)}" class="kp-product-photo-link" aria-label="View ${esc(product.name)}">
                        <span class="kp-product-photo"><img src="${esc(img)}" alt="${esc(product.name)}" loading="lazy" /></span>
                    </a>
                        <button type="button" class="kp-fav-btn is-active" data-kp-favorite data-product-id="${product.id}" aria-pressed="true" aria-label="Remove ${esc(product.name)} from favorites">
                            <i class="ti ti-heart-filled" aria-hidden="true"></i>
                        </button>
                    <div class="kp-product-info">
                        <div class="kp-product-text">
                            <p class="kp-product-name">${esc(product.name)}</p>
                            ${price}
                        </div>
                    </div>
                </article>`;
            }).join('');

            grid.querySelectorAll('[data-kp-favorite]').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    btn.disabled = true;
                    const result = await window.KipanyaCart.toggleFavorite(Number(btn.dataset.productId), true);
                    if (result.ok) {
                        btn.closest('[data-product-id]')?.remove();
                        toast('Removed from favorites.', 'success');
                        if (!grid.querySelector('[data-product-id]')) {
                            grid.innerHTML = '<p class="kp-empty-state">No favorites yet — tap the heart on a product to save it here.</p>';
                        }
                    } else {
                        btn.disabled = false;
                        toast('Could not update favorites. Try again.', 'error');
                    }
                });
            });
        } catch (_) {
            grid.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">Couldn’t load your favorites.</p>
                <button type="button" class="kp-secondary-btn" data-kp-wish-retry>Retry</button>
            </div>`;
            grid.querySelector('[data-kp-wish-retry]')?.addEventListener('click', load);
        }
    }

    document.addEventListener('DOMContentLoaded', load);
})();
