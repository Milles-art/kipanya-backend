(()=>{let e=`kp_api_token`,t=`kp_user`,n=`kp_guest_cart_token`,r=(e,t)=>localStorage.setItem(e,JSON.stringify(t)),i=e=>{let t=document.querySelector(`#toast`);t&&(t.textContent=e,t.classList.remove(`hidden`),clearTimeout(window.kpToast),window.kpToast=setTimeout(()=>t.classList.add(`hidden`),2400))};window.addEventListener(`kp:toast`,e=>{e.detail&&i(e.detail)});let a=e=>String(e??``).replaceAll(`&`,`&amp;`).replaceAll(`<`,`&lt;`).replaceAll(`>`,`&gt;`).replaceAll(`"`,`&quot;`).replaceAll(`'`,`&#039;`),o=()=>localStorage.getItem(e),s=()=>document.querySelector(`meta[name="kp-signed-in"]`)?.getAttribute(`content`)===`1`||!!o(),c=()=>localStorage.getItem(n),l=()=>{let e=c();if(!e){let t=new Uint8Array(32);crypto.getRandomValues(t),e=[...t].map(e=>e.toString(16).padStart(2,`0`)).join(``),localStorage.setItem(n,e)}return e},u=async(n,r={})=>{let a=new Headers(r.headers||{});a.set(`Accept`,`application/json`),r.body&&!(r.body instanceof FormData)&&a.set(`Content-Type`,`application/json`),o()&&a.set(`Authorization`,`Bearer ${o()}`),s()||a.set(`X-Guest-Cart-Token`,l());let c=await fetch(`/api/v1${n}`,{...r,headers:a,body:r.body&&!(r.body instanceof FormData)?JSON.stringify(r.body):r.body}),u=await c.text(),d=null;try{d=u?JSON.parse(u):null}catch{}if(c.status===401&&(localStorage.removeItem(e),localStorage.removeItem(t),!location.pathname.includes(`/login`)&&!location.pathname.includes(`/register`)&&i(`Please sign in to continue.`)),!c.ok){let e=d?.message||Object.values(d?.errors||{})?.flat?.()?.[0]||`Request failed (${c.status})`;throw Error(e)}return d},d=e=>{if(typeof e!=`string`)return null;let t=e.trim();return!t.startsWith(`/`)||t.startsWith(`//`)||/[\\]/.test(t)?null:t},f=e=>{let t=d(e?.querySelector(`input[name="redirect"]`)?.value);if(t)return t;try{let e=new URLSearchParams(location.search),t=d(e.get(`redirect`));if(t)return t}catch{}return`/account`},p=()=>{location.href=`/login?redirect=`+encodeURIComponent(location.pathname+location.search)},m=n=>{n?.token&&localStorage.setItem(e,n.token),n?.user&&r(t,n.user)},h=async()=>{try{let e=await u(`/cart`);document.querySelectorAll(`[data-cart-count]`).forEach(t=>{t.textContent=e?.data?.item_count??0,t.classList.toggle(`hidden`,!(e?.data?.item_count>0))})}catch{}if(s())try{let e=await u(`/wishlist`),t=(e?.data||[]).length;document.querySelectorAll(`[data-wishlist-count]`).forEach(e=>{e.textContent=t,e.classList.toggle(`hidden`,t===0)});let n=new Set((e?.data||[]).map(e=>String(e.product?.id??e.wear_product_id)));document.querySelectorAll(`[data-wishlist-product]`).forEach(e=>{n.has(String(e.dataset.wishlistProduct))&&(e.dataset.saved=`1`,e.innerHTML=g(`heart`),e.classList.add(`bg-rose-500`,`text-white`),e.classList.remove(`bg-white`,`text-black`))})}catch{}else document.querySelectorAll(`[data-wishlist-count]`).forEach(e=>{e.classList.add(`hidden`)})},g=(e,t=18,n=``)=>`
      <svg
        xmlns="http://www.w3.org/2000/svg"
        width="${t}"
        height="${t}"
        viewBox="0 0 24 24"
        fill="none"
        stroke="currentColor"
        stroke-width="1.8"
        stroke-linecap="round"
        stroke-linejoin="round"
        aria-hidden="true"
        ${n?`class="${n}"`:``}
      >
        ${{heart:`<path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78L12 21.23l8.84-8.84a5.5 5.5 0 0 0 0-7.78z"></path>`,bag:`<path d="M6 8h12l1 13H5L6 8Z"></path><path d="M9 8a3 3 0 0 1 6 0"></path>`,x:`<path d="M6 6l12 12M18 6 6 18"></path>`,clock:`<circle cx="12" cy="12" r="9"></circle><path d="M12 7v5l3 2"></path>`,star:`<path d="m12 3 2.78 5.63 6.22.9-4.5 4.39 1.06 6.2L12 17.2l-5.56 2.92 1.06-6.2L3 9.53l6.22-.9L12 3Z"></path>`,spinner:`<path d="M12 3a9 9 0 1 0 9 9"></path>`}[e]||``}
      </svg>
    `,_=async(e,t)=>{if(!e||e.dataset.kpBusy===`1`)return;let n=e.innerHTML,r=e.disabled;e.dataset.kpBusy=`1`,e.disabled=!0,e.classList.add(`kp-btn-busy`),e.innerHTML=g(`spinner`,Number(e.dataset.kpIconSize)||18,`kp-spin`);try{await t()}finally{e.dataset.kpBusy=`0`,e.disabled=r,e.classList.remove(`kp-btn-busy`),e.innerHTML=n}},v={black:`#000000`,white:`#f5f5f5`,navy:`#1e3a5f`,olive:`#5b6f4a`,burgundy:`#6b2c2c`,camel:`#c4a882`,gray:`#000000`,sage:`#9caf88`,sand:`#d7c4a3`},y=e=>String(e??``).trim().toLowerCase().replace(/[^a-z0-9]+/g,`-`).replace(/^-+|-+$/g,``),b=e=>(e.variants||[]).reduce((e,t)=>e+Number(t.stock||0),0),x=e=>String(e.badge||``).toLowerCase()===`new`,S=e=>e.compare_at_price!==null&&Number(e.compare_at_price)>Number(e.price),C=`/assets/wear/catalog/placeholder.svg`,w=e=>{let t=e?.image;if(!t)return C;try{let e=new URL(t,window.location.origin);return`${e.pathname}${e.search}`}catch{return t}},T=(e,t=8)=>{e&&(e.innerHTML=Array.from({length:t},()=>`
      <article class="min-w-0 animate-pulse">
        <div class="mb-4 aspect-square overflow-hidden rounded-2xl bg-white"></div>
        <div class="mb-2 h-3 w-20 rounded bg-white"></div>
        <div class="h-4 w-3/4 rounded bg-white"></div>
        <div class="mt-2 h-4 w-24 rounded bg-white"></div>
      </article>
    `).join(``))},E=(e,t)=>{if(e){if(e.innerHTML=t.map(e=>{let t=e.compare_at_price===null?null:Number(e.compare_at_price),n=Number(e.price||0),r=t!==null&&t>n,i=r?Math.round((t-n)/t*100):0,o=b(e),s=[...new Set((e.variants||[]).map(e=>e.color).filter(Boolean))],c=w(e),l=j(e),u=Number(e.rating||0),d=Number(e.review_count||e.reviewCount||0);return`
            <article
              class="kp-product-card group relative min-w-0"
              data-product-card
              data-product-id="${a(e.id)}"
            >
              <div
                class="relative mb-4 aspect-square overflow-hidden rounded-2xl bg-white"
              >
                <a
                  href="/product/${encodeURIComponent(e.slug)}"
                  class="block h-full w-full"
                >
                  <img
                    src="${a(c)}"
                    alt="${a(e.name||`Product`)}"
                    loading="lazy"
                    onerror="this.onerror=null;this.src='/assets/wear/catalog/placeholder.svg'"
                    class="h-full w-full object-contain p-4 transition duration-500 ease-out group-hover:scale-105"
                  >
                </a>

                <div
                  class="pointer-events-none absolute left-3 top-3 flex flex-col gap-1.5"
                >
                  ${r?`
                        <span class="w-fit rounded-full bg-rose-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          -${i}%
                        </span>
                      `:``}

                  ${x(e)?`
                        <span class="w-fit rounded-full bg-emerald-600 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          New
                        </span>
                      `:!r&&e.badge?`
                        <span class="w-fit rounded-full bg-black px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                          ${a(e.badge)}
                        </span>
                      `:``}
                </div>

                ${e.availability===`out_of_stock`?`
                      <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-black px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                        Sold out
                      </span>
                    `:o>0&&o<10?`
                      <span class="pointer-events-none absolute right-3 top-3 rounded-full bg-amber-500 px-2.5 py-1 text-[11px] font-bold text-white shadow-sm">
                        Low stock
                      </span>
                    `:``}

                <button
                  type="button"
                  data-wishlist-product="${a(e.id)}"
                  data-saved="0"
                  class="absolute bottom-3 left-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-black opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-rose-500 hover:text-white"
                  aria-label="Toggle wishlist for ${a(e.name||`product`)}"
                >
                  ${g(`heart`)}
                </button>

                <button
                  type="button"
                  data-quick-add="${a(e.id)}"
                  data-quick-variant="${a(l?.id||``)}"
                  ${e.availability===`out_of_stock`?`disabled`:``}
                  class="absolute bottom-3 right-3 flex h-10 w-10 translate-y-2 items-center justify-center rounded-full bg-white text-black opacity-0 shadow-lg transition duration-300 group-hover:translate-y-0 group-hover:opacity-100 hover:bg-emerald-600 hover:text-white disabled:cursor-not-allowed disabled:opacity-50"
                  aria-label="Add ${a(e.name||`product`)} to bag"
                >
                  ${g(`bag`)}
                </button>
              </div>

              <div>
                <p class="mb-1 text-xs text-black">
                  ${a(e.category||``)}
                </p>

                ${u>0?`
                      <div class="mb-1 flex items-center gap-1 text-xs text-black">
                        <span class="inline-flex text-amber-400">
                          ${g(`star`,13)}
                        </span>

                        <span>
                          ${u.toFixed(1)}
                          ${d?` (${d})`:``}
                        </span>
                      </div>
                    `:``}

                <a
                  href="/product/${encodeURIComponent(e.slug)}"
                  class="line-clamp-1 text-sm font-medium text-black transition-colors hover:text-emerald-700"
                >
                  ${a(e.name||``)}
                </a>

                <div class="mt-1 flex items-center gap-2">
                  <span class="text-sm font-semibold text-black">
                    ${n.toLocaleString()} TZS
                  </span>

                  ${r?`
                        <span class="text-xs text-black line-through">
                          ${t.toLocaleString()} TZS
                        </span>
                      `:``}
                </div>

                ${s.length?`
                      <div class="mt-2.5 flex items-center gap-1.5">
                        ${s.slice(0,5).map(e=>`
                              <span
                                class="h-3.5 w-3.5 rounded-full border border-black shadow-sm"
                                style="background-color:${v[String(e).toLowerCase()]||`#000000`}"
                                title="${a(e)}"
                              ></span>
                            `).join(``)}

                        ${s.length>5?`
                              <span class="text-[11px] text-black">
                                +${s.length-5}
                              </span>
                            `:``}
                      </div>
                    `:``}
              </div>
            </article>
          `}).join(``),t.length>=12){let t=document.createElement(`div`);t.setAttribute(`data-catalog-editorial-slider`,``),t.setAttribute(`aria-label`,`KP Wear editorial feature`),t.setAttribute(`role`,`region`),t.className=`col-span-full`;let n=e.children[11];n&&n.insertAdjacentElement(`afterend`,t)}document.dispatchEvent(new Event(`kp:grid-rendered`))}},D=async(e=``)=>(await u(`/wear/products${e?`?${e}`:``}`))?.data||[],O=async()=>(await u(`/wear/storefront`))?.data||null,k=async()=>(await u(`/wear/categories`))?.data||[],A=async()=>(await u(`/wear/collections`))?.data||[],j=e=>(e.variants||[]).find(e=>e.in_stock)||e.variants?.[0]||null,M=async(e,t=1)=>{let n=Number(t);if(!Number.isInteger(n)||n<1||n>50)throw Error(`Quantity must be between 1 and 50.`);await u(`/cart/items`,{method:`POST`,body:{variant_id:Number(e),quantity:n}}),i(`Added to your bag`),await h()},N=async(e,t=null)=>{if(!s()){p();return}t?.dataset?.saved===`1`?(await u(`/wishlist/${e}`,{method:`DELETE`}),t&&(t.dataset.saved=`0`,t.innerHTML=g(`heart`),t.classList.remove(`bg-rose-500`,`text-white`),t.classList.add(`bg-white`,`text-black`)),i(`Removed from wishlist`)):(await u(`/wishlist`,{method:`POST`,body:{product_id:Number(e)}}),t&&(t.dataset.saved=`1`,t.innerHTML=g(`heart`),t.classList.add(`bg-rose-500`,`text-white`),t.classList.remove(`bg-white`,`text-black`)),i(`Saved to wishlist`)),await h()},P=async()=>{let e=document.querySelector(`[data-home-featured]`);if(!e)return;T(e,8);let t=e=>{let t=document.querySelector(`[data-home-collections]`);if(t){if(!e.length){t.innerHTML=`<div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-dashed border-black py-16 text-center text-sm text-black">No featured collections are available yet.</div>`;return}t.innerHTML=e.slice(0,3).map(e=>{let t=e.cover||e.image||`/assets/wear/catalog/placeholder.svg`;return`
          <a
            href="/collections/${encodeURIComponent(e.slug)}"
            class="group relative overflow-hidden rounded-2xl bg-white aspect-[4/5]"
          >
            <img
              src="${a(t)}"
              alt="${a(e.name||`Collection`)}"
              class="absolute inset-0 h-full w-full object-cover object-top transition duration-700 group-hover:scale-[1.035]"
              loading="lazy"
              onerror="this.onerror=null;this.src='/assets/wear/catalog/placeholder.svg'"
            >
            <div class="absolute inset-0 bg-gradient-to-t from-black/75 via-black/15 to-transparent"></div>
            <div class="absolute inset-x-0 bottom-0 p-5 text-white sm:p-6">
              <p class="text-[10px] font-bold uppercase tracking-[0.22em] text-white/65">
                ${Number(e.product_count||0)} products
              </p>
              <h3 class="mt-1 text-xl font-semibold tracking-tight sm:text-2xl">
                ${a(e.name||``)}
              </h3>
              ${e.description?`
                <p class="mt-2 max-w-md text-sm leading-6 text-white/70">
                  ${a(e.description)}
                </p>
              `:``}
            </div>
          </a>
        `}).join(``)}},n=e=>{if(!e)return;let t=document.querySelector(`.kp-hero-section`);if(t&&e.is_active===!1){t.classList.add(`hidden`);return}let n=document.querySelector(`[data-storefront-hero-eyebrow]`),r=document.querySelector(`[data-storefront-hero-title]`),i=document.querySelector(`[data-storefront-hero-description]`),a=document.querySelector(`[data-storefront-hero-cta]`),o=document.querySelector(`[data-storefront-hero-cta-label]`),s=document.querySelector(`[data-storefront-hero-image]`);n&&e.eyebrow&&(n.textContent=e.eyebrow),r&&e.title&&(r.textContent=e.title),i&&(i.textContent=e.description||``,i.classList.toggle(`hidden`,!e.description)),a&&(e.cta_url&&(a.href=e.cta_url),e.cta_label&&(o?o.textContent=e.cta_label:a.textContent=e.cta_label)),s&&e.image_desktop&&(s.src=e.image_desktop),s&&e.image_mobile&&(s.dataset.mobileSrc=e.image_mobile)};try{let[r,i]=await Promise.all([O(),k()]),o=Array.isArray(r?.homepage?.featured_products)?r.homepage.featured_products:[],s=o.length?o:await D(`per_page=50`).then(e=>Array.isArray(e?.data)?e.data:[]);E(e,s.slice(0,8));let c=Array.isArray(r?.homepage?.featured_collections)?r.homepage.featured_collections:[];t(c.length?c:await A()),n(r?.hero);let l=document.querySelector(`[data-home-categories]`);if(l){let e=i.map(e=>({name:e.name,slug:e.slug||y(e.name)})),t=[{bg:`bg-orange-50`,border:`border-orange-200 hover:border-orange-400`,title:`text-orange-900`,sub:`text-orange-600`},{bg:`bg-blue-50`,border:`border-blue-200 hover:border-blue-400`,title:`text-blue-900`,sub:`text-blue-600`},{bg:`bg-emerald-50`,border:`border-emerald-200 hover:border-emerald-400`,title:`text-emerald-900`,sub:`text-emerald-600`},{bg:`bg-rose-50`,border:`border-rose-200 hover:border-rose-400`,title:`text-rose-900`,sub:`text-rose-600`},{bg:`bg-violet-50`,border:`border-violet-200 hover:border-violet-400`,title:`text-violet-900`,sub:`text-violet-600`}];l.innerHTML=e.map((e,n)=>{let r=t[n%t.length];return`
            <a
              href="/category/${encodeURIComponent(e.slug)}"
              class="group rounded-xl border ${r.border} ${r.bg} p-3.5 transition hover:-translate-y-0.5"
            >
              <p class="text-xs font-semibold ${r.title} sm:text-sm">
                ${a(e.name)}
              </p>
              <p class="mt-0.5 text-[11px] ${r.sub} sm:text-xs">
                Shop collection
              </p>
            </a>
          `}).join(``)}}catch(t){e.innerHTML=``;let n=document.querySelector(`[data-home-collections]`);n&&(n.innerHTML=`<div class="sm:col-span-2 lg:col-span-3 rounded-2xl border border-red-100 bg-red-50 py-12 text-center text-sm text-red-700">Unable to load featured store content right now.</div>`),i(t.message||`Unable to load store content.`)}},F=async()=>{let e=document.querySelector(`[data-catalog-grid]`);if(!e)return;let t=document.querySelector(`[data-catalog-count]`);t&&(t.textContent=``);let n=document.querySelector(`[data-catalog-search]`),r=document.querySelector(`[data-catalog-sort]`),o=document.querySelector(`[data-catalog-categories]`);document.querySelector(`[data-catalog-prices]`);let s=document.querySelector(`[data-catalog-sale-toggle]`),c=document.querySelector(`[data-catalog-empty]`);document.querySelector(`[data-catalog-empty-clear]`);let l=document.querySelector(`[data-catalog-clear]`),d=document.querySelector(`[data-catalog-filter-count]`),f=document.querySelector(`[data-catalog-mobile-panel]`),p=document.querySelector(`[data-catalog-mobile-content]`),m=location.pathname.split(`/`).filter(Boolean),h=m[0]===`collections`?decodeURIComponent(m[1]||``):``,g={products:[],collection:h,category:(m[0]===`category`?decodeURIComponent(m[1]||``):``)||new URLSearchParams(location.search).get(`category`)||``,search:new URLSearchParams(location.search).get(`q`)||``,price:`all`,saleOnly:!1,sort:`featured`},_,v=null,b=()=>g.price===`under-50000`?{price_max:49999}:g.price===`50000-150000`?{price_min:5e4,price_max:15e4}:g.price===`over-150000`?{price_min:150001}:{},x=t=>{e.setAttribute(`aria-busy`,t?`true`:`false`),t&&T(e,8)},C=async()=>{v&&v.abort(),v=new AbortController,x(!0);let n=new URLSearchParams;n.set(`per_page`,`24`),n.set(`page`,String(g.page||1)),g.category&&!g.collection&&n.set(`category`,g.category),g.search&&n.set(`q`,g.search),g.saleOnly&&n.set(`sale`,`1`),g.sort&&n.set(`sort`,g.sort),Object.entries(b()).forEach(([e,t])=>n.set(e,String(t)));try{let r=g.collection?await u(`/wear/collections/${encodeURIComponent(g.collection)}`):await u(`/wear/products?${n.toString()}`,{signal:v.signal}),i=r?.data||{};if(g.collection){let e=Array.isArray(i.products)?i.products:[];g.products=e.filter(e=>{if(g.search){let t=g.search.toLowerCase();if(!`${e.name||``} ${e.description||``} ${e.category||``}`.toLowerCase().includes(t))return!1}return!(g.price===`under-50000`&&Number(e.price||0)>=5e4||g.price===`50000-150000`&&(Number(e.price||0)<5e4||Number(e.price||0)>15e4)||g.price===`over-150000`&&Number(e.price||0)<=15e4||g.saleOnly&&!S(e))}),g.sort===`price-asc`?g.products.sort((e,t)=>Number(e.price)-Number(t.price)):g.sort===`price-desc`?g.products.sort((e,t)=>Number(t.price)-Number(e.price)):g.sort===`newest`?g.products.sort((e,t)=>Number(t.id)-Number(e.id)):g.products.sort((e,t)=>Number(t.is_featured)-Number(e.is_featured));let t=document.querySelector(`.kp-shop-page h1`),n=document.querySelector(`.kp-shop-page [data-catalog-count]`);t&&(t.textContent=i.name||`Collection`),n&&(n.textContent=i.description||``)}else g.products=Array.isArray(i)?i:[];E(e,g.products);let a=g.collection?g.products.length:Number(r?.meta?.total??g.products.length);t&&(t.textContent=`${a} ${a===1?`product`:`products`} found`),c?.classList.toggle(`hidden`,g.products.length>0),c?.classList.toggle(`flex`,g.products.length===0);let o=document.querySelector(`[data-catalog-pagination]`);if(o){let e=Number(r?.meta?.current_page||1),t=Number(r?.meta?.last_page||1);o.innerHTML=!g.collection&&t>1?`
            <div class="mt-10 flex items-center justify-center gap-3">
              <button type="button" data-catalog-page="${Math.max(1,e-1)}" ${e<=1?`disabled`:``} class="rounded-xl border border-black px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Previous</button>
              <span class="text-sm text-black">Page ${e} of ${t}</span>
              <button type="button" data-catalog-page="${Math.min(t,e+1)}" ${e>=t?`disabled`:``} class="rounded-xl border border-black px-4 py-2 text-sm font-semibold disabled:cursor-not-allowed disabled:opacity-40">Next</button>
            </div>`:``}}catch(n){if(n.name===`AbortError`)return;e.innerHTML=``,c?.classList.remove(`hidden`),c?.classList.add(`flex`),t&&(t.textContent=``),i(n.message||`Unable to load products.`)}finally{x(!1)}},w=()=>{g.page=1;let e=+!!g.category+(g.price===`all`?0:1)+ +!!g.saleOnly+ +!!g.search;d?.classList.toggle(`hidden`,e===0),d?.classList.toggle(`inline-flex`,e>0),d&&(d.textContent=String(e)),l?.classList.toggle(`hidden`,e===0),s?.classList.toggle(`bg-emerald-600`,g.saleOnly),s?.classList.toggle(`bg-white`,!g.saleOnly),s?.setAttribute(`aria-pressed`,g.saleOnly?`true`:`false`),s?.querySelector(`span`)?.classList.toggle(`translate-x-5`,g.saleOnly),s?.querySelector(`span`)?.classList.toggle(`translate-x-0`,!g.saleOnly),document.querySelectorAll(`[data-catalog-categories] [data-category]`).forEach(e=>{let t=y(e.dataset.category)===y(g.category||``);e.classList.toggle(`bg-emerald-50`,t),e.classList.toggle(`text-emerald-700`,t),e.classList.toggle(`font-medium`,t),e.classList.toggle(`text-black`,!t)}),clearTimeout(_),_=setTimeout(C,g.search?250:0)},D=await k();if(r&&!h)try{let e=await O(),t=e?.shop?.default_sort||`featured`;[...r.options].some(e=>e.value===t)&&(g.sort=t,r.value=t);let n=document.querySelector(`[data-storefront-shop-banner]`),i=document.querySelector(`[data-storefront-shop-banner-title]`),a=document.querySelector(`[data-storefront-shop-banner-description]`),o=document.querySelector(`[data-storefront-shop-banner-image]`),s=e?.shop?.banner;n&&s?.is_active&&(n.classList.remove(`hidden`),i&&(i.textContent=s.title||``),a&&(a.textContent=s.description||``,a.classList.toggle(`hidden`,!s.description)),o&&s.image&&(o.src=s.image,o.alt=s.title||`KP Wear shop banner`))}catch{}n&&(n.value=g.search),o&&(o.innerHTML=[`<button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>`,...D.map(e=>`<button type="button" data-category="${a(e.slug||y(e.name))}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${a(e.name)}</button>`)].join(``)),p&&(p.innerHTML=`<div class="space-y-8"><div><div class="mb-4 flex items-center justify-between"><h3 class="text-sm font-semibold uppercase tracking-wider text-black">Category</h3><button type="button" data-catalog-clear-mobile class="text-xs font-medium text-emerald-600">Clear all</button></div><div data-mobile-categories class="space-y-1"><button type="button" data-category="" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Products</button>${D.map(e=>`<button type="button" data-category="${a(e.slug||y(e.name))}" class="w-full rounded-lg px-3 py-2 text-left text-sm">${a(e.name)}</button>`).join(``)}</div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-black">Price Range</h3><div class="space-y-1"><button type="button" data-price="all" class="w-full rounded-lg px-3 py-2 text-left text-sm">All Prices</button><button type="button" data-price="under-50000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Under 50,000 TZS</button><button type="button" data-price="50000-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">50,000 – 150,000 TZS</button><button type="button" data-price="over-150000" class="w-full rounded-lg px-3 py-2 text-left text-sm">Over 150,000 TZS</button></div></div><div><h3 class="mb-4 text-sm font-semibold uppercase tracking-wider text-black">Special</h3><label class="flex items-center gap-3"><button type="button" data-catalog-sale-toggle-mobile class="relative h-6 w-11 rounded-full bg-white"><span class="absolute left-0.5 top-0.5 h-5 w-5 rounded-full bg-white shadow-sm transition-transform"></span></button><span class="text-sm text-black">On Sale Only</span></label></div></div>`),document.addEventListener(`click`,e=>{let t=e.target.closest(`[data-category]`),r=e.target.closest(`[data-price]`),i=e.target.closest(`[data-catalog-clear],[data-catalog-clear-mobile],[data-catalog-empty-clear],[data-catalog-mobile-clear]`),a=e.target.closest(`[data-catalog-sale-toggle-mobile]`),o=e.target.closest(`[data-catalog-page]`),s=e.target.closest(`[data-catalog-mobile-apply]`);t&&(g.category=t.dataset.category||``,w(),f?.classList.add(`hidden`)),r&&(g.price=r.dataset.price||`all`,w()),i&&(g.category=``,g.search=``,g.price=`all`,g.saleOnly=!1,n&&(n.value=``),w()),a&&(g.saleOnly=!g.saleOnly,w()),s&&(w(),f?.classList.add(`hidden`)),o&&!o.disabled&&(g.page=Number(o.dataset.catalogPage),C(),window.scrollTo({top:0,behavior:`smooth`}))}),n?.addEventListener(`input`,()=>{g.search=n.value.trim(),w()}),r?.addEventListener(`change`,()=>{g.sort=r.value,g.page=1,C()}),s?.addEventListener(`click`,()=>{g.saleOnly=!g.saleOnly,w()}),document.querySelector(`[data-catalog-search-clear]`)?.addEventListener(`click`,()=>{g.search=``,n&&(n.value=``),w()}),document.querySelector(`[data-catalog-filter-toggle]`)?.addEventListener(`click`,()=>f?.classList.remove(`hidden`)),document.querySelector(`[data-catalog-filter-close]`)?.addEventListener(`click`,()=>f?.classList.add(`hidden`)),f?.addEventListener(`click`,e=>{e.target===f&&f.classList.add(`hidden`)}),await C()},I=async()=>{let e=document.querySelector(`[data-product-page]`);if(!e)return;let t=e.querySelector(`[data-product-loading]`),n=e.querySelector(`[data-product-content]`),r=e.querySelector(`[data-product-load-error]`),o=e.querySelector(`[data-product-load-error-message]`),c=e.dataset.slug;try{let r=(await u(`/wear/products/${encodeURIComponent(c)}`))?.data;if(!r)throw Error(`Product not found`);let o=r.variants||[],l=j(r),d=o.reduce((e,t)=>e+Number(t.stock||0),0);e.querySelector(`[data-product-name]`).textContent=r.name||``,e.querySelector(`[data-product-category]`).textContent=r.category||``,e.querySelector(`[data-product-description]`).textContent=r.description||`No description available.`,e.querySelector(`[data-product-price]`).textContent=`${Number(r.price||0).toLocaleString()} TZS`;let f=e.querySelector(`[data-product-compare]`);r.compare_at_price!==null&&Number(r.compare_at_price)>Number(r.price)&&(f.textContent=`${Number(r.compare_at_price).toLocaleString()} TZS`,f.classList.remove(`hidden`));let p=e.querySelector(`[data-product-badge]`);r.badge&&(p.textContent=r.badge,p.classList.remove(`hidden`));let m=e.querySelector(`[data-product-stock]`);m.textContent=d>0?`${d} in stock`:`Out of stock`,m.classList.toggle(`text-red-600`,d===0),m.classList.toggle(`text-black`,d>0);let h=e.querySelector(`[data-product-image]`);h.src=r.image||C,h.alt=r.name||`Product`,h.onerror=()=>{h.onerror=null,h.src=C};let g=e.querySelector(`[data-product-gallery]`);if(g){let e=Array.isArray(r.gallery)&&r.gallery.length?r.gallery.map(e=>({url:e?.url||``,alt:e?.alt||r.name||`Product`})).filter(e=>e.url):r.image?[{url:r.image,alt:r.name||`Product`}]:[];if(!e.length)g.innerHTML=``;else{let t=e=>{h.src=e};g.innerHTML=e.map((e,t)=>`
            <button type="button" data-gallery-image="${a(e.url)}"
              aria-label="View image ${t+1}"
              class="overflow-hidden rounded-xl border-2 ${t===0?`border-black`:`border-transparent`} bg-white">
              <img src="${a(e.url)}" alt="${a(e.alt)}"
                loading="lazy"
                onerror="this.onerror=null;this.src='${C}'"
                class="aspect-square w-full object-contain p-2">
            </button>`).join(``);let n=[...g.querySelectorAll(`[data-gallery-image]`)];g.onclick=e=>{let r=e.target.closest(`[data-gallery-image]`);r&&(t(r.dataset.galleryImage),n.forEach(e=>{let t=e===r;e.classList.toggle(`border-black`,t),e.classList.toggle(`border-transparent`,!t)}))},t(e[0].url)}}let v=e.querySelector(`[data-product-variants]`),y=e.querySelector(`[data-selected-variant]`);if(e.dataset.selectedVariant=l?.id||``,v&&(v.innerHTML=o.map(e=>{let t=[e.size,e.color].filter(Boolean).join(` · `)||`Option ${e.id}`;return`<button type="button" data-variant="${a(e.id)}" data-stock="${a(e.stock)}" data-label="${a(t)}" class="inline-flex min-h-11 items-center justify-center rounded-xl border px-4 py-2.5 text-sm font-semibold transition-colors ${String(e.id)===String(l?.id||``)?`border-black bg-black text-white`:`border-black bg-white text-black hover:border-emerald-600 hover:bg-emerald-50`} focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-600 focus-visible:ring-offset-2" ${e.in_stock?``:`disabled aria-disabled="true"`}>${a(t)}${e.in_stock?``:` · Sold out`}</button>`}).join(``)||`<span class="text-sm text-black">No variants available.</span>`,y.textContent=l?[l.size,l.color].filter(Boolean).join(` · `):``,e.querySelector(`[data-quantity]`)?.setAttribute(`max`,String(Math.min(50,Math.max(1,Number(l?.stock||1)))))),s()&&o.length)try{let t=(await u(`/account/preferences`))?.data?.size_profile||{},n=String(r.category||``).toLowerCase(),i=n.includes(`shoe`)||n.includes(`footwear`)?t.shoe:n.includes(`bottom`)||n.includes(`trouser`)||n.includes(`pant`)?t.bottom:t.top,a=o.find(e=>e.in_stock&&String(e.size||``)===String(i||``));a&&(l=a,e.dataset.selectedVariant=a.id,y.textContent=[a.size,a.color].filter(Boolean).join(` · `),v?.querySelectorAll(`[data-variant]`).forEach(e=>{let t=String(e.dataset.variant)===String(a.id);e.classList.toggle(`border-black`,t),e.classList.toggle(`bg-black`,t),e.classList.toggle(`text-white`,t)}))}catch{}let b=t=>{e.dataset.selectedVariant=t.dataset.variant,y.textContent=t.dataset.label||``,e.querySelectorAll(`[data-variant]`).forEach(e=>e.classList.remove(`border-black`,`bg-black`,`text-white`)),t.classList.add(`border-black`,`bg-black`,`text-white`);let n=e.querySelector(`[data-quantity]`),r=Math.min(50,Math.max(1,Number(t.dataset.stock||1)));n.max=String(r),n.value=1};v?.addEventListener(`click`,e=>{let t=e.target.closest(`[data-variant]`);t&&!t.disabled&&b(t)});let x=e.querySelector(`[data-quantity]`);e.querySelector(`[data-quantity-minus]`)?.addEventListener(`click`,()=>{x.value=Math.max(1,Number(x.value||1)-1)}),e.querySelector(`[data-quantity-plus]`)?.addEventListener(`click`,()=>{x.value=Math.min(Number(x.max||50),Number(x.value||1)+1)}),x?.addEventListener(`input`,()=>{let e=Number(x.max||50),t=Math.max(1,Math.min(e,Number(x.value||1)));x.value=t}),e.querySelector(`[data-product-wishlist]`)?.addEventListener(`click`,async e=>{let t=e.currentTarget;await _(t,async()=>{try{await N(r.id,t),t.dataset.saved=`1`}catch(e){i(e.message)}}),t.dataset.saved===`1`&&(t.textContent=`♥ Saved`)}),e.querySelector(`[data-add-selected]`)?.addEventListener(`click`,async t=>{if(!e.dataset.selectedVariant)return i(d?`Select an available size`:`This product is out of stock`);let n=t.currentTarget;await _(n,async()=>{try{await M(e.dataset.selectedVariant,Number(x.value||1))}catch(e){i(e.message)}})}),e.querySelector(`[data-add-selected]`).disabled=d===0,t?.classList.add(`hidden`),n?.classList.remove(`hidden`)}catch(i){t?.classList.add(`hidden`),n?.classList.add(`hidden`),o&&(o.textContent=i.message||`Unable to load this product.`),r&&r.classList.remove(`hidden`),e.querySelector(`[data-product-retry]`)?.addEventListener(`click`,()=>location.reload())}},L=async()=>{let e=document.querySelector(`[data-cart-page]`);if(!e)return;if(e.dataset.cartVersion===`2`){h();return}let t=async(t=!1)=>{let n=e.querySelector(`[data-cart-loading]`);t&&n?.classList.remove(`hidden`);let r=(await u(`/cart`))?.data||{items:[],subtotal:0,item_count:0},i=Array.isArray(r.items)?r.items:[],o=e.querySelector(`[data-cart-items]`),s=e.querySelector(`[data-cart-error]`);if(!o)return;s?.classList.add(`hidden`),o.innerHTML=i.map(e=>{let t=e.product?.image||``,n=[e.variant?.size,e.variant?.color].filter(Boolean).join(` · `),r=Number(e.quantity||0),i=Number(e.unit_price||0),o=Number(e.line_total||i*r);return`
          <article class="kp-cart-item grid grid-cols-[88px_minmax(0,1fr)] gap-x-4 gap-y-4 py-5 sm:grid-cols-[152px_minmax(0,1fr)_150px_150px] sm:items-center sm:gap-6 lg:grid-cols-[152px_minmax(0,1fr)_132px_150px]">
            <a href="/product/${encodeURIComponent(e.product?.slug||``)}" class="h-[88px] w-[88px] shrink-0 overflow-hidden rounded-xl bg-emerald-50/30 ring-1 ring-emerald-950/6 sm:h-[152px] sm:w-[152px]">
              <img src="${a(t)}" alt="${a(e.product?.name||`Product`)}" class="h-full w-full object-contain p-2.5" loading="lazy" onerror="this.onerror=null;this.src='/assets/wear/catalog/placeholder.svg'">
            </a>

            <div class="min-w-0 self-stretch sm:flex sm:flex-col sm:justify-center">
              <a href="/product/${encodeURIComponent(e.product?.slug||``)}" class="text-base font-bold tracking-tight text-black hover:underline hover:underline-offset-4">
                ${a(e.product?.name||`Product`)}
              </a>
              ${n?`<p class="mt-1.5 text-sm text-black">${a(n.replace(` · `,`  ·  `))}</p>`:``}
              <p class="mt-2 text-sm text-black">${i.toLocaleString()} TZS each</p>
              <button type="button" data-cart-remove="${e.variant_id}" class="kp-remove-btn mt-3 inline-flex w-fit items-center gap-1.5 text-sm font-medium text-black underline underline-offset-4" aria-label="Remove ${a(e.product?.name||`item`)} from bag">
                <span aria-hidden="true">×</span> Remove
              </button>
            </div>

            <div class="col-span-2 flex items-center justify-start sm:col-span-1 sm:justify-center">
              <div class="inline-flex h-11 items-center rounded-xl border border-black bg-white px-1">
                <button type="button" data-cart-minus="${e.variant_id}" class="kp-qty-btn flex h-9 w-9 items-center justify-center rounded-lg text-base font-medium text-black" aria-label="Decrease quantity for ${a(e.product?.name||`item`)}">−</button>
                <span class="min-w-9 text-center text-sm font-semibold text-black" aria-live="polite">${r}</span>
                <button type="button" data-cart-plus="${e.variant_id}" class="kp-qty-btn flex h-9 w-9 items-center justify-center rounded-lg text-base font-medium text-black" aria-label="Increase quantity for ${a(e.product?.name||`item`)}">+</button>
              </div>
            </div>

            <p class="col-span-2 text-right text-base font-bold tracking-tight text-black sm:col-span-1 sm:text-right sm:text-lg">${o.toLocaleString()} TZS</p>
          </article>
        `}).join(``),e.querySelector(`[data-cart-loading]`)?.classList.add(`hidden`),e.querySelector(`[data-cart-loading]`)?.setAttribute(`aria-busy`,`false`),e.querySelector(`[data-cart-empty]`)?.classList.toggle(`hidden`,i.length>0),e.querySelector(`[data-cart-content]`)?.classList.toggle(`hidden`,i.length===0),e.querySelector(`[data-cart-mobile-actions]`)?.classList.toggle(`hidden`,i.length>0),e.querySelector(`[data-cart-summary]`).textContent=`${Number(r.subtotal||0).toLocaleString()} TZS`;let c=Number(r.item_count||i.reduce((e,t)=>e+Number(t.quantity||0),0));e.querySelector(`[data-cart-item-count]`).textContent=c;let l=e.querySelector(`[data-cart-header-count]`);l&&(l.textContent=c),await h()};try{await t(!0)}catch(t){e.querySelector(`[data-cart-loading]`)?.classList.add(`hidden`),e.querySelector(`[data-cart-loading]`)?.setAttribute(`aria-busy`,`false`),e.querySelector(`[data-cart-empty]`)?.classList.add(`hidden`);let n=e.querySelector(`[data-cart-error]`);n?(n.textContent=t.message||`Unable to load your bag.`,n.classList.remove(`hidden`)):i(t.message)}e.addEventListener(`click`,async e=>{let n=e.target.closest(`[data-cart-plus],[data-cart-minus],[data-cart-remove],[data-cart-clear]`);if(n)try{if(n.matches(`[data-cart-clear]`)){if(!await window.kpConfirm?.(`Remove all items from your bag?`,{title:`Clear your bag?`}))return;await u(`/cart`,{method:`DELETE`}),await t();return}let e=n.dataset.cartPlus||n.dataset.cartMinus||n.dataset.cartRemove,r=((await u(`/cart`)).data.items||[]).find(t=>String(t.variant_id)===String(e));if(!r)return;n.dataset.cartRemove||n.dataset.cartMinus&&Number(r.quantity)<=1?await u(`/cart/items/${e}`,{method:`DELETE`}):await u(`/cart/items/${e}`,{method:`PUT`,body:{variant_id:Number(e),quantity:Math.max(1,Number(r.quantity)+(n.dataset.cartPlus?1:-1))}}),await t()}catch(e){i(e.message)}})},R=()=>{let e=document.querySelector(`[data-login-form]`),t=document.querySelector(`[data-register-form]`),n=e||t;if(!n)return;let r=n.querySelector(`[data-auth-error]`),a=()=>{r?.classList.add(`hidden`),r?.removeAttribute(`role`),n.querySelectorAll(`[aria-invalid="true"]`).forEach(e=>{e.removeAttribute(`aria-invalid`),e.removeAttribute(`aria-describedby`)})},o=e=>{if(!r)return;r.textContent=e||`Something went wrong. Please try again.`,r.classList.remove(`hidden`),r.setAttribute(`role`,`alert`);let t=(n.querySelector(`[data-otp-step]:not(.hidden)`)||n.querySelector(`[data-primary-step]:not(.hidden)`))?.querySelector(`input:not([type="hidden"]):not([disabled])`);t&&(t.setAttribute(`aria-invalid`,`true`),t.setAttribute(`aria-describedby`,`kp-auth-error`),t.focus())};r?.setAttribute(`id`,`kp-auth-error`),n.noValidate=!0;let s=n.querySelector(`.kp-auth-submit`);n.addEventListener(`submit`,async t=>{if(t.preventDefault(),s?.dataset.kpBusy===`1`)return;a();let r=new FormData(n),l=r.get(`phone`),d=s?.innerHTML;s&&(s.dataset.kpBusy=`1`,s.disabled=!0,s.classList.add(`kp-btn-busy`),s.innerHTML=`Please wait…`);try{let t=n.dataset.step!==`otp`,a=e?`/auth/login/request-otp`:`/auth/register/request-otp`;if(t){let e=await u(a,{method:`POST`,body:{phone:l}});n.dataset.step=`otp`;let t=n.querySelector(`[data-otp-step]`),r=t?.querySelector(`input[name="code"]`);t?.classList.remove(`hidden`),r&&(r.required=!0),n.querySelector(`[data-primary-step]`)?.classList.add(`hidden`),n.querySelectorAll(`[data-primary-step] input, [data-primary-step] select, [data-primary-step] textarea`).forEach(e=>{e.required=!1}),i(e?.dev_otp?`Development OTP: ${e.dev_otp}`:`Verification code requested`);return}let o=r.get(`code`),s=e?`/auth/login`:`/auth/register`,d=e?{phone:l,code:o}:{phone:l,code:o,name:r.get(`name`),referral_code:r.get(`referral_code`)||null},p=await u(s,{method:`POST`,body:d});m(p);let h=c();if(h)try{await u(`/cart/merge`,{method:`POST`,body:{guest_cart_token:h}})}catch{}location.href=f(n)}catch(e){o(e.message)}finally{s&&(s.dataset.kpBusy=`0`,s.disabled=!1,s.classList.remove(`kp-btn-busy`),s.innerHTML=d)}})},z=async()=>{let e=document.querySelector(`[data-wishlist-page]`);if(!e)return;let t=e.querySelector(`[data-wishlist-loading]`),n=e.querySelector(`[data-wishlist-skeleton]`),r=e.querySelector(`[data-wishlist-grid]`),a=e.querySelector(`[data-wishlist-empty]`),o=e.querySelector(`[data-wishlist-auth]`),c=e.querySelector(`[data-wishlist-page-count]`);if(!s()){t?.classList.add(`hidden`),o?.classList.remove(`hidden`),r?.classList.add(`hidden`),a?.classList.add(`hidden`);return}T(n,8);try{let e=((await u(`/wishlist`))?.data||[]).map(e=>e.product).filter(Boolean),n=e.length;if(c&&(c.textContent=n,c.classList.toggle(`hidden`,n===0)),t?.classList.add(`hidden`),o?.classList.add(`hidden`),!n){r?.classList.add(`hidden`),a?.classList.remove(`hidden`),await h();return}a?.classList.add(`hidden`),r?.classList.remove(`hidden`),E(r,e),await h()}catch(e){t?.classList.add(`hidden`),i(e.message)}},B=()=>{document.querySelectorAll(`[data-search-toggle]`).forEach(e=>e.addEventListener(`click`,()=>document.querySelector(`[data-search-panel]`)?.classList.toggle(`hidden`))),document.addEventListener(`click`,async e=>{let t=e.target.closest(`[data-quick-add]`);t&&await _(t,async()=>{try{let e=t.dataset.quickVariant;if(e)await M(e);else{let e=(await D(`per_page=50`)).find(e=>String(e.id)===String(t.dataset.quickAdd)),n=j(e);if(!n)throw Error(`No variant available`);await M(n.id)}}catch(e){i(e.message)}});let n=e.target.closest(`[data-wishlist-product]`);n&&await _(n,async()=>{try{await N(n.dataset.wishlistProduct,n)}catch(e){i(e.message)}})}),h()},V=async()=>{let e=document.querySelector(`[data-account-page]`),t=document.querySelector(`[data-account-profile]`),n=document.querySelector(`aside [data-sidebar-name]`)?document.querySelector(`aside`):null;if(e||t||n){if(!s()){e&&p();return}try{let t=await u(`/auth/me`);m({user:t.data}),document.querySelectorAll(`[data-account-name]`).forEach(e=>e.textContent=`Welcome back, ${t.data.name}`),document.querySelectorAll(`[data-profile-name]`).forEach(e=>e.textContent=t.data.name),document.querySelectorAll(`[data-profile-phone]`).forEach(e=>e.textContent=t.data.phone),document.querySelectorAll(`[data-sidebar-name]`).forEach(e=>e.textContent=t.data.name),document.querySelectorAll(`[data-sidebar-phone]`).forEach(e=>e.textContent=t.data.phone);let n=await u(`/orders`);e&&(e.querySelector(`[data-account-orders-count]`).textContent=n.data?.length??0)}catch(e){i(e.message)}}},H=e=>{let t=String(e||``).toLowerCase();return t.includes(`confirmed`)||t.includes(`paid`)||t.includes(`completed`)?`bg-emerald-50 text-emerald-700 ring-1 ring-inset ring-emerald-100`:t.includes(`cancel`)||t.includes(`failed`)?`bg-rose-50 text-rose-700 ring-1 ring-inset ring-rose-100`:`bg-amber-50 text-amber-700 ring-1 ring-inset ring-amber-100`},U=e=>String(e||`pending_payment`).replaceAll(`_`,` `),W=e=>{let t=String(e||``).toLowerCase();return t===`approved`||t===`completed`?`bg-emerald-50 text-emerald-700`:t===`rejected`||t===`cancelled`?`bg-rose-50 text-rose-700`:`bg-amber-50 text-amber-700`},G=async()=>{let e=document.querySelector(`[data-returns-page]`);if(!e)return;if(!s()){p();return}let t=e.querySelector(`[data-returns-list]`),n=e.querySelector(`[data-returns-empty]`),r=document.querySelector(`[data-return-modal]`),o=document.querySelector(`[data-return-form]`),c=document.querySelector(`[data-return-order-select]`),l=document.querySelector(`[data-return-items]`),d=e=>{if(!e.length){t.innerHTML=``,n.classList.remove(`hidden`),n.classList.add(`flex`);return}n.classList.add(`hidden`),n.classList.remove(`flex`),t.innerHTML=e.map(e=>`
        <article class="rounded-2xl border border-black bg-white p-5 shadow-sm sm:p-6">
          <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
              <p class="text-xs font-semibold uppercase tracking-wider text-black">${a(e.request_type||`request`)}</p>
              <h2 class="mt-1 text-sm font-bold text-black">Order #${a(e.order_number||``)}</h2>
              <p class="mt-1 text-xs text-black">${e.created_at?new Date(e.created_at).toLocaleString():``}</p>
            </div>
            <span class="rounded-full px-3 py-1 text-[11px] font-semibold capitalize ${W(e.status)}">${a(String(e.status||``).replaceAll(`_`,` `))}</span>
          </div>
          <div class="mt-4 border-t border-black pt-4">
            ${(e.items||[]).map(e=>`<p class="text-sm text-black">${a(e.product_name)}${e.size?` · Size ${a(e.size)}`:``} · Qty ${Number(e.quantity||0)}</p>`).join(``)}
            <p class="mt-1 text-xs text-black">Reason: ${a(String(e.reason||``).replaceAll(`_`,` `))}</p>
          </div>
        </article>`).join(``)};try{let[t,n]=await Promise.all([u(`/returns`),u(`/orders`)]),s=Array.isArray(t?.data)?t.data:[],f=Array.isArray(n?.data)?n.data:[];d(s);let p=f.filter(e=>String(e.status||``).toLowerCase()===`delivered`);c.innerHTML=`<option value="">Choose an order…</option>`+p.map(e=>`<option value="${a(e.id)}">${a(e.order_number)} · ${Number(e.total||0).toLocaleString()} TZS</option>`).join(``);let m=()=>{r.classList.remove(`hidden`),r.classList.add(`flex`)},h=()=>{r.classList.add(`hidden`),r.classList.remove(`flex`),o.reset(),l.innerHTML=`Select an order to see items`};e.querySelectorAll(`[data-new-request]`).forEach(e=>e.addEventListener(`click`,m)),document.querySelector(`[data-close-return-modal]`)?.addEventListener(`click`,h),r?.addEventListener(`click`,e=>{e.target===r&&h()}),e.querySelector(`[data-contact-support]`)?.addEventListener(`click`,()=>{location.href=`/contact`}),c.addEventListener(`change`,async()=>{let e=p.find(e=>String(e.id)===String(c.value));if(!e){l.innerHTML=`Select an order to see items`;return}try{let t=await u(`/orders/${encodeURIComponent(e.order_number)}`),n=Array.isArray(t?.data?.items)?t.data.items:[];l.innerHTML=n.length?n.map(e=>`
            <label class="flex cursor-pointer items-start gap-3 rounded-xl border border-black p-3 hover:bg-white">
              <input type="checkbox" name="item_ids[]" value="${a(e.id)}" class="mt-1 rounded border-black" checked>
              <span class="min-w-0"><span class="block text-sm font-medium text-black">${a(e.name)}</span><span class="mt-0.5 block text-xs text-black">${a([e.size,e.color].filter(Boolean).join(` · `)||`Standard`)} · Qty ${Number(e.quantity||0)}</span></span>
            </label>`).join(``):`<p class="text-sm text-black">No items found for this order.</p>`}catch(e){l.innerHTML=`<p class="text-sm text-rose-600">${a(e.message)}</p>`}}),o.addEventListener(`submit`,async e=>{e.preventDefault();let t=o.querySelector(`button[type="submit"]`),n=[...o.querySelectorAll(`input[name="item_ids[]"]:checked`)].map(e=>Number(e.value));if(!c.value||!n.length){i(`Select an order and at least one item.`);return}t.disabled=!0,t.textContent=`Submitting…`;try{await u(`/returns`,{method:`POST`,body:{order_id:Number(c.value),item_ids:n,request_type:o.querySelector(`input[name="request_type"]:checked`)?.value||`return`,reason:o.querySelector(`[name="reason"]`)?.value,notes:o.querySelector(`[name="notes"]`)?.value||null}}),h(),d((await u(`/returns`))?.data||[]),i(`Return request submitted.`)}catch(e){i(e.message)}finally{t.disabled=!1,t.textContent=`Submit request`}})}catch(e){t.innerHTML=`<div class="rounded-2xl border border-rose-100 bg-rose-50 px-5 py-6 text-sm text-rose-700">${a(e.message||`Unable to load returns.`)}</div>`}},K=async()=>{let e=document.querySelector(`[data-order-status]`);if(!e)return;let t=e.dataset.orderNumber,n=e.querySelector(`[data-order-status-text]`),r=e.querySelector(`[data-order-status-title]`),o=e.querySelector(`[data-order-status-icon]`),c=e.querySelector(`[data-order-status-actions]`);if(!s()){n&&(n.textContent=`Please sign in to view this order.`);return}let l=t=>{let s=String(t?.status||`pending_payment`).toLowerCase(),l=String(t?.payment_status||`pending`).toLowerCase()===`paid`,d=s===`cancelled`,f=!l&&!d;r&&(r.textContent=d?`Order cancelled`:l?`Order confirmed`:`Complete your payment`),n&&(n.innerHTML=d?`<span class="h-2 w-2 rounded-full bg-rose-500"></span> This order has been cancelled.`:l?`<span class="h-2 w-2 rounded-full bg-emerald-500"></span> Payment received. Your order is confirmed.`:`<span class="h-2 w-2 animate-pulse rounded-full bg-amber-500"></span> Step 2 of 3 — waiting for your payment.`),o&&(o.className=`mx-auto flex h-16 w-16 items-center justify-center rounded-full ${d?`bg-rose-100 text-rose-700`:l?`bg-emerald-100 text-emerald-700`:`bg-amber-100 text-amber-700`} ring-8 ${d?`ring-rose-50`:l?`ring-emerald-50`:`ring-amber-50`}`,o.innerHTML=g(d?`x`:l?`check`:`clock`,30));let p=e.querySelector(`[data-track-step="2"]`),m=e.querySelector(`[data-track-step="3"]`);if(p&&(p.classList.toggle(`done`,l),p.classList.toggle(`active`,f)),m&&(m.classList.toggle(`done`,l),m.classList.toggle(`active`,!1)),e.querySelector(`[data-payment-help]`)?.classList.toggle(`hidden`,!f),c){let e=(Array.isArray(t.payment)?t.payment:[]).find(e=>e&&typeof e.payment_gateway_url==`string`&&e.payment_gateway_url)?.payment_gateway_url,n=f&&e?`<a href="${a(e)}" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-xl bg-black px-8 py-3.5 text-base font-bold text-white hover:bg-emerald-700">Pay now — TZS ${Number(t.total||0).toLocaleString()}</a>`:``;c.innerHTML=`${n}<a href="/account/orders/${encodeURIComponent(t.order_number)}" class="inline-flex items-center justify-center gap-2 rounded-xl border border-black px-6 py-3 text-sm font-medium text-black hover:bg-white">View order</a>${f?`<button type="button" data-cancel-pending-order class="inline-flex items-center justify-center gap-2 rounded-xl border border-rose-200 px-6 py-3 text-sm font-medium text-rose-600 hover:bg-rose-50">Cancel order</button>`:``}`,c.querySelector(`[data-cancel-pending-order]`)?.addEventListener(`click`,async e=>{let n=e.currentTarget;if(await window.kpConfirm?.(`Cancel this order?`,{title:`Cancel this order?`})){n.disabled=!0,n.textContent=`Cancelling…`;try{await u(`/orders/${encodeURIComponent(t.order_number)}/cancel`,{method:`POST`}),i(`Order cancelled`),location.reload()}catch(e){n.disabled=!1,n.textContent=`Cancel order`,i(e.message)}}})}},d=null,f=e=>{let t=String(e?.status||``).toLowerCase();return String(e?.payment_status||``).toLowerCase()===`paid`||t===`cancelled`},p=e=>{f(e)||d||(d=setInterval(async()=>{if(!document.hidden)try{let e=await u(`/orders/${encodeURIComponent(t)}`);l(e.data),f(e.data)&&(clearInterval(d),d=null)}catch{}},8e3))};try{let e=await u(`/orders/${encodeURIComponent(t)}`);l(e.data),p(e.data)}catch(e){n&&(n.textContent=e.message||`Unable to load this order.`)}},q=async()=>{let e=document.querySelector(`[data-orders-page]`);if(e){if(!s()){p();return}if(!e.querySelector(`[data-orders-list]`)?.querySelector(`[data-server-order]`))try{let t=await u(`/orders`),n=e.querySelector(`[data-orders-list]`),r=Array.isArray(t.data)?t.data:[];if(!r.length){n.innerHTML=`
          <div class="rounded-2xl border border-emerald-950/10 bg-emerald-50/40 px-6 py-14 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/15">
              ${g(`bag`,24)}
            </div>
            <h2 class="mt-5 text-lg font-semibold text-black">No orders yet</h2>
            <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-black">Your completed and pending purchases will appear here.</p>
            <a href="/shop" class="button-dark mt-6">Start shopping</a>
          </div>`;return}n.innerHTML=r.map(e=>`
        <article class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm transition hover:border-black hover:shadow-md">
          <div class="flex flex-col gap-5 p-5 sm:p-6">
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
              <div>
                <p class="text-xs font-semibold uppercase tracking-wider text-black">Order</p>
                <h2 class="mt-1 text-base font-bold text-black">${a(e.order_number)}</h2>
                <p class="mt-1 text-sm text-black">${e.created_at?new Date(e.created_at).toLocaleString():`Date unavailable`}</p>
              </div>
              <span class="w-fit rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${H(e.status)}">${a(U(e.status))}</span>
            </div>

            <div class="grid gap-3 border-t border-black pt-5 sm:grid-cols-3">
              <div>
                <p class="text-xs text-black">Items</p>
                <p class="mt-1 text-sm font-semibold text-black">${Array.isArray(e.items)?e.items.reduce((e,t)=>e+Number(t.quantity||0),0):`—`}</p>
              </div>
              <div>
                <p class="text-xs text-black">Payment</p>
                <p class="mt-1 text-sm font-semibold capitalize text-black">${a(U(e.payment_status))}</p>
              </div>
              <div>
                <p class="text-xs text-black">Total</p>
                <p class="mt-1 text-sm font-bold text-black">${Number(e.total||0).toLocaleString()} TZS</p>
              </div>
            </div>

            <div class="flex flex-wrap items-center justify-end gap-3 border-t border-black pt-5">
              <a href="/account/orders/${encodeURIComponent(e.order_number)}" class="inline-flex items-center justify-center rounded-xl border border-black px-4 py-2.5 text-sm font-semibold text-black transition hover:border-black hover:bg-white">View details</a>
              ${String(e.status||``).toLowerCase()===`pending_payment`?`<a href="/orders/${encodeURIComponent(e.order_number)}" class="inline-flex items-center justify-center rounded-xl bg-black px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-black">Continue payment</a>`:``}
            </div>
          </div>
        </article>
      `).join(``)}catch(t){e.querySelector(`[data-orders-list]`).innerHTML=`
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-sm text-red-700" role="alert">${a(t.message||`Unable to load your orders.`)}</div>`}}},J=async()=>{let e=document.querySelector(`[data-order-detail]`);if(e){if(!s()){p();return}if(!e.querySelector(`[data-server-detail]`))try{let t=(await u(`/orders/${encodeURIComponent(e.dataset.orderNumber)}`)).data,n=Array.isArray(t.items)?t.items:[],r=String(t.status||``).toLowerCase()===`pending_payment`,o=String(t.payment_status||``).toLowerCase()!==`paid`,s=(Array.isArray(t.payment)?t.payment:[]).find(e=>e&&typeof e.payment_gateway_url==`string`&&e.payment_gateway_url)?.payment_gateway_url;e.querySelector(`[data-order-detail-content]`).innerHTML=`
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
          <section class="overflow-hidden rounded-2xl border border-black bg-white shadow-sm">
            <div class="border-b border-black p-5 sm:p-6">
              <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                  <p class="text-xs font-semibold uppercase tracking-wider text-black">Order summary</p>
                  <p class="mt-1 text-sm text-black">${t.created_at?new Date(t.created_at).toLocaleString():``}</p>
                </div>
                <span class="rounded-full px-3 py-1.5 text-xs font-semibold capitalize ${H(t.status)}">${a(U(t.status))}</span>
              </div>
            </div>

            <div class="divide-y divide-black">
              ${n.length?n.map(e=>`
                <div class="flex items-start justify-between gap-5 p-5 sm:p-6">
                  <div class="min-w-0">
                    <p class="font-semibold text-black">${a(e.name)}</p>
                    <p class="mt-1 text-sm text-black">${a([e.size,e.color].filter(Boolean).join(` · `)||`Standard`)} · Qty ${Number(e.quantity||0)}</p>
                    ${e.sku?`<p class="mt-1 text-xs text-black">SKU ${a(e.sku)}</p>`:``}
                  </div>
                  <p class="shrink-0 text-sm font-bold text-black">${Number(e.line_total||0).toLocaleString()} TZS</p>
                </div>`).join(``):`<div class="flex flex-col items-center px-6 py-12 text-center"><div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-50 text-emerald-700">`+g(`bag`,22)+`</div><p class="mt-4 text-sm font-semibold text-black">No order items available</p><p class="mt-1 max-w-sm text-sm text-black">This order does not currently contain any item details.</p></div>`}
            </div>
          </section>

          <aside class="space-y-4">
            <div class="rounded-2xl border border-black bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-black">Payment</p>
              <p class="mt-2 text-sm font-semibold capitalize text-black">${a(U(t.payment_status))}</p>
              <div class="mt-5 space-y-3 border-t border-black pt-5 text-sm">
                <div class="flex justify-between gap-4"><span class="text-black">Subtotal</span><strong>${Number(t.subtotal||0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4"><span class="text-black">Delivery</span><strong>${Number(t.delivery_fee||0).toLocaleString()} TZS</strong></div>
                <div class="flex justify-between gap-4 border-t border-black pt-3 text-base"><span class="font-semibold">Total</span><strong>${Number(t.total||0).toLocaleString()} TZS</strong></div>
              </div>
              ${o&&s?`<a href="${a(s)}" rel="noopener noreferrer" class="mt-4 inline-flex w-full items-center justify-center gap-2 rounded-xl bg-black px-4 py-3 text-sm font-semibold text-white hover:bg-emerald-700">Pay now</a>`:``}
            </div>

            <div class="rounded-2xl border border-black bg-white p-5 shadow-sm">
              <p class="text-xs font-semibold uppercase tracking-wider text-black">Delivery</p>
              <p class="mt-2 text-sm font-semibold text-black">${a(t.customer?.name||`Customer`)}</p>
              <p class="mt-1 text-sm leading-6 text-black">${a([t.delivery?.address,t.delivery?.city].filter(Boolean).join(`, `)||`Delivery address unavailable`)}</p>
              ${t.customer?.phone?`<p class="mt-3 text-sm text-black">${a(t.customer.phone)}</p>`:``}
              ${t.delivery?.provider||t.delivery?.tracking_number?`<div class="mt-4 border-t border-black pt-4 text-sm">${t.delivery?.provider?`<p><span class="text-black">Provider:</span> <strong>${a(t.delivery.provider)}</strong></p>`:``}${t.delivery?.tracking_number?`<p class="mt-1"><span class="text-black">Tracking:</span> <strong>${a(t.delivery.tracking_number)}</strong></p>`:``}</div>`:``}
              ${t.delivery?.shipped_at||t.delivery?.delivered_at?`<div class="mt-4 space-y-1 text-xs text-black">${t.delivery?.shipped_at?`<p>Shipped ${new Date(t.delivery.shipped_at).toLocaleString()}</p>`:``}${t.delivery?.delivered_at?`<p>Delivered ${new Date(t.delivery.delivered_at).toLocaleString()}</p>`:``}</div>`:``}
            </div>

            ${r?`<button data-cancel-order type="button" class="w-full rounded-xl border border-rose-200 bg-white px-4 py-3 text-sm font-semibold text-rose-600 transition hover:bg-rose-50">Cancel order</button>`:``}
          </aside>
        </div>
      `,e.querySelector(`[data-cancel-order]`)?.addEventListener(`click`,async t=>{let n=t.currentTarget;if(await window.kpConfirm?.(`Cancel this order?`,{title:`Cancel this order?`})){n.disabled=!0,n.textContent=`Cancelling…`;try{(await u(`/orders/${encodeURIComponent(e.dataset.orderNumber)}/cancel`,{method:`POST`})).data,i(`Order cancelled`),setTimeout(()=>location.reload(),350)}catch(e){n.disabled=!1,n.textContent=`Cancel order`,i(e.message)}}})}catch(t){e.querySelector(`[data-order-detail-content]`).innerHTML=`
        <div class="rounded-2xl border border-red-200 bg-red-50 px-5 py-6 text-sm text-red-700" role="alert">
          <p>${a(t.message||`Unable to load this order.`)}</p>
          <button type="button" data-order-detail-retry class="kp-button-secondary mt-4">Try again</button>
        </div>`,e.querySelector(`[data-order-detail-retry]`)?.addEventListener(`click`,()=>J())}}};B(),P(),F(),I(),L(),R(),z(),V(),G(),q(),K(),J(),(async()=>{let e=document.querySelector(`[data-account-addresses]`);if(e){if(!s()){p();return}try{let t=await u(`/addresses`);e.querySelector(`[data-addresses-list]`).innerHTML=(t.data||[]).map(e=>`
                <div class="rounded-2xl border border-black p-5">
                  <div class="flex items-center justify-between">
                    <strong>
                      ${e.label||`Shipping address`}
                    </strong>

                    ${e.is_default?`
                          <span class="text-xs font-semibold text-emerald-700">
                            Default
                          </span>
                        `:``}
                  </div>

                  <p class="mt-3 text-sm leading-6 text-black">
                    ${a(e.recipient_name)}<br>
                    ${a(e.phone)}<br>
                    ${a(e.region)}, ${a(e.district)}${e.ward?`, `+a(e.ward):``}<br>
                    ${a(e.street)}
                  </p>
                </div>
              `).join(``)||`<p class="text-sm text-black">No addresses saved.</p>`}catch(e){i(e.message)}}})(),document.querySelectorAll(`[data-logout]`).forEach(n=>n.addEventListener(`click`,async()=>{try{await u(`/auth/logout`,{method:`POST`})}catch{}localStorage.removeItem(e),localStorage.removeItem(t),location.href=`/`}))})();var e=null,t=null,n=()=>{if(e)return e;let n=document.createElement(`div`);n.id=`kp-confirm-modal`,n.className=`fixed inset-0 z-[100] hidden items-center justify-center bg-black/40 p-4`,n.setAttribute(`role`,`dialog`),n.setAttribute(`aria-modal`,`true`),n.setAttribute(`aria-labelledby`,`kp-confirm-title`),n.innerHTML=`
    <div class="w-full max-w-md rounded-2xl border border-emerald-950/10 bg-white p-6 shadow-2xl">
      <div class="flex items-start justify-between gap-4">
        <div>
          <h2 id="kp-confirm-title" class="text-lg font-semibold text-black"></h2>
          <p id="kp-confirm-message" class="mt-2 text-sm leading-6 text-black"></p>
        </div>
        <button type="button" data-kp-confirm-close class="inline-flex h-9 w-9 items-center justify-center rounded-full text-black transition hover:bg-emerald-50 focus:outline-none focus:ring-2 focus:ring-emerald-600" aria-label="Close confirmation dialog">×</button>
      </div>
      <div class="mt-6 flex justify-end gap-2.5">
        <button type="button" data-kp-confirm-cancel class="kp-button-secondary min-h-10 rounded-full px-4 py-2.5">Cancel</button>
        <button type="button" data-kp-confirm-ok class="min-h-10 rounded-full bg-black px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-emerald-600 focus:outline-none focus:ring-2 focus:ring-emerald-600 focus:ring-offset-2">Confirm</button>
      </div>
    </div>`,document.body.appendChild(n),e=n;let r=e=>{n.classList.add(`hidden`),n.classList.remove(`flex`),document.body.classList.remove(`overflow-hidden`);let r=t;t=null,r?.(e)};return n.querySelector(`[data-kp-confirm-close]`)?.addEventListener(`click`,()=>r(!1)),n.querySelector(`[data-kp-confirm-cancel]`)?.addEventListener(`click`,()=>r(!1)),n.querySelector(`[data-kp-confirm-ok]`)?.addEventListener(`click`,()=>r(!0)),n.addEventListener(`click`,e=>{e.target===n&&r(!1)}),n.addEventListener(`keydown`,e=>{e.key===`Escape`&&r(!1)}),n};window.kpConfirm=(e,r={})=>{let i=n();i.querySelector(`#kp-confirm-title`).textContent=r.title||`Please confirm`,i.querySelector(`#kp-confirm-message`).textContent=e||`Are you sure you want to continue?`,i.classList.remove(`hidden`),i.classList.add(`flex`),document.body.classList.add(`overflow-hidden`);let a=i.querySelector(`[data-kp-confirm-ok]`);return window.requestAnimationFrame(()=>a?.focus()),new Promise(e=>{t=e})},document.addEventListener(`submit`,async e=>{let t=e.target.closest(`form[data-confirm]`);t&&t.dataset.confirmHandled!==`1`&&(e.preventDefault(),t.dataset.confirmHandled=`1`,await window.kpConfirm(t.dataset.confirm,{title:`Please confirm`})?t.submit():t.dataset.confirmHandled=`0`)}),document.addEventListener(`change`,e=>{let t=e.target.closest(`[data-submit-on-change]`);t?.form&&t.form.submit()}),document.addEventListener(`error`,e=>{let t=e.target;t?.tagName===`IMG`&&t.dataset.fallback&&!t.dataset.fallbackApplied&&(t.dataset.fallbackApplied=`1`,t.src=t.dataset.fallback)},!0);