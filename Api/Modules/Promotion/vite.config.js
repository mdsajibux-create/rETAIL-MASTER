import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
export default defineConfig({
    build: {
        outDir: '../../public/build-promotion',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-promotion',
            input: [
                __dirname + '/resources/assets/sass/app.scss',
                __dirname + '/resources/assets/js/app.js'
            ],
            refresh: true,
        }),
    ],
});