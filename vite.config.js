import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/css/custom.css', // Agrega tu archivo personalizado
                'resources/js/app.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});