#!/usr/bin/env node
/**
 * Fails when a generated asset is missing, empty or half built.
 *
 * Generated assets are gitignored and built fresh (`pnpm build`, then
 * `pnpm run i18n` for a release), so a broken build would otherwise ship a
 * plugin whose admin screens do not load. Run after `pnpm build`; pass
 * `--release` after `pnpm run i18n` to also check the release-only outputs.
 */
import { existsSync, readFileSync, statSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { built, release } from './build-outputs.mjs';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );

const files = process.argv.includes( '--release' ) ? [ ...built, ...release ] : built;
const errors = [];

for ( const file of files ) {
    const path = join( root, file );

    if ( ! existsSync( path ) ) {
        errors.push( `${ file }: missing` );
        continue;
    }

    if ( 0 === statSync( path ).size ) {
        errors.push( `${ file }: empty` );
        continue;
    }

    const text = readFileSync( path, 'utf8' );

    if ( file.endsWith( '.asset.php' ) && ! ( text.includes( "'dependencies'" ) && text.includes( "'version'" ) ) ) {
        errors.push( `${ file }: no dependencies/version` );
    }

    // webpack replaces an import it could not resolve with a throwing stub.
    if ( file.endsWith( '.js' ) && /webpackMissingModule|Cannot find module '/.test( text ) ) {
        errors.push( `${ file }: contains an unresolved import` );
    }
}

if ( errors.length ) {
    for ( const error of errors ) {
        console.error( `::error::${ error }` );
    }
    console.error( `verify-build: ${ errors.length } problem(s); the build is incomplete.` );
    process.exit( 1 );
}

console.log( `verify-build: ${ files.length } generated assets present.` );
