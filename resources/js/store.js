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

    // Browser auth uses the HttpOnly kp_web_session cookie. Fetch sends
    // same-origin cookies automatically, so never require a JS-readable token.
    if (token()) {
      headers.set(
        'Authorization',
        `Bearer ${token()}`
      );
    } else if (!signedIn()) {
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

  // Browser authentication is carried by the HttpOnly kp_web_session cookie.
  // The cookie is intentionally unreadable from JavaScript, so the server-rendered
  // signed-in state is the source of truth for frontend guards.
  const signedIn = () =>
    document
      .querySelector('meta[name="kp-signed-in"]')
      ?.getAttribute('content') === '1';

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

    if (signedIn()) {
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
                'text-black'
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
    black: '#000000',
    white: '#f5f5f5',
    navy: '#1e3a5f',
    olive: '#5b6f4a',
    burgundy: '#6b2c2c',
    camel: '#c4a882',
    gray: '#000000',
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
        <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-white"></div>
        <div class="mb-2 h-3 w-20 rounded bg-white"></div>
        <div class="h-4 w-3/4 rounded bg-white"></div>
        <div class="mt-2 h-4 w-24 rounded bg-white"></div>
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
                class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-white"
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
                        <span class="w-fit rounded-full bg-black px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
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
                      <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-black px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
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
                  class="absolute bottom-3 left-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-black opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-rose-500 hover:text-white"
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
                  class="absolute bottom-3 right-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-black opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-emerald-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                  aria-label="Add ${escapeHtml(p.name || 'product')} to bag"
                >
                  ${icon('bag')}
                </button>
              </div>

              <div>
                <p class="mb-1 text-xs text-black">
                  ${escapeHtml(p.category || '')}
                </p>

                ${
                  rating > 0
                    ? `
                      <div class="mb-1 flex items-center gap-1 text-xs text-black">
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
                  class="line-clamp-1 text-sm font-medium text-black transition-colors hover:text-emerald-700"
                >
                  ${escapeHtml(p.name || '')}
                </a>

                <div class="mt-1 flex items-center gap-2">
                  <span class="text-sm font-semibold text-black">
                    ${price.toLocaleString()} TZS
                  </span>

                  ${
                    onSale
                      ? `
                        <span class="text-xs text-black line-through">
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
                                class="h-3.5 w-3.5 rounded-full border border-black shadow-sm"
                                style="background-color:${
                                  colorHex[
                                    String(
                                      c
                                    ).toLowerCase()
                                  ] ||
                                  '#000000'
                                }"
                                title="${escapeHtml(c)}"
                              ></span>
                            `
                          )
                          .join('')}

                        ${
                          colors.length > 5
                            ? `
                              <span class="text-[11px] text-black">
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

    if (products.length >= 12) {
      const editorialMount = document.createElement('div');
      editorialMount.setAttribute('data-catalog-editorial-slider', '');
      editorialMount.setAttribute('aria-label', 'KP Wear editorial feature');
      editorialMount.setAttribute('role', 'region');
      editorialMount.className = 'col-span-full';
      const anchor = container.children[11];
      if (anchor) {
        anchor.insertAdjacentElement('afterend', editorialMount);
      }
    }

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

  const fetchStorefront = async () =>
    (await api('/wear/storefront'))?.data || null;

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
    if (!signedIn()) {
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
          'text-black'
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
          'text-black'
        );
      }

      toast('Saved to wishlist');
    }

    await counts();
  };

