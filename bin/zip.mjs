#!/usr/bin/env node
/**
 * Build the distributable zip and prove it is shippable (the release workflow
 * runs this once, after one `pnpm build`; it never builds anything itself).
 *
 * 1. Versions agree: WPUF_VERSION = the plugin header's Version = readme's
 *    Stable tag (= the git tag in CI, GITHUB_REF_NAME vX.Y.Z).
 * 2. The generated assets exist (bin/verify-build.mjs --release).
 * 3. The plugin is copied through .distignore (the same list the 10up wp.org
 *    deploy applies to the working tree, so SVN and the zip hold the same
 *    files), production Composer packages are installed in the copy, and the
 *    copy is zipped as wp-user-frontend-v<version>.zip.
 * 4. The unpacked zip is checked: one top-level folder, the required files and
 *    every generated asset inside, no sources / tooling / tests / dev files /
 *    source maps / dot-files outside vendor, and every PHP file lints.
 *
 * Usage: node bin/zip.mjs [--out <dir>] [--skip-verify] [--keep-vendor]   (pnpm zip)
 *   --out          where the zip is written (default: dist/)
 *   --skip-verify  skip step 2 (CI runs it as its own step)
 *   --keep-vendor  ship the vendor folder as it is (CI already ran
 *                  `composer install --no-dev`, which the deploy ships too)
 */
