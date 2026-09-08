import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/wear-novashop.css',
                'resources/css/public-redesign.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
});