const bootHome = async () => {
    const grid = document.querySelector('[data-home-featured]');
    if (!grid) return;

    renderProductSkeletons(grid, 8);

    const renderCollections = collections => {
      const host = document.querySelector('[data-home-collections]');
      if (!host) return;

      if (!collections.length) {
        host.innerHTML = '<div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-dashed border-black py-16 text-center text-sm text-black">No featured collections are available yet.</div>';
        return;
      }

      host.innerHTML = collections.slice(0, 3).map(collection => {
        const image = collection.cover || collection.image || '/assets/wear/catalog/generated/product-01.jpg';

        return `
          <a
            href="/collections/${encodeURIComponent(collection.slug)}"
            class="group relative overflow-hidden rounded-2xl bg-white aspect-[4/3]"
          >
            <img
              src="${escapeHtml(image)}"
              alt="${escapeHtml(collection.name || 'Collection')}"
              class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]"
              loading="lazy"
              onerror="this.onerror=null;this.src='/assets/wear/catalog/generated/product-01.jpg'"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-6">
              <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">
                ${Number(collection.product_count || 0)} products
              </p>
              <h3 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                ${escapeHtml(collection.name || '')}
              </h3>
              ${collection.description ? `
                <p class="mt-2 max-w-md text-sm leading-6 text-white/70">
                  ${escapeHtml(collection.description)}
                </p>
              ` : ''}
            </div>
          </a>
        `;
      }).join('');
    };

    const applyHero = hero => {
      if (!hero) return;

      const section = document.querySelector('.kp-hero-section');
      if (section && hero.is_active === false) {
        section.classList.add('hidden');
        return;
      }

      const eyebrow = document.querySelector('[data-storefront-hero-eyebrow]');
      const title = document.querySelector('[data-storefront-hero-title]');
      const description = document.querySelector('[data-storefront-hero-description]');
      const cta = document.querySelector('[data-storefront-hero-cta]');
      const ctaLabel = document.querySelector('[data-storefront-hero-cta-label]');
      const image = document.querySelector('[data-storefront-hero-image]');

      if (eyebrow && hero.eyebrow) eyebrow.textContent = hero.eyebrow;

      if (title && hero.title) {
        title.textContent = hero.title;
      }

      if (description) {
        description.textContent = hero.description || '';
        description.classList.toggle('hidden', !hero.description);
      }

      if (cta) {
        if (hero.cta_url) cta.href = hero.cta_url;
        if (hero.cta_label) ctaLabel ? ctaLabel.textContent = hero.cta_label : cta.textContent = hero.cta_label;
      }

      if (image && hero.image_desktop) {
        image.src = hero.image_desktop;
      }

      if (image && hero.image_mobile) {
        image.dataset.mobileSrc = hero.image_mobile;
      }
    };

    try {
      const [storefront, categories] = await Promise.all([
        fetchStorefront(),
        fetchCategories()
      ]);

      const featuredProducts = Array.isArray(storefront?.homepage?.featured_products)
        ? storefront.homepage.featured_products
        : [];
      const productsForHome = featuredProducts.length
        ? featuredProducts
        : await fetchProducts('per_page=50').then(response => Array.isArray(response?.data) ? response.data : []);

      renderProductGrid(grid, productsForHome.slice(0, 8));

      const featuredCollections = Array.isArray(storefront?.homepage?.featured_collections)
        ? storefront.homepage.featured_collections
        : [];
      const collectionsForHome = featuredCollections.length
        ? featuredCollections
        : await fetchCollections();

      renderCollections(collectionsForHome);

      applyHero(storefront?.hero);

      const catGrid = document.querySelector('[data-home-categories]');
      if (catGrid) {
        const categorySource = categories.map(c => ({
          name: c.name,
          slug: c.slug || slugify(c.name)
        }));

        catGrid.innerHTML = categorySource.map(category => `
          <a
            href="/category/${encodeURIComponent(category.slug)}"
            class="group rounded-xl border border-black bg-white p-3.5 transition hover:-translate-y-0.5 hover:border-black"
          >
            <p class="text-xs font-semibold text-slate-900 sm:text-sm">
              ${escapeHtml(category.name)}
            </p>
            <p class="mt-0.5 text-[11px] text-black sm:text-xs">
              Shop collection
            </p>
          </a>
        `).join('');
      }
    } catch (e) {
      grid.innerHTML = '';
      const collectionHost = document.querySelector('[data-home-collections]');
      if (collectionHost) {
        collectionHost.innerHTML = '<div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-red-100 bg-red-50 py-12 text-center text-sm text-red-700">Unable to load featured store content right now.</div>';
      }
      toast(e.message || 'Unable to load store content.');
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
        <a href="/collections/${encodeURIComponent(collection.slug)}" class="group relative overflow-hidden rounded-[1.5rem] bg-white ${wide ? 'lg:col-span-8' : 'lg:col-span-4'} aspect-[4/3]">
          <img src="${escapeHtml(image)}" alt="${escapeHtml(collection.name)}" class="absolute inset-0 h-full w-full object-cover transition duration-700 group-hover:scale-[1.035]">
          <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
          <div class="absolute inset-x-0 bottom-0 p-6 text-white sm:p-8">
            <div class="flex items-end justify-between gap-5">
              <div>
                <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">${Number(collection.product_count || 0)} products</p>
                <h3 class="mt-1 text-2xl font-semibold tracking-tight sm:text-3xl">${escapeHtml(collection.name)}</h3>
                <p class="mt-2 max-w-md text-sm leading-6 text-white/70">${escapeHtml(collection.description || '')}</p>
              </div>
              <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-white text-lg text-black transition group-hover:-rotate-45">↗</span>
            </div>
          </div>
        </a>`;
    }).join('');

    if (!collections.length) {
      grid.innerHTML = '<div class="col-span-full rounded-2xl border border-dashed border-black py-20 text-center text-sm text-black">No collections are available yet.</div>';
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

    const pathParts = location.pathname.split('/').filter(Boolean);
    const collectionSlug = pathParts[0] === 'collections'
      ? decodeURIComponent(pathParts[1] || '')
      : '';

    const routeCategory = pathParts[0] === 'category'
      ? decodeURIComponent(pathParts[1] || '')
      : '';

    const state = {
      products: [],
      collection: collectionSlug,
      category:
        routeCategory ||
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
                <h3 class="text-sm font-semibold uppercase tracking-wider text-black">
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
              <h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-black">
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
              <button type="button" data-catalog-page="${Math.max(1,current-1)}" ${current <= 1 ? 'disabled' : ''} class="rounded-xl border border-black px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Previous</button>
              <span class="text-sm text-black">Page ${current} of ${last}</span>
              <button type="button" data-catalog-page="${Math.min(last,current+1)}" ${current >= last ? 'disabled' : ''} class="rounded-xl border border-black px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Next</button>
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
      saleToggle?.classList.toggle('bg-white', !state.saleOnly);
      saleToggle?.setAttribute('aria-pressed', state.saleOnly ? 'true' : 'false');
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-5', state.saleOnly);
      saleToggle?.querySelector('span')?.classList.toggle('translate-x-0', !state.saleOnly);

      document.querySelectorAll('[data-catalog-categories] [data-category]').forEach(button => {
        const active = slugify(button.dataset.category) === slugify(state.category || '');
        button.classList.toggle('bg-emerald-50', active);
        button.classList.toggle('text-emerald-700', active);
        button.classList.toggle('font-medium', active);
        button.classList.toggle('text-black', !active);
      });

      clearTimeout(debounceTimer);
      debounceTimer = setTimeout(loadProducts, state.search ? 250 : 0);
    };

    const categories = await fetchCategories();

    if (sortSelect && !collectionSlug) {
      try {
        const storefront = await fetchStorefront();
        const defaultSort = storefront?.shop?.default_sort || 'featured';
        if ([...sortSelect.options].some(option => option.value === defaultSort)) {
          state.sort = defaultSort;
          sortSelect.value = defaultSort;
        }

        const banner = document.querySelector('[data-storefront-shop-banner]');
        const bannerTitle = document.querySelector('[data-storefront-shop-banner-title]');
        const bannerDescription = document.querySelector('[data-storefront-shop-banner-description]');
        const bannerImage = document.querySelector('[data-storefront-shop-banner-image]');
        const bannerData = storefront?.shop?.banner;

        if (banner && bannerData?.is_active) {
          banner.classList.remove('hidden');
          if (bannerTitle) bannerTitle.textContent = bannerData.title || '';
          if (bannerDescription) {
            bannerDescription.textContent = bannerData.description || '';
            bannerDescription.classList.toggle('hidden', !bannerData.description);
          }
          if (bannerImage && bannerData.image) {
            bannerImage.src = bannerData.image;
            bannerImage.alt = bannerData.title || 'KP Wear shop banner';
          }
        }
      } catch {}
    }

    if (searchInput) searchInput.value = state.search;
    if (categoryHost) {
      categoryHost.innerHTML = ['<button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>', ...categories.map(c => `<button type="button" data-category="${escapeHtml(c.slug || slugify(c.name))}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${escapeHtml(c.name)}</button>`)].join('');
    }
    if (mobileContent) {
      mobileContent.innerHTML = `<div class="space-y-8"><div><div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold uppercase tracking-wider text-black">Category</h3><button type="button" data-catalog-clear-mobile class="text-xs font-medium text-emerald-600">Clear all</button></div><div data-mobile-categories class="space-y-1"><button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>${categories.map(c=>`<button type="button" data-category="${escapeHtml(c.slug || slugify(c.name))}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${escapeHtml(c.name)}</button>`).join('')}</div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-black">Price Range</h3><div class="space-y-1"><button type="button" data-price="all" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Prices</button><button type="button" data-price="under-50000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Under 50,000 TZS</button><button type="button" data-price="50000-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">50,000 – 150,000 TZS</button><button type="button" data-price="over-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Over 150,000 TZS</button></div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-black">Special</h3><label class="flex items-center gap-3"><button type="button" data-catalog-sale-toggle-mobile class="relative h-6 w-11 rounded-full bg-white"><span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span></button><span class="text-sm text-black">On Sale Only</span></label></div></div>`;
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
      stock.classList.toggle('text-black', totalStock > 0);

      const main = page.querySelector('[data-product-image]');
      main.src = p.image || '';
      main.alt = p.name || 'Product';

      const gallery = page.querySelector('[data-product-gallery]');
      if (gallery && p.image) {
        gallery.innerHTML = `<button type="button" data-gallery-image="${escapeHtml(p.image)}" class="overflow-hidden rounded-xl border-2 border-black bg-white"><img src="${escapeHtml(p.image)}" alt="${escapeHtml(p.name || 'Product')}" class="aspect-square w-full object-contain p-2"></button>`;
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
          return `<button type="button" data-variant="${escapeHtml(v.id)}" data-stock="${escapeHtml(v.stock)}" data-label="${escapeHtml(label)}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors ${String(v.id) === String(firstAvailable?.id || '') ? 'border-black bg-black text-white' : 'border-black bg-white text-black hover:border-emerald-600 hover:bg-emerald-50'} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2" ${!v.in_stock ? 'disabled aria-disabled="true"' : ''}>${escapeHtml(label)}${!v.in_stock ? ' · Sold out' : ''}</button>`;
        }).join('') || '<span class="text-sm text-black">No variants available.</span>';
        selectedLabel.textContent = firstAvailable ? [firstAvailable.size, firstAvailable.color].filter(Boolean).join(' · ') : '';
        page.querySelector('[data-quantity]')?.setAttribute('max', String(Math.min(50, Math.max(1, Number(firstAvailable?.stock || 1)))));
      }

      // Pre-select the customer's saved size when it matches an available variant.
      if (signedIn() && variants.length) {
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
              button.classList.toggle('border-black', active);
              button.classList.toggle('bg-black', active);
              button.classList.toggle('text-white', active);
            });
          }
        } catch {}
      }

      const setVariant = btn => {
        page.dataset.selectedVariant = btn.dataset.variant;
        selectedLabel.textContent = btn.dataset.label || '';
        page.querySelectorAll('[data-variant]').forEach(x => x.classList.remove('border-black', 'bg-black', 'text-white'));
        btn.classList.add('border-black', 'bg-black', 'text-white');
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

    const render = async (showLoading = false) => {
      const loading = page.querySelector('[data-cart-loading]');
      if (showLoading) loading?.classList.remove('hidden');
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
          <article class="kp-cart-item grid grid-cols-[88px_minmax(0,1fr)] gap-x-4 gap-y-4 py-5 sm:grid-cols-[152px_minmax(0,1fr)_150px_150px] sm:items-center sm:gap-6 lg:grid-cols-[152px_minmax(0,1fr)_132px_150px]">
            <a href="/product/${encodeURIComponent(item.product?.slug || '')}" class="h-[88px] w-[88px] shrink-0 overflow-hidden rounded-xl bg-emerald-50/30 ring-1 ring-emerald-950/6 sm:h-[152px] sm:w-[152px]">
              <img src="${escapeHtml(image)}" alt="${escapeHtml(item.product?.name || 'Product')}" class="h-full w-full object-contain p-2.5" loading="lazy" onerror="this.onerror=null;this.src='/assets/wear/catalog/generated/product-01.jpg'">
            </a>

            <div class="min-w-0 self-stretch sm:flex sm:flex-col sm:justify-center">
              <a href="/product/${encodeURIComponent(item.product?.slug || '')}" class="text-base font-bold tracking-tight text-black hover:underline hover:underline-offset-4">
                ${escapeHtml(item.product?.name || 'Product')}
              </a>
              ${variantLabel ? `<p class="mt-1.5 text-sm text-black">${escapeHtml(variantLabel.replace(' · ', '  ·  '))}</p>` : ''}
              <p class="mt-2 text-sm text-black">${unitPrice.toLocaleString()} TZS each</p>
              <button type="button" data-cart-remove="${item.variant_id}" class="kp-remove-btn mt-3 inline-flex w-fit items-center gap-1.5 text-sm font-medium text-black underline underline-offset-4" aria-label="Remove ${escapeHtml(item.product?.name || 'item')} from bag">
                <span aria-hidden="true">×</span> Remove
              </button>
            </div>

            <div class="col-span-2 flex items-center justify-start sm:col-span-1 sm:justify-center">
              <div class="inline-flex h-11 items-center rounded-xl border border-black bg-white px-1">
                <button type="button" data-cart-minus="${item.variant_id}" class="kp-qty-btn flex h-9 w-9 items-center justify-center rounded-lg text-base font-medium text-black" aria-label="Decrease quantity for ${escapeHtml(item.product?.name || 'item')}">−</button>
                <span class="min-w-9 text-center text-sm font-semibold text-black" aria-live="polite">${quantity}</span>
                <button type="button" data-cart-plus="${item.variant_id}" class="kp-qty-btn flex h-9 w-9 items-center justify-center rounded-lg text-base font-medium text-black" aria-label="Increase quantity for ${escapeHtml(item.product?.name || 'item')}">+</button>
              </div>
            </div>

            <p class="col-span-2 text-right text-base font-bold tracking-tight text-black sm:col-span-1 sm:text-right sm:text-lg">${lineTotal.toLocaleString()} TZS</p>
          </article>
        `;
      }).join('');

      page.querySelector('[data-cart-loading]')?.classList.add('hidden');
      page.querySelector('[data-cart-loading]')?.setAttribute('aria-busy', 'false');
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
      await render(true);
    } catch (e) {
      page.querySelector('[data-cart-loading]')?.classList.add('hidden');
      page.querySelector('[data-cart-loading]')?.setAttribute('aria-busy', 'false');
      page.querySelector('[data-cart-empty]')?.classList.add('hidden');
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
          if (!(await window.kpConfirm?.('Remove all items from your bag?', { title: 'Clear your bag?' }))) return;
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

    const authError = form.querySelector('[data-auth-error]');
    const clearAuthError = () => {
      authError?.classList.add('hidden');
      authError?.removeAttribute('role');
      form.querySelectorAll('[aria-invalid=\"true\"]').forEach(input => {
        input.removeAttribute('aria-invalid');
        input.removeAttribute('aria-describedby');
      });
    };
    const showAuthError = (message) => {
      if (!authError) return;
      authError.textContent = message || 'Something went wrong. Please try again.';
      authError.classList.remove('hidden');
      authError.setAttribute('role', 'alert');
      const activeStep = form.querySelector('[data-otp-step]:not(.hidden)') || form.querySelector('[data-primary-step]:not(.hidden)');
      const input = activeStep?.querySelector('input:not([type=\"hidden\"]):not([disabled])');
      if (input) {
        input.setAttribute('aria-invalid', 'true');
        input.setAttribute('aria-describedby', 'kp-auth-error');
        input.focus();
      }
    };
    authError?.setAttribute('id', 'kp-auth-error');

    // Auth is a two-step flow. Native browser validation must not block the
    // submit handler while the OTP step is still hidden. Validation is handled
    // by the API/FormRequest on each step instead.
    form.noValidate = true;

    form.addEventListener(
      'submit',
      async e => {
        e.preventDefault();
        clearAuthError();

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
          showAuthError(err.message);
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

      if (!signedIn()) {
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
    if (!signedIn()) { location.href = '/login'; return; }

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
        <article class="rounded-2xl border border-black bg-white p-5 shadow-sm sm:p-6">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-black">${escapeHtml(r.request_type || 'request')}</p>
              <h2 class="mt-1 text-sm font-bold text-black">Order #${escapeHtml(r.order_number || '')}</h2>
              <p class="mt-1 text-xs text-black">${r.created_at ? new Date(r.created_at).toLocaleString() : ''}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold capitalize ${returnStatusClass(r.status)}">${escapeHtml(String(r.status || '').replaceAll('_', ' '))}</span>
          </div>
          <div class="mt-4 border-t border-black pt-4">
            ${(r.items || []).map(i => `<p class="text-sm text-black">${escapeHtml(i.product_name)}${i.size ? ` · Size ${escapeHtml(i.size)}` : ''} · Qty ${Number(i.quantity || 0)}</p>`).join('')}
            <p class="mt-1 text-xs text-black">Reason: ${escapeHtml(String(r.reason || '').replaceAll('_', ' '))}</p>
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
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-black p-3 hover:bg-white">
              <input type="checkbox" name="item_ids[]" value="${escapeHtml(i.id)}" class="mt-1 rounded border-black" checked>
              <span class="min-w-0"><span class="block text-sm font-medium text-black">${escapeHtml(i.name)}</span><span class="mt-0.5 block text-xs text-black">${escapeHtml([i.size, i.color].filter(Boolean).join(' · ') || 'Standard')} · Qty ${Number(i.quantity || 0)}</span></span>
            </label>`).join('') : '<p class="text-sm text-black">No items found for this order.</p>';
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

    if (!signedIn()) {
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
        actions.innerHTML = `<a href="/account/orders/${encodeURIComponent(order.order_number)}" class="inline-flex items-center justify-center gap-2 rounded-full border border-black px-6 py-3 text-sm font-medium text-black hover:bg-white">View order</a>${pending ? `<button type="button" data-cancel-pending-order class="inline-flex items-center justify-center gap-2 rounded-full border border-rose-200 px-6 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50">Cancel order</button>` : ''}`;
        actions.querySelector('[data-cancel-pending-order]')?.addEventListener('click', async e => {
          const button=e.currentTarget; if (!(await window.kpConfirm?.('Cancel this order?', { title: 'Cancel this order?' }))) return; button.disabled=true; button.textContent='Cancelling…';
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

    if (!signedIn()) {
      location.href = '/login';
      return;
    }

    try {
      const orders = await api('/orders');
      const list = page.querySelector('[data-orders-list]');
      const data = Array.isArray(orders.data) ? orders.data : [];

      if (!data.length) {
        list.innerHTML = `
          <div class="rounded-2xl border border-emerald-950/10 bg-emerald-50/40 px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15">
              ${icon('bag', 24)}
            </div>
            <h2 class="mt-5 text-lg font-semibold text-black">No orders yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-black">Your completed and pending purchases will appear here.</p>
            <a href="/shop" class="button-dark mt-6">Start shopping</a>
          </div>`;
        return;
      }

      list.innerHTML = data.map(o => `
        <article class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm transition hover:border-black hover:shadow-md">
          <div class="flex flex-col gap-5 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-black">Order</p>
                <h2 class="mt-1 text-base font-bold text-black">${escapeHtml(o.order_number)}</h2>
                <p class="mt-1 text-sm text-black">${o.created_at ? new Date(o.created_at).toLocaleString() : 'Date unavailable'}</p>
              </div>
              <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${orderStatusClass(o.status)}">${escapeHtml(formatOrderStatus(o.status))}</span>
            </div>

            <div class="grid gap-3 border-t border-black pt-5 sm:grid-cols-3">
              <div>
                <p class="text-xs text-black">Items</p>
                <p class="mt-1 text-sm font-semibold text-black">${Array.isArray(o.items) ? o.items.reduce((n, i) => n + Number(i.quantity || 0), 0) : '—'}</p>
              </div>
              <div>
                <p class="text-xs text-black">Payment</p>
                <p class="mt-1 text-sm font-semibold capitalize text-black">${escapeHtml(formatOrderStatus(o.payment_status))}</p>
              </div>
              <div>
                <p class="text-xs text-black">Total</p>
                <p class="mt-1 text-sm font-bold text-black">${Number(o.total || 0).toLocaleString()} TZS</p>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-black pt-5">
              <a href="/account/orders/${encodeURIComponent(o.order_number)}" class="inline-flex items-center justify-center rounded-xl border border-black px-4 py-2.5 text-sm font-semibold text-black transition hover:border-black hover:bg-white">View details</a>
              ${String(o.status || '').toLowerCase() === 'pending_payment' ? `<a href="/orders/${encodeURIComponent(o.order_number)}" class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black">Continue payment</a>` : ''}
            </div>
          </div>
        </article>
      `).join('');
    } catch (e) {
      page.querySelector('[data-orders-list]').innerHTML = `
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-sm text-red-700" role="alert">${escapeHtml(e.message || 'Unable to load your orders.')}</div>`;
    }
  };

  const bootOrderDetail = async () => {
    const page = document.querySelector('[data-order-detail]');
    if (!page) return;

    if (!signedIn()) {
      location.href = '/login';
      return;
    }

    try {
      const o = (await api(`/orders/${encodeURIComponent(page.dataset.orderNumber)}`)).data;
      const items = Array.isArray(o.items) ? o.items : [];
      const canCancel = String(o.status || '').toLowerCase() === 'pending_payment';

      page.querySelector('[data-order-detail-content]').innerHTML = `
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
          <section class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm">
            <div class="border-b border-black p-5 sm:p-6">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-black">Order summary</p>
                  <p class="mt-1 text-sm text-black">${o.created_at ? new Date(o.created_at).toLocaleString() : ''}</p>
                </div>
                <span class="rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${orderStatusClass(o.status)}">${escapeHtml(formatOrderStatus(o.status))}</span>
              </div>
            </div>

            <div class="divide-y divide-black">
              ${items.length ? items.map(i => `
                <div class="flex items-start justify-between gap-5 p-5 sm:p-6">
                  <div class="min-w-0">
                    <p class="font-semibold text-black">${escapeHtml(i.name)}</p>
                    <p class="mt-1 text-sm text-black">${escapeHtml([i.size, i.color].filter(Boolean).join(' · ') || 'Standard')} · Qty ${Number(i.quantity || 0)}</p>
                    ${i.sku ? `<p class="mt-1 text-xs text-black">SKU ${escapeHtml(i.sku)}</p>` : ''}
                  </div>
                  <p class="shrink-0 text-sm font-bold text-black">${Number(i.line_total || 0).toLocaleString()} TZS</p>
                </div>`).join('') : '<div class="flex flex-col items-center px-6 py-12 text-center"><div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">' + icon('bag', 22) + '</div><p class="mt-4 text-sm font-semibold text-black">No order items available</p><p class="mt-1 max-w-sm text-sm text-black">This order does not currently contain any item details.</p></div>'}
            </div>
          </section>

          <aside class="space-y-4">
            <div class="rounded-2xl border border-black bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-black">Payment</p>
              <p class="mt-2 text-sm font-semibold capitalize text-black">${escapeHtml(formatOrderStatus(o.payment_status))}</p>
              <div class="mt-5 space-y-3 border-t border-black pt-5 text-sm">
                <div class="flex justify-between gap-4"><span class="text-black">Subtotal</span><strong>${Number(o.subtotal || 0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4"><span class="text-black">Delivery</span><strong>${Number(o.delivery_fee || 0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4 border-t border-black pt-3 text-base"><span class="font-semibold">Total</span><strong>${Number(o.total || 0).toLocaleString()} TZS</strong></div>
              </div>
            </div>

            <div class="rounded-2xl border border-black bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-black">Delivery</p>
              <p class="mt-2 text-sm font-semibold text-black">${escapeHtml(o.customer?.name || 'Customer')}</p>
              <p class="mt-1 text-sm leading-6 text-black">${escapeHtml([o.delivery?.address, o.delivery?.city].filter(Boolean).join(', ') || 'Delivery address unavailable')}</p>
              ${o.customer?.phone ? `<p class="mt-3 text-sm text-black">${escapeHtml(o.customer.phone)}</p>` : ''}
              ${o.delivery?.provider || o.delivery?.tracking_number ? `<div class="mt-4 border-t border-black pt-4 text-sm">${o.delivery?.provider ? `<p><span class="text-black">Provider:</span> <strong>${escapeHtml(o.delivery.provider)}</strong></p>` : ''}${o.delivery?.tracking_number ? `<p class="mt-1"><span class="text-black">Tracking:</span> <strong>${escapeHtml(o.delivery.tracking_number)}</strong></p>` : ''}</div>` : ''}
              ${o.delivery?.shipped_at || o.delivery?.delivered_at ? `<div class="mt-4 space-y-1 text-xs text-black">${o.delivery?.shipped_at ? `<p>Shipped ${new Date(o.delivery.shipped_at).toLocaleString()}</p>` : ''}${o.delivery?.delivered_at ? `<p>Delivered ${new Date(o.delivery.delivered_at).toLocaleString()}</p>` : ''}</div>` : ''}
            </div>

            ${canCancel ? `<button data-cancel-order type="button" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Cancel order</button>` : ''}
          </aside>
        </div>
      `;

      page.querySelector('[data-cancel-order]')?.addEventListener('click', async event => {
        const button = event.currentTarget;
        if (!(await window.kpConfirm?.('Cancel this order?', { title: 'Cancel this order?' }))) return;
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
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-sm text-red-700" role="alert">
          <p>${escapeHtml(e.message || 'Unable to load this order.')}</p>
          <button type="button" data-order-detail-retry class="kp-button-secondary mt-4">Try again</button>
        </div>`;
      page.querySelector('[data-order-detail-retry]')?.addEventListener('click', () => bootOrderDetail());
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
                <div class="rounded-2xl border border-black p-5">
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

                  <p class="mt-3 text-sm leading-6 text-black">
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
          '<p class="text-sm text-black">No addresses saved.</p>';
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
  bootAccount();
  bootReturns();
  bootOrders();
  bootOrderStatus();
  bootOrderDetail();
  bootAddresses();
  bootLogout();
})();
