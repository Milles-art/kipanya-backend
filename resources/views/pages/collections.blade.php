@extends('layouts.app')

@push('head')
<style nonce="{{ Vite::cspNonce() }}">
    .kp-store-page{background:#fff;color:#000000}
    .kp-store-wrap{max-width:var(--kp-content-wide);margin:0 auto;padding:0 28px}
    .kp-store-hero{display:grid;grid-template-columns:1fr 2fr;gap:4px;margin-top:18px}
    .kp-store-hero-main,.kp-store-hero-side article{position:relative;overflow:hidden;background:var(--kp-emerald-soft)}
    .kp-store-hero-main{min-height:258px}
    .kp-store-hero-side{display:grid;grid-template-columns:1fr 1fr;gap:4px}
    .kp-store-hero-side article{min-height:258px}
    .kp-store-img{width:100%;height:100%;display:block;object-fit:contain;object-position:center}
    .kp-store-hero-main .kp-store-img,.kp-store-hero-side article .kp-store-img{object-fit:cover}
    .kp-store-overlay{position:absolute;inset:auto 0 0;padding:34px 28px;background:linear-gradient(transparent,rgba(0,0,0,.72));color:#fff}
    .kp-store-label{font-size:12px;font-weight:700;letter-spacing:.08em;text-transform:uppercase}
    .kp-store-overlay h1,.kp-store-overlay h2{margin:6px 0 0;font-weight:800;letter-spacing:-.045em;line-height:.95}
    .kp-store-overlay h1{font-size:clamp(34px,5vw,68px)}
    .kp-store-overlay h2{font-size:clamp(25px,3vw,42px)}
    .kp-store-link{display:inline-flex;align-items:center;gap:8px;margin-top:16px;font-size:12px;font-weight:800;text-transform:uppercase;letter-spacing:.04em}
    .kp-store-section{padding:72px 0 0}
    .kp-store-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:20px;margin-bottom:24px}
    .kp-store-heading h2{font-size:clamp(36px,5vw,58px);line-height:.95;letter-spacing:-.05em;font-weight:800;margin:0}
    .kp-store-heading a{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.06em;white-space:nowrap}
    .kp-store-products{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
    .kp-product-card{min-width:0;color:#000000;text-decoration:none}
    .kp-product-media{position:relative;aspect-ratio:4/5;background:var(--kp-emerald-soft);overflow:hidden}
    .kp-product-media img{width:100%;height:100%;object-fit:contain;display:block;transition:transform .45s ease}
    .kp-product-card:hover .kp-product-media img{transform:scale(1.025)}
    .kp-product-meta{padding:10px 2px 0}
    .kp-product-brand{font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.08em;color:var(--kp-ink-muted)}
    .kp-product-name{font-size:12px;font-weight:600;margin-top:4px;line-height:1.35}
    .kp-product-price{font-size:11px;margin-top:5px;font-weight:700}
    .kp-feature{display:grid;grid-template-columns:1fr 1fr;gap:10px;align-items:stretch}
    .kp-feature-image{min-height:660px;background:var(--kp-emerald-soft);overflow:hidden}
    .kp-feature-image img{width:100%;height:100%;object-fit:contain;display:block}
    .kp-feature-products{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
    .kp-feature-copy{padding:0 0 22px;display:flex;align-items:flex-end;justify-content:space-between;gap:20px;grid-column:1/-1}
    .kp-feature-copy h2{font-size:clamp(34px,4vw,52px);font-weight:800;line-height:.95;letter-spacing:-.05em;margin:0}
    .kp-feature-copy p{max-width:520px;margin:12px 0 0;font-size:14px;line-height:1.7;color:var(--kp-ink-secondary)}
    .kp-feature-products .kp-product-media{aspect-ratio:1/1}
    .kp-full-image{margin-top:10px;min-height:560px;background:var(--kp-emerald-soft);overflow:hidden}
    .kp-full-image img{width:100%;height:100%;min-height:560px;display:block;object-fit:contain}
    .kp-story{padding:84px 0 0;display:grid;grid-template-columns:.8fr 1.2fr;gap:40px;align-items:start}
    .kp-story-kicker{font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.14em;color:var(--kp-ink-muted)}
    .kp-story h2{font-size:clamp(38px,5vw,64px);line-height:.94;letter-spacing:-.055em;margin:10px 0 0;font-weight:800}
    .kp-story p{font-size:15px;line-height:1.8;color:var(--kp-ink-secondary);max-width:650px;margin:0}
    .kp-story-actions{margin-top:22px;display:flex;gap:12px;flex-wrap:wrap}
    @media(max-width:900px){
        .kp-store-wrap{padding:0 16px}.kp-store-hero{grid-template-columns:1fr}.kp-store-hero-main{min-height:208px}.kp-store-hero-side{grid-template-columns:1fr 1fr;grid-template-rows:none}.kp-store-hero-side article{min-height:208px}.kp-store-products{grid-template-columns:repeat(2,minmax(0,1fr))}.kp-feature{grid-template-columns:1fr}.kp-feature-image{min-height:520px}.kp-feature-copy{grid-column:auto}.kp-feature-products{grid-template-columns:repeat(2,minmax(0,1fr))}.kp-story{grid-template-columns:1fr;gap:18px}}
    @media(max-width:560px){
        .kp-store-hero{gap:3px}.kp-store-hero-main{min-height:178px}.kp-store-hero-side{gap:3px}.kp-store-hero-side article{min-height:178px}.kp-store-overlay{padding:22px 18px}.kp-store-section{padding-top:54px}.kp-store-heading{align-items:center}.kp-store-heading h2{font-size:35px}.kp-store-heading a{font-size:9px}.kp-store-products{grid-template-columns:repeat(2,minmax(0,1fr));gap:8px}.kp-product-name{font-size:11px}.kp-feature-image{min-height:390px}.kp-feature-products{gap:8px}.kp-full-image,.kp-full-image img{min-height:360px}.kp-story{padding-top:62px;padding-bottom:45px}}
</style>
@endpush

@section('content')
{{--
    data-product-url-template carries the real product-detail route with a
    swappable placeholder, so the JS below never has to guess a URL shape.
    Confirm 'product' is the correct route name for your product page (the
    same one product.blade.php resolves to) — swap it here if it's named
    differently, and the JS needs no further changes.
--}}
<div class="kp-store-page" data-product-url-template="{{ route('product', ['slug' => '__SLUG__']) }}">
    <div class="kp-store-wrap">
        {{-- Fashion-store hero: three shop catalogue images, no generated/editorial artwork. --}}
        <section class="kp-store-hero kp-reveal" aria-label="KP Wear collections">
            <article class="kp-store-hero-main">
                <img class="kp-store-img" src="{{ asset('assets/wear/catalog/products/t-shirts/kp-wear-kp-icon-black-front.webp') }}" alt="KP Wear featured piece">
                <div class="kp-store-overlay">
                    <div class="kp-store-label">KP Wear / Collections</div>
                    <h1>More than clothes.<br>It's a lifestyle.</h1>
                    <a class="kp-store-link" href="{{ route('shop') }}">Shop the pieces <span>→</span></a>
                </div>
            </article>
            <div class="kp-store-hero-side">
                <article>
                    <img class="kp-store-img" src="{{ asset('assets/wear/catalog/products/hoodies/kp-wear-redefined-graphic-red-front.webp') }}" alt="KP Wear hoodie">
                    <div class="kp-store-overlay"><div class="kp-store-label">Knitwear & Layers</div><h2>Everyday layers</h2></div>
                </article>
                <article>
                    <img class="kp-store-img" src="{{ asset('assets/wear/catalog/products/t-shirts/kp-wear-nothing-but-konfidence-black-front.webp') }}" alt="KP Wear streetwear piece">
                    <div class="kp-store-overlay"><div class="kp-store-label">Streetwear</div><h2>Made to move</h2></div>
                </article>
            </div>
        </section>

        {{-- New arrivals: live Wear catalogue. --}}
        <section class="kp-store-section kp-reveal">
            <div class="kp-store-heading">
                <h2>New Arrivals</h2>
                <a href="{{ route('shop') }}">View all →</a>
            </div>
            <div class="kp-store-products" data-collections-new-arrivals>
                @foreach([
                    ['kp-wear-redefined-graphic-red-front.webp', 'hoodies', 'KP Redefined Graphic Hoodie'],
                    ['kp-redefined-graffiti-white-front.webp', 't-shirts', 'KP Redefined Graffiti T-Shirt'],
                    ['kp-wear-sand-red-graffiti-front.webp', 'polos', 'KP Redefined Graffiti Polo'],
                    ['kp-wear-kilimanjaro-heritage-sand-front.webp', 'long-sleeves', 'Kilimanjaro Heritage Long Sleeve'],
                ] as [$file, $folder, $name])
                    <a class="kp-product-card" href="{{ route('shop') }}">
                        <div class="kp-product-media"><img src="{{ asset('assets/wear/catalog/products/'.$folder.'/'.$file) }}" alt="{{ $name }}" loading="lazy"></div>
                        <div class="kp-product-meta"><div class="kp-product-brand">KP Wear</div><div class="kp-product-name">{{ $name }}</div><div class="kp-product-price">Shop now →</div></div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- Everyday chapter: one large shop image plus catalogue products, mirroring the reference composition. --}}
        <section class="kp-store-section kp-reveal">
            <div class="kp-feature-copy">
                <div>
                    <div class="kp-store-label" style="color:var(--kp-ink-muted)">Chapter 01 / Everyday</div>
                    <h2>Style for real life.</h2>
                    <p>Clean silhouettes, easy layers and pieces designed to work together without trying too hard.</p>
                </div>
                <a class="kp-store-link" href="{{ route('collection', 'everyday-essentials') }}">Explore Everyday Essentials →</a>
            </div>
            <div class="kp-feature">
                <div class="kp-feature-image"><img src="{{ asset('assets/wear/catalog/products/long-sleeves/kp-wear-kilimanjaro-heritage-sand-front.webp') }}" alt="KP Wear everyday piece" loading="lazy"></div>
                
                <div class="kp-feature-products" data-collections-everyday>
                    @foreach([
                        ['kp-wear-kp-icon-navy-front.webp', 'hoodies', 'KP Icon Hoodie'],
                        ['kp-wear-kp-mascot-black-front.webp', 't-shirts', 'KP Mascot T-Shirt Black'],
                        ['kp-wear-sand-minimal-icon-front.webp', 'polos', 'KP Minimal Icon Polo'],
                        ['kp-wear-black-lion-front.webp', 'long-sleeves', 'Lion Heritage Long Sleeve'],
                    ] as [$file, $folder, $name])
                        <a class="kp-product-card" href="{{ route('shop') }}">
                            <div class="kp-product-media"><img src="{{ asset('assets/wear/catalog/products/'.$folder.'/'.$file) }}" alt="{{ $name }}" loading="lazy"></div>
                            <div class="kp-product-meta"><div class="kp-product-brand">KP Wear</div><div class="kp-product-name">{{ $name }}</div><div class="kp-product-price">Shop now →</div></div>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        {{-- Story intro. --}}
        <section id="the-story" class="kp-story kp-reveal">
            <div>
                <div class="kp-story-kicker">KP Wear / Collections</div>
                <h2>Wear your story.</h2>
            </div>
            <div>
                <p>A collection story built around the pieces you can actually wear — from everyday essentials to the looks that move with you.</p>
                {{-- Removed the "Read the story →" button: it linked to
                     href="#the-story", the very section it sits in, so it
                     was a no-op. "Shop the pieces" is kept as the one real
                     CTA here; add a genuine story/about link back in once
                     that page exists. --}}
                <div class="kp-story-actions">
                    <a class="kp-btn" href="{{ route('shop') }}">Shop the pieces</a>
                </div>
            </div>
        </section>

        {{-- Full-width closing shop image. --}}
        <section class="kp-store-section kp-reveal" style="padding-bottom:72px">
            <div class="kp-full-image">
                <img src="{{ asset('assets/wear/catalog/products/polos/kp-wear-white-graffiti-front.webp') }}" alt="KP Wear collection" loading="lazy">
            </div>
        </section>
    </div>
</div>
@endsection

@push('scripts')
<script nonce="{{ Vite::cspNonce() }}">
(function(){
    const items=document.querySelectorAll('.kp-reveal');
    if(items.length){
        if(!('IntersectionObserver' in window)){
            items.forEach(el=>el.classList.add('is-visible'));
        } else {
            const io=new IntersectionObserver(entries=>entries.forEach(entry=>{
                if(!entry.isIntersecting)return;
                entry.target.classList.add('is-visible');io.unobserve(entry.target);
            }),{threshold:.08,rootMargin:'0px 0px -40px 0px'});
            items.forEach(el=>io.observe(el));
        }
    }

    function escapeHtml(value){return String(value).replace(/[&<>'"]/g,m=>({'&':'&amp;','<':'&lt;','>':'&gt;',"'":'&#39;','"':'&quot;'}[m]));}

    // Product-detail URL template lives on the page wrapper (see the Blade
    // comment above) so this never has to hardcode a guessed URL shape.
    const urlTemplate = document.querySelector('.kp-store-page')?.dataset.productUrlTemplate || '';
    function productUrl(product){
        if (product.slug && urlTemplate) {
            return urlTemplate.replace('__SLUG__', encodeURIComponent(product.slug));
        }
        return '{{ route('shop') }}';
    }

    function renderCards(root, products){
        if(!root || !products.length)return;
        root.innerHTML = products.map(p=>{
            const image=p.image||p.image_url||'';
            const name=p.name||'KP Wear product';
            const price=Number(p.price||0).toLocaleString('en-TZ');
            return `<a class="kp-product-card" href="${productUrl(p)}"><div class="kp-product-media"><img src="${escapeHtml(image)}" alt="${escapeHtml(name)}" loading="lazy"></div><div class="kp-product-meta"><div class="kp-product-brand">KP Wear</div><div class="kp-product-name">${escapeHtml(name)}</div><div class="kp-product-price">TZS ${price}</div></div></a>`;
        }).join('');
    }

    // Fetched once and shared — New Arrivals and Everyday previously each
    // Shared API fetch for both collection sections; keep the canonical v1 endpoint.
    // hydrates both sections from different slices of the same response.
    (async function hydrateAll(){
        const newArrivalsRoot = document.querySelector('[data-collections-new-arrivals]');
        const everydayRoot = document.querySelector('[data-collections-everyday]');
        if (!newArrivalsRoot && !everydayRoot) return;

        try {
            const res = await fetch('/api/v1/wear/products?per_page=50&sort=newest', { headers: { Accept: 'application/json' } });
            if (!res.ok) throw new Error(`Request failed with status ${res.status}`);
            const json = await res.json();
            const products = Array.isArray(json?.data) ? json.data : [];
            if (!products.length) return;

            renderCards(newArrivalsRoot, products.slice(0, 4));
            renderCards(everydayRoot, products.slice(4, 8));
        } catch (e) {
            // Was previously a silent catch(e){} — now logged so a failed
            // fetch is visible in devtools instead of leaving the static
            // placeholder copy up with no way to diagnose why.
            console.error('Failed to load Wear products for collections page:', e);
        }
    })();
})();
</script>
@endpush
