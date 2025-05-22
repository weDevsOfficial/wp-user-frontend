import { createRequire } from 'module';
import { fileURLToPath } from 'url';
import { dirname, resolve } from 'path';
import { register } from 'ts-node';

const require = createRequire(import.meta.url);
const __dirname = dirname(fileURLToPath(import.meta.url));

// Register ts-node programmatically
register({
    esm: true,
    experimentalSpecifierResolution: 'node',
    transpileOnly: true,
    compilerOptions: {
        module: 'ESNext',
        moduleResolution: 'node'
    }
});

// Import and run the generate-summary script
const summaryPath = resolve(__dirname, './generate-summary.ts');
await import(summaryPath).catch(err => {
    console.error('Failed to run generate-summary:', err);
    process.exit(1);
}); 