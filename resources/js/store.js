(() => {
  const API = '/api/v1';
  const TOKEN_KEY = 'kp_api_token';
  const USER_KEY = 'kp_user';
  const GUEST_KEY = 'kp_guest_cart_token';
  const getJson = (key, fallback = null) => {
    try { return JSON.parse(localStorage.getItem(key) ?? JSON.stringify(fallback)); } catch { return fallback; }
  };
  const setJson = (key, value) => localStorage.setItem(key, JSON.stringify(value));
  const toast = (message) => {
    const node = document.querySelector('#toast');
    if (!node) return;
    node.textContent = message;
    node.classList.remove('hidden');
    clearTimeout(window.kpToast);
    window.kpToast = setTimeout(() => node.classList.add('hidden'), 2400);
  };
  const token = () => localStorage.getItem(TOKEN_KEY);
  const guestToken = () => localStorage.getItem(GUEST_KEY);
  const ensureGuestToken = () => {
    let value = guestToken();
    if (!value) {
      const bytes = new Uint8Array(48);
      crypto.getRandomValues(bytes);
      value = [...bytes].map(b => b.toString(16).padStart(2, '0')).join('');
      localStorage.setItem(GUEST_KEY, value);
    }
    return value;
  };
  const api = async (path, options = {}) => {
    const headers = new Headers(options.headers || {});
    headers.set('Accept', 'application/json');
    if (options.body && !(options.body instanceof FormData)) headers.set('Content-Type', 'application/json');
    if (token()) headers.set('Authorization', `Bearer ${token()}`);
    if (!token()) headers.set('X-Guest-Cart-Token', ensureGuestToken());
    const response = await fetch(`${API}${path}`, { ...options, headers, body: options.body && !(options.body instanceof FormData) ? JSON.stringify(options.body) : options.body });
    const text = await response.text();
    let data = null;
    try { data = text ? JSON.parse(text) : null; } catch { /* non-json response */ }
    if (response.status === 401) {
      localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY);
      if (!location.pathname.includes('/login') && !location.pathname.includes('/register')) toast('Please sign in to continue.');
    }
    if (!response.ok) {
      const message = data?.message || Object.values(data?.errors || {})?.flat?.()?.[0] || `Request failed (${response.status})`;
      throw new Error(message);
    }
    return data;
  };
  const setAuth = (payload) => {
    if (payload?.token) localStorage.setItem(TOKEN_KEY, payload.token);
    if (payload?.user) setJson(USER_KEY, payload.user);
  };
  const currentUser = () => getJson(USER_KEY, null);
  const counts = async () => {
    try {
      const data = await api('/cart');
      document.querySelectorAll('[data-cart-count]').forEach(n => {
        n.textContent = data?.data?.item_count ?? 0;
        n.classList.toggle('hidden', !(data?.data?.item_count > 0));
      });
    } catch {}

    if (token()) {
      try {
        const wishlist = await api('/wishlist');
        const wishCount = (wishlist?.data || []).length;
        document.querySelectorAll('[data-wishlist-count]').forEach(n => {
          n.textContent = wishCount;
          n.classList.toggle('hidden', wishCount === 0);
        });
        const savedIds = new Set((wishlist?.data || []).map(item => String(item.product?.id ?? item.wear_product_id)));
        document.querySelectorAll('[data-wishlist-product]').forEach(button => {
          if (savedIds.has(String(button.dataset.wishlistProduct))) {
            button.dataset.saved = '1';
            button.innerHTML = icon('heart');
            button.classList.add('bg-rose-500', 'text-white');
            button.classList.remove('bg-white', 'text-gray-800');
          }
        });
      } catch {}
    } else {
      document.querySelectorAll('[data-wishlist-count]').forEach(n => {
        n.classList.add('hidden');
      });
    }
  };

  const icon = (name, size = 18) => {
    const paths = {
      heart: '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"></path>',
      bag: '<path d="M6 8h12l1 13H5L6 8Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>',
      star: '<path d="m12 3 2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.92 1.06-6.2L3 9.53l6.22-.9L12 3Z"></path>',
    };
    return `<svg xmlns="http://www.w3.org/2000/svg" width="${size}" height="${size}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] || ''}</svg>`;
  };

  const colorHex = {
    black: '#1a1a1a', white: '#f5f5f5', navy: '#1e3a5f', olive: '#5b6f4a',
    burgundy: '#6b2c2c', camel: '#c4a882', gray: '#8a8a8a', sage: '#9caf88', sand: '#d7c4a3'
  };

  const productStock = (product) => (product.variants || []).reduce((sum, v) => sum + Number(v.stock || 0), 0);

  const productIsNew = (product) => String(product.badge || '').toLowerCase() === 'new';
  const productOnSale = (product) => product.compare_at_price !== null && Number(product.compare_at_price) > Number(product.price);

  const resolveProductImage = (product) =>
    product?.image || '/assets/wear/catalog/generated/product-01.jpg';


  const renderProductGrid = (container, products) => {
    if (!container) return;

    container.innerHTML = products.map((p) => {
      const compare = p.compare_at_price !== null ? Number(p.compare_at_price) : null;
      const price = Number(p.price || 0);
      const onSale = compare !== null && compare > price;
      const discount = onSale ? Math.round(((compare - price) / compare) * 100) : 0;
      const stock = productStock(p);
      const colors = [...new Set((p.variants || []).map(v => v.color).filter(Boolean))];
      const image = resolveProductImage(p);
      const primaryVariant = findFirstVariant(p);
      const rating = Number(p.rating || 0);
      const reviewCount = Number(p.review_count || p.reviewCount || 0);

      return `
        <article class="kp-product-card group relative min-w-0" data-product-card data-product-id="${p.id}">
          <div class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-gray-100">
            <a href="/product/${encodeURIComponent(p.slug)}" class="block h-full w-full">
              <img src="${image}" alt="${p.name || 'Product'}" loading="lazy" class="h-full w-full object-cover transition duration-500 ease-out group-hover:scale-105">
            </a>

            <div class="pointer-events-none absolute left-3 top-3 flex flex-col gap-1.5">
              ${onSale ? `<span class="w-fit rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">-${discount}%</span>` : ''}
              ${productIsNew(p) ? '<span class="w-fit rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">New</span>' : (!onSale && p.badge ? `<span class="w-fit rounded-full bg-gray-950 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">${p.badge}</span>` : '')}
            </div>

            ${p.availability === 'out_of_stock' ? '<span class="pointer-events-none absolute right-3 top-3 rounded-full bg-gray-900 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">Sold out</span>' : (stock > 0 && stock < 10 ? '<span class="pointer-events-none absolute right-3 top-3 rounded-full bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">Low stock</span>' : '')}

            <button type="button" data-wishlist-product="${p.id}" data-saved="0" class="absolute bottom-3 left-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-rose-500 hover:text-white" aria-label="Toggle wishlist for ${p.name || 'product'}">
              ${icon('heart')}
            </button>

            <button type="button" data-quick-add="${p.id}" data-quick-variant="${primaryVariant?.id || ''}" ${p.availability === 'out_of_stock' ? 'disabled' : ''} class="absolute bottom-3 right-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-emerald-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-50" aria-label="Add ${p.name || 'product'} to bag">
              ${icon('bag')}
            </button>
          </div>

          <div>
            <p class="mb-1 text-xs text-gray-400">${p.category || ''}</p>

            ${rating > 0 ? `
              <div class="mb-1 flex items-center gap-1 text-xs text-gray-500">
                <span class="inline-flex text-amber-400">${icon('star', 13)}</span>
                <span>${rating.toFixed(1)}${reviewCount ? ` (${reviewCount})` : ''}</span>
              </div>
            ` : ''}

            <a href="/product/${encodeURIComponent(p.slug)}" class="line-clamp-1 text-sm font-medium text-gray-950 transition-colors hover:text-emerald-700">${p.name || ''}</a>

            <div class="mt-1 flex items-center gap-2">
              <span class="text-sm font-semibold text-gray-950">${price.toLocaleString()} TZS</span>
              ${onSale ? `<span class="text-xs text-gray-400 line-through">${compare.toLocaleString()} TZS</span>` : ''}
            </div>

            ${colors.length ? `
              <div class="mt-2.5 flex items-center gap-1.5">
                ${colors.slice(0, 5).map(c => `<span class="h-3.5 w-3.5 rounded-full border border-gray-200 shadow-sm" style="background-color:${colorHex[String(c).toLowerCase()] || '#d1d5db'}" title="${c}"></span>`).join('')}
                ${colors.length > 5 ? `<span class="text-[11px] text-gray-400">+${colors.length - 5}</span>` : ''}
              </div>
            ` : ''}
          </div>
        </article>`;
    }).join('');

    document.dispatchEvent(new Event('kp:grid-rendered'));
  };

  const fetchProducts = async (params = '') => (await api(`/wear/products${params ? `?${params}` : ''}`))?.data || [];
  const fetchCategories = async () => (await api('/wear/categories'))?.data || [];
  const findFirstVariant = (product) => (product.variants || []).find(v => v.in_stock) || product.variants?.[0] || null;

  const addVariantToCart = async (variantId, quantity = 1) => {
    await api('/cart/items', { method: 'POST', body: { variant_id: Number(variantId), quantity: Number(quantity) } });
    toast('Added to your bag');
    await counts();
  };
  const toggleWishlist = async (productId, button = null) => {
    if (!token()) {
      location.href = '/login';
      return;
    }

    const state = button?.dataset?.saved === '1';

    if (state) {
      await api(`/wishlist/${productId}`, { method: 'DELETE' });
      if (button) {
        button.dataset.saved = '0';
        button.innerHTML = icon('heart');
        button.classList.remove('bg-rose-500', 'text-white');
        button.classList.add('bg-white', 'text-gray-800');
      }
      toast('Removed from wishlist');
    } else {
      await api('/wishlist', { method: 'POST', body: { product_id: Number(productId) } });
      if (button) {
        button.dataset.saved = '1';
        button.innerHTML = icon('heart');
        button.classList.add('bg-rose-500', 'text-white');
        button.classList.remove('bg-white', 'text-gray-800');
      }
      toast('Saved to wishlist');
    }

    await counts();
  };

  const bootHome = async () => {
    const grid = document.querySelector('[data-home-featured]');
    if (!grid) return;
    try {
      const [products, categories] = await Promise.all([fetchProducts('per_page=12'), fetchCategories()]);
      renderProductGrid(grid, products.slice(0, 8));
      const catGrid = document.querySelector('[data-home-categories]');
      if (catGrid) catGrid.innerHTML = categories.map(c => `<a href="/category/${encodeURIComponent(c.slug)}" class="group rounded-2xl border border-gray-100 p-5 hover:border-emerald-200"><p class="text-sm font-semibold">${c.name}</p><p class="mt-1 text-xs text-gray-400">Shop collection</p></a>`).join('');
    } catch (e) { toast(e.message); }
  };
  const bootCatalog = async () => {
    const page = document.querySelector('[data-catalog-grid]');
    if (!page) return;

    const searchInput = document.querySelector('[data-catalog-search]');
    const sortSelect = document.querySelector('[data-catalog-sort]');
    const categoryHost = document.querySelector('[data-catalog-categories]');
    const priceHost = document.querySelector('[data-catalog-prices]');
    const saleToggle = document.querySelector('[data-catalog-sale-toggle]');
    const count = document.querySelector('[data-catalog-count]');
    const empty = document.querySelector('[data-catalog-empty]');
    const emptyClear = document.querySelector('[data-catalog-empty-clear]');
    const clearButton = document.querySelector('[data-catalog-clear]');
    const filterCount = document.querySelector('[data-catalog-filter-count]');
    const mobilePanel = document.querySelector('[data-catalog-mobile-panel]');
    const mobileContent = document.querySelector('[data-catalog-mobile-content]');

    const state = {
      products: [],
      category: new URLSearchParams(location.search).get('category') || '',
      search: new URLSearchParams(location.search).get('q') || '',
      price: 'all',
      saleOnly: false,
      sort: 'featured',
    };

    const priceMatch = (product) => {
      const value = Number(product.price || 0);
      if (state.price === 'under-50000') return value < 50000;
      if (state.price === '50000-150000') return value >= 50000 && value <= 150000;
      if (state.price === 'over-150000') return value > 150000;
      return true;
    };

    const apply = () => {
      let result = state.products.filter((p) => {
        if (state.category && String(p.category).toLowerCase() !== state.category.toLowerCase()) return false;
        if (state.search) {
          const q = state.search.toLowerCase();
          const haystack = `${p.name || ''} ${p.description || ''} ${p.category || ''}`.toLowerCase();
          if (!haystack.includes(q)) return false;
        }
        if (!priceMatch(p)) return false;
        if (state.saleOnly && !productOnSale(p)) return false;
        return true;
      });

      if (state.sort === 'price-asc') result.sort((a,b) => Number(a.price) - Number(b.price));
      else if (state.sort === 'price-desc') result.sort((a,b) => Number(b.price) - Number(a.price));
      else if (state.sort === 'newest') result.sort((a,b) => Number(b.id) - Number(a.id));
      else if (state.sort === 'rating') result.sort((a,b) => Number(b.id) - Number(a.id));
      else result.sort((a,b) => Number(b.is_featured) - Number(a.is_featured));

      renderProductGrid(page, result);
      if (count) count.textContent = `${result.length} ${result.length === 1 ? 'product' : 'products'} found`;
      empty?.classList.toggle('hidden', result.length > 0);
      empty?.classList.toggle('flex', result.length === 0);

      const filters = (state.category ? 1 : 0) + (state.price !== 'all' ? 1 : 0) + (state.saleOnly ? 1 : 0) + (state.search ? 1 : 0);
      filterCount?.classList.toggle('hidden', filters === 0);
      filterCount?.classList.toggle('inline-flex', filters > 0);
      if (filterCount) filterCount.textContent = String(filters);
      clearButton?.classList.toggle('hidden', filters === 0);
      saleToggle?.classList.toggle('bg-emerald-600', state.saleOnly);
      saleToggle?.classList.toggle('bg-gray-200', !state.saleOnly);
      saleToggle?.setAttribute('aria-pressed', state.saleOnly ? 'true' : 'false');
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-5', state.saleOnly);
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-0', !state.saleOnly);

      document.querySelectorAll('[data-catalog-categories] [data-category]').forEach((button) => {
        const active = String(button.dataset.category) === String(state.category || '');
        button.classList.toggle('bg-emerald-50', active);
        button.classList.toggle('text-emerald-700', active);
        button.classList.toggle('font-medium', active);
        button.classList.toggle('text-gray-600', !active);
      });
    };

    const renderFilters = (categories) => {
      const markup = `
        <div class="space-y-8">
          <div>
            <div class="mb-4 flex items-center justify-between">
              <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Category</h3>
              <button type="button" data-catalog-clear-mobile class="text-xs font-medium text-emerald-600">Clear all</button>
            </div>
            <div class="space-y-1">
              <button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>
              ${categories.map(c => `<button type="button" data-category="${c.name}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${c.name}</button>`).join('')}
            </div>
          </div>
          <div>
            <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">Price Range</h3>
            <div class="space-y-1">
              <button type="button" data-price="all" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Prices</button>
              <button type="button" data-price="under-50000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Under 50,000 TZS</button>
              <button type="button" data-price="50000-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">50,000 – 150,000 TZS</button>
              <button type="button" data-price="over-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Over 150,000 TZS</button>
            </div>
          </div>
        </div>`;
      if (mobileContent) mobileContent.innerHTML = markup;
      if (categoryHost) categoryHost.innerHTML = markup.split('<div class="space-y-8">')[1]?.split('<div><h3')[0]?.replace('</div></div><div>', '') || '';
    };

    try {
      const [products, categories] = await Promise.all([
        fetchProducts('per_page=50'),
        fetchCategories(),
      ]);

      state.products = products;

      if (searchInput) searchInput.value = state.search;
      if (state.category) state.category = decodeURIComponent(state.category).replaceAll('-', ' ');

      if (categoryHost) {
        categoryHost.innerHTML = [
          '<button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>',
          ...categories.map(c => `<button type="button" data-category="${c.name}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${c.name}</button>`),
        ].join('');
      }

      if (mobileContent) {
        mobileContent.innerHTML = `
          <div class="space-y-8">
            <div><div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Category</h3><button type="button" data-catalog-clear-mobile class="text-xs font-medium text-emerald-600">Clear all</button></div><div data-mobile-categories class="space-y-1"><button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>${categories.map(c => `<button type="button" data-category="${c.name}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${c.name}</button>`).join('')}</div></div>
            <div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">Price Range</h3><div class="space-y-1"><button type="button" data-price="all" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Prices</button><button type="button" data-price="under-50000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Under 50,000 TZS</button><button type="button" data-price="50000-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">50,000 – 150,000 TZS</button><button type="button" data-price="over-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Over 150,000 TZS</button></div></div>
            <div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">Special</h3><label class="flex items-center gap-3"><button type="button" data-catalog-sale-toggle-mobile class="relative h-6 w-11 rounded-full bg-gray-200"><span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span></button><span class="text-sm text-gray-700">On Sale Only</span></label></div>
          </div>`;
      }

      const catalogClickHandler = (event) => {
        const category = event.target.closest('[data-category]');
        if (category) {
          state.category = category.dataset.category || '';
          apply();
          mobilePanel?.classList.add('hidden');
        }

        const price = event.target.closest('[data-price]');
        if (price) {
          state.price = price.dataset.price || 'all';
          document.querySelectorAll('[data-catalog-prices] [data-price], [data-catalog-mobile-content] [data-price]').forEach((el) => {
            const active = el.dataset.price === state.price;
            el.classList.toggle('bg-emerald-50', active);
            el.classList.toggle('text-emerald-700', active);
            el.classList.toggle('font-medium', active);
          });
          apply();
        }

        const clear = event.target.closest('[data-catalog-clear],[data-catalog-clear-mobile],[data-catalog-empty-clear]');
        if (clear) {
          state.category = '';
          state.search = '';
          state.price = 'all';
          state.saleOnly = false;
          if (searchInput) searchInput.value = '';
          apply();
        }

        const mobileSale = event.target.closest('[data-catalog-sale-toggle-mobile]');
        if (mobileSale) {
          state.saleOnly = !state.saleOnly;
          apply();
        }
      };

      document.addEventListener('click', catalogClickHandler);

      searchInput?.addEventListener('input', () => {
        state.search = searchInput.value.trim();
        apply();
      });
      sortSelect?.addEventListener('change', () => {
        state.sort = sortSelect.value;
        apply();
      });
      saleToggle?.addEventListener('click', () => {
        state.saleOnly = !state.saleOnly;
        apply();
      });

      document.querySelector('[data-catalog-filter-toggle]')?.addEventListener('click', () => mobilePanel?.classList.remove('hidden'));
      document.querySelector('[data-catalog-filter-close]')?.addEventListener('click', () => mobilePanel?.classList.add('hidden'));
      mobilePanel?.addEventListener('click', (event) => { if (event.target === mobilePanel) mobilePanel.classList.add('hidden'); });

      apply();
    } catch (e) {
      toast(e.message);
    }
  };

  const bootProduct = async () => {
    const page = document.querySelector('[data-product-page]');
    if (!page) return;
    const slug = page.dataset.slug;
    try {
      const response = await api(`/wear/products/${encodeURIComponent(slug)}`);
      const p = response?.data;
      if (!p) throw new Error('Product not found');
      page.querySelector('[data-product-name]').textContent = p.name;
      page.querySelector('[data-product-category]').textContent = p.category || '';
      page.querySelector('[data-product-description]').textContent = p.description || '';
      page.querySelector('[data-product-price]').textContent = `${Number(p.price).toLocaleString()} TZS`;
      const compare = page.querySelector('[data-product-compare]');
      if (p.compare_at_price !== null) { compare.textContent = `${Number(p.compare_at_price).toLocaleString()} TZS`; compare.classList.remove('hidden'); }
      const main = page.querySelector('[data-product-image]'); if (main) { main.src = p.image || ''; main.alt = p.name; }
      const sizes = page.querySelector('[data-product-variants]');
      if (sizes) {
        sizes.innerHTML = (p.variants || []).map((v, i) => `<button type="button" data-variant="${v.id}" data-stock="${v.stock}" class="rounded-lg border px-4 py-2 text-sm ${i === 0 ? 'border-gray-950 bg-gray-950 text-white' : 'border-gray-200'}" ${!v.in_stock ? 'disabled' : ''}>${v.size}${v.color ? ` · ${v.color}` : ''}</button>`).join('');
        page.dataset.selectedVariant = findFirstVariant(p)?.id || '';
      }
      page.querySelector('[data-add-selected]')?.addEventListener('click', async () => {
        if (!page.dataset.selectedVariant) return toast('Select an available size');
        try { await addVariantToCart(page.dataset.selectedVariant, Number(page.querySelector('[data-quantity]')?.value || 1)); } catch (e) { toast(e.message); }
      });
      sizes?.addEventListener('click', e => { const btn = e.target.closest('[data-variant]'); if (!btn || btn.disabled) return; page.dataset.selectedVariant = btn.dataset.variant; sizes.querySelectorAll('[data-variant]').forEach(x => x.classList.remove('border-gray-950','bg-gray-950','text-white')); btn.classList.add('border-gray-950','bg-gray-950','text-white'); });
    } catch (e) { toast(e.message); }
  };
  const bootCart = async () => {
    const page = document.querySelector('[data-cart-page]'); if (!page) return;
    const render = async () => {
      const data = await api('/cart'); const cart = data?.data; const rows = page.querySelector('[data-cart-items]'); if (!rows) return;
      rows.innerHTML = (cart.items || []).map(item => `<div class="flex gap-4 border-b border-gray-100 py-5"><img src="${item.product.image || ''}" class="h-24 w-20 rounded-xl object-cover"><div class="min-w-0 flex-1"><a href="/product/${item.product.slug}" class="font-medium">${item.product.name}</a><p class="mt-1 text-sm text-gray-500">${item.variant.size || ''}${item.variant.color ? ` · ${item.variant.color}` : ''}</p><div class="mt-3 flex items-center gap-3"><button data-cart-minus="${item.variant_id}" class="rounded border px-2">−</button><span>${item.quantity}</span><button data-cart-plus="${item.variant_id}" class="rounded border px-2">+</button><button data-cart-remove="${item.variant_id}" class="ml-3 text-sm text-rose-500">Remove</button></div></div><p class="font-semibold">${Number(item.line_total).toLocaleString()} TZS</p></div>`).join('');
      page.querySelector('[data-cart-empty]')?.classList.toggle('hidden', cart.items.length > 0); page.querySelector('[data-cart-content]')?.classList.toggle('hidden', cart.items.length === 0); page.querySelector('[data-cart-summary]').textContent = `${Number(cart.subtotal).toLocaleString()} TZS`; await counts();
    };
    try { await render(); } catch(e) { toast(e.message); }
    page.addEventListener('click', async e => {
      const b = e.target.closest('[data-cart-plus],[data-cart-minus],[data-cart-remove]'); if (!b) return;
      try {
        const variant = b.dataset.cartPlus || b.dataset.cartMinus || b.dataset.cartRemove;
        const cart = (await api('/cart')).data; const item = cart.items.find(i => String(i.variant_id) === String(variant));
        if (b.dataset.cartRemove) await api(`/cart/items/${variant}`, { method: 'DELETE' });
        else await api(`/cart/items/${variant}`, { method: 'PUT', body: { quantity: Math.max(0, Number(item.quantity) + (b.dataset.cartPlus ? 1 : -1)) } });
        await render();
      } catch(e) { toast(e.message); }
    });
  };

  const bootAuth = () => {
    const login = document.querySelector('[data-login-form]');
    const register = document.querySelector('[data-register-form]');
    const form = login || register; if (!form) return;
    form.addEventListener('submit', async e => {
      e.preventDefault();
      const fd = new FormData(form); const phone = fd.get('phone');
      try {
        const first = form.dataset.step !== 'otp';
        const requestPath = login ? '/auth/login/request-otp' : '/auth/register/request-otp';
        if (first) { await api(requestPath, { method: 'POST', body: { phone } }); form.dataset.step = 'otp'; form.querySelector('[data-otp-step]')?.classList.remove('hidden'); form.querySelector('[data-primary-step]')?.classList.add('hidden'); toast('Verification code requested'); return; }
        const code = fd.get('code');
        const path = login ? '/auth/login' : '/auth/register';
        const body = login ? { phone, code } : { phone, code, name: fd.get('name'), referral_code: fd.get('referral_code') || null };
        const payload = await api(path, { method: 'POST', body }); setAuth(payload); const guest = guestToken(); if (guest) { try { await api('/cart/merge', { method: 'POST', body: { guest_cart_token: guest } }); } catch {} }
        location.href = '/account';
      } catch (err) { toast(err.message); }
    });
  };
  const bootWishlist = async () => {
    const page = document.querySelector('[data-wishlist-page]'); if (!page) return;
    if (!token()) return;
    try {
      const data = await api('/wishlist'); const products = (data?.data || []).map(i => i.product).filter(Boolean);
      renderProductGrid(page.querySelector('[data-wishlist-grid]'), products); await counts();
    } catch (e) { toast(e.message); }
  };
  const bindGlobal = () => {
    document.querySelectorAll('[data-search-toggle]').forEach(b => b.addEventListener('click', () => document.querySelector('[data-search-panel]')?.classList.toggle('hidden')));
    document.querySelectorAll('[data-menu-toggle]').forEach(b => b.addEventListener('click', () => document.querySelector('[data-mobile-menu]')?.classList.toggle('hidden')));
    document.addEventListener('click', async e => {
      const add = e.target.closest('[data-quick-add]'); if (add) {
        try {
          const quickVariant = add.dataset.quickVariant;
          if (quickVariant) {
            await addVariantToCart(quickVariant);
          } else {
            const products = await fetchProducts('per_page=50');
            const product = products.find(x => String(x.id) === String(add.dataset.quickAdd));
            const variant = findFirstVariant(product);
            if (!variant) throw new Error('No variant available');
            await addVariantToCart(variant.id);
          }
        } catch(err) { toast(err.message); }
      }
      const wish = e.target.closest('[data-wishlist-product]'); if (wish) { try { await toggleWishlist(wish.dataset.wishlistProduct, wish); } catch(err) { toast(err.message); } }
    });
    counts();
  };

  const bootCheckout = async () => {
    const page = document.querySelector('[data-checkout-page]'); if (!page) return;
    if (!token()) { page.querySelector('[data-checkout-auth]')?.classList.remove('hidden'); page.querySelector('[data-address-form]')?.classList.add('hidden'); page.querySelector('[data-place-order]')?.setAttribute('disabled','disabled'); return; }
    const addressList = page.querySelector('[data-address-list]');
    const summary = page.querySelector('[data-checkout-summary]');
    let selectedAddress = null;
    const renderAddresses = (addresses) => {
      addressList.innerHTML = addresses.length ? addresses.map((a,i) => `<label class="flex cursor-pointer gap-3 rounded-xl border p-4 ${i===0?'border-gray-950':''}"><input type="radio" name="address_id" value="${a.id}" ${i===0?'checked':''} class="mt-1"/><span><strong class="text-sm">${a.recipient_name}</strong><span class="mt-1 block text-sm text-gray-500">${a.phone} · ${a.region}, ${a.district}${a.ward?`, ${a.ward}`:''} · ${a.street}</span></span></label>`).join('') : '<p class="text-sm text-gray-500">No saved addresses yet. Add one below.</p>';
      selectedAddress = addresses[0]?.id || null;
      addressList.querySelectorAll('input[name="address_id"]').forEach(input => input.addEventListener('change', () => selectedAddress = Number(input.value)));
    };
    try {
      const [addresses, preview] = await Promise.all([api('/addresses'), api('/cart/checkout/preview')]);
      renderAddresses(addresses.data || []);
      const d = preview?.data || {};
      summary.innerHTML = `<div class="flex justify-between"><span>Subtotal</span><strong>${Number(d.subtotal || 0).toLocaleString()} TZS</strong></div><div class="flex justify-between"><span>Delivery</span><strong>${Number(d.delivery_fee || 0).toLocaleString()} TZS</strong></div><div class="mt-3 flex justify-between border-t border-gray-200 pt-3 text-base"><span>Total</span><strong>${Number(d.total || d.subtotal || 0).toLocaleString()} TZS</strong></div>`;
    } catch(e) { toast(e.message); }
    page.querySelector('[data-address-form]')?.addEventListener('submit', async e => {
      e.preventDefault(); const fd = new FormData(e.currentTarget);
      try { const payload = await api('/addresses', {method:'POST', body:{type:'shipping', recipient_name:fd.get('recipient_name'), phone:fd.get('phone'), region:fd.get('region'), district:fd.get('district'), ward:fd.get('ward')||null, street:fd.get('street'), is_default:true}}); selectedAddress = payload.data?.id || null; toast('Address saved'); location.reload(); } catch(err) { toast(err.message); }
    });
    page.querySelector('[data-place-order]')?.addEventListener('click', async () => {
      if (!selectedAddress) return toast('Add or select a delivery address');
      const key = `kp-${Date.now()}-${crypto.randomUUID().replaceAll('-','').slice(0,16)}`;
      try { const order = await api('/checkout', {method:'POST', headers:{'Idempotency-Key':key}, body:{address_id:Number(selectedAddress), notes:page.querySelector('[data-order-notes]')?.value || null}}); location.href = `/orders/${order.data.order_number}`; } catch(e) { toast(e.message); }
    });
  };
  const bootAccount = async () => {
    const account = document.querySelector('[data-account-page]');
    const profile = document.querySelector('[data-account-profile]');
    const sidebar = document.querySelector('aside [data-sidebar-name]') ? document.querySelector('aside') : null;
    if (!account && !profile && !sidebar) return;
    if (!token()) { if (account) location.href='/login'; return; }
    try {
      const me = await api('/auth/me'); setAuth({user:me.data});
      document.querySelectorAll('[data-account-name]').forEach(n => n.textContent = `Welcome back, ${me.data.name}`);
      document.querySelectorAll('[data-profile-name]').forEach(n => n.textContent = me.data.name);
      document.querySelectorAll('[data-profile-phone]').forEach(n => n.textContent = me.data.phone);
      document.querySelectorAll('[data-sidebar-name]').forEach(n => n.textContent = me.data.name);
      document.querySelectorAll('[data-sidebar-phone]').forEach(n => n.textContent = me.data.phone);
      const orders = await api('/orders');
      if (account) account.querySelector('[data-account-orders-count]').textContent = orders.data?.length ?? 0;
    } catch(e) { toast(e.message); }
  };
  const bootOrders = async () => {
    const page = document.querySelector('[data-orders-page]'); if (!page) return;
    if (!token()) { location.href='/login'; return; }
    try {
      const orders = await api('/orders');
      page.querySelector('[data-orders-list]').innerHTML = (orders.data || []).map(o => `<div class="flex flex-wrap items-center justify-between gap-4 rounded-2xl border border-gray-100 p-5"><div><p class="font-semibold">${o.order_number}</p><p class="mt-1 text-sm text-gray-500">${o.created_at ? new Date(o.created_at).toLocaleString() : ''}</p></div><span class="rounded-full bg-gray-100 px-3 py-1 text-xs font-semibold">${o.status}</span><strong>${Number(o.total).toLocaleString()} TZS</strong><a href="/account/orders/${o.order_number}" class="text-sm font-medium text-emerald-600">View details →</a></div>`).join('') || '<p class="text-sm text-gray-500">No orders yet.</p>';
    } catch(e) { toast(e.message); }
  };
  const bootOrderDetail = async () => {
    const page = document.querySelector('[data-order-detail]'); if (!page) return;
    if (!token()) { location.href='/login'; return; }
    try { const o = (await api(`/orders/${encodeURIComponent(page.dataset.orderNumber)}`)).data; page.querySelector('[data-order-detail-content]').innerHTML = `<div class="flex flex-wrap items-start justify-between gap-4"><div><p class="text-sm text-gray-500">Status</p><p class="mt-1 font-semibold">${o.status}</p></div><div><p class="text-sm text-gray-500">Payment</p><p class="mt-1 font-semibold">${o.payment_status}</p></div><div><p class="text-sm text-gray-500">Total</p><p class="mt-1 font-semibold">${Number(o.total).toLocaleString()} TZS</p></div></div><div class="mt-8 border-t border-gray-100 pt-6">${(o.items || []).map(i => `<div class="flex justify-between border-b border-gray-100 py-3 text-sm"><span>${i.name} · ${i.size || ''} × ${i.quantity}</span><strong>${Number(i.line_total).toLocaleString()} TZS</strong></div>`).join('')}</div>`; } catch(e) { toast(e.message); }
  };
  const bootAddresses = async () => {
    const page = document.querySelector('[data-account-addresses]'); if (!page) return;
    if (!token()) { location.href='/login'; return; }
    try { const data = await api('/addresses'); page.querySelector('[data-addresses-list]').innerHTML = (data.data || []).map(a => `<div class="rounded-2xl border border-gray-100 p-5"><div class="flex items-center justify-between"><strong>${a.label || 'Shipping address'}</strong>${a.is_default?'<span class="text-xs font-semibold text-emerald-700">Default</span>':''}</div><p class="mt-3 text-sm leading-6 text-gray-600">${a.recipient_name}<br>${a.phone}<br>${a.region}, ${a.district}${a.ward?', '+a.ward:''}<br>${a.street}</p></div>`).join('') || '<p class="text-sm text-gray-500">No addresses saved.</p>'; } catch(e) { toast(e.message); }
  };
  const bootLogout = () => document.querySelectorAll('[data-logout]').forEach(b => b.addEventListener('click', async () => { try { await api('/auth/logout',{method:'POST'}); } catch {} localStorage.removeItem(TOKEN_KEY); localStorage.removeItem(USER_KEY); location.href='/'; }));

  bindGlobal();
  bootHome(); bootCatalog(); bootProduct(); bootCart(); bootAuth(); bootWishlist(); bootCheckout(); bootAccount(); bootOrders(); bootOrderDetail(); bootAddresses(); bootLogout();
})();
