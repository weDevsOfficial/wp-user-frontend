#!/usr/bin/env node
/**
 * Remove every generated asset so the next build starts from nothing.
 *
 * The list is bin/build-outputs.mjs (the same list verify-build.mjs checks), plus
 * the zip output dir. Nothing tracked by git is touched: generated assets are
 * gitignored and rebuilt by `pnpm build` / `grunt release`.
 *
 * Usage: node bin/clean-build.mjs  (pnpm clean)
 */
import { existsSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { built, release } from './build-outputs.mjs';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const targets = [ ...built, ...release, 'dist', 'assets/js/react', 'assets/js/frontend' ];
let removed = 0;

for ( const file of targets ) {
    const path = join( root, file );

    if ( ! existsSync( path ) ) {
        continue;
    }

    rmSync( path, { recursive: true, force: true } );
    removed++;
}

console.log( `clean-build: ${ removed } generated path(s) removed.` );
