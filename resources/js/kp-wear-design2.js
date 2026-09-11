(() => {
  const qs = (s, r = document) => r.querySelector(s);
  const qsa = (s, r = document) => [...r.querySelectorAll(s)];
  const esc = (v) => String(v ?? '').replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
  const tzs = n => `TZS ${Number(n || 0).toLocaleString()}`;
  const api = (url, opts = {}) => fetch(url, { ...opts, headers: { Accept:'application/json', 'Content-Type':'application/json', ...(opts.headers||{}) } });

  const nav = qs('[data-kp-design2-nav]');
  if (nav) {
    const onScroll = () => nav.classList.toggle('is-scrolled', window.scrollY > 20);
    window.addEventListener('scroll', onScroll, { passive:true }); onScroll();
    qs('[data-d2-search-toggle]', nav)?.addEventListener('click', () => { const el=qs('[data-d2-search]',nav); el.hidden=!el.hidden; if(!el.hidden) qs('[data-d2-search-input]',nav)?.focus(); });
    qs('[data-d2-menu-toggle]', nav)?.addEventListener('click', () => { const el=qs('[data-d2-mobile-menu]',nav); el.hidden=!el.hidden; });
  }
  const toast = (message) => { let el=qs('[data-kp-d2-toast]'); if(!el){el=document.createElement('div');el.className='kp-d2-toast';el.dataset.kpD2Toast='';document.body.appendChild(el);} el.textContent=message;el.classList.add('is-visible');clearTimeout(el._t);el._t=setTimeout(()=>el.classList.remove('is-visible'),2200); };
  window.KipanyaDesign2 = { toast };

  const renderCard = (p) => {
    const vars = p.variants || [], inStock = vars.filter(v=>v.in_stock), out = vars.length > 0 && !inStock.length;
    const sale = Number(p.compare_at_price||0)>Number(p.price||0);
    return `<article class="kp-d2-card"><a href="/wear/products/${encodeURIComponent(p.slug)}" class="kp-d2-card-media"><img src="${esc(p.image)}" alt="${esc(p.name)}" loading="lazy">${p.badge?`<span class="kp-d2-card-badge">${esc(p.badge)}</span>`:''}${out?'<span class="kp-d2-card-stock">Sold Out</span>':''}</a><button class="kp-d2-card-action" type="button" data-d2-add data-product-id="${p.id}" aria-label="Add ${esc(p.name)} to bag" ${out?'disabled':''}><i class="ti ti-shopping-bag"></i></button><div class="kp-d2-card-meta"><div class="kp-d2-card-meta-top"><a class="kp-d2-card-name" href="/wear/products/${encodeURIComponent(p.slug)}">${esc(p.name)}</a><span class="kp-d2-price">${tzs(p.price)}${sale?` <s>${tzs(p.compare_at_price)}</s>`:''}</span></div><div class="kp-d2-card-category">${esc(p.category||'KP Wear')}</div></div></article>`;
  };
  const wireGridButtons = (grid, products) => qsa('[data-d2-add]',grid).forEach(btn=>btn.addEventListener('click',async e=>{e.preventDefault();const p=products.find(x=>String(x.id)===String(btn.dataset.productId));const vars=(p?.variants||[]).filter(v=>v.in_stock);if(!vars.length)return;if(vars.length>1){location.href=`/wear/products/${encodeURIComponent(p.slug)}`;return;}btn.disabled=true;const r=await window.KipanyaCart?.addItem(vars[0].id,1);btn.disabled=false;if(r?.ok)toast(`${p.name} added to bag.`);else toast('Could not add this item.');}));

  const home = qs('[data-d2-home]');
  if (home) {
    (async()=>{
      try {
        const [cr, pr] = await Promise.all([
          api('/api/v1/wear/categories'),
          api('/api/v1/wear/products?per_page=50')
        ]);
        const cats = (await cr.json()).data || [];
        const all = (await pr.json()).data || [];
        const featured = all.filter(p => p.is_featured).slice(0, 6);
        const newArrivals = all.slice(Math.max(0, all.length - 6));
        const picks = all.slice(6, 8);

        const categoryWrap = qs('[data-d2-home-categories]', home);
        if (categoryWrap) {
          categoryWrap.innerHTML = cats.map(c => {
            const sample = all.find(p => p.category === c.name);
            return `<a href="/wear/shop?category=${encodeURIComponent(c.slug)}" class="kp-d2-category-card">
              <img src="${esc(sample?.image || '')}" alt="${esc(c.name)}" loading="lazy">
              <span class="kp-d2-category-overlay"><strong>${esc(c.name)}</strong></span>
            </a>`;
          }).join('');
        }

        const renderHomeGrid = (selector, items, emptyText) => {
          const grid = qs(selector, home);
          if (!grid) return;
          grid.innerHTML = items.length
            ? items.map(renderCard).join('')
            : `<div class="kp-d2-empty" style="grid-column:1/-1">${esc(emptyText)}</div>`;
          wireGridButtons(grid, items);
        };

        renderHomeGrid('[data-d2-home-new-arrivals]', newArrivals, 'No new arrivals yet.');
        renderHomeGrid('[data-d2-home-featured-products]', featured, 'No featured products yet.');
        renderHomeGrid('[data-d2-home-picks]', picks, 'No additional picks yet.');
      } catch (_) {
        ['[data-d2-home-categories]', '[data-d2-home-new-arrivals]', '[data-d2-home-featured-products]', '[data-d2-home-picks]'].forEach(selector => {
          const el = qs(selector, home);
          if (el) el.innerHTML = '<div class="kp-d2-error" style="grid-column:1/-1">Could not load the collection. Please try again.</div>';
        });
      }
    })();
  }

  const catalog = qs('[data-d2-catalog]:not([data-d2-home-featured])');
  if (catalog) {
    const grid=qs('[data-d2-product-grid]',catalog), count=qs('[data-d2-count]',catalog), pills=qs('[data-d2-pills]',catalog), status=qs('[data-d2-status]',catalog), sort=qs('[data-d2-sort]',catalog);
    const params=new URLSearchParams(location.search), search=params.get('q')||''; let active=params.get('category')||'all', categories=[], products=[];
    const render=()=>{let list=[...products];if(active!=='all')list=list.filter(p=>String(p.category||'').toLowerCase().replace(/\s+/g,'-')===active);const q=search.trim().toLowerCase();if(q)list=list.filter(p=>`${p.name} ${p.description||''} ${p.category||''}`.toLowerCase().includes(q));switch(sort?.value){case'price-low':list.sort((a,b)=>a.price-b.price);break;case'price-high':list.sort((a,b)=>b.price-a.price);break;case'alpha':list.sort((a,b)=>String(a.name).localeCompare(String(b.name)));break;}grid.innerHTML=list.length?list.map(renderCard).join(''):'<div class="kp-d2-empty" style="grid-column:1/-1"><strong>No products found</strong><p>Try another category or search.</p></div>';if(count)count.textContent=`${list.length} ${list.length===1?'piece':'pieces'}`;wireGridButtons(grid,list);};
    const load=async()=>{try{const [cr,pr]=await Promise.all([api('/api/v1/wear/categories'),api('/api/v1/wear/products?per_page=50')]);categories=(await cr.json()).data||[];products=(await pr.json()).data||[];if(pills){pills.innerHTML=`<button type="button" class="kp-d2-pill${active==='all'?' is-active':''}" data-cat="all" aria-pressed="${active==='all'}">All</button>`+categories.map(c=>`<button type="button" class="kp-d2-pill${active===c.slug?' is-active':''}" data-cat="${esc(c.slug)}" aria-pressed="${active===c.slug}">${esc(c.name)}</button>`).join('');qsa('[data-cat]',pills).forEach(b=>b.addEventListener('click',()=>{active=b.dataset.cat;const u=new URL(location.href);if(active==='all')u.searchParams.delete('category');else u.searchParams.set('category',active);history.replaceState({},'',u);qsa('[data-cat]',pills).forEach(x=>{const on=x===b;x.classList.toggle('is-active',on);x.setAttribute('aria-pressed',on)});render();}));}render();}catch(_){status.innerHTML='<div class="kp-d2-error">Could not load products.</div>';}};
    sort?.addEventListener('change',render);load();
  }

  const productRoot=qs('[data-d2-product]');
  if(productRoot){let product;try{product=JSON.parse(qs('[data-d2-product-data]',productRoot)?.textContent||'{}');}catch{product=null;}if(product){const vars=product.variants||[],sizeWrap=qs('[data-d2-sizes]',productRoot),colorWrap=qs('[data-d2-colors]',productRoot),qtyEl=qs('[data-d2-qty]',productRoot);let qty=1,selectedSize='',selectedColor='';const visible=()=>vars.filter(v=>!selectedColor||v.color===selectedColor);const renderSizes=()=>{const names=[...new Set(vars.map(v=>v.size).filter(Boolean))];sizeWrap.innerHTML=names.map(s=>{const ok=visible().some(v=>v.size===s&&v.in_stock);return `<button type="button" class="kp-d2-option-btn${s===selectedSize?' is-selected':''}" data-size="${esc(s)}" ${ok?'':'disabled'}>${esc(s)}</button>`}).join('');qsa('[data-size]',sizeWrap).forEach(b=>b.onclick=()=>{selectedSize=b.dataset.size;renderSizes();update()});};const colors=[...new Set(vars.map(v=>v.color).filter(Boolean))];if(colors.length){colorWrap.hidden=false;qs('[data-d2-color-list]',colorWrap).innerHTML=colors.map(c=>`<button type="button" class="kp-d2-option-btn" data-color="${esc(c)}">${esc(c)}</button>`).join('');qsa('[data-color]',colorWrap).forEach(b=>b.onclick=()=>{selectedColor=b.dataset.color;qsa('[data-color]',colorWrap).forEach(x=>x.classList.toggle('is-selected',x===b));renderSizes();update();});}const match=()=>visible().find(v=>v.size===selectedSize&&v.in_stock);const add=qs('[data-d2-add-product]',productRoot);const update=()=>{const v=match();add.disabled=!v;add.textContent=v?'Add to bag':'Select a size';};qs('[data-d2-minus]',productRoot)?.addEventListener('click',()=>{qty=Math.max(1,qty-1);qtyEl.textContent=qty});qs('[data-d2-plus]',productRoot)?.addEventListener('click',()=>{qty=Math.min(99,qty+1);qtyEl.textContent=qty});add?.addEventListener('click',async()=>{const v=match();if(!v)return;add.disabled=true;const r=await window.KipanyaCart?.addItem(v.id,qty);add.disabled=false;if(r?.ok)toast(`${product.name} added to bag.`);else toast('Could not add this item.');});renderSizes();update();}
  }

  const cartRoot=qs('[data-d2-cart]');
  if(cartRoot&&window.KipanyaCart){const list=qs('[data-d2-cart-list]',cartRoot), subtotalEls=qsa('[data-d2-subtotal]',cartRoot), count=qs('[data-d2-cart-count]',cartRoot);const renderCart=async()=>{list.innerHTML='<div class="kp-d2-empty">Loading your bag…</div>';const r=await window.KipanyaCart.getCart(),cart=r.data,items=cart?.items||[];if(!items.length){list.innerHTML='<div class="kp-d2-empty"><strong>Your bag is empty.</strong><p>Browse KP Wear and add something you love.</p><a class="kp-d2-btn kp-d2-btn-dark" href="/wear/shop">Shop Wear</a></div>';subtotalEls.forEach(x=>x.textContent=tzs(0));if(count)count.textContent='0 items';return;}list.innerHTML=items.map(item=>`<div class="kp-d2-cart-item"><a class="kp-d2-cart-thumb" href="/wear/products/${esc(item.product.slug)}"><img src="${esc(item.product.image)}" alt="${esc(item.product.name)}" loading="lazy"></a><div><a class="kp-d2-cart-name" href="/wear/products/${esc(item.product.slug)}">${esc(item.product.name)}</a><div class="kp-d2-cart-meta">${esc(item.variant.size||'')}${item.variant.color?` · ${esc(item.variant.color)}`:''}</div><div class="kp-d2-cart-price">${tzs(item.unit_price)}</div></div><div class="kp-d2-cart-actions"><div class="kp-d2-qty"><button type="button" data-dec="${item.variant.id}">−</button><span>${item.quantity}</span><button type="button" data-inc="${item.variant.id}">+</button></div><button class="kp-d2-remove" type="button" data-remove="${item.variant.id}">Remove</button></div></div>`).join('');subtotalEls.forEach(x=>x.textContent=tzs(cart.subtotal));if(count)count.textContent=`${cart.item_count||0} ${(cart.item_count||0)===1?'item':'items'}`;qsa('[data-dec]',list).forEach(b=>b.onclick=async()=>{const n=Number(b.parentElement.querySelector('span').textContent);const v=await window.KipanyaCart.setItemQuantity(b.dataset.dec,Math.max(1,n-1));if(v.ok)renderCart()});qsa('[data-inc]',list).forEach(b=>b.onclick=async()=>{const n=Number(b.parentElement.querySelector('span').textContent);const v=await window.KipanyaCart.setItemQuantity(b.dataset.inc,n+1);if(v.ok)renderCart();else toast(v.raw?.message||'Could not update quantity.')});qsa('[data-remove]',list).forEach(b=>b.onclick=async()=>{const v=await window.KipanyaCart.removeItem(b.dataset.remove);if(v.ok)renderCart()});};renderCart();}
})();
