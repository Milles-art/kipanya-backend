/**
 * KP Wear bag drawer — slide-over mini cart. Opens on [data-kp-bag-open]
 * click (with href fallback to the bag page), renders live backend cart,
 * per-row steppers + remove, live subtotal. Focus trapped lightly:
 * focus moves in on open, returns on close, Escape closes.
 */
(() => {
    const drawer = document.querySelector('[data-kp-bag-drawer]');
    if (!drawer) return;

    const scrim = document.querySelector('[data-kp-bag-scrim]');
    const itemsEl = drawer.querySelector('[data-kp-bag-items]');
    const footEl = drawer.querySelector('[data-kp-bag-foot]');
    const titleEl = drawer.querySelector('[data-kp-bag-title]');
    const subtotalEl = drawer.querySelector('[data-kp-bag-subtotal]');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    let returnFocus = null;
    let open = false;

    function setOpen(next, trigger) {
        open = next;
        window.clearTimeout(setOpen._t);
        if (next) {
            returnFocus = trigger || document.activeElement;
            drawer.hidden = false;
            if (scrim) { scrim.hidden = false; scrim.classList.add('is-open'); }
            requestAnimationFrame(() => drawer.classList.add('is-open'));
            document.body.style.overflow = 'hidden';
            render();
            drawer.querySelector('[data-kp-bag-close]')?.focus();
        } else {
            drawer.classList.remove('is-open');
            if (scrim) scrim.classList.remove('is-open');
            setOpen._t = window.setTimeout(() => {
                drawer.hidden = true;
                if (scrim) scrim.hidden = true;
            }, 380);
            document.body.style.overflow = '';
            if (returnFocus && document.body.contains(returnFocus)) returnFocus.focus();
            returnFocus = null;
        }
    }

    async function render() {
        itemsEl.innerHTML = '<p class="kp-empty-state">Loading your bag…</p>';
        footEl.hidden = true;
        let result = null;
        try { result = await window.KipanyaCart.getCart(); } catch (_) { result = { ok: false }; }
        const cart = result?.data;
        const items = cart?.items || [];
        if (!result?.ok) {
            itemsEl.innerHTML = '<p class="kp-empty-state">Could not load your bag.</p>';
            return;
        }
        const count = cart?.item_count ?? items.reduce((s, i) => s + Number(i.quantity || 0), 0);
        titleEl.textContent = count ? `Your bag (${count})` : 'Your bag';
        if (!items.length) {
            itemsEl.innerHTML = '<p class="kp-empty-state">Your bag is empty.</p>';
            return;
        }
        itemsEl.innerHTML = items.map((item) => `
            <div class="kp-drawer-row" data-variant-id="${item.variant_id}">
                <img class="kp-drawer-thumb" src="${esc(item.product?.image)}" alt="" loading="lazy" />
                <div class="kp-drawer-text">
                    <p class="kp-drawer-name">${esc(item.product?.name)}</p>
                    <p class="kp-drawer-meta">${item.variant?.color ? `${esc(item.variant.color)} · ` : ''}Size: ${esc(item.variant?.size)} · Qty: ${item.quantity}</p>
                    <p class="kp-drawer-price">${tzs(item.unit_price)}</p>
                </div>
                <div class="kp-drawer-qty" role="group" aria-label="Quantity for ${esc(item.product?.name)}">
                    <button type="button" data-kp-dec aria-label="Decrease quantity">−</button>
                    <span>${item.quantity}</span>
                    <button type="button" data-kp-inc aria-label="Increase quantity">+</button>
                </div>
                <button type="button" class="kp-drawer-rm" data-kp-rm aria-label="Remove ${esc(item.product?.name)}">
                    <i class="ti ti-trash" aria-hidden="true"></i>
                </button>
            </div>`).join('');
        subtotalEl.textContent = tzs(cart.subtotal);
        footEl.hidden = false;

        itemsEl.querySelectorAll('[data-variant-id]').forEach((row) => {
            const vid = Number(row.dataset.variantId);
            const qty = Number(row.querySelector('[data-kp-dec]')?.parentElement.querySelector('span')?.textContent || 1);
            row.querySelector('[data-kp-inc]')?.addEventListener('click', async () => {
                await window.KipanyaCart.setItemQuantity(vid, qty + 1);
                render();
            });
            row.querySelector('[data-kp-dec]')?.addEventListener('click', async () => {
                if (qty <= 1) await window.KipanyaCart.removeItem(vid);
                else await window.KipanyaCart.setItemQuantity(vid, qty - 1);
                render();
            });
            row.querySelector('[data-kp-rm]')?.addEventListener('click', async () => {
                await window.KipanyaCart.removeItem(vid);
                render();
            });
        });
    }

    document.querySelectorAll('[data-kp-bag-open]').forEach((link) => link.addEventListener('click', (e) => {
        e.preventDefault();
        setOpen(true, link);
    }));
    drawer.querySelector('[data-kp-bag-close]')?.addEventListener('click', () => setOpen(false));
    scrim?.addEventListener('click', () => setOpen(false));
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && open) setOpen(false);
    });
    document.addEventListener('kp:bag-open', () => setOpen(true));
})();
