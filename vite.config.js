import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { bunny } from 'laravel-vite-plugin/fonts';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
            fonts: [
                // One clean sans for headings and body (the reference design is all sans-serif).
                bunny('Inter', {
                    alias: 'body',
                    variable: '--font-vmn-body',
                    weights: [400, 500, 600, 700],
                    fallbacks: ['Helvetica Neue', 'Arial', 'sans-serif'],
                }),
                bunny('IBM Plex Mono', {
                    alias: 'mono',
                    variable: '--font-vmn-mono',
                    weights: [400, 500],
                    preload: false,
                    fallbacks: ['ui-monospace', 'monospace'],
                }),
            ],
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});
