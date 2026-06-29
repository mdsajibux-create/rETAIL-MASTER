import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

export default defineConfig({
    build: {
        outDir: '../../public/build-paymentgateways',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-paymentgateways',
            input: [
                __dirname + '/resources/asset/sass/app.scss',
                __dirname + '/resources/asset/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
