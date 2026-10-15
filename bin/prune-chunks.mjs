#!/usr/bin/env node
/**
 * Delete the lazy chunks no built frontend bundle loads any more.
 *
 * The frontend webpack build cannot clean its output folder (assets/js is
 * shared with the other builds, and `WPUF_ENTRY=<name>` rebuilds one entry),
 * so every rebuild of a lazy chunk (the TipTap editor ...) left the old hashed
 * file behind. A chunk is kept while one of the entry files names its content
 * hash (webpack's chunk map); the rest are stale.
 *
 * Usage: node bin/prune-chunks.mjs  (run by build:frontend-react)
 */
import { existsSync, readdirSync, readFileSync, rmSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const dir = join( dirname( fileURLToPath( import.meta.url ) ), '..', 'assets', 'js', 'frontend' );

if ( ! existsSync( dir ) ) {
    process.exit( 0 );
}

const files = readdirSync( dir );
const entries = files.filter( ( file ) => file.endsWith( '.js' ) && ! file.endsWith( '.chunk.js' ) ).map( ( file ) => readFileSync( join( dir, file ), 'utf8' ) );
let removed = 0;

for ( const file of files ) {
    const match = /\.([0-9a-f]{8})\.chunk\.js$/.exec( file );

    if ( ! match || entries.some( ( source ) => source.includes( match[ 1 ] ) ) ) {
        continue;
    }

    rmSync( join( dir, file ) );
    removed++;
}

console.log( `prune-chunks: ${ removed } stale chunk(s) removed.` );
