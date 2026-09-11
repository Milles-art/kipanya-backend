/**
 * KP Wear product page: size+color variant selection (exact variant ids,
 * never size-only guessing), quantity, add-to-bag, favorites, share,
 * sizing-quiz estimate. Data comes from the [data-kp-product-data] island,
 * which mirrors the backend variants payload.
 */
(() => {
    const page = document.querySelector('[data-product-page]');
    if (!page) return;

    const $ = (sel, r = page) => r.querySelector(sel);
    const $$ = (sel, r = page) => [...r.querySelectorAll(sel)];
    const esc = (v) => String(v ?? '').replace(/[&<>"']/g, (c) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
    }[c]));
    const toast = (m, t) => window.KipanyaToast?.show(m, t);

    let data = null;
    try { data = JSON.parse($('[data-kp-product-data]')?.textContent || 'null'); } catch (_) {}
    if (!data) return;

    const variants = data.variants ?? [];
    const inStock = variants.filter((v) => v.in_stock);
    let size = null;
    let color = null;
    let qty = 1;

    const sizeRow = $('[data-kp-size-selector]');
    const colorWrap = $('[data-kp-color-wrap]');
    const colorRow = $('[data-kp-color-selector]');
    const addBtn = $('[data-kp-add-to-cart]');
    const addLabel = $('[data-kp-add-label]');
    const stockNote = $('[data-kp-stock-note]');
    const qtyValue = $('[data-kp-qty-value]');

    if (!variants.length) {
        addBtn.disabled = true;
        addLabel.textContent = 'Unavailable';
    } else if (!inStock.length) {
        stockNote.hidden = false;
        stockNote.textContent = 'Out of stock';
        stockNote.classList.add('is-out');
        addBtn.disabled = true;
        addLabel.textContent = 'Out of stock';
    }

    const sizes = [...new Set(variants.map((v) => v.size))];
    const sizeInStock = (s) => inStock.some((v) => v.size === s);
    const colorsFor = (s) => [...new Set(inStock.filter((v) => v.size === s).map((v) => v.color))];
    const currentVariant = () => (size && color
        ? inStock.find((v) => v.size === size && v.color === color) ?? null
        : null);

    function paintAdd() {
        if (addBtn.disabled) return;
        const v = currentVariant();
        if (!size) addLabel.textContent = 'Choose a size to add';
        else if (!v) addLabel.textContent = 'That combination is out of stock';
        else addLabel.textContent = `Add to bag — ${v.size}${v.color ? ` · ${v.color}` : ''}`;
    }

    function renderSizes() {
        sizeRow.innerHTML = sizes.map((s) => {
            const ok = sizeInStock(s);
            return `<button type="button" class="kp-size-pill${s === size ? ' is-active' : ''}"`
                + ` data-size="${esc(s)}" aria-pressed="${s === size}"${ok ? '' : ' disabled aria-disabled="true" title="Out of stock"'}>${esc(s)}</button>`;
        }).join('');
        $$('[data-size]', sizeRow).forEach((pill) => pill.addEventListener('click', () => {
            size = pill.dataset.size;
            const colors = colorsFor(size);
            color = colors.length === 1 ? colors[0] : null;
            renderSizes();
            renderColors();
            paintAdd();
        }));
    }

    function renderColors() {
        if (!size) { colorWrap.hidden = true; colorRow.innerHTML = ''; return; }
        const colors = colorsFor(size);
        if (colors.length <= 1) {
            colorWrap.hidden = true;
            colorRow.innerHTML = '';
            if (colors.length === 1) color = colors[0];
            return;
        }
        colorWrap.hidden = false;
        colorRow.innerHTML = colors.map((c) => `<button type="button" class="kp-size-pill${c === color ? ' is-active' : ''}"`
            + ` data-color="${esc(c)}" aria-pressed="${c === color}">${esc(c)}</button>`).join('');
        $$('[data-color]', colorRow).forEach((pill) => pill.addEventListener('click', () => {
            color = pill.dataset.color;
            renderColors();
            paintAdd();
        }));
    }

    renderSizes();
    renderColors();
    paintAdd();

    /* Sticky mobile bar: reveals past the main CTA, delegates to it */
    const stickyBar = document.querySelector('[data-kp-sticky-atc]');
    const stickyAdd = document.querySelector('[data-kp-sticky-add]');
    const reduceMotion = window.matchMedia?.('(prefers-reduced-motion: reduce)').matches;
    const syncSticky = () => {
        if (!stickyBar || !addBtn) return;
        const past = addBtn.getBoundingClientRect().bottom < 0;
        const unavailable = addBtn.disabled;
        const show = past && !unavailable && window.innerWidth < 860;
        stickyBar.hidden = !show;
        stickyBar.classList.toggle('is-visible', show);
        if (stickyAdd) stickyAdd.textContent = !size ? 'Choose size' : 'Add to bag';
    };
    window.addEventListener('scroll', syncSticky, { passive: true });
    window.addEventListener('resize', syncSticky);
    window.setTimeout(syncSticky, 300);
    stickyAdd?.addEventListener('click', () => {
        if (!size) {
            sizeRow.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'center' });
            sizeRow.querySelector('[data-size]:not([disabled])')?.focus({ preventScroll: true });
            toast('Choose a size first.', 'info');
            return;
        }
        addBtn?.click();
        syncSticky();
    });

    $('[data-kp-qty-inc]')?.addEventListener('click', () => {
        qty = Math.min(qty + 1, 20);
        qtyValue.textContent = qty;
    });
    $('[data-kp-qty-dec]')?.addEventListener('click', () => {
        qty = Math.max(qty - 1, 1);
        qtyValue.textContent = qty;
    });

    addBtn?.addEventListener('click', async () => {
        const v = currentVariant();
        if (!v) {
            addLabel.textContent = size ? 'Pick an available combination' : 'Pick a size first';
            window.setTimeout(paintAdd, 1500);
            return;
        }
        addBtn.disabled = true;
        addLabel.textContent = 'Adding…';
        const result = await window.KipanyaCart.addItem(Number(v.id), qty);
        addBtn.disabled = false;
        if (result.ok) {
            document.dispatchEvent(new CustomEvent('kp:bag-bump'));
            toast(`${data.name} (${v.size}${v.color ? ` · ${v.color}` : ''}) × ${qty} added to bag.`, 'success');
        } else {
            window.KipanyaAnim?.shake(addBtn);
            toast(result.data?.message || 'Could not add to bag. Try again.', 'error');
        }
        paintAdd();
    });

    $$('[data-kp-favorite]').forEach((btn) => {
        btn.addEventListener('click', async () => {
            const isActive = btn.classList.contains('is-active');
            const result = await window.KipanyaCart.toggleFavorite(Number(btn.dataset.productId), isActive);
            if (result.requiresLogin) {
                toast('Favorites need a Kipanya account — account sign-in on web is coming soon.', 'info');
                return;
            }
            if (result.ok) {
                btn.classList.toggle('is-active', !isActive);
                btn.setAttribute('aria-pressed', String(!isActive));
                const icon = btn.querySelector('i');
                if (icon) {
                    icon.className = `ti ${!isActive ? 'ti-heart-filled' : 'ti-heart'}`;
                    if (!isActive) window.KipanyaAnim?.pop(icon);
                }
            }
        });
    });

    $('[data-kp-share]')?.addEventListener('click', async () => {
        const url = window.location.href;
        try {
            if (navigator.share) { await navigator.share({ title: data.name, url }); return; }
            await navigator.clipboard.writeText(url);
            toast('Product link copied.', 'success');
        } catch (_) {
            toast('Could not share this product.', 'error');
        }
    });

    /* Sizing quiz (client-side estimate from height + weight — no backend
       endpoint exists; general alpha-size logic, honestly labeled) */
    const modal = $('[data-kp-sizing-modal]');
    const openBtn = $('[data-kp-sizing-quiz-open]');
    const closeBtn = $('[data-kp-sizing-close]');
    const showModal = (m) => {
        if (!m) return;
        m.hidden = false;
        requestAnimationFrame(() => m.classList.add('is-open'));
    };
    const hideModal = (m, returnTo) => {
        if (!m || m.hidden) return;
        m.classList.remove('is-open');
        window.setTimeout(() => { m.hidden = true; }, 300);
        returnTo?.focus();
    };
    $('[data-kp-sizing-skip]')?.addEventListener('click', () => hideModal(modal, openBtn));
    openBtn?.addEventListener('click', () => {
        showModal(modal);
        $('[data-kp-quiz-height]')?.focus();
    });
    closeBtn?.addEventListener('click', () => hideModal(modal, openBtn));
    modal?.addEventListener('click', (e) => { if (e.target === modal) hideModal(modal, openBtn); });

    /* General size chart dialog */
    const chartModal = document.querySelector('[data-kp-chart-modal]');
    const chartOpen = document.querySelector('[data-kp-chart-open]');
    const chartClose = document.querySelector('[data-kp-chart-close]');
    chartOpen?.addEventListener('click', () => {
        showModal(chartModal);
        chartClose?.focus();
    });
    chartClose?.addEventListener('click', () => hideModal(chartModal, chartOpen));
    chartModal?.addEventListener('click', (e) => { if (e.target === chartModal) hideModal(chartModal, chartOpen); });
    document.addEventListener('keydown', (e) => {
        if (e.key !== 'Escape') return;
        if (modal && !modal.hidden) hideModal(modal, openBtn);
        if (chartModal && !chartModal.hidden) hideModal(chartModal, chartOpen);
    });

    const LADDER = ['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL'];

    const estimateSize = (heightCm, weightKg, fit) => {
        let base;
        if (heightCm < 160) base = 'S';
        else if (heightCm < 175) base = 'M';
        else if (heightCm < 188) base = 'L';
        else base = 'XL';

        // Weight adjustment around a reference BMI of 22.
        const ref = 22 * ((heightCm / 100) ** 2);
        let idx = LADDER.indexOf(base);
        if (weightKg > ref + 25) idx += 2;
        else if (weightKg > ref + 12) idx += 1;
        else if (weightKg < ref - 12) idx -= 1;

        if (fit === 'slim') idx -= 1;
        if (fit === 'relaxed') idx += 1;
        if (fit === 'oversized') idx += 2;
        return LADDER[Math.max(0, Math.min(LADDER.length - 1, idx))];
    };

    const availableSizes = [...new Set(variants.map((v) => v.size))];
    const nearestOffered = (guess) => {
        if (availableSizes.includes(guess)) return { size: guess, exact: true };
        const gi = LADDER.indexOf(guess);
        const ranked = [...availableSizes].sort((a, b) =>
            Math.abs(LADDER.indexOf(a) - gi) - Math.abs(LADDER.indexOf(b) - gi));
        return { size: ranked[0], exact: false };
    };

    $('[data-kp-quiz-submit]')?.addEventListener('click', () => {
        const hRaw = $('[data-kp-quiz-height]').value.trim();
        const wRaw = $('[data-kp-quiz-weight]').value.trim();
        const height = Number(hRaw);
        const weight = Number(wRaw);
        const fit = $('[data-kp-quiz-fit]').value;
        const resultEl = $('[data-kp-quiz-result]');
        if (!hRaw || !Number.isFinite(height) || height < 120 || height > 230) {
            resultEl.textContent = 'Enter a realistic height between 120 and 230 cm.';
            $('[data-kp-quiz-height]')?.focus();
            return;
        }
        if (!wRaw || !Number.isFinite(weight) || weight < 30 || weight > 250) {
            resultEl.textContent = 'Enter a realistic weight between 30 and 250 kg.';
            $('[data-kp-quiz-weight]')?.focus();
            return;
        }
        const guess = estimateSize(height, weight, fit);
        const { size: pick, exact } = nearestOffered(guess);
        const pill = sizeRow.querySelector(`[data-size="${CSS.escape(pick)}"]`);
        if (pill && !pill.disabled) {
            resultEl.textContent = exact
                ? `Suggested size: ${pick}. Choose it above if it feels right.`
                : `Suggested size: ${guess}; nearest available size is ${pick}. Choose it above if it feels right.`;
        } else {
            resultEl.textContent = `Suggested size: ${pick}, but it is out of stock right now.`;
        }
    });

    /* Accordion motion is owned by kipanya-wear-animations.js
       (animated single-open); no local handler to avoid double toggles. */
})();
