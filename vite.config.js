import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    resolve: {
        // Mengikuti jalur Scoop asli di Windows untuk kelancaran resolusi node_modules
        preserveSymlinks: true,
    },
});
