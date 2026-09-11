/**
 * KP Wear orders list. Authenticated endpoint; guests get an honest notice.
 * Order-status CSS classes use an allowlist with a neutral fallback.
 */
(() => {
    const listEl = document.querySelector('[data-kp-orders-list]');
    if (!listEl) return;

    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const tzs = (n) => `TZS ${Number(n || 0).toLocaleString()}`;
    const KNOWN = new Set(['pending', 'processing', 'confirmed', 'paid', 'shipped', 'delivered', 'cancelled', 'failed']);

    async function load() {
        if (document.body.dataset.kpAuth !== '1') {
            listEl.innerHTML = `<div class="kp-state-block">
                <p class="kp-empty-state">Orders need a Kipanya account — account sign-in on web is coming soon.</p>
                <a href="/wear" class="kp-secondary-btn">Continue shopping</a>
            </div>`;
            return;
        }
        try {
            const res = await fetch('/api/v1/orders', { headers: { Accept: 'application/json' } });
            const json = await res.json();
            if (!res.ok) throw new Error(json.message || 'request failed');
            const orders = json.data || [];
            if (!orders.length) {
                listEl.innerHTML = '<p class="kp-empty-state">No orders yet.</p><a href="/wear" class="kp-secondary-btn">Start shopping</a>';
                return;
            }
            listEl.innerHTML = orders.map((o) => {
                const status = String(o.status ?? 'pending');
                const cls = KNOWN.has(status) ? ` kp-order-status-${status}` : '';
                return `<article class="kp-order-card">
                    <dl class="kp-order-dl">
                        <div><dt>Order #</dt><dd>${esc(o.order_number)}</dd></div>
                        <div><dt>Date</dt><dd>${o.placed_at ? esc(new Date(o.placed_at).toLocaleDateString()) : '—'}</dd></div>
                        <div><dt>Order Total</dt><dd>${tzs(o.total)}</dd></div>
                        <div><dt>Status</dt><dd><span class="kp-order-status${cls}">${esc(status)}</span></dd></div>
                    </dl>
                    <a href="/wear/orders/${encodeURIComponent(o.order_number)}" class="kp-order-view">View Order</a>
                </article>`;
            }).join('');
        } catch (_) {
            listEl.innerHTML = `<div class="kp-state-block" role="alert">
                <p class="kp-empty-state">Couldn’t load your orders.</p>
                <button type="button" class="kp-secondary-btn" data-kp-orders-retry>Retry</button>
            </div>`;
            listEl.querySelector('[data-kp-orders-retry]')?.addEventListener('click', load);
        }
    }

    document.addEventListener('DOMContentLoaded', load);
})();
