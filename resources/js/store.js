(() => {
  const API = '/api/v1';
  const TOKEN_KEY = 'kp_api_token';
  const USER_KEY = 'kp_user';
  const GUEST_KEY = 'kp_guest_cart_token';

  const getJson = (key, fallback = null) => {
    try {
      return JSON.parse(
        localStorage.getItem(key) ?? JSON.stringify(fallback)
      );
    } catch {
      return fallback;
    }
  };

  const setJson = (key, value) =>
    localStorage.setItem(key, JSON.stringify(value));

  const toast = (message) => {
    const node = document.querySelector('#toast');
    if (!node) return;

    node.textContent = message;
    node.classList.remove('hidden');

    clearTimeout(window.kpToast);
    window.kpToast = setTimeout(
      () => node.classList.add('hidden'),
      2400
    );
  };

  window.addEventListener('kp:toast', event => {
    if (event.detail) toast(event.detail);
  });

  const escapeHtml = (value) => String(value ?? '')
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('\"', '&quot;')
    .replaceAll("'", '&#039;');

  const token = () => localStorage.getItem(TOKEN_KEY);
  const guestToken = () => localStorage.getItem(GUEST_KEY);

  const ensureGuestToken = () => {
    let value = guestToken();

    if (!value) {
      const bytes = new Uint8Array(32);
      crypto.getRandomValues(bytes);

      value = [...bytes]
        .map(b => b.toString(16).padStart(2, '0'))
        .join('');

      localStorage.setItem(GUEST_KEY, value);
    }

    return value;
  };

  const api = async (path, options = {}) => {
    const headers = new Headers(options.headers || {});

    headers.set('Accept', 'application/json');

    if (
      options.body &&
      !(options.body instanceof FormData)
    ) {
      headers.set('Content-Type', 'application/json');
    }

    if (token()) {
      headers.set(
        'Authorization',
        `Bearer ${token()}`
      );
    }

    if (!token()) {
      headers.set(
        'X-Guest-Cart-Token',
        ensureGuestToken()
      );
    }

    const response = await fetch(`${API}${path}`, {
      ...options,
      headers,
      body:
        options.body &&
        !(options.body instanceof FormData)
          ? JSON.stringify(options.body)
          : options.body
    });

    const text = await response.text();

    let data = null;

    try {
      data = text ? JSON.parse(text) : null;
    } catch {
      /* non-json response */
    }

    if (response.status === 401) {
      localStorage.removeItem(TOKEN_KEY);
      localStorage.removeItem(USER_KEY);

      if (
        !location.pathname.includes('/login') &&
        !location.pathname.includes('/register')
      ) {
        toast('Please sign in to continue.');
      }
    }

    if (!response.ok) {
      const message =
        data?.message ||
        Object.values(data?.errors || {})
          ?.flat?.()?.[0] ||
        `Request failed (${response.status})`;

      throw new Error(message);
    }

    return data;
  };

  const setAuth = (payload) => {
    if (payload?.token) {
      localStorage.setItem(
        TOKEN_KEY,
        payload.token
      );
    }

    if (payload?.user) {
      setJson(USER_KEY, payload.user);
    }
  };

  const currentUser = () =>
    getJson(USER_KEY, null);

  const counts = async () => {
    try {
      const data = await api('/cart');

      document
        .querySelectorAll('[data-cart-count]')
        .forEach(n => {
          n.textContent =
            data?.data?.item_count ?? 0;

          n.classList.toggle(
            'hidden',
            !(data?.data?.item_count > 0)
          );
        });
    } catch {}

    if (token()) {
      try {
        const wishlist =
          await api('/wishlist');

        const wishCount =
          (wishlist?.data || []).length;

        document
          .querySelectorAll('[data-wishlist-count]')
          .forEach(n => {
            n.textContent = wishCount;

            n.classList.toggle(
              'hidden',
              wishCount === 0
            );
          });

        const savedIds = new Set(
          (wishlist?.data || []).map(
            item =>
              String(
                item.product?.id ??
                item.wear_product_id
              )
          )
        );

        document
          .querySelectorAll(
            '[data-wishlist-product]'
          )
          .forEach(button => {
            if (
              savedIds.has(
                String(
                  button.dataset
                    .wishlistProduct
                )
              )
            ) {
              button.dataset.saved = '1';

              button.innerHTML =
                icon('heart');

              button.classList.add(
                'bg-rose-500',
                'text-white'
              );

              button.classList.remove(
                'bg-white',
                'text-gray-800'
              );
            }
          });
      } catch {}
    } else {
      document
        .querySelectorAll(
          '[data-wishlist-count]'
        )
        .forEach(n => {
          n.classList.add('hidden');
        });
    }
  };

  const icon = (name, size = 18) => {
    const paths = {
      heart:
        '<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"></path>',

      bag:
        '<path d="M6 8h12l1 13H5L6 8Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>',

      x:
        '<path d="M6 6l12 12M18 6 6 18"></path>',

      clock:
        '<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>',

      star:
        '<path d="m12 3 2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.92 1.06-6.2L3 9.53l6.22-.9L12 3Z"></path>'
    };

    return `
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="${size}"
        height="${size}"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
      >
        ${paths[name] || ''}
      </svg>
    `;
  };

  const colorHex = {
    black: '#1a1a1a',
    white: '#f5f5f5',
    navy: '#1e3a5f',
    olive: '#5b6f4a',
    burgundy: '#6b2c2c',
    camel: '#c4a882',
    gray: '#8a8a8a',
    sage: '#9caf88',
    sand: '#d7c4a3'
  };

  const slugify = value => String(value ?? '')
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-+|-+$/g, '');

  const productStock = product =>
    (product.variants || []).reduce(
      (sum, v) =>
        sum + Number(v.stock || 0),
      0
    );

  const productIsNew = product =>
    String(product.badge || '')
      .toLowerCase() === 'new';

  const productOnSale = product =>
    product.compare_at_price !== null &&
    Number(product.compare_at_price) >
      Number(product.price);

  const resolveProductImage = product => {
    const image = product?.image;

    if (!image) {
      return '/assets/wear/catalog/generated/product-01.jpg';
    }

    try {
      const url = new URL(image, window.location.origin);
      return `${url.pathname}${url.search}`;
    } catch {
      return image;
    }
  };

  const renderProductSkeletons = (container, count = 8) => {
    if (!container) return;

    container.innerHTML = Array.from({ length: count }, () => `
      <article class="min-w-0 animate-pulse">
        <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-gray-200"></div>
        <div class="mb-2 h-3 w-20 rounded bg-gray-200"></div>
        <div class="h-4 w-3/4 rounded bg-gray-200"></div>
        <div class="mt-2 h-4 w-24 rounded bg-gray-200"></div>
      </article>
    `).join('');
  };

  const renderProductGrid = (
    container,
    products
  ) => {
    if (!container) return;

    container.innerHTML =
      products
        .map(p => {
          const compare =
            p.compare_at_price !== null
              ? Number(p.compare_at_price)
              : null;

          const price =
            Number(p.price || 0);

          const onSale =
            compare !== null &&
            compare > price;

          const discount = onSale
            ? Math.round(
                ((compare - price) /
                  compare) *
                  100
              )
            : 0;

          const stock =
            productStock(p);

          const colors = [
            ...new Set(
              (p.variants || [])
                .map(v => v.color)
                .filter(Boolean)
            )
          ];

          const image =
            resolveProductImage(p);

          const primaryVariant =
            findFirstVariant(p);

          const rating =
            Number(p.rating || 0);

          const reviewCount =
            Number(
              p.review_count ||
              p.reviewCount ||
              0
            );

          return `
            <article
              class="kp-product-card group relative min-w-0"
              data-product-card
              data-product-id="${escapeHtml(p.id)}"
            >
              <div
                class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-gray-100"
              >
                <a
                  href="/product/${encodeURIComponent(p.slug)}"
                  class="block h-full w-full"
                >
                  <img
                    src="${escapeHtml(image)}"
                    alt="${escapeHtml(p.name || 'Product')}"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='/assets/wear/catalog/generated/product-01.jpg'"
                    class="h-full w-full object-contain p-4 transition duration-500 ease-out group-hover:scale-105"
                  >
                </a>

                <div
                  class="pointer-events-none absolute left-3 top-3 flex flex-col gap-1.5"
                >
                  ${
                    onSale
                      ? `
                        <span class="w-fit rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          -${discount}%
                        </span>
                      `
                      : ''
                  }

                  ${
                    productIsNew(p)
                      ? `
                        <span class="w-fit rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          New
                        </span>
                      `
                      : !onSale && p.badge
                      ? `
                        <span class="w-fit rounded-full bg-gray-950 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          ${escapeHtml(p.badge)}
                        </span>
                      `
                      : ''
                  }
                </div>

                ${
                  p.availability ===
                  'out_of_stock'
                    ? `
                      <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-gray-900 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                        Sold out
                      </span>
                    `
                    : stock > 0 && stock < 10
                    ? `
                      <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                        Low stock
                      </span>
                    `
                    : ''
                }

                <button
                  type="button"
                  data-wishlist-product="${escapeHtml(p.id)}"
                  data-saved="0"
                  class="absolute bottom-3 left-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-rose-500 hover:text-white"
                  aria-label="Toggle wishlist for ${escapeHtml(p.name || 'product')}"
                >
                  ${icon('heart')}
                </button>

                <button
                  type="button"
                  data-quick-add="${escapeHtml(p.id)}"
                  data-quick-variant="${escapeHtml(primaryVariant?.id || '')}"
                  ${
                    p.availability ===
                    'out_of_stock'
                      ? 'disabled'
                      : ''
                  }
                  class="absolute bottom-3 right-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-gray-800 opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-emerald-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                  aria-label="Add ${escapeHtml(p.name || 'product')} to bag"
                >
                  ${icon('bag')}
                </button>
              </div>

              <div>
                <p class="mb-1 text-xs text-gray-400">
                  ${escapeHtml(p.category || '')}
                </p>

                ${
                  rating > 0
                    ? `
                      <div class="mb-1 flex items-center gap-1 text-xs text-gray-500">
                        <span class="inline-flex text-amber-400">
                          ${icon('star', 13)}
                        </span>

                        <span>
                          ${rating.toFixed(1)}
                          ${
                            reviewCount
                              ? ` (${reviewCount})`
                              : ''
                          }
                        </span>
                      </div>
                    `
                    : ''
                }

                <a
                  href="/product/${encodeURIComponent(p.slug)}"
                  class="line-clamp-1 text-sm font-medium text-gray-950 transition-colors hover:text-emerald-700"
                >
                  ${escapeHtml(p.name || '')}
                </a>

                <div class="mt-1 flex items-center gap-2">
                  <span class="text-sm font-semibold text-gray-950">
                    ${price.toLocaleString()} TZS
                  </span>

                  ${
                    onSale
                      ? `
                        <span class="text-xs text-gray-400 line-through">
                          ${compare.toLocaleString()} TZS
                        </span>
                      `
                      : ''
                  }
                </div>

                ${
                  colors.length
                    ? `
                      <div class="mt-2.5 flex items-center gap-1.5">
                        ${colors
                          .slice(0, 5)
                          .map(
                            c => `
                              <span
                                class="h-3.5 w-3.5 rounded-full border border-gray-200 shadow-sm"
                                style="background-color:${
                                  colorHex[
                                    String(
                                      c
                                    ).toLowerCase()
                                  ] ||
                                  '#d1d5db'
                                }"
                                title="${escapeHtml(c)}"
                              ></span>
                            `
                          )
                          .join('')}

                        ${
                          colors.length > 5
                            ? `
                              <span class="text-[11px] text-gray-400">
                                +${colors.length - 5}
                              </span>
                            `
                            : ''
                        }
                      </div>
                    `
                    : ''
                }
              </div>
            </article>
          `;
        })
        .join('');

    document.dispatchEvent(
      new Event('kp:grid-rendered')
    );
  };

  const fetchProducts = async (
    params = ''
  ) =>
    (
      await api(
        `/wear/products${
          params ? `?${params}` : ''
        }`
      )
    )?.data || [];

  const fetchCategories = async () =>
    (await api('/wear/categories'))?.data || [];

  const fetchCollections = async () =>
    (await api('/wear/collections'))?.data || [];

  const fetchCollection = async slug =>
    (await api(`/wear/collections/${encodeURIComponent(slug)}`))?.data || null;

  const findFirstVariant = product =>
    (product.variants || []).find(
      v => v.in_stock
    ) ||
    product.variants?.[0] ||
    null;

  const addVariantToCart = async (
    variantId,
    quantity = 1
  ) => {
    const numericQuantity = Number(quantity);

    if (!Number.isInteger(numericQuantity) || numericQuantity < 1 || numericQuantity > 50) {
      throw new Error('Quantity must be between 1 and 50.');
    }

    await api('/cart/items', {
      method: 'POST',
      body: {
        variant_id: Number(variantId),
        quantity: numericQuantity
      }
    });

    toast('Added to your bag');

    await counts();
  };

  const toggleWishlist = async (
    productId,
    button = null
  ) => {
    if (!token()) {
      location.href = '/login';
      return;
    }

    const state =
      button?.dataset?.saved === '1';

    if (state) {
      await api(
        `/wishlist/${productId}`,
        {
          method: 'DELETE'
        }
      );

      if (button) {
        button.dataset.saved = '0';
        button.innerHTML = icon('heart');

        button.classList.remove(
          'bg-rose-500',
          'text-white'
        );

        button.classList.add(
          'bg-white',
          'text-gray-800'
        );
      }

      toast('Removed from wishlist');
    } else {
      await api('/wishlist', {
        method: 'POST',
        body: {
          product_id: Number(productId)
        }
      });

      if (button) {
        button.dataset.saved = '1';
        button.innerHTML = icon('heart');

        button.classList.add(
          'bg-rose-500',
          'text-white'
        );

        button.classList.remove(
          'bg-white',
          'text-gray-800'
        );
      }

      toast('Saved to wishlist');
    }

    await counts();
  };

const bootHome = async () => {
  const grid =
    document.querySelector(
      '[data-home-featured]'
    );

  if (!grid) return;

  // Show loading skeletons immediately
  renderProductSkeletons(grid, 8);

  try {
    const [
      products,
      categories
    ] = await Promise.all([
      fetchProducts('per_page=12&featured=1'),
      fetchCategories()
    ]);

    // Replace skeletons with real products
    renderProductGrid(
      grid,
      products.slice(0, 8)
    );

    const catGrid =
      document.querySelector(
        '[data-home-categories]'
      );

      const categorySource = categories.map(c => ({
        name: c.name,
        slug: c.slug || slugify(c.name)
      }));

      const visibleCategories = categorySource.filter(category => [
        'hoodies',
        'long-sleeves',
        't-shirts',
        'shirts',
        'polos'
      ].includes(category.slug));

        const categoryColors = {
  't-shirts': {
    background: 'rgb(255, 255, 255)',
    text: 'rgb(15, 23, 42)',
    subtext: 'rgb(107, 114, 128)'
  },
  'shirts': {
    background: 'rgba(59, 130, 246, 0.5)',
    text: 'rgb(15, 23, 42)',
    subtext: 'rgb(71, 85, 105)'
  },
  'polos': {
    background: 'rgb(4, 120, 87)',
    text: 'rgb(255, 255, 255)',
    subtext: 'rgba(255, 255, 255, 0.8)'
  },
  'long-sleeves': {
    background: 'rgb(249, 115, 22)',
    text: 'rgb(255, 255, 255)',
    subtext: 'rgba(255, 255, 255, 0.8)'
  }
};

catGrid.innerHTML = visibleCategories
  .map(category => {
    const colors = categoryColors[category.slug] || {
      background: 'rgb(245, 245, 245)',
      text: 'rgb(15, 23, 42)',
      subtext: 'rgb(107, 114, 128)'
    };

    return `
      <a
        href="/category/${encodeURIComponent(category.slug)}"
        class="group rounded-xl border border-gray-100 p-3.5"
        style="background-color: ${colors.background};"
      >
        <p
          class="text-xs font-semibold sm:text-sm"
          style="color: ${colors.text};"
        >
          ${escapeHtml(category.name)}
        </p>

        <p
          class="mt-0.5 text-[11px] sm:text-xs"
          style="color: ${colors.subtext};"
        >
          Shop collection
        </p>
      </a>
    `;
  })
  .join('');
  } catch (e) {
    grid.innerHTML = '';
    toast(e.message);
  }
};

  const bootCollections = async () => {
  const page = document.querySelector('[data-collections-page]');
  const grid = document.querySelector('[data-collections-grid]');
  if (!page || !grid) return;

  const fallbackImages = {
    'new-arrivals': 'product-15.jpg',
    'everyday-essentials': 'product-10.jpg',
    'streetwear': 'product-07.jpg',
    'polos-and-shirts': 'product-13.jpg',
    't-shirts': 'product-03.jpg',
    'hoodies-and-sweatshirts': 'product-11.jpg',
    'premium-edit': 'product-16.jpg'
  };

  try {
    const collections = await fetchCollections();
    grid.innerHTML = collections.map((collection, index) => {
      const image = collection.cover || `/assets/wear/catalog/generated/${fallbackImages[collection.slug] || 'product-01.jpg'}`;
      const wide = index === 0 || index === 2;
      return `
        <a href="/collections/${encodeURIComponent(collection.slug)}" class="group relative overflow-hidden rounded-[1.5rem] bg-gray-100 ${wide ? 'lg:col-span-8' : 'lg:col-span-4'} aspect-[4/3]">
          <img src="${escapeHtml(image)}" alt="${escapeHtml(collection.name)}" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]">
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8">
            <div class="flex items-end justify-between gap-5">
              <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">${Number(collection.product_count || 0)} products</p>
                <h3 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">${escapeHtml(collection.name)}</h3>
                <p class="mt-2 max-w-md text-sm leading-6 text-white/70">${escapeHtml(collection.description || '')}</p>
              </div>
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-lg text-gray-950 transition group-hover:-rotate-45">↗</span>
            </div>
          </div>
        </a>`;
    }).join('');

    if (!collections.length) {
      grid.innerHTML = '<div class="col-span-full rounded-2xl border border-dashed border-gray-200 py-20 text-center text-sm text-gray-500">No collections are available yet.</div>';
    }
  } catch (error) {
    grid.innerHTML = '<div class="col-span-full rounded-2xl border border-red-100 bg-red-50 py-16 text-center text-sm text-red-700">Unable to load collections right now.</div>';
  }
};

const bootCatalog = async () => {
    const page =
      document.querySelector(
        '[data-catalog-grid]'
      );

    if (!page) return;

    const count =
      document.querySelector(
        '[data-catalog-count]'
      );

    // Keep the heading clean while the API is loading.
    if (count) count.textContent = '';

    const searchInput =
      document.querySelector(
        '[data-catalog-search]'
      );

    const sortSelect =
      document.querySelector(
        '[data-catalog-sort]'
      );

    const categoryHost =
      document.querySelector(
        '[data-catalog-categories]'
      );

    const priceHost =
      document.querySelector(
        '[data-catalog-prices]'
      );

    const saleToggle =
      document.querySelector(
        '[data-catalog-sale-toggle]'
      );

    const empty =
      document.querySelector(
        '[data-catalog-empty]'
      );

    const emptyClear =
      document.querySelector(
        '[data-catalog-empty-clear]'
      );

    const clearButton =
      document.querySelector(
        '[data-catalog-clear]'
      );

    const filterCount =
      document.querySelector(
        '[data-catalog-filter-count]'
      );

    const mobilePanel =
      document.querySelector(
        '[data-catalog-mobile-panel]'
      );

    const mobileContent =
      document.querySelector(
        '[data-catalog-mobile-content]'
      );

    const collectionSlug = location.pathname.startsWith('/collections/')
      ? decodeURIComponent(location.pathname.split('/').filter(Boolean)[1] || '')
      : '';

    const state = {
      products: [],
      collection: collectionSlug,
      category:
        new URLSearchParams(
          location.search
        ).get('category') || '',
      search:
        new URLSearchParams(
          location.search
        ).get('q') || '',
      price: 'all',
      saleOnly: false,
      sort: 'featured'
    };

    const priceMatch = product => {
      const value =
        Number(product.price || 0);

      if (
        state.price ===
        'under-50000'
      ) {
        return value < 50000;
      }

      if (
        state.price ===
        '50000-150000'
      ) {
        return (
          value >= 50000 &&
          value <= 150000
        );
      }

      if (
        state.price ===
        'over-150000'
      ) {
        return value > 150000;
      }

      return true;
    };

    const renderFilters =
      categories => {
        const markup = `
          <div class="space-y-8">
            <div>
              <div class="mb-4 flex items-center justify-between">
                <h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">
                  Category
                </h3>

                <button
                  type="button"
                  data-catalog-clear-mobile
                  class="text-xs font-medium text-emerald-600"
                >
                  Clear all
                </button>
              </div>

              <div class="space-y-1">
                <button
                  type="button"
                  data-category=""
                  class="w-full rounded-lg px-3 py-2 text-left text-sm"
                >
                  All Products
                </button>

                ${categories
                  .map(
                    c => `
                      <button
                        type="button"
                        data-category="${c.slug || slugify(c.name)}"
                        class="w-full rounded-lg px-3 py-2 text-left text-sm"
                      >
                        ${c.name}
                      </button>
                    `
                  )
                  .join('')}
              </div>
            </div>

            <div>
              <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">
                Price Range
              </h3>

              <div class="space-y-1">
                <button
                  type="button"
                  data-price="all"
                  class="w-full rounded-lg px-3 py-2 text-left text-sm"
                >
                  All Prices
                </button>

                <button
                  type="button"
                  data-price="under-50000"
                  class="w-full rounded-lg px-3 py-2 text-left text-sm"
                >
                  Under 50,000 TZS
                </button>

                <button
                  type="button"
                  data-price="50000-150000"
                  class="w-full rounded-lg px-3 py-2 text-left text-sm"
                >
                  50,000 – 150,000 TZS
                </button>

                <button
                  type="button"
                  data-price="over-150000"
                  class="w-full rounded-lg px-3 py-2 text-left text-sm"
                >
                  Over 150,000 TZS
                </button>
              </div>
            </div>
          </div>
        `;

        if (mobileContent) {
          mobileContent.innerHTML =
            markup;
        }

        if (categoryHost) {
          categoryHost.innerHTML =
            markup
              .split(
                '<div class="space-y-8">'
              )[1]
              ?.split(
                '<div><h3'
              )[0]
              ?.replace(
                '</div></div><div>',
                ''
              ) || '';
        }
      };

    let debounceTimer;
    let requestController = null;

    const priceParams = () => {
      if (state.price === 'under-50000') return { price_max: 49999 };
      if (state.price === '50000-150000') return { price_min: 50000, price_max: 150000 };
      if (state.price === 'over-150000') return { price_min: 150001 };
      return {};
    };

    const setLoading = (loading) => {
      page.setAttribute('aria-busy', loading ? 'true' : 'false');
      if (loading) renderProductSkeletons(page, 8);
    };

    const loadProducts = async () => {
      if (requestController) requestController.abort();
      requestController = new AbortController();
      setLoading(true);

      const params = new URLSearchParams();
      params.set('per_page', '24');
      params.set('page', String(state.page || 1));
      if (state.category && !state.collection) params.set('category', state.category);
      if (state.search) params.set('q', state.search);
      if (state.saleOnly) params.set('sale', '1');
      if (state.sort) params.set('sort', state.sort);
      Object.entries(priceParams()).forEach(([key, value]) => params.set(key, String(value)));

      try {
        const response = state.collection
          ? await api(`/wear/collections/${encodeURIComponent(state.collection)}`)
          : await api(`/wear/products?${params.toString()}`, { signal: requestController.signal });

        const payload = response?.data || {};
        if (state.collection) {
          const collectionProducts = Array.isArray(payload.products) ? payload.products : [];
          state.products = collectionProducts.filter(product => {
            if (state.search) {
              const q = state.search.toLowerCase();
              const haystack = `${product.name || ''} ${product.description || ''} ${product.category || ''}`.toLowerCase();
              if (!haystack.includes(q)) return false;
            }
            if (state.price === 'under-50000' && Number(product.price || 0) >= 50000) return false;
            if (state.price === '50000-150000' && (Number(product.price || 0) < 50000 || Number(product.price || 0) > 150000)) return false;
            if (state.price === 'over-150000' && Number(product.price || 0) <= 150000) return false;
            if (state.saleOnly && !productOnSale(product)) return false;
            return true;
          });
          if (state.sort === 'price-asc') state.products.sort((a,b) => Number(a.price)-Number(b.price));
          else if (state.sort === 'price-desc') state.products.sort((a,b) => Number(b.price)-Number(a.price));
          else if (state.sort === 'newest') state.products.sort((a,b) => Number(b.id)-Number(a.id));
          else state.products.sort((a,b) => Number(b.is_featured)-Number(a.is_featured));
          const heading = document.querySelector('.kp-shop-page h1');
          const description = document.querySelector('.kp-shop-page [data-catalog-count]');
          if (heading) heading.textContent = payload.name || 'Collection';
          if (description) description.textContent = payload.description || '';
        } else {
          state.products = Array.isArray(payload) ? payload : [];
        }

        renderProductGrid(page, state.products);

        const total = state.collection
          ? state.products.length
          : Number(response?.meta?.total ?? state.products.length);
        if (count) count.textContent = `${total} ${total === 1 ? 'product' : 'products'} found`;
        empty?.classList.toggle('hidden', state.products.length > 0);
        empty?.classList.toggle('flex', state.products.length === 0);

        const pagination = document.querySelector('[data-catalog-pagination]');
        if (pagination) {
          const current = Number(response?.meta?.current_page || 1);
          const last = Number(response?.meta?.last_page || 1);
          pagination.innerHTML = (!state.collection && last > 1) ? `
            <div class="mt-10 flex items-center justify-center gap-3">
              <button type="button" data-catalog-page="${Math.max(1,current-1)}" ${current <= 1 ? 'disabled' : ''} class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Previous</button>
              <span class="text-sm text-gray-500">Page ${current} of ${last}</span>
              <button type="button" data-catalog-page="${Math.min(last,current+1)}" ${current >= last ? 'disabled' : ''} class="rounded-xl border border-gray-200 px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Next</button>
            </div>` : '';
        }
      } catch (e) {
        if (e.name === 'AbortError') return;
        page.innerHTML = '';
        empty?.classList.remove('hidden');
        empty?.classList.add('flex');
        if (count) count.textContent = '';
        toast(e.message || 'Unable to load products.');
      } finally {
        setLoading(false);
      }
    };

    const apply = () => {
      state.page = 1;
      const filters =
        (state.category ? 1 : 0) +
        (state.price !== 'all' ? 1 : 0) +
        (state.saleOnly ? 1 : 0) +
        (state.search ? 1 : 0);

      filterCount?.classList.toggle('hidden', filters === 0);
      filterCount?.classList.toggle('inline-flex', filters > 0);
      if (filterCount) filterCount.textContent = String(filters);
      clearButton?.classList.toggle('hidden', filters === 0);
      saleToggle?.classList.toggle('bg-emerald-600', state.saleOnly);
      saleToggle?.classList.toggle('bg-gray-200', !state.saleOnly);
      saleToggle?.setAttribute('aria-pressed', state.saleOnly ? 'true' : 'false');
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-5', state.saleOnly);
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-0', !state.saleOnly);

      document.querySelectorAll('[data-catalog-categories] [data-category]').forEach(button => {
        const active = slugify(button.dataset.category) === slugify(state.category || '');
        button.classList.toggle('bg-emerald-50', active);
        button.classList.toggle('text-emerald-700', active);
        button.classList.toggle('font-medium', active);
        button.classList.toggle('text-gray-600', !active);
      });

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(loadProducts, state.search ? 250 : 0);
    };

    const categories = [
      'Hoodies','Long Sleeves','T-Shirts','Shirts','Polos'
    ].map(name => ({ name, slug: slugify(name) }));

    if (searchInput) searchInput.value = state.search;
    if (categoryHost) {
      categoryHost.innerHTML = ['<button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>', ...categories.map(c => `<button type="button" data-category="${c.slug}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${c.name}</button>`)].join('');
    }
    if (mobileContent) {
      mobileContent.innerHTML = `<div class="space-y-8"><div><div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold uppercase tracking-wider text-gray-900">Category</h3><button type="button" data-catalog-clear-mobile class="text-xs font-medium text-emerald-600">Clear all</button></div><div data-mobile-categories class="space-y-1"><button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>${categories.map(c=>`<button type="button" data-category="${c.slug}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${c.name}</button>`).join('')}</div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">Price Range</h3><div class="space-y-1"><button type="button" data-price="all" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Prices</button><button type="button" data-price="under-50000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Under 50,000 TZS</button><button type="button" data-price="50000-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">50,000 – 150,000 TZS</button><button type="button" data-price="over-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Over 150,000 TZS</button></div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-900">Special</h3><label class="flex items-center gap-3"><button type="button" data-catalog-sale-toggle-mobile class="relative h-6 w-11 rounded-full bg-gray-200"><span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span></button><span class="text-sm text-gray-700">On Sale Only</span></label></div></div>`;
    }

    const catalogClickHandler = event => {
      const category = event.target.closest('[data-category]');
      const price = event.target.closest('[data-price]');
      const clear = event.target.closest('[data-catalog-clear],[data-catalog-clear-mobile],[data-catalog-empty-clear]');
      const mobileSale = event.target.closest('[data-catalog-sale-toggle-mobile]');
      const pageButton = event.target.closest('[data-catalog-page]');

      if (category) { state.category = category.dataset.category || ''; apply(); mobilePanel?.classList.add('hidden'); }
      if (price) { state.price = price.dataset.price || 'all'; apply(); }
      if (clear) { state.category=''; state.search=''; state.price='all'; state.saleOnly=false; if(searchInput) searchInput.value=''; apply(); }
      if (mobileSale) { state.saleOnly=!state.saleOnly; apply(); }
      if (pageButton && !pageButton.disabled) { state.page = Number(pageButton.dataset.catalogPage); loadProducts(); window.scrollTo({top:0,behavior:'smooth'}); }
    };

    document.addEventListener('click', catalogClickHandler);
    searchInput?.addEventListener('input', () => { state.search=searchInput.value.trim(); apply(); });
    sortSelect?.addEventListener('change', () => { state.sort=sortSelect.value; state.page=1; loadProducts(); });
    saleToggle?.addEventListener('click', () => { state.saleOnly=!state.saleOnly; apply(); });
    document.querySelector('[data-catalog-search-clear]')?.addEventListener('click', () => { state.search=''; if(searchInput) searchInput.value=''; apply(); });
    document.querySelector('[data-catalog-filter-toggle]')?.addEventListener('click', () => mobilePanel?.classList.remove('hidden'));
    document.querySelector('[data-catalog-filter-close]')?.addEventListener('click', () => mobilePanel?.classList.add('hidden'));
    mobilePanel?.addEventListener('click', event => { if(event.target === mobilePanel) mobilePanel.classList.add('hidden'); });

    // Collection pages use the collection endpoint; normal shop pages use the paginated product endpoint.
    await loadProducts();
  };

  const bootProduct = async () => {
    const page = document.querySelector('[data-product-page]');
    if (!page) return;

    const loading = page.querySelector('[data-product-loading]');
    const content = page.querySelector('[data-product-content]');
    const errorBox = page.querySelector('[data-product-error]');
    const slug = page.dataset.slug;

    try {
      const response = await api(`/wear/products/${encodeURIComponent(slug)}`);
      const p = response?.data;
      if (!p) throw new Error('Product not found');

      const variants = p.variants || [];
      let firstAvailable = findFirstVariant(p);
      const totalStock = variants.reduce((sum, v) => sum + Number(v.stock || 0), 0);

      page.querySelector('[data-product-name]').textContent = p.name || '';
      page.querySelector('[data-product-category]').textContent = p.category || '';
      page.querySelector('[data-product-description]').textContent = p.description || 'No description available.';
      page.querySelector('[data-product-price]').textContent = `${Number(p.price || 0).toLocaleString()} TZS`;

      const compare = page.querySelector('[data-product-compare]');
      if (p.compare_at_price !== null && Number(p.compare_at_price) > Number(p.price)) {
        compare.textContent = `${Number(p.compare_at_price).toLocaleString()} TZS`;
        compare.classList.remove('hidden');
      }

      const badge = page.querySelector('[data-product-badge]');
      if (p.badge) {
        badge.textContent = p.badge;
        badge.classList.remove('hidden');
      }

      const stock = page.querySelector('[data-product-stock]');
      stock.textContent = totalStock > 0 ? `${totalStock} in stock` : 'Out of stock';
      stock.classList.toggle('text-red-600', totalStock === 0);
      stock.classList.toggle('text-gray-700', totalStock > 0);

      const main = page.querySelector('[data-product-image]');
      main.src = p.image || '';
      main.alt = p.name || 'Product';

      const gallery = page.querySelector('[data-product-gallery]');
      if (gallery && p.image) {
        gallery.innerHTML = `<button type="button" data-gallery-image="${escapeHtml(p.image)}" class="overflow-hidden rounded-xl border-2 border-gray-950 bg-gray-50"><img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name || 'Product')}" class="aspect-square w-full object-contain p-2"></button>`;
        gallery.addEventListener('click', e => {
          const button = e.target.closest('[data-gallery-image]');
          if (button) main.src = button.dataset.galleryImage;
        });
      }

      const variantsBox = page.querySelector('[data-product-variants]');
      const selectedLabel = page.querySelector('[data-selected-variant]');
      page.dataset.selectedVariant = firstAvailable?.id || '';

      if (variantsBox) {
        variantsBox.innerHTML = variants.map(v => {
          const label = [v.size, v.color].filter(Boolean).join(' · ') || `Option ${v.id}`;
          return `<button type="button" data-variant="${escapeHtml(v.id)}" data-stock="${escapeHtml(v.stock)}" data-label="${escapeHtml(label)}" class="rounded-xl border px-4 py-2.5 text-sm ${String(v.id) === String(firstAvailable?.id || '') ? 'border-gray-950 bg-gray-950 text-white' : 'border-gray-200'}" ${!v.in_stock ? 'disabled aria-disabled="true"' : ''}>${escapeHtml(label)}${!v.in_stock ? ' · Sold out' : ''}</button>`;
        }).join('') || '<span class="text-sm text-gray-500">No variants available.</span>';
        selectedLabel.textContent = firstAvailable ? [firstAvailable.size, firstAvailable.color].filter(Boolean).join(' · ') : '';
        page.querySelector('[data-quantity]')?.setAttribute('max', String(Math.min(50, Math.max(1, Number(firstAvailable?.stock || 1)))));
      }

      // Pre-select the customer's saved size when it matches an available variant.
      if (token() && variants.length) {
        try {
          const preferenceResponse = await api('/account/preferences');
          const saved = preferenceResponse?.data?.size_profile || {};
          const categoryText = String(p.category || '').toLowerCase();
          const preferredSize = categoryText.includes('shoe') || categoryText.includes('footwear') ? saved.shoe : (categoryText.includes('bottom') || categoryText.includes('trouser') || categoryText.includes('pant') ? saved.bottom : saved.top);
          const preferred = variants.find(v => v.in_stock && String(v.size || '') === String(preferredSize || ''));
          if (preferred) {
            firstAvailable = preferred;
            page.dataset.selectedVariant = preferred.id;
            selectedLabel.textContent = [preferred.size, preferred.color].filter(Boolean).join(' · ');
            variantsBox?.querySelectorAll('[data-variant]').forEach(button => {
              const active = String(button.dataset.variant) === String(preferred.id);
              button.classList.toggle('border-gray-950', active);
              button.classList.toggle('bg-gray-950', active);
              button.classList.toggle('text-white', active);
            });
          }
        } catch {}
      }

      const setVariant = btn => {
        page.dataset.selectedVariant = btn.dataset.variant;
        selectedLabel.textContent = btn.dataset.label || '';
        page.querySelectorAll('[data-variant]').forEach(x => x.classList.remove('border-gray-950', 'bg-gray-950', 'text-white'));
        btn.classList.add('border-gray-950', 'bg-gray-950', 'text-white');
        const quantityInput = page.querySelector('[data-quantity]');
        const maxStock = Math.min(50, Math.max(1, Number(btn.dataset.stock || 1)));
        quantityInput.max = String(maxStock);
        quantityInput.value = 1;
      };

      variantsBox?.addEventListener('click', e => {
        const btn = e.target.closest('[data-variant]');
        if (btn && !btn.disabled) setVariant(btn);
      });

      const quantity = page.querySelector('[data-quantity]');
      page.querySelector('[data-quantity-minus]')?.addEventListener('click', () => {
        quantity.value = Math.max(1, Number(quantity.value || 1) - 1);
      });
      page.querySelector('[data-quantity-plus]')?.addEventListener('click', () => {
        quantity.value = Math.min(Number(quantity.max || 50), Number(quantity.value || 1) + 1);
      });
      quantity?.addEventListener('input', () => {
        const max = Number(quantity.max || 50);
        const value = Math.max(1, Math.min(max, Number(quantity.value || 1)));
        quantity.value = value;
      });

      page.querySelector('[data-product-wishlist]')?.addEventListener('click', async event => {
        try {
          await toggleWishlist(p.id, event.currentTarget);
          event.currentTarget.textContent = '♥ Saved';
          event.currentTarget.dataset.saved = '1';
        } catch (e) {
          toast(e.message);
        }
      });

      page.querySelector('[data-add-selected]')?.addEventListener('click', async () => {
        if (!page.dataset.selectedVariant) return toast(totalStock ? 'Select an available size' : 'This product is out of stock');
        try {
          await addVariantToCart(page.dataset.selectedVariant, Number(quantity.value || 1));
        } catch (e) {
          toast(e.message);
        }
      });

      page.querySelector('[data-add-selected]').disabled = totalStock === 0;
      loading?.classList.add('hidden');
      content?.classList.remove('hidden');
    } catch (e) {
      loading?.classList.add('hidden');
      content?.classList.add('hidden');
      if (errorBox) {
        errorBox.textContent = e.message || 'Unable to load this product.';
        errorBox.classList.remove('hidden');
      }
    }
  };

  const bootCart = async () => {
    const page = document.querySelector('[data-cart-page]');
    if (!page) return;

    const render = async () => {
      const data = await api('/cart');
      const cart = data?.data || { items: [], subtotal: 0, item_count: 0 };
      const items = Array.isArray(cart.items) ? cart.items : [];
      const rows = page.querySelector('[data-cart-items]');
      const errorBox = page.querySelector('[data-cart-error]');

      if (!rows) return;
      errorBox?.classList.add('hidden');

      rows.innerHTML = items.map(item => {
        const image = item.product?.image || '';
        const variantLabel = [item.variant?.size, item.variant?.color].filter(Boolean).join(' · ');
        const quantity = Number(item.quantity || 0);
        const unitPrice = Number(item.unit_price || 0);
        const lineTotal = Number(item.line_total || unitPrice * quantity);

        return `
          <article class="flex gap-4 py-5 sm:gap-6">
            <a href="/product/${encodeURIComponent(item.product?.slug || '')}" class="h-28 w-24 shrink-0 overflow-hidden rounded-xl bg-gray-50 sm:h-32 sm:w-28">
              <img src="${escapeHtml(image)}" alt="${escapeHtml(item.product?.name || 'Product')}" class="h-full w-full object-contain p-2" loading="lazy" onerror="this.onerror=null;this.src='/assets/wear/catalog/generated/product-01.jpg'">
            </a>

            <div class="min-w-0 flex-1">
              <div class="flex flex-col gap-1 sm:flex-row sm:items-start sm:justify-between sm:gap-4">
                <div class="min-w-0">
                  <a href="/product/${encodeURIComponent(item.product?.slug || '')}" class="font-semibold hover:underline underline-offset-4">
                    ${escapeHtml(item.product?.name || 'Product')}
                  </a>
                  ${variantLabel ? `<p class="mt-1 text-sm text-gray-500">${escapeHtml(variantLabel)}</p>` : ''}
                </div>
                <p class="font-semibold whitespace-nowrap">${lineTotal.toLocaleString()} TZS</p>
              </div>

              <p class="mt-1 text-xs text-gray-500">${unitPrice.toLocaleString()} TZS each</p>

              <div class="mt-4 flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center rounded-full border border-gray-300 bg-white">
                  <button type="button" data-cart-minus="${item.variant_id}" class="flex h-9 w-9 items-center justify-center rounded-full text-lg hover:bg-gray-50" aria-label="Decrease quantity">−</button>
                  <span class="min-w-8 text-center text-sm font-medium">${quantity}</span>
                  <button type="button" data-cart-plus="${item.variant_id}" class="flex h-9 w-9 items-center justify-center rounded-full text-lg hover:bg-gray-50" aria-label="Increase quantity">+</button>
                </div>
                <button type="button" data-cart-remove="${item.variant_id}" class="text-sm text-gray-500 underline underline-offset-4 hover:text-black">Remove</button>
              </div>
            </div>
          </article>
        `;
      }).join('');

      page.querySelector('[data-cart-empty]')?.classList.toggle('hidden', items.length > 0);
      page.querySelector('[data-cart-content]')?.classList.toggle('hidden', items.length === 0);
      page.querySelector('[data-cart-mobile-actions]')?.classList.toggle('hidden', items.length > 0);
      page.querySelector('[data-cart-summary]').textContent = `${Number(cart.subtotal || 0).toLocaleString()} TZS`;
      const itemCount = Number(cart.item_count || items.reduce((sum, item) => sum + Number(item.quantity || 0), 0));
      page.querySelector('[data-cart-item-count]').textContent = itemCount;
      const headerCount = page.querySelector('[data-cart-header-count]');
      if (headerCount) headerCount.textContent = itemCount;

      await counts();
    };

    try {
      await render();
    } catch (e) {
      const errorBox = page.querySelector('[data-cart-error]');
      if (errorBox) {
        errorBox.textContent = e.message || 'Unable to load your bag.';
        errorBox.classList.remove('hidden');
      } else {
        toast(e.message);
      }
    }

    page.addEventListener('click', async e => {
      const button = e.target.closest('[data-cart-plus],[data-cart-minus],[data-cart-remove],[data-cart-clear]');
      if (!button) return;

      try {
        if (button.matches('[data-cart-clear]')) {
          if (!window.confirm('Remove all items from your bag?')) return;
          await api('/cart', { method: 'DELETE' });
          await render();
          return;
        }

        const variant = button.dataset.cartPlus || button.dataset.cartMinus || button.dataset.cartRemove;
        const cart = (await api('/cart')).data;
        const item = (cart.items || []).find(i => String(i.variant_id) === String(variant));
        if (!item) return;

        if (button.dataset.cartRemove || (button.dataset.cartMinus && Number(item.quantity) <= 1)) {
          await api(`/cart/items/${variant}`, { method: 'DELETE' });
        } else {
          await api(`/cart/items/${variant}`, {
            method: 'PUT',
            body: {
              variant_id: Number(variant),
              quantity: Math.max(1, Number(item.quantity) + (button.dataset.cartPlus ? 1 : -1)),
            },
          });
        }

        await render();
      } catch (e) {
        toast(e.message);
      }
    });
  };

  const bootAuth = () => {
    const login =
      document.querySelector(
        '[data-login-form]'
      );

    const register =
      document.querySelector(
        '[data-register-form]'
      );

    const form =
      login || register;

    if (!form) return;

    // Auth is a two-step flow. Native browser validation must not block the
    // submit handler while the OTP step is still hidden. Validation is handled
    // by the API/FormRequest on each step instead.
    form.noValidate = true;

    form.addEventListener(
      'submit',
      async e => {
        e.preventDefault();

        const fd =
          new FormData(form);

        const phone =
          fd.get('phone');

        try {
          const first =
            form.dataset.step !==
            'otp';

          const requestPath =
            login
              ? '/auth/login/request-otp'
              : '/auth/register/request-otp';

          if (first) {
            const otpResponse = await api(
              requestPath,
              {
                method: 'POST',
                body: { phone }
              }
            );

            form.dataset.step =
              'otp';

            const otpStep = form.querySelector('[data-otp-step]');
            const otpInput = otpStep?.querySelector('input[name="code"]');

            otpStep?.classList.remove('hidden');
            if (otpInput) otpInput.required = true;

            form
              .querySelector('[data-primary-step]')
              ?.classList.add('hidden');

            const primaryInputs = form.querySelectorAll('[data-primary-step] input, [data-primary-step] select, [data-primary-step] textarea');
            primaryInputs.forEach(input => { input.required = false; });

            toast(
              otpResponse?.dev_otp
                ? `Development OTP: ${otpResponse.dev_otp}`
                : 'Verification code requested'
            );

            return;
          }

          const code =
            fd.get('code');

          const path =
            login
              ? '/auth/login'
              : '/auth/register';

          const body = login
            ? {
                phone,
                code
              }
            : {
                phone,
                code,
                name:
                  fd.get('name'),
                referral_code:
                  fd.get(
                    'referral_code'
                  ) || null
              };

          const payload =
            await api(path, {
              method: 'POST',
              body
            });

          setAuth(payload);

          const guest =
            guestToken();

          if (guest) {
            try {
              await api(
                '/cart/merge',
                {
                  method: 'POST',
                  body: {
                    guest_cart_token:
                      guest
                  }
                }
              );
            } catch {}
          }

          location.href =
            '/account';
        } catch (err) {
          toast(err.message);
        }
      }
    );
  };

  const bootWishlist =
    async () => {
      const page =
        document.querySelector(
          '[data-wishlist-page]'
        );

      if (!page) return;

      const loading =
        page.querySelector('[data-wishlist-loading]');
      const skeleton =
        page.querySelector('[data-wishlist-skeleton]');
      const grid =
        page.querySelector('[data-wishlist-grid]');
      const empty =
        page.querySelector('[data-wishlist-empty]');
      const auth =
        page.querySelector('[data-wishlist-auth]');
      const count =
        page.querySelector('[data-wishlist-page-count]');

      if (!token()) {
        loading?.classList.add('hidden');
        auth?.classList.remove('hidden');
        grid?.classList.add('hidden');
        empty?.classList.add('hidden');
        return;
      }

      renderProductSkeletons(skeleton, 8);

      try {
        const data =
          await api('/wishlist');

        const products =
          (data?.data || [])
            .map(i => i.product)
            .filter(Boolean);

        const total = products.length;
        if (count) {
          count.textContent = total;
          count.classList.toggle('hidden', total === 0);
        }

        loading?.classList.add('hidden');
        auth?.classList.add('hidden');

        if (!total) {
          grid?.classList.add('hidden');
          empty?.classList.remove('hidden');
          await counts();
          return;
        }

        empty?.classList.add('hidden');
        grid?.classList.remove('hidden');
        renderProductGrid(grid, products);

        await counts();
      } catch (e) {
        loading?.classList.add('hidden');
        toast(e.message);
      }
    };

  const bindGlobal = () => {
    document
      .querySelectorAll(
        '[data-search-toggle]'
      )
      .forEach(b =>
        b.addEventListener(
          'click',
          () =>
            document
              .querySelector(
                '[data-search-panel]'
              )
              ?.classList.toggle(
                'hidden'
              )
        )
      );

    document
      .querySelectorAll(
        '[data-menu-toggle]'
      )
      .forEach(b =>
        b.addEventListener(
          'click',
          () =>
            document
              .querySelector(
                '[data-mobile-menu]'
              )
              ?.classList.toggle(
                'hidden'
              )
        )
      );

    document.addEventListener(
      'click',
      async e => {
        const add =
          e.target.closest(
            '[data-quick-add]'
          );

        if (add) {
          try {
            const quickVariant =
              add.dataset
                .quickVariant;

            if (quickVariant) {
              await addVariantToCart(
                quickVariant
              );
            } else {
              const products =
                await fetchProducts(
                  'per_page=50'
                );

              const product =
                products.find(
                  x =>
                    String(x.id) ===
                    String(
                      add.dataset
                        .quickAdd
                    )
                );

              const variant =
                findFirstVariant(
                  product
                );

              if (!variant) {
                throw new Error(
                  'No variant available'
                );
              }

              await addVariantToCart(
                variant.id
              );
            }
          } catch (err) {
            toast(err.message);
          }
        }

        const wish =
          e.target.closest(
            '[data-wishlist-product]'
          );

        if (wish) {
          try {
            await toggleWishlist(
              wish.dataset
                .wishlistProduct,
              wish
            );
          } catch (err) {
            toast(err.message);
          }
        }
      }
    );

    counts();
  };

  const bootCheckout = async () => {
    const page = document.querySelector('[data-checkout-page]');
    if (!page) return;

    const authNotice = page.querySelector('[data-checkout-auth]');
    const addressList = page.querySelector('[data-address-list]');
    const addressForm = page.querySelector('[data-address-form]');
    const summary = page.querySelector('[data-checkout-summary]');
    const itemCount = page.querySelector('[data-checkout-item-count]');
    const errorBox = page.querySelector('[data-checkout-error]');
    const placeButton = page.querySelector('[data-place-order]');

    const showError = (message) => {
      if (!errorBox) return;
      errorBox.textContent = message;
      errorBox.classList.remove('hidden');
    };

    const clearError = () => errorBox?.classList.add('hidden');

    if (!token()) {
      authNotice?.classList.remove('hidden');
      addressForm?.classList.add('hidden');
      placeButton?.setAttribute('disabled', 'disabled');
      summary.innerHTML = '<p class="text-sm text-gray-500">Sign in to continue to checkout.</p>';
      return;
    }

    let selectedAddress = null;

    const renderAddresses = (addresses) => {
      const list = Array.isArray(addresses) ? addresses : [];
      if (!list.length) {
        selectedAddress = null;
        addressList.innerHTML = '<div class="rounded-xl border border-dashed border-gray-200 p-4 text-sm text-gray-500">No saved addresses yet. Add your delivery address below.</div>';
        return;
      }

      const defaultIndex = Math.max(0, list.findIndex(a => a.is_default));
      selectedAddress = Number(list[defaultIndex].id);

      addressList.innerHTML = list.map((a, i) => `
        <label class="flex cursor-pointer gap-3 rounded-xl border p-4 transition ${i === defaultIndex ? 'border-gray-950 bg-gray-50' : 'border-gray-200 hover:border-gray-400'}">
          <input type="radio" name="address_id" value="${Number(a.id)}" ${i === defaultIndex ? 'checked' : ''} class="mt-1">
          <span class="min-w-0">
            <strong class="text-sm">${escapeHtml(a.recipient_name)}</strong>
            <span class="mt-1 block text-sm leading-6 text-gray-500">${escapeHtml(a.phone)} · ${escapeHtml(a.region)}, ${escapeHtml(a.district)}${a.ward ? `, ${escapeHtml(a.ward)}` : ''} · ${escapeHtml(a.street)}</span>
          </span>
        </label>
      `).join('');

      addressList.querySelectorAll('input[name="address_id"]').forEach(input => {
        input.addEventListener('change', () => {
          selectedAddress = Number(input.value);
          if (placeButton && !placeButton.dataset.checkoutBlocked) placeButton.removeAttribute('disabled');
          addressList.querySelectorAll('label').forEach(label => label.classList.remove('border-gray-950', 'bg-gray-50'));
          input.closest('label')?.classList.add('border-gray-950', 'bg-gray-50');
          clearError();
        });
      });
    };

    try {
      const [addresses, preview] = await Promise.all([
        api('/addresses'),
        api('/cart/checkout/preview')
      ]);

      const addressData = addresses?.data || [];
      renderAddresses(addressData);

      const d = preview?.data || {};
      const items = Array.isArray(d.items) ? d.items : [];
      const count = items.reduce((total, item) => total + Number(item.quantity || 0), 0);
      if (itemCount) itemCount.textContent = `${count} item${count === 1 ? '' : 's'}`;

      summary.innerHTML = `
        ${items.length ? `<div class="divide-y divide-gray-200 border-b border-gray-200">${items.map(item => `
          <div class="flex justify-between gap-4 py-3">
            <span class="text-gray-600">Item × ${Number(item.quantity || 0)}</span>
            <strong>${Number(item.line_total || 0).toLocaleString()} TZS</strong>
          </div>
        `).join('')}</div>` : ''}
        <div class="flex justify-between pt-1"><span>Subtotal</span><strong>${Number(d.subtotal || 0).toLocaleString()} TZS</strong></div>
        <div class="flex justify-between"><span>Delivery</span><strong>${Number(d.delivery_fee || 0).toLocaleString()} TZS</strong></div>
        <div class="mt-3 flex justify-between border-t border-gray-200 pt-3 text-base"><span>Total</span><strong>${Number(d.total || 0).toLocaleString()} TZS</strong></div>
      `;

      if (items.length && selectedAddress) placeButton?.removeAttribute('disabled');
    } catch (e) {
      summary.innerHTML = '<p class="text-sm text-red-600">We could not load your checkout summary. Please return to your cart and try again.</p>';
      placeButton?.setAttribute('disabled', 'disabled');
      showError(e.message);
    }

    addressForm?.addEventListener('submit', async e => {
      e.preventDefault();
      clearError();
      const submit = e.currentTarget.querySelector('button');
      submit?.setAttribute('disabled', 'disabled');
      const fd = new FormData(e.currentTarget);

      try {
        await api('/addresses', {
          method: 'POST',
          body: {
            type: 'shipping',
            recipient_name: fd.get('recipient_name'),
            phone: fd.get('phone'),
            region: fd.get('region'),
            district: fd.get('district'),
            ward: fd.get('ward') || null,
            street: fd.get('street'),
            is_default: true
          }
        });
        toast('Address saved');
        location.reload();
      } catch (err) {
        showError(err.message);
        submit?.removeAttribute('disabled');
      }
    });

    placeButton?.addEventListener('click', async () => {
      clearError();
      if (!selectedAddress) return showError('Add or select a delivery address before placing your order.');
      if (placeButton.disabled) return;

      placeButton.setAttribute('disabled', 'disabled');
      placeButton.setAttribute('aria-busy', 'true');
      const originalText = placeButton.textContent;
      placeButton.textContent = 'Placing order…';

      const key = `kp-${Date.now()}-${crypto.randomUUID().replaceAll('-', '').slice(0, 16)}`;

      try {
        const order = await api('/checkout', {
          method: 'POST',
          headers: { 'Idempotency-Key': key },
          body: {
            address_id: Number(selectedAddress),
            notes: page.querySelector('[data-order-notes]')?.value || null
          }
        });

        location.href = `/orders/${order.data.order_number}`;
      } catch (e) {
        placeButton.removeAttribute('disabled');
        placeButton.removeAttribute('aria-busy');
        placeButton.textContent = originalText;
        showError(e.message);
      }
    });
  };

  const bootAccount =
    async () => {
      const account =
        document.querySelector(
          '[data-account-page]'
        );

      const profile =
        document.querySelector(
          '[data-account-profile]'
        );

      const sidebar =
        document.querySelector(
          'aside [data-sidebar-name]'
        )
          ? document.querySelector(
              'aside'
            )
          : null;

      if (
        !account &&
        !profile &&
        !sidebar
      ) {
        return;
      }

      if (!token()) {
        if (account) {
          location.href =
            '/login';
        }

        return;
      }

      try {
        const me =
          await api('/auth/me');

        setAuth({
          user: me.data
        });

        document
          .querySelectorAll(
            '[data-account-name]'
          )
          .forEach(
            n =>
              (n.textContent =
                `Welcome back, ${me.data.name}`)
          );

        document
          .querySelectorAll(
            '[data-profile-name]'
          )
          .forEach(
            n =>
              (n.textContent =
                me.data.name)
          );

        document
          .querySelectorAll(
            '[data-profile-phone]'
          )
          .forEach(
            n =>
              (n.textContent =
                me.data.phone)
          );

        document
          .querySelectorAll(
            '[data-sidebar-name]'
          )
          .forEach(
            n =>
              (n.textContent =
                me.data.name)
          );

        document
          .querySelectorAll(
            '[data-sidebar-phone]'
          )
          .forEach(
            n =>
              (n.textContent =
                me.data.phone)
          );

        const orders =
          await api('/orders');

        if (account) {
          account.querySelector(
            '[data-account-orders-count]'
          ).textContent =
            orders.data?.length ??
            0;
        }
      } catch (e) {
        toast(e.message);
      }
    };

  const orderStatusClass = status => {
    const value = String(status || '').toLowerCase();
    if (value.includes('confirmed') || value.includes('paid') || value.includes('completed')) {
      return 'bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100';
    }
    if (value.includes('cancel') || value.includes('failed')) {
      return 'bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-100';
    }
    return 'bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100';
  };

  const formatOrderStatus = status => String(status || 'pending_payment').replaceAll('_', ' ');

  const returnStatusClass = status => {
    const value = String(status || '').toLowerCase();
    if (value === 'approved' || value === 'completed') return 'bg-emerald-50 text-emerald-700';
    if (value === 'rejected' || value === 'cancelled') return 'bg-rose-50 text-rose-700';
    return 'bg-amber-50 text-amber-700';
  };

  const bootReturns = async () => {
    const page = document.querySelector('[data-returns-page]');
    if (!page) return;
    if (!token()) { location.href = '/login'; return; }

    const list = page.querySelector('[data-returns-list]');
    const empty = page.querySelector('[data-returns-empty]');
    const modal = document.querySelector('[data-return-modal]');
    const form = document.querySelector('[data-return-form]');
    const orderSelect = document.querySelector('[data-return-order-select]');
    const itemsNode = document.querySelector('[data-return-items]');

    const renderRequests = requests => {
      if (!requests.length) {
        list.innerHTML = '';
        empty.classList.remove('hidden');
        empty.classList.add('flex');
        return;
      }
      empty.classList.add('hidden');
      empty.classList.remove('flex');
      list.innerHTML = requests.map(r => `
        <article class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm sm:p-6">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">${escapeHtml(r.request_type || 'request')}</p>
              <h2 class="mt-1 text-sm font-bold text-gray-950">Order #${escapeHtml(r.order_number || '')}</h2>
              <p class="mt-1 text-xs text-gray-500">${r.created_at ? new Date(r.created_at).toLocaleString() : ''}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold capitalize ${returnStatusClass(r.status)}">${escapeHtml(String(r.status || '').replaceAll('_', ' '))}</span>
          </div>
          <div class="mt-4 border-t border-gray-100 pt-4">
            ${(r.items || []).map(i => `<p class="text-sm text-gray-700">${escapeHtml(i.product_name)}${i.size ? ` · Size ${escapeHtml(i.size)}` : ''} · Qty ${Number(i.quantity || 0)}</p>`).join('')}
            <p class="mt-1 text-xs text-gray-400">Reason: ${escapeHtml(String(r.reason || '').replaceAll('_', ' '))}</p>
          </div>
        </article>`).join('');
    };

    try {
      const [requestsResponse, ordersResponse] = await Promise.all([api('/returns'), api('/orders')]);
      const requests = Array.isArray(requestsResponse?.data) ? requestsResponse.data : [];
      const orders = Array.isArray(ordersResponse?.data) ? ordersResponse.data : [];
      renderRequests(requests);

      const eligibleOrders = orders.filter(o => String(o.status || '').toLowerCase() === 'delivered');
      orderSelect.innerHTML = '<option value="">Choose an order…</option>' + eligibleOrders.map(o =>
        `<option value="${escapeHtml(o.id)}">${escapeHtml(o.order_number)} · ${Number(o.total || 0).toLocaleString()} TZS</option>`
      ).join('');

      const openModal = () => {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
      };
      const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        form.reset();
        itemsNode.innerHTML = 'Select an order to see items';
      };

      page.querySelector('[data-new-request]')?.addEventListener('click', openModal);
      document.querySelector('[data-close-return-modal]')?.addEventListener('click', closeModal);
      modal?.addEventListener('click', e => { if (e.target === modal) closeModal(); });
      page.querySelector('[data-contact-support]')?.addEventListener('click', () => { location.href = '/contact'; });

      orderSelect.addEventListener('change', async () => {
        const selected = eligibleOrders.find(o => String(o.id) === String(orderSelect.value));
        if (!selected) { itemsNode.innerHTML = 'Select an order to see items'; return; }
        try {
          const response = await api(`/orders/${encodeURIComponent(selected.order_number)}`);
          const items = Array.isArray(response?.data?.items) ? response.data.items : [];
          itemsNode.innerHTML = items.length ? items.map(i => `
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-gray-100 p-3 hover:bg-gray-50">
              <input type="checkbox" name="item_ids[]" value="${escapeHtml(i.id)}" class="mt-1 rounded border-gray-300" checked>
              <span class="min-w-0"><span class="block text-sm font-medium text-gray-800">${escapeHtml(i.name)}</span><span class="mt-0.5 block text-xs text-gray-500">${escapeHtml([i.size, i.color].filter(Boolean).join(' · ') || 'Standard')} · Qty ${Number(i.quantity || 0)}</span></span>
            </label>`).join('') : '<p class="text-sm text-gray-500">No items found for this order.</p>';
        } catch (e) { itemsNode.innerHTML = `<p class="text-sm text-rose-600">${escapeHtml(e.message)}</p>`; }
      });

      form.addEventListener('submit', async e => {
        e.preventDefault();
        const submit = form.querySelector('button[type="submit"]');
        const itemIds = [...form.querySelectorAll('input[name="item_ids[]"]:checked')].map(i => Number(i.value));
        if (!orderSelect.value || !itemIds.length) { toast('Select an order and at least one item.'); return; }
        submit.disabled = true;
        submit.textContent = 'Submitting…';
        try {
          await api('/returns', { method: 'POST', body: {
            order_id: Number(orderSelect.value),
            item_ids: itemIds,
            request_type: form.querySelector('input[name="request_type"]:checked')?.value || 'return',
            reason: form.querySelector('[name="reason"]')?.value,
            notes: form.querySelector('[name="notes"]')?.value || null,
          }});
          closeModal();
          renderRequests((await api('/returns'))?.data || []);
          toast('Return request submitted.');
        } catch (e) { toast(e.message); }
        finally { submit.disabled = false; submit.textContent = 'Submit request'; }
      });
    } catch (e) {
      list.innerHTML = `<div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-6 text-sm text-rose-700">${escapeHtml(e.message || 'Unable to load returns.')}</div>`;
    }
  };

  const bootOrderStatus = async () => {
    const page = document.querySelector('[data-order-status]');
    if (!page) return;
    const orderNumber = page.dataset.orderNumber;
    const text = page.querySelector('[data-order-status-text]');
    const title = page.querySelector('[data-order-status-title]');
    const iconBox = page.querySelector('[data-order-status-icon]');
    const actions = page.querySelector('[data-order-status-actions]');

    if (!token()) {
      if (text) text.textContent = 'Please sign in to view this order.';
      return;
    }

    const render = order => {
      const status = String(order?.status || 'pending_payment').toLowerCase();
      const payment = String(order?.payment_status || 'pending').toLowerCase();
      const paid = payment === 'paid';
      const cancelled = status === 'cancelled';
      const pending = !paid && !cancelled;
      if (title) title.textContent = cancelled ? 'Order cancelled' : paid ? 'Order confirmed' : 'Order created';
      if (text) {
        text.innerHTML = cancelled
          ? '<span class="h-2 w-2 rounded-full bg-rose-500"></span> This order has been cancelled.'
          : paid
          ? '<span class="h-2 w-2 rounded-full bg-emerald-500"></span> Payment received. Your order is confirmed.'
          : '<span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span> Your order is awaiting payment.';
      }
      if (iconBox) {
        iconBox.className = `mx-auto flex h-16 w-16 items-center justify-center rounded-full ${cancelled ? 'bg-rose-100 text-rose-700' : paid ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'} ring-8 ${cancelled ? 'ring-rose-50' : paid ? 'ring-emerald-50' : 'ring-amber-50'}`;
        iconBox.innerHTML = cancelled ? icon('x',30) : paid ? icon('check',30) : icon('clock',30);
      }
      if (actions) {
        actions.innerHTML = `<a href="/account/orders/${encodeURIComponent(order.order_number)}" class="inline-flex items-center justify-center gap-2 rounded-full border border-gray-200 px-6 py-3 text-sm font-medium text-gray-700 hover:bg-gray-50">View order</a>${pending ? `<button type="button" data-cancel-pending-order class="inline-flex items-center justify-center gap-2 rounded-full border border-rose-200 px-6 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50">Cancel order</button>` : ''}`;
        actions.querySelector('[data-cancel-pending-order]')?.addEventListener('click', async e => {
          const button=e.currentTarget; if(!confirm('Cancel this order?')) return; button.disabled=true; button.textContent='Cancelling…';
          try { await api(`/orders/${encodeURIComponent(order.order_number)}/cancel`, {method:'POST'}); toast('Order cancelled'); location.reload(); }
          catch(err){button.disabled=false;button.textContent='Cancel order';toast(err.message);}
        });
      }
    };

    try {
      const response = await api(`/orders/${encodeURIComponent(orderNumber)}`);
      render(response.data);
    } catch (e) {
      if (text) text.textContent = e.message || 'Unable to load this order.';
    }
  };

  const bootOrders = async () => {
    const page = document.querySelector('[data-orders-page]');
    if (!page) return;

    if (!token()) {
      location.href = '/login';
      return;
    }

    try {
      const orders = await api('/orders');
      const list = page.querySelector('[data-orders-list]');
      const data = Array.isArray(orders.data) ? orders.data : [];

      if (!data.length) {
        list.innerHTML = `
          <div class="rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-white text-gray-400 shadow-sm ring-1 ring-gray-100">
              <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M6 9V4h12v5"/><path d="M4 9h16v11H4z"/><path d="M9 13h6"/></svg>
            </div>
            <h2 class="mt-5 text-lg font-semibold text-gray-950">No orders yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-gray-500">Your completed and pending purchases will appear here.</p>
            <a href="/shop" class="button-dark mt-6">Start shopping</a>
          </div>`;
        return;
      }

      list.innerHTML = data.map(o => `
        <article class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm transition hover:border-gray-200 hover:shadow-md">
          <div class="flex flex-col gap-5 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Order</p>
                <h2 class="mt-1 text-base font-bold text-gray-950">${escapeHtml(o.order_number)}</h2>
                <p class="mt-1 text-sm text-gray-500">${o.created_at ? new Date(o.created_at).toLocaleString() : 'Date unavailable'}</p>
              </div>
              <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${orderStatusClass(o.status)}">${escapeHtml(formatOrderStatus(o.status))}</span>
            </div>

            <div class="grid gap-3 border-t border-gray-100 pt-5 sm:grid-cols-3">
              <div>
                <p class="text-xs text-gray-400">Items</p>
                <p class="mt-1 text-sm font-semibold text-gray-900">${Array.isArray(o.items) ? o.items.reduce((n, i) => n + Number(i.quantity || 0), 0) : '—'}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Payment</p>
                <p class="mt-1 text-sm font-semibold capitalize text-gray-900">${escapeHtml(formatOrderStatus(o.payment_status))}</p>
              </div>
              <div>
                <p class="text-xs text-gray-400">Total</p>
                <p class="mt-1 text-sm font-bold text-gray-950">${Number(o.total || 0).toLocaleString()} TZS</p>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-gray-100 pt-5">
              <a href="/account/orders/${encodeURIComponent(o.order_number)}" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-semibold text-gray-700 transition hover:border-gray-300 hover:bg-gray-50">View details</a>
              ${String(o.status || '').toLowerCase() === 'pending_payment' ? `<a href="/orders/${encodeURIComponent(o.order_number)}" class="inline-flex items-center justify-center rounded-xl bg-gray-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-gray-800">Continue payment</a>` : ''}
            </div>
          </div>
        </article>
      `).join('');
    } catch (e) {
      page.querySelector('[data-orders-list]').innerHTML = `
        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-6 text-sm text-rose-700">${escapeHtml(e.message || 'Unable to load your orders.')}</div>`;
    }
  };

  const bootOrderDetail = async () => {
    const page = document.querySelector('[data-order-detail]');
    if (!page) return;

    if (!token()) {
      location.href = '/login';
      return;
    }

    try {
      const o = (await api(`/orders/${encodeURIComponent(page.dataset.orderNumber)}`)).data;
      const items = Array.isArray(o.items) ? o.items : [];
      const canCancel = String(o.status || '').toLowerCase() === 'pending_payment';

      page.querySelector('[data-order-detail-content]').innerHTML = `
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
          <section class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
            <div class="border-b border-gray-100 p-5 sm:p-6">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Order summary</p>
                  <p class="mt-1 text-sm text-gray-500">${o.created_at ? new Date(o.created_at).toLocaleString() : ''}</p>
                </div>
                <span class="rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${orderStatusClass(o.status)}">${escapeHtml(formatOrderStatus(o.status))}</span>
              </div>
            </div>

            <div class="divide-y divide-gray-100">
              ${items.length ? items.map(i => `
                <div class="flex items-start justify-between gap-5 p-5 sm:p-6">
                  <div class="min-w-0">
                    <p class="font-semibold text-gray-950">${escapeHtml(i.name)}</p>
                    <p class="mt-1 text-sm text-gray-500">${escapeHtml([i.size, i.color].filter(Boolean).join(' · ') || 'Standard')} · Qty ${Number(i.quantity || 0)}</p>
                    ${i.sku ? `<p class="mt-1 text-xs text-gray-400">SKU ${escapeHtml(i.sku)}</p>` : ''}
                  </div>
                  <p class="shrink-0 text-sm font-bold text-gray-950">${Number(i.line_total || 0).toLocaleString()} TZS</p>
                </div>`).join('') : '<p class="p-6 text-sm text-gray-500">No order items available.</p>'}
            </div>
          </section>

          <aside class="space-y-4">
            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Payment</p>
              <p class="mt-2 text-sm font-semibold capitalize text-gray-950">${escapeHtml(formatOrderStatus(o.payment_status))}</p>
              <div class="mt-5 space-y-3 border-t border-gray-100 pt-5 text-sm">
                <div class="flex justify-between gap-4"><span class="text-gray-500">Subtotal</span><strong>${Number(o.subtotal || 0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4"><span class="text-gray-500">Delivery</span><strong>${Number(o.delivery_fee || 0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4 border-t border-gray-100 pt-3 text-base"><span class="font-semibold">Total</span><strong>${Number(o.total || 0).toLocaleString()} TZS</strong></div>
              </div>
            </div>

            <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Delivery</p>
              <p class="mt-2 text-sm font-semibold text-gray-950">${escapeHtml(o.customer?.name || 'Customer')}</p>
              <p class="mt-1 text-sm leading-6 text-gray-500">${escapeHtml(o.delivery?.address || 'Delivery address unavailable')}</p>
              ${o.customer?.phone ? `<p class="mt-3 text-sm text-gray-600">${escapeHtml(o.customer.phone)}</p>` : ''}
            </div>

            ${canCancel ? `<button data-cancel-order type="button" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Cancel order</button>` : ''}
          </aside>
        </div>
      `;

      page.querySelector('[data-cancel-order]')?.addEventListener('click', async event => {
        const button = event.currentTarget;
        if (!window.confirm('Cancel this order?')) return;
        button.disabled = true;
        button.textContent = 'Cancelling…';
        try {
          const response = await api(`/orders/${encodeURIComponent(page.dataset.orderNumber)}/cancel`, { method: 'POST' });
          const updated = response.data;
          toast('Order cancelled');
          setTimeout(() => location.reload(), 350);
        } catch (e) {
          button.disabled = false;
          button.textContent = 'Cancel order';
          toast(e.message);
        }
      });
    } catch (e) {
      page.querySelector('[data-order-detail-content]').innerHTML = `
        <div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-6 text-sm text-rose-700">${escapeHtml(e.message || 'Unable to load this order.')}</div>`;
    }
  };

  const bootAddresses =
    async () => {
      const page =
        document.querySelector(
          '[data-account-addresses]'
        );

      if (!page) return;

      if (!token()) {
        location.href =
          '/login';

        return;
      }

      try {
        const data =
          await api('/addresses');

        page.querySelector(
          '[data-addresses-list]'
        ).innerHTML =
          (data.data || [])
            .map(
              a => `
                <div class="rounded-2xl border border-gray-100 p-5">
                  <div class="flex items-center justify-between">
                    <strong>
                      ${
                        a.label ||
                        'Shipping address'
                      }
                    </strong>

                    ${
                      a.is_default
                        ? `
                          <span class="text-xs font-semibold text-emerald-700">
                            Default
                          </span>
                        `
                        : ''
                    }
                  </div>

                  <p class="mt-3 text-sm leading-6 text-gray-600">
                    ${escapeHtml(a.recipient_name)}<br>
                    ${escapeHtml(a.phone)}<br>
                    ${escapeHtml(a.region)}, ${
                      escapeHtml(a.district)
                    }${
                      a.ward
                        ? ', ' +
                          escapeHtml(a.ward)
                        : ''
                    }<br>
                    ${escapeHtml(a.street)}
                  </p>
                </div>
              `
            )
            .join('') ||
          '<p class="text-sm text-gray-500">No addresses saved.</p>';
      } catch (e) {
        toast(e.message);
      }
    };

  const bootLogout = () =>
    document
      .querySelectorAll(
        '[data-logout]'
      )
      .forEach(b =>
        b.addEventListener(
          'click',
          async () => {
            try {
              await api(
                '/auth/logout',
                {
                  method: 'POST'
                }
              );
            } catch {}

            localStorage.removeItem(
              TOKEN_KEY
            );

            localStorage.removeItem(
              USER_KEY
            );

            location.href = '/';
          }
        )
      );

  bindGlobal();

  bootHome();
  bootCollections();
  bootCatalog();
  bootProduct();
  bootCart();
  bootAuth();
  bootWishlist();
  bootCheckout();
  bootAccount();
  bootReturns();
  bootOrders();
  bootOrderStatus();
  bootOrderDetail();
  bootAddresses();
  bootLogout();
})();
