#!/usr/bin/env node
/**
 * The "no overlap with WordPress" check of the frontend sheets
 * (frontend-react-architecture.md 3.2, rule 8): every rule's first compound
 * selector is `.wpuf-frontend` (or `:where(.wpuf-frontend)`), no `:root`,
 * `html`, `body`, `*` rules, no `@font-face`. Fails the build otherwise.
 */
import { existsSync, readFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..', '..' );
const sheets = [ 'runtime', 'forms', 'account-page', 'account-sections' ].flatMap( ( name ) => [ `assets/css/frontend/${ name }.css`, `assets/css/frontend/${ name }-rtl.css` ] );
const scope = /^(:where\(\s*)?\.wpuf-frontend(\b|[\s.:#\[>+~)])/;
/** Split a selector list on its top-level commas (not the ones inside :not() / :is()). */
const splitTop = ( list ) => {
    const out = [];
    let depth = 0;
    let current = '';

    for ( const char of list ) {
        if ( '(' === char ) {
            depth++;
        } else if ( ')' === char ) {
            depth--;
        }

        if ( ',' === char && 0 === depth ) {
            out.push( current );
            current = '';
        } else {
            current += char;
        }
    }

    out.push( current );

    return out;
};

const problems = [];

for ( const sheet of sheets ) {
    const file = join( root, sheet );

    if ( ! existsSync( file ) ) {
        continue;
    }

    const css = readFileSync( file, 'utf8' )
        .replace( /\/\*[\s\S]*?\*\//g, '' )
        .replace( /@(media|supports|container)[^{]*\{/g, '{' );

    if ( /@font-face/.test( css ) ) {
        problems.push( `${ sheet }: @font-face` );
    }

    // Keyframes bodies are percentages, not selectors.
    const body = css.replace( /@keyframes[^{]*\{(?:[^{}]*\{[^{}]*\})*[^{}]*\}/g, '' );

    for ( const match of body.matchAll( /(^|\})\s*([^{}@]+?)\s*\{/g ) ) {
        const selectors = splitTop( match[ 2 ] ).map( ( s ) => s.trim() ).filter( Boolean );

        for ( const selector of selectors ) {
            if ( /^(:root|html|body|\*)/.test( selector ) ) {
                problems.push( `${ sheet }: global rule "${ selector }"` );
            } else if ( ! scope.test( selector ) ) {
                problems.push( `${ sheet }: unscoped rule "${ selector }"` );
            }
        }
    }
}

if ( problems.length ) {
    for ( const problem of problems ) {
        console.error( `::error::${ problem }` );
    }
    console.error( `frontend-css check: ${ problems.length } rule(s) would leak outside .wpuf-frontend.` );
    process.exit( 1 );
}

console.log( 'frontend-css check: every rule stays inside .wpuf-frontend.' );
