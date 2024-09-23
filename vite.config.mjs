import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const input = [
    './assets/js/subscriptions.js',
    './src/js/transactions.js',
];

const adminAssets = [
    'subscriptions.css',
    'transactions.css',
];

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue()],
    build: {
        minify: false,
        rollupOptions: {
            input: input, // Specify your main entry point file
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        if (adminAssets.includes( assetInfo.name )) {
                            return `css/admin/[name].${extType}`;
                        }

                        return `css/[name].${extType}`;
                    }
                }
            },
        },
        outDir: './assets', // Output to the same directory as source code
        emptyOutDir: false
    },
})
