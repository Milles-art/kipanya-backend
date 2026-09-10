import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/kipanya-wear-all.css',
                'resources/js/kipanya-cart.js',
                'resources/js/kipanya-nav.js',
                'resources/js/kp-bag-drawer.js',
                'resources/js/kp-wear-catalog.js',
                'resources/js/kp-wear-product.js',
                'resources/js/kp-wear-cart.js',
                'resources/js/kp-wear-checkout.js',
                'resources/js/kp-wear-orders.js',
                'resources/js/kp-wear-confirmation.js',
                'resources/js/kp-wear-wishlist.js',
            ],
            refresh: true,
        }),
    ],
});