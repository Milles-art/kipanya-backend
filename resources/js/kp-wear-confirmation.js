/**
 * KP Wear order confirmation. Reads the order via the owner-scoped detail
 * endpoint and renders paid / failed / pending states honestly.
 */
(() => {
    const card = document.querySelector('[data-kp-order-card]');
    if (!card) return;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;

    let orderNumber = null;
    try { orderNumber = JSON.parse(document.querySelector('[data-kp-order-number]')?.textContent || 'null'); } catch (_) {}
    orderNumber = orderNumber || window.location.pathname.split('/').filter(Boolean).pop();

    async function load() {
        try {
            const res = await fetch(`/api/v1/orders/${encodeURIComponent(orderNumber)}`, {
                headers: { Accept: 'application/json' },
            });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'request failed');
            const order = json.data;
            if (!order) { card.innerHTML = '<p class="kp-empty-state">Order not found.</p>'; return; }

            const itemsHtml = (order.items || []).map((i) => `
                <div class="kp-summary-row">
                    <span>${esc(i.name)} — ${esc(i.size)}${i.color ? ` · ${esc(i.color)}` : ''} × ${esc(i.quantity ?? 1)}</span>
                    <span>${tzs(i.line_total)}</span>
                </div>`).join('');

            const totals = `<div class="kp-result-items">${itemsHtml}
                <div class="kp-summary-row kp-summary-total"><span>Total</span><span>${tzs(order.total)}</span></div>
            </div>`;

            if (order.payment_status === 'paid') {
                card.innerHTML = `
                    <div class="kp-result-icon kp-result-icon-success"><i class="ti ti-check" aria-hidden="true"></i></div>
                    <p class="kp-result-title">Order confirmed</p>
                    <p class="kp-result-subtitle">Order #${esc(order.order_number)}</p>
                    ${totals}
                    <p class="kp-hint-text" style="text-align:center;">We’ll contact you about delivery.</p>
                    <a href="/wear" class="kp-secondary-btn">Continue shopping</a>`;
            } else if (order.payment_status === 'failed') {
                card.innerHTML = `
                    <div class="kp-result-icon kp-result-icon-failure"><i class="ti ti-x" aria-hidden="true"></i></div>
                    <p class="kp-result-title">Payment didn’t go through</p>
                    <p class="kp-hint-text" style="text-align:center;">Order #${esc(order.order_number)} — total ${tzs(order.total)}</p>
                    <p class="kp-hint-text" style="text-align:center;">Payment retry isn’t live yet — contact support to complete this order.</p>
                    <a href="/wear" class="kp-secondary-btn">Continue shopping</a>`;
            } else {
                card.innerHTML = `
                    <div class="kp-result-icon"><i class="ti ti-clock" aria-hidden="true"></i></div>
                    <p class="kp-result-title">Order placed — payment pending</p>
                    <p class="kp-result-subtitle">Order #${esc(order.order_number)}</p>
                    ${totals}
                    <p class="kp-hint-text" style="text-align:center;">We’ll confirm payment and contact you about delivery.</p>
                    <a href="/wear" class="kp-secondary-btn">Continue shopping</a>`;
            }
        } catch (_) {
            card.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">Couldn’t load this order. Sign in as the ordering account and try again.</p>
                <button type="button" class="kp-secondary-btn" data-kp-order-retry>Retry</button>
            </div>`;
            card.querySelector('[data-kp-order-retry]')?.addEventListener('click', load);
        }
    }

    document.addEventListener('DOMContentLoaded', load);
})();
