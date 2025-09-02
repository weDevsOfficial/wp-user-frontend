import { defineConfig } from 'vite'
import vue from '@vitejs/plugin-vue'

const input = {
    'subscriptions': './assets/js/subscriptions.js',
    'forms-list': './assets/js/forms-list.js',
    'ai-form-builder': './assets/js/ai-form-builder.js',
};

const adminAssets = [
    'subscriptions.css',
];

// Create separate configs for each entry point
export default defineConfig(({ mode }) => {
    // Get the entry point from command line argument or process all
    const entryPoint = process.env.ENTRY;
    const entries = entryPoint ? { [entryPoint]: input[entryPoint] } : input;

    return {
        plugins: [vue()],
        build: {
            rollupOptions: {
                input: entries,
                output: {
                    entryFileNames: 'js/[name].min.js',
                    assetFileNames: (assetInfo) => {
                        const info = assetInfo.name.split('.');
                        const extType = info[info.length - 1];
                        if (/\.(css)$/.test(assetInfo.name)) {
                            if (adminAssets.includes(assetInfo.name)) {
                                return `css/admin/[name].min.${extType}`;
                            }
                            return `css/[name].min.${extType}`;
                        }
                    },
                    format: 'iife',
                    name: 'WPUF',
                    inlineDynamicImports: true,
                },
            },
            outDir: './assets',
            emptyOutDir: false,
            sourcemap: true,
            assetsInlineLimit: 0,
            chunkSizeWarningLimit: 1000, // Increase warning limit to 1000 kB
        },
    }
});
