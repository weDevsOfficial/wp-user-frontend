#!/usr/bin/env node
/**
 * The classic account sheet for the sections the React account shows as PHP
 * output (billing address, subscription, invoices, add-on sections): the
 * templates there carry the classic Tailwind classes, and the classic
 * `assets/css/frontend/account.css` cannot load next to the React shell (it
 * has global rules, and its shell rules use the class names the React shell
 * reuses). This writes `assets/css/frontend/account-sections.css` from it:
 * every selector under `:where(.wpuf-frontend .wpuf-account-html)` (same
 * specificity as in account.css), the
 * `.wpuf-account-container` scope renamed to `.wpuf-account-html`, the
 * `:root` / `html` / `*` rules moved onto that scope (Tailwind and daisyUI
 * variables keep working), the dark theme and `::backdrop` rules dropped,
 * the bare container rule (the classic flex shell) dropped, and the form
 * rules dropped (forms keep their own layout look in the account).
 */
import { readFileSync, writeFileSync } from 'node:fs';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..', '..' );
const source = join( root, 'assets/css/frontend/account.css' );
const target = join( root, 'assets/css/frontend/account-sections.css' );
// In :where() so every rule keeps the specificity it has in account.css
// (the chat and other add-on sheets must still win over the preflight).
const SCOPE = ':where(.wpuf-frontend .wpuf-account-html)';

/** Split on top-level commas (not inside :where() / :not()). */
const splitTop = ( list ) => {
    const out = [];
    let depth = 0;
    let current = '';

    for ( const char of list ) {
        if ( '(' === char || '[' === char ) {
            depth++;
        } else if ( ')' === char || ']' === char ) {
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

    return out.map( ( part ) => part.trim() ).filter( Boolean );
};

/** One selector of a rule, scoped; null drops it. */
const scopeSelector = ( selector ) => {
    if ( '.wpuf-account-container' === selector || /^::backdrop/.test( selector ) ) {
        return null;
    }

    // Forms (Submit Post, Edit Profile) render with the forms app and their own
    // layout sheet (layout1-5, registration templates), exactly as on a page:
    // the classic account's form look does not apply to them.
    if ( /\.wpuf-form-add|\.wpuf-form-root|\.wpuf-reg-form-card/.test( selector ) ) {
        return null;
    }

    if ( /^(:root|\[data-theme\]|html|body)$/.test( selector ) ) {
        return SCOPE;
    }

    const renamed = selector.replace( /\.wpuf-account-container(?![\w-])/g, '.wpuf-account-html' );

    if ( renamed.startsWith( '.wpuf-account-html' ) ) {
        return ':where(.wpuf-frontend) ' + renamed;
    }

    return SCOPE + ' ' + renamed.replace( /^html\s+/, '' );
};

/** Index of the brace that closes the block opened at `open`. */
const closing = ( css, open ) => {
    let depth = 0;
    let quote = '';

    for ( let i = open; i < css.length; i++ ) {
        const char = css[ i ];

        if ( quote ) {
            if ( char === quote && '\\' !== css[ i - 1 ] ) {
                quote = '';
            }
        } else if ( '"' === char || "'" === char ) {
            quote = char;
        } else if ( '{' === char ) {
            depth++;
        } else if ( '}' === char && 0 === --depth ) {
            return i;
        }
    }

    throw new Error( 'Unbalanced braces in account.css' );
};

const transform = ( css ) => {
    let out = '';
    let i = 0;

    while ( i < css.length ) {
        const open = css.indexOf( '{', i );

        if ( -1 === open ) {
            break;
        }

        const prelude = css.slice( i, open ).trim();
        const end = closing( css, open );
        const body = css.slice( open + 1, end );

        i = end + 1;

        if ( prelude.startsWith( '@' ) ) {
            if ( /prefers-color-scheme\s*:\s*dark/.test( prelude ) || /^@font-face/.test( prelude ) ) {
                continue;
            }

            if ( /^@(media|supports|container|layer)/.test( prelude ) ) {
                const inner = transform( body );

                if ( inner ) {
                    out += `${ prelude }{${ inner }}`;
                }

                continue;
            }

            // @keyframes and the like carry no selectors.
            out += `${ prelude }{${ body }}`;
            continue;
        }

        const selectors = splitTop( prelude.replace( /\/\*[\s\S]*?\*\//g, '' ) ).map( scopeSelector ).filter( Boolean );

        if ( selectors.length ) {
            out += `${ [ ...new Set( selectors ) ].join( ',' ) }{${ body }}`;
        }
    }

    return out;
};

const css = readFileSync( source, 'utf8' ).replace( /\/\*[\s\S]*?\*\//g, '' );

writeFileSync( target, '/* Generated by tools/frontend-css/account-sections.mjs from account.css. Do not edit. */\n' + transform( css ) + '\n' );
console.log( 'account-sections.css written' );
