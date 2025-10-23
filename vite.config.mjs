import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const entries = {
    'subscriptions': './assets/js/subscriptions.js',
    'frontend-subscriptions': './assets/js/frontend-subscriptions.js', 
    'forms-list': './assets/js/forms-list.js',
    'account': './assets/js/account.js',
};

export default defineConfig(() => {
    const entryPoint = process.env.ENTRY;
    const input = entryPoint ? { [entryPoint]: entries[entryPoint] } : entries;

    return {
        plugins: [vue()],
        build: {
            rollupOptions: {
                input,
                output: {
                    entryFileNames: 'js/[name].min.js',
                    assetFileNames: (assetInfo) => {
                        if (assetInfo.name.endsWith('.css')) {
                            return assetInfo.name === 'subscriptions.css' 
                                ? 'css/admin/[name].min.css'
                                : 'css/[name].min.css';
                        }
                        return 'assets/[name]-[hash][extname]';
                    },
                    format: 'iife',
                    name: 'WPUF',
                },
            },
            outDir: './assets',
            emptyOutDir: false,
            sourcemap: true,
            assetsInlineLimit: 0,
            chunkSizeWarningLimit: 1000,
        },
    }
});
