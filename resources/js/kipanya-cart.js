/**
 * Kipanya Wear — shared cart/wishlist helper.
 * Talks to the REAL backend API as it exists today:
 *   GET    /api/v1/cart
 *   POST   /api/v1/cart/items          { variant_id, quantity }
 *   PUT    /api/v1/cart/items/{variant} { quantity }
 *   DELETE /api/v1/cart/items/{variant}
 *   POST   /api/v1/cart/merge          { guest_cart_token }   (auth only)
 *   GET    /api/v1/wishlist            (auth only)
 *   POST   /api/v1/wishlist            { product_id }         (auth only)
 *   DELETE /api/v1/wishlist/{product}                          (auth only)
 *
 * Guest cart token: generated client-side, stored in localStorage, sent as
 * X-Guest-Cart-Token on every cart request while logged out. The backend
 * only ever sees/stores a hash of it.
 */
window.KipanyaCart = (() => {
    const GUEST_TOKEN_KEY = 'kipanya-guest-cart-token';
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const isLoggedIn = () => document.body.dataset.kpAuth === '1';

    const getGuestToken = () => {
        try { return localStorage.getItem(GUEST_TOKEN_KEY); } catch (_) { return null; }
    };
    const setGuestToken = (token) => {
        try { localStorage.setItem(GUEST_TOKEN_KEY, token); } catch (_) {}
    };

    const safeFetch = async (url, options = {}) => {
        try {
            const response = await fetch(url, options);
            return response;
        } catch (_) {
            return new Response(JSON.stringify({ message: 'Network error' }), { status: 0, headers: { 'Content-Type': 'application/json' } });
        }
    };

    const headers = (extra = {}) => {
        const h = {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrf(),
            ...extra,
        };
        if (!isLoggedIn()) {
            const token = getGuestToken();
            if (token) h['X-Guest-Cart-Token'] = token;
        }
        return h;
    };

    const afterCartResponse = async (res) => {
        const json = await res.json();
        const cart = json.data;
        if (cart?.guest_cart_token) setGuestToken(cart.guest_cart_token);
        if (res.ok) updateCartBadge(cart?.item_count ?? 0);
        return { ok: res.ok, status: res.status, data: cart, raw: json };
    };

    const updateCartBadge = (count) => {
        const n = Number(count ?? 0);
        const label = n === 1 ? 'Open shopping bag, 1 item' : `Open shopping bag, ${n} items`;
        document.querySelectorAll('[data-kp-cart-count]').forEach((badge) => {
            badge.textContent = n > 99 ? '99+' : String(n);
            badge.hidden = n < 1;
            const link = badge.closest('[data-kp-cart-link]') || badge.closest('[data-kp-cart]') || badge.closest('a');
            if (link) link.setAttribute('aria-label', label);
        });
    };

    const getCart = async () => {
        const res = await fetch('/api/v1/cart', { headers: headers() });
        return afterCartResponse(res);
    };

    const addItem = async (variantId, quantity = 1) => {
        const res = await fetch('/api/v1/cart/items', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ variant_id: variantId, quantity }),
        });
        return afterCartResponse(res);
    };

    const setItemQuantity = async (variantId, quantity) => {
        const res = await fetch(`/api/v1/cart/items/${variantId}`, {
            method: 'PUT',
            headers: headers(),
            body: JSON.stringify({ quantity }),
        });
        return afterCartResponse(res);
    };

    const removeItem = async (variantId) => {
        const res = await fetch(`/api/v1/cart/items/${variantId}`, {
            method: 'DELETE',
            headers: headers(),
        });
        return afterCartResponse(res);
    };

    // Call this right after a successful login so the guest cart (if any)
    // gets folded into the user's account cart.
    const mergeGuestCartAfterLogin = async () => {
        const token = getGuestToken();
        if (!token || !isLoggedIn()) return null;
        const res = await fetch('/api/v1/cart/merge', {
            method: 'POST',
            headers: headers(),
            body: JSON.stringify({ guest_cart_token: token }),
        });
        try { localStorage.removeItem(GUEST_TOKEN_KEY); } catch (_) {}
        return afterCartResponse(res);
    };

    // Wishlist — authenticated only, no guest fallback exists on the backend.
    // Guests get { requiresLogin: true } so callers can show an honest
    // message. (There is no web login route yet, so we never redirect.)
    const toggleFavorite = async (productId, isCurrentlyFavorited) => {
        if (!isLoggedIn()) {
            return { ok: false, requiresLogin: true };
        }
        const res = isCurrentlyFavorited
            ? await safeFetch(`/api/v1/wishlist/${productId}`, { method: 'DELETE', headers: headers() })
            : await safeFetch('/api/v1/wishlist', { method: 'POST', headers: headers(), body: JSON.stringify({ product_id: productId }) });
        return { ok: res.ok, status: res.status };
    };

    return { getCart, addItem, setItemQuantity, removeItem, mergeGuestCartAfterLogin, toggleFavorite, updateCartBadge, isLoggedIn };
})();
