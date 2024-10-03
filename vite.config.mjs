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

export default defineConfig({
    plugins: [
        vue({
            template: {
                compilerOptions: {
                    // This is needed if your Vue components are inside PHP files
                    isCustomElement: (tag) => tag.includes('-')
                }
            }
        })
    ],
    build: {
        minify: false,
        sourcemap: true, // Enable source maps for debugging
        rollupOptions: {
            input: input,
            output: {
                entryFileNames: 'js/[name].js',
                assetFileNames: (assetInfo) => {
                    const info = assetInfo.name.split('.');
                    const extType = info[info.length - 1];
                    if (/\.(css)$/.test(assetInfo.name)) {
                        if (adminAssets.includes(assetInfo.name)) {
                            return `css/admin/[name].${extType}`;
                        }
                        return `css/[name].${extType}`;
                    }
                }
            },
        },
        outDir: './assets',
        emptyOutDir: false
    },
    server: {
        hmr: true, // Enable hot module replacement
    },
});
