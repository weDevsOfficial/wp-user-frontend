#!/usr/bin/env node
/**
 * Generate languages/wp-user-frontend.pot, React admin strings included.
 *
 * WordPress loads a script's JSON translations by the md5 of the enqueued
 * file's path, and `wp i18n make-json` names each JSON after the JS files the
 * PO references. The React bundles are built to assets/js/screens/<name>.js (not
 * .min.js, which extraction skips), so extracting from them gives references
 * to exactly the files that are enqueued; src/admin is extracted too so
 * translators also see the source location. translate.wordpress.org runs the
 * same extraction on the released files (which carry the bundles, not src/),
 * so its language packs get JSON for the React screens as well.
 *
 * Needs WP-CLI (`wp`, or the path in $WP_CLI) and a built assets/js/screens/.
 *
 * Usage: node bin/make-pot.mjs
 */
import { execFileSync } from 'node:child_process';
import { existsSync, readFileSync } from 'node:fs';
import { join, dirname } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const out = join( root, 'languages', 'wp-user-frontend.pot' );
const wp = process.env.WP_CLI || 'wp';

if ( ! existsSync( join( root, 'assets/js/screens/form-builder.js' ) ) ) {
    console.error( 'make-pot: assets/js/screens/ is not built; run the build first (pnpm build).' );
    process.exit( 1 );
}

// What the release leaves out (.distignore), except src/admin: kept for the
// source references.
const exclude = [
    'node_modules', 'vendor', 'tests', 'tools', 'build', 'bin', 'plugins', 'openspec',
    '.github', '.claude', 'modules/user-directory/node_modules', 'modules/user-directory/src',
].join( ',' );

execFileSync( wp, [
    'i18n', 'make-pot', root, out,
    '--domain=wp-user-frontend',
    '--slug=wp-user-frontend',
    '--skip-audit',
    `--exclude=${ exclude }`,
    '--headers={"Report-Msgid-Bugs-To":"https://wedevs.com/contact/"}',
], { stdio: [ 'ignore', 'inherit', 'inherit' ] } );

const pot = readFileSync( out, 'utf8' );
const strings = ( pot.match( /^msgid "/gm ) || [] ).length - 1;
const react = ( pot.match( /assets\/js\/screens\/[\w-]+\.js/g ) || [] ).length;

if ( ! react ) {
    console.error( 'make-pot: no string references the React bundles; the React screens would be untranslatable.' );
    process.exit( 1 );
}

console.log( `make-pot: ${ strings } strings, ${ react } references to assets/js/screens bundles.` );
