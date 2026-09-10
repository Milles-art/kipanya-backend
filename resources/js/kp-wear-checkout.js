/**
 * KP Wear checkout, built exactly against the backend contract:
 * - auth required (CreateOrderRequest::authorize)
 * - address_id required, must exist in addresses table
 * - notes optional (max 1000)
 * - Idempotency-Key header: [A-Za-z0-9._-]{16,100}
 * Addresses come from GET|POST /api/v1/addresses.
 */
(() => {
    const root = document.querySelector('[data-kp-checkout]');
    if (!root) return;

    const $ = (sel, r = root) => r.querySelector(sel);
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    const toast = (m, t) => window.KipanyaToast?.show(m, t);
    const authed = () => document.body.dataset.kpAuth === '1';
    const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content || '';
    const api = (url, options = {}) => fetch(url, {
        ...options,
        headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf(), ...(options.headers || {}) },
    });

    const loadingEl = $('[data-kp-checkout-loading]');
    const guestEl = $('[data-kp-checkout-guest]');
    const mainEl = $('[data-kp-checkout-main]');
    const itemsEl = $('[data-kp-checkout-items]');
    const addrList = $('[data-kp-address-list]');
    const addrToggle = $('[data-kp-address-toggle]');
    const addrForm = $('[data-kp-address-form]');
    const addrError = $('[data-kp-addr-error]');
    const summaryEl = $('[data-kp-checkout-summary]');
    const subtotalEl = $('[data-kp-checkout-subtotal]');
    const placeBtn = $('[data-kp-place-order]');
    const placeLabel = $('[data-kp-place-order-label]');
    const orderError = $('[data-kp-checkout-error]');

    let selectedAddressId = null;

    function paintAddresses(addresses) {
        if (!addresses.length) {
            addrList.innerHTML = '<p class="kp-hint-text">No saved addresses yet — add one below.</p>';
            setFormOpen(true);
            return;
        }
        addrList.innerHTML = addresses.map((a) => `
            <label class="kp-address-option${a.id === selectedAddressId ? ' is-selected' : ''}">
                <input type="radio" name="kp-address" value="${a.id}"${a.id === selectedAddressId ? ' checked' : ''} />
                <span>
                    <strong>${esc(a.recipient_name)} · ${esc(a.phone)}</strong><br>
                    ${esc(a.street)}${a.ward ? `, ${esc(a.ward)}` : ''}, ${esc(a.district)}, ${esc(a.region)}
                    ${a.is_default ? ' <span class="kp-soon-tag">Default</span>' : ''}
                </span>
            </label>`).join('');
        addrList.querySelectorAll('input[name="kp-address"]').forEach((radio) => radio.addEventListener('change', () => {
            selectedAddressId = Number(radio.value);
            addrList.querySelectorAll('.kp-address-option').forEach((o) => o.classList.remove('is-selected'));
            radio.closest('.kp-address-option')?.classList.add('is-selected');
            orderError.hidden = true;
        }));
    }

    function setFormOpen(open) {
        addrForm.hidden = !open;
        addrToggle.setAttribute('aria-expanded', String(open));
        addrToggle.textContent = open ? 'Cancel' : 'Add a new address';
    }
    addrToggle.addEventListener('click', () => setFormOpen(addrForm.hidden));

    addrForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        addrError.hidden = true;
        const payload = {
            type: 'shipping',
            recipient_name: $('[data-kp-addr-name]').value.trim(),
            phone: $('[data-kp-addr-phone]').value.trim(),
            region: $('[data-kp-addr-region]').value.trim(),
            district: $('[data-kp-addr-district]').value.trim(),
            ward: $('[data-kp-addr-ward]').value.trim() || null,
            street: $('[data-kp-addr-street]').value.trim(),
            notes: $('[data-kp-addr-notes]').value.trim() || null,
        };
        const missing = ['recipient_name', 'phone', 'region', 'district', 'street'].filter((k) => !payload[k]);
        if (missing.length) {
            addrError.textContent = 'Please fill in name, phone, region, district and street.';
            addrError.hidden = false;
            return;
        }
        const btn = $('[data-kp-addr-submit]');
        btn.disabled = true;
        btn.textContent = 'Saving…';
        try {
            const res = await api('/api/v1/addresses', { method: 'POST', body: JSON.stringify(payload) });
            const json = await res.json();
            if (!res.ok) {
                const first = json.errors ? Object.values(json.errors).flat()[0] : json.message;
                addrError.textContent = first || 'Could not save this address.';
                addrError.hidden = false;
                return;
            }
            selectedAddressId = json.data.id;
            addrForm.reset();
            setFormOpen(false);
            toast('Address saved.', 'success');
            await reloadAddresses();
        } catch (_) {
            addrError.textContent = 'Network error — please try again.';
            addrError.hidden = false;
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save address';
        }
    });

    async function reloadAddresses() {
        try {
            const res = await api('/api/v1/addresses');
            const json = await res.json();
            const addresses = res.ok ? (json.data ?? []) : [];
            if (!selectedAddressId) {
                selectedAddressId = addresses.find((a) => a.is_default)?.id ?? addresses[0]?.id ?? null;
            }
            paintAddresses(addresses);
        } catch (_) {
            addrList.innerHTML = '<p class="kp-hint-text">Could not load addresses.</p>';
        }
    }

    placeBtn.addEventListener('click', async () => {
        orderError.hidden = true;
        if (!selectedAddressId) {
            orderError.textContent = 'Choose or add a delivery address first.';
            orderError.hidden = false;
            return;
        }
        placeBtn.disabled = true;
        placeLabel.textContent = 'Placing order…';
        try {
            const res = await api('/api/v1/checkout', {
                method: 'POST',
                headers: { 'Idempotency-Key': crypto.randomUUID() },
                body: JSON.stringify({
                    address_id: selectedAddressId,
                    notes: $('[data-kp-checkout-notes]').value.trim() || null,
                }),
            });
            const json = await res.json();
            if (!res.ok) {
                const first = json.errors ? Object.values(json.errors).flat()[0] : json.message;
                orderError.textContent = first || 'Checkout failed. Try again.';
                orderError.hidden = false;
                placeBtn.disabled = false;
                placeLabel.textContent = 'Place order';
                return;
            }
            const orderNumber = json.data?.order_number;
            if (orderNumber) window.location.href = `/wear/orders/${orderNumber}`;
            else {
                orderError.textContent = 'Order may have been created but no order number was returned.';
                orderError.hidden = false;
            }
        } catch (_) {
            orderError.textContent = 'Network error — your card was not charged. Try again (same order uses idempotency protection).';
            orderError.hidden = false;
            placeBtn.disabled = false;
            placeLabel.textContent = 'Place order';
        }
    });

    document.addEventListener('DOMContentLoaded', async () => {
        if (!authed()) {
            loadingEl.hidden = true;
            guestEl.hidden = false;
            return;
        }
        const cartResult = await window.KipanyaCart.getCart();
        const items = cartResult.data?.items || [];
        loadingEl.hidden = true;
        if (!items.length) {
            mainEl.hidden = false;
            itemsEl.innerHTML = '<p class="kp-empty-state">Your cart is empty.</p>';
            placeBtn.disabled = true;
            placeLabel.textContent = 'Bag is empty';
            return;
        }
        itemsEl.innerHTML = items.map((item) => `
            <div class="kp-cart-row">
                <img class="kp-cart-thumb-img" src="${esc(item.product?.image)}" alt="" loading="lazy" />
                <div class="kp-cart-row-text">
                    <p class="kp-cart-item-name">${esc(item.product?.name)} — ${esc(item.variant?.size)}${item.variant?.color ? ` · ${esc(item.variant.color)}` : ''}</p>
                    <p class="kp-cart-item-price">${tzs(item.unit_price)} × ${item.quantity}</p>
                </div>
            </div>`).join('');
        subtotalEl.textContent = tzs(cartResult.data.subtotal);
        summaryEl.hidden = false;
        mainEl.hidden = false;
        await reloadAddresses();
    });
})();
