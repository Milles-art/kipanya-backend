import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
  plugins: [
    laravel({
      input: [
        'resources/css/kp-wear-design2.css',
        'resources/js/kipanya-cart.js',
        'resources/js/kp-wear-design2.js',
        'resources/js/kp-wear-checkout.js',
      ],
      refresh: true,
    }),
  ],
});
