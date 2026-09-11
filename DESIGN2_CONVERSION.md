# Kipanya Wear — Design #2 Laravel Conversion

Source design: `project-bolt-sb1-wtphed6h.zip`.

Converted from React/Supabase to Laravel Blade + the existing Kipanya JSON API.

## Implemented routes
- `/wear` — Design #2 home
- `/wear/shop` — Design #2 catalog
- `/wear/products/{slug}` — Design #2 product detail
- `/wear/cart` — Design #2 cart using `/api/v1/cart`
- `/wear/checkout` — Design #2 checkout using the existing address/cart/checkout APIs
- `/wear/about`
- `/wear/contact`

## Backend remains the source of truth
Catalog and categories load from `/api/v1/wear/*`.
Cart operations use `/api/v1/cart/*` through the existing `KipanyaCart` helper.
Checkout uses `/api/v1/addresses` and `/api/v1/checkout`.

## KP Wear homepage campaign assets
The homepage uses the generated KP Wear campaign pack under `public/assets/wear/home/` and the 14 generated product visuals under `public/assets/wear/catalog/generated/`.
The Wear demo seeder maps one generated product image to each of the 14 demo products, so the same product imagery is available through the existing Wear API on the homepage, shop, product detail and cart flows.
