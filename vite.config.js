// Este arquivo é um stub do Laravel.
// O frontend React real está em /frontend/vite.config.js
// Este arquivo pode ser ignorado.

import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
});
