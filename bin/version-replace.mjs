#!/usr/bin/env node
/**
 * Stamp the release version into every `@since WPUF_SINCE` placeholder (the
 * Dokan `bin/version-replace` step). Run once while preparing a release, after
 * the version bump in wpuf.php, and commit the result.
 *
 * Usage: node bin/version-replace.mjs [--check] [--version <x.y.z>]
 *   --check    list the files that still carry the placeholder, change nothing
 *   --version  stamp this version instead of WPUF_VERSION from wpuf.php
 */
import { readFileSync, readdirSync, statSync, writeFileSync } from 'node:fs';
import { dirname, join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const args = process.argv.slice( 2 );
const check = args.includes( '--check' );
const versionIndex = args.indexOf( '--version' );
const version = versionIndex === -1
    ? ( readFileSync( join( root, 'wpuf.php' ), 'utf8' ).match( /define\(\s*'WPUF_VERSION',\s*'([^']+)'/ ) || [] )[ 1 ]
    : args[ versionIndex + 1 ];

if ( ! version ) {
    console.error( 'version-replace: no version (wpuf.php WPUF_VERSION or --version)' );
    process.exit( 1 );
}

// Shipped PHP and JS sources only; never vendor, node_modules or generated assets.
const roots = [ 'wpuf.php', 'wpuf-functions.php', 'includes', 'Lib', 'templates', 'admin', 'class', 'views', 'src', 'modules/user-directory/includes', 'modules/user-directory/src' ];
const extensions = [ '.php', '.js', '.jsx', '.ts', '.tsx' ];
const placeholder = /WPUF_SINCE/g;

function walk( path, out ) {
    const stat = statSync( path );

    if ( stat.isFile() ) {
        if ( extensions.some( ( ext ) => path.endsWith( ext ) ) ) {
            out.push( path );
        }
        return;
    }

    for ( const name of readdirSync( path ) ) {
        if ( 'node_modules' === name || 'vendor' === name ) {
            continue;
        }
        walk( join( path, name ), out );
    }
}

const files = [];

for ( const entry of roots ) {
    try {
        walk( join( root, entry ), files );
    } catch ( e ) {
        // an optional dir that does not exist in this checkout
    }
}

let touched = 0;

for ( const file of files ) {
    const source = readFileSync( file, 'utf8' );

    if ( ! placeholder.test( source ) ) {
        continue;
    }

    touched++;

    if ( check ) {
        console.log( relative( root, file ) );
        continue;
    }

    writeFileSync( file, source.replace( placeholder, version ) );
}

console.log( `version-replace: ${ touched } file(s) ${ check ? 'still carry WPUF_SINCE' : `stamped ${ version }` }.` );
process.exit( check && touched ? 1 : 0 );
