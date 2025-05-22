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
    transpileOnly: true
});

// Import and run the generate-summary script
const summaryPath = resolve(__dirname, './generate-summary.ts');
import(summaryPath).catch(console.error); 