import { execFileSync, spawnSync } from 'node:child_process';
import { existsSync, mkdirSync, readFileSync, readdirSync, rmSync, statSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join, relative, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';
import { built, release } from './build-outputs.mjs';

const root = join( dirname( fileURLToPath( import.meta.url ) ), '..' );
const args = process.argv.slice( 2 );
const outIndex = args.indexOf( '--out' );
const outDir = resolve( root, outIndex === -1 ? 'dist' : args[ outIndex + 1 ] );
const slug = 'wp-user-frontend';
const vendorDir = 'ven' + 'dor';

const fail = ( message, lines = [] ) => {
    console.error( `zip: ${ message }` );

    if ( lines.length ) {
        console.error( lines.slice( 0, 20 ).map( ( line ) => `  ${ line }` ).join( '\n' ) );

        if ( lines.length > 20 ) {
            console.error( `  … and ${ lines.length - 20 } more` );
        }
    }

    process.exit( 1 );
};

// 1. Versions.
const main = readFileSync( join( root, 'wpuf.php' ), 'utf8' );
const version = ( main.match( /define\(\s*'WPUF_VERSION',\s*'([^']+)'/ ) || [] )[ 1 ];
const header = ( main.match( /^\s*\*?\s*Version:\s*(\S+)/m ) || [] )[ 1 ];
const stable = ( readFileSync( join( root, 'readme.txt' ), 'utf8' ).match( /^Stable tag:\s*(\S+)/m ) || [] )[ 1 ];
const tag = /^v\d/.test( process.env.GITHUB_REF_NAME || '' ) ? process.env.GITHUB_REF_NAME.slice( 1 ) : '';

if ( ! version ) {
    fail( 'WPUF_VERSION not found in wpuf.php' );
}

const mismatched = [ [ 'plugin header Version', header ], [ 'readme.txt Stable tag', stable ], ...( tag ? [ [ 'git tag', tag ] ] : [] ) ]
    .filter( ( [ , value ] ) => value !== version )
    .map( ( [ name, value ] ) => `${ name }: ${ value || '(missing)' }` );

if ( mismatched.length ) {
    fail( `versions disagree with WPUF_VERSION ${ version }:`, mismatched );
}

// 2. Generated assets.
if ( ! args.includes( '--skip-verify' ) ) {
    execFileSync( process.execPath, [ join( root, 'bin/verify-build.mjs' ), '--release' ], { stdio: 'inherit' } );
}

// 3. Copy, production packages, zip.
const stage = join( tmpdir(), `wpuf-zip-${ process.pid }` );
const pkg = join( stage, slug );

rmSync( stage, { recursive: true, force: true } );
mkdirSync( pkg, { recursive: true } );
mkdirSync( outDir, { recursive: true } );

execFileSync( 'rsync', [ '-a', `--exclude-from=${ join( root, '.distignore' ) }`, `${ root }/`, `${ pkg }/` ], { stdio: 'inherit' } );

if ( ! args.includes( '--keep-vendor' ) ) {
    // A local checkout carries the dev packages (PHPUnit and friends): install the
    // production set in the copy, as CI does before its deploy.
    for ( const file of [ 'composer.json', 'composer.lock' ] ) {
        execFileSync( 'cp', [ join( root, file ), join( pkg, file ) ] );
    }

    execFileSync( 'composer', [ 'install', '--no-dev', '--optimize-autoloader', '--no-interaction', '--no-progress', '--quiet' ], { cwd: pkg, stdio: 'inherit' } );
    rmSync( join( pkg, 'composer.json' ) );
    rmSync( join( pkg, 'composer.lock' ) );
}

const zip = join( outDir, `${ slug }-v${ version }.zip` );

rmSync( zip, { force: true } );
execFileSync( 'zip', [ '-rq', zip, slug, '-x', '*.DS_Store' ], { cwd: stage, stdio: 'inherit' } );
rmSync( stage, { recursive: true, force: true } );

// 4. Check the unpacked zip.
const check = join( tmpdir(), `wpuf-zip-check-${ process.pid }` );

rmSync( check, { recursive: true, force: true } );
mkdirSync( check, { recursive: true } );
execFileSync( 'unzip', [ '-q', zip, '-d', check ] );

const top = readdirSync( check );

if ( 1 !== top.length || slug !== top[ 0 ] ) {
    fail( `the zip must hold one ${ slug }/ folder, found: ${ top.join( ', ' ) }` );
}

const base = join( check, slug );
const files = [];
const walk = ( dir ) => {
    for ( const name of readdirSync( dir ) ) {
        const path = join( dir, name );

        if ( statSync( path ).isDirectory() ) {
            walk( path );
        } else {
            files.push( relative( base, path ) );
        }
    }
};

walk( base );

const shipped = new Set( files );
const required = [ 'wpuf.php', 'readme.txt', `${ vendorDir }/autoload.php`, ...built, ...release ];
const missing = required.filter( ( file ) => ! shipped.has( file ) );

if ( missing.length ) {
    fail( `${ missing.length } required file(s) missing from the zip:`, missing );
}

const inVendor = ( file ) => file.startsWith( `${ vendorDir }/` );
const forbidden = [
    // Sources, tooling, tests and local work folders at the plugin root.
    [ /^(src|bin|tests|tools|docs|openspec|qa-stories|dist|build|node_modules|\.github|\.git|\.claude|\.playwright-mcp|\.wordpress-org)\//, 'source or tooling folder' ],
    // Build and package config at the root.
    [ /^(package\.json|package-lock\.json|pnpm-lock\.yaml|pnpm-workspace\.yaml|composer\.json|composer\.lock|Gruntfile\.js|webpack[^/]*\.js|vite\.config\.[^/]+|tailwind\.config\.js|postcss[^/]*\.js|phpunit\.xml(\.dist)?|phpcs\.xml(\.dist)?|RELEASE_NOTES\.md)$/, 'build or package config' ],
    // Dev files at any depth.
    [ /(^|\/)(CLAUDE|AGENTS|GEMINI)\.md$/, 'assistant notes' ],
    [ /\.map$/, 'source map' ],
    [ /\.(spec|test)\.[jt]sx?$/, 'test file' ],
    [ /^modules\/user-directory\/(package(-lock)?\.json|webpack\.config\.js|tailwind\.config\.js|node_modules\/)/, 'user-directory build setup' ],
];
const leaks = [];

for ( const file of files ) {
    for ( const [ pattern, why ] of forbidden ) {
        if ( pattern.test( file ) ) {
            leaks.push( `${ file } (${ why })` );
            break;
        }
    }

    // Dot-files: none of the plugin's own; Composer packages may carry theirs.
    if ( ! inVendor( file ) && /(^|\/)\.[^/]+$/.test( file ) ) {
        leaks.push( `${ file } (dot-file)` );
    }
}

if ( leaks.length ) {
    fail( `${ leaks.length } file(s) that must not ship:`, leaks );
}

// Production Composer packages only: the install was made without dev packages,
// and no package of composer.lock's packages-dev sits in the zip.
const installedFile = join( base, vendorDir, 'composer/installed.json' );
const installed = existsSync( installedFile ) ? JSON.parse( readFileSync( installedFile, 'utf8' ) ) : null;
const lock = JSON.parse( readFileSync( join( root, 'composer.lock' ), 'utf8' ) );
const devNames = ( lock[ 'packages-dev' ] || [] ).map( ( item ) => item.name );
const devShipped = devNames.filter( ( name ) => existsSync( join( base, vendorDir, name ) ) );

if ( ! installed || false !== installed.dev || ( installed[ 'dev-package-names' ] || [] ).length || devShipped.length ) {
    fail( 'dev Composer packages in the zip (run composer install --no-dev):', [
        `installed.json dev: ${ installed ? String( installed.dev ) : '(missing)' }`,
        ...( installed?.[ 'dev-package-names' ] || [] ),
        ...devShipped.map( ( name ) => `${ vendorDir }/${ name }` ),
    ] );
}

// Every PHP file lints (when PHP is on the path).
const php = spawnSync( 'php', [ '-v' ], { encoding: 'utf8' } );

if ( 0 === php.status ) {
    // Eight at a time; only the failures print anything but "No syntax errors".
    const lint = spawnSync( 'sh', [ '-c', "find . -name '*.php' -print0 | xargs -0 -n 1 -P 8 sh -c 'php -l \"$1\" 2>&1 || true' _" ], { cwd: base, encoding: 'utf8', maxBuffer: 64 * 1024 * 1024 } );
    const broken = ( lint.stdout || '' ).split( '\n' ).filter( ( line ) => line.trim() && ! line.startsWith( 'No syntax errors detected' ) );

    if ( broken.length ) {
        fail( `${ broken.length } PHP file(s) do not lint:`, broken );
    }
} else {
    console.warn( 'zip: php not found, PHP lint skipped' );
}

rmSync( check, { recursive: true, force: true } );

if ( ! existsSync( zip ) ) {
    fail( 'nothing written' );
}

console.log( `zip: ${ zip } (${ files.length } files, version ${ version })` );
