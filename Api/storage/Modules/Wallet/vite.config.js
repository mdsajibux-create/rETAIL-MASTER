import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import { readdirSync, statSync } from 'fs';
import { join,relative,dirname } from 'path';
import { fileURLToPath } from 'url';

export default defineConfig({
    build: {
        outDir: '../../public/build-wallet',
        emptyOutDir: true,
        manifest: true,
    },
    plugins: [
        laravel({
            publicDirectory: '../../public',
            buildDirectory: 'build-wallet',
            input: [
                __dirname + '/resources/asset/sass/app.scss',
                __dirname + '/resources/asset/js/app.js'
            ],
            refresh: true,
        }),
    ],
});
// Scen all resources for asset file. Return array
//function getFilePaths(dir) {
//    const filePaths = [];
//
//    function walkDirectory(currentPath) {
//        const files = readdirSync(currentPath);
//        for (const file of files) {
//            const filePath = join(currentPath, file);
//            const stats = statSync(filePath);
//            if (stats.isFile() && !file.startsWith('.')) {
//                const relativePath = 'Modules/Wallet/'+relative(__dirname, filePath);
//                filePaths.push(relativePath);
//            } else if (stats.isDirectory()) {
//                walkDirectory(filePath);
//            }
//        }
//    }
//
//    walkDirectory(dir);
//    return filePaths;
//}

//const __filename = fileURLToPath(import.meta.url);
//const __dirname = dirname(__filename);

//const assetsDir = join(__dirname, 'resources/asset');
//export const paths = getFilePaths(assetsDir);


//export const paths = [
//    'Modules/Wallet/resources/asset/sass/app.scss',
//    'Modules/Wallet/resources/asset/js/app.js',
//];
