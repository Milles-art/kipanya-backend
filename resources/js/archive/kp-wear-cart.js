/**
 * KP Wear cart page. Renders backend cart state, per-row quantity steppers
 * with focus retention, remove, and honest loading/empty/error states.
 */
(() => {
    const itemsEl = document.querySelector('[data-kp-cart-items]');
    if (!itemsEl) return;

    const summaryEl = document.querySelector('[data-kp-cart-summary]');
    const subtotalEl = document.querySelector('[data-kp-cart-subtotal]');
    const checkoutLink = document.querySelector('[data-kp-checkout-link]');
    const continueLink = document.querySelector('[data-kp-continue-shopping]');
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    const toast = (m, t) => window.KipanyaToast?.show(m, t);

    const showLinks = (hasItems) => {
        summaryEl.hidden = !hasItems;
        checkoutLink.hidden = !hasItems;
        continueLink.hidden = hasItems;
    };

    async function render(focusSel) {
        let result = null;
        try {
            result = await window.KipanyaCart.getCart();
        } catch (_) { result = { ok: false }; }
        const cart = result?.data;
        const items = cart?.items || [];

        if (!result?.ok) {
            itemsEl.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">We could not load your cart. Check your connection and try again.</p>
                <button type="button" class="kp-secondary-btn" data-kp-cart-retry>Retry</button>
            </div>`;
            document.querySelector('[data-kp-cart-retry]')?.addEventListener('click', () => render());
            showLinks(false);
            continueLink.hidden = false;
            return;
        }

        if (!items.length) {
            itemsEl.innerHTML = '<p class="kp-empty-state">Your cart is empty.</p>';
            showLinks(false);
            return;
        }

        itemsEl.innerHTML = items.map((item) => `
            <div class="kp-cart-row" data-variant-id="${item.variant_id}">
                <img class="kp-cart-thumb-img" src="${esc(item.product?.image)}" alt="" loading="lazy" />
                <div class="kp-cart-row-text">
                    <p class="kp-cart-item-name">${esc(item.product?.name)} — ${esc(item.variant?.size)}${item.variant?.color ? ` · ${esc(item.variant.color)}` : ''}</p>
                    <p class="kp-cart-item-price">${tzs(item.unit_price)} × ${item.quantity}</p>
                </div>
                <div class="kp-qty-stepper" role="group" aria-label="Quantity for ${esc(item.product?.name)}">
                    <button type="button" data-kp-cart-dec aria-label="Decrease quantity">−</button>
                    <span data-kp-cart-qty-value aria-live="polite">${item.quantity}</span>
                    <button type="button" data-kp-cart-inc aria-label="Increase quantity">+</button>
                </div>
                <button type="button" class="kp-cart-remove" data-kp-cart-remove aria-label="Remove ${esc(item.product?.name)} from cart">
                    <i class="ti ti-trash" aria-hidden="true"></i>
                </button>
            </div>`).join('');

        subtotalEl.textContent = tzs(cart.subtotal);
        showLinks(true);

        itemsEl.querySelectorAll('[data-variant-id]').forEach((row) => {
            const variantId = Number(row.dataset.variantId);
            const qtyValue = row.querySelector('[data-kp-cart-qty-value]');

            const busy = (on) => row.querySelectorAll('button').forEach((b) => { b.disabled = on; });

            row.querySelector('[data-kp-cart-inc]').addEventListener('click', async () => {
                busy(true);
                const r = await window.KipanyaCart.setItemQuantity(variantId, Number(qtyValue.textContent) + 1);
                if (!r.ok) { toast(r.data?.message || 'Could not update quantity.', 'error'); busy(false); return; }
                render(`[data-variant-id="${variantId}"] [data-kp-cart-inc]`);
            });
            row.querySelector('[data-kp-cart-dec]').addEventListener('click', async () => {
                busy(true);
                const next = Number(qtyValue.textContent) - 1;
                const r = next <= 0
                    ? await window.KipanyaCart.removeItem(variantId)
                    : await window.KipanyaCart.setItemQuantity(variantId, next);
                if (!r.ok) { toast(r.data?.message || 'Could not update quantity.', 'error'); busy(false); return; }
                render(next <= 1 ? null : `[data-variant-id="${variantId}"] [data-kp-cart-dec]`);
            });
            row.querySelector('[data-kp-cart-remove]').addEventListener('click', async () => {
                busy(true);
                const r = await window.KipanyaCart.removeItem(variantId);
                if (!r.ok) { toast(r.data?.message || 'Could not remove item.', 'error'); busy(false); return; }
                toast('Removed from cart.', 'success');
                render();
            });
        });

        if (focusSel) document.querySelector(focusSel)?.focus();
    }

    document.addEventListener('DOMContentLoaded', () => render());
})();
