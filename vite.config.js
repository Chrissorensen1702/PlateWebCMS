import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/sales/app.css',
                'resources/css/cms/app.css',
                'resources/css/cms/auth.css',
                'resources/css/sites/app.css',
                'resources/js/sales/app.js',
                'resources/js/cms/app.js',
                'resources/js/sites/app.js',
            ],
            refresh: true,
        }),
    ],
});
