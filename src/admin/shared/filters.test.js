/**
 * Every hook and slot name the React admin uses must be listed in filters.js
 * (public contract), and listed names must be unique.
 */
const fs = require( 'fs' );
const path = require( 'path' );

import { HOOKS, HOOK_NAMES, SLOTS } from './filters';

const root = path.resolve( __dirname, '../../..' );
const dirs = [ 'src/admin', 'src/js' ];

function files( dir ) {
    const abs = path.join( root, dir );

    if ( ! fs.existsSync( abs ) ) {
        return [];
    }

    return fs.readdirSync( abs, { withFileTypes: true } ).flatMap( ( entry ) => {
        const rel = path.join( dir, entry.name );

        if ( entry.isDirectory() ) {
            return [ 'node_modules', '__mocks__' ].includes( entry.name ) ? [] : files( rel );
        }

        return /\.(jsx?|tsx?)$/.test( entry.name ) && ! /\.test\./.test( entry.name ) ? [ rel ] : [];
    } );
}

const sources = dirs.flatMap( files ).map( ( file ) => [ file, fs.readFileSync( path.join( root, file ), 'utf8' ) ] );

test( 'hook names are unique', () => {
    expect( new Set( HOOK_NAMES ).size ).toBe( HOOK_NAMES.length );
    expect( new Set( Object.values( SLOTS ) ).size ).toBe( Object.values( SLOTS ).length );
} );

test( 'every wp.hooks name used in the React admin is listed in HOOKS', () => {
    const used = new Map();

    for ( const [ file, text ] of sources ) {
        for ( const match of text.matchAll( /\b(?:applyFilters|doAction|addFilter|addAction|removeFilter|hasFilter)\(\s*['"`]([^'"`$]+)['"`]/g ) ) {
            used.set( match[ 1 ], file );
        }
    }

    const missing = [ ...used ].filter( ( [ name ] ) => ! HOOK_NAMES.includes( name ) ).map( ( [ name, file ] ) => `${ name } (${ file })` );

    expect( used.size ).toBeGreaterThan( 20 );
    expect( missing ).toEqual( [] );
} );

test( 'every SlotFill name is listed in SLOTS', () => {
    const slots = Object.values( SLOTS );
    const missing = [];

    for ( const [ file, text ] of sources ) {
        for ( const match of text.matchAll( /createSlotFill\(\s*['"`]([^'"`]+)['"`]/g ) ) {
            if ( ! slots.includes( match[ 1 ] ) ) {
                missing.push( `${ match[ 1 ] } (${ file })` );
            }
        }
    }

    expect( missing ).toEqual( [] );
} );

test( 'names keep the wpuf. prefix', () => {
    Object.values( HOOKS ).forEach( ( name ) => expect( name ).toMatch( /^wpuf\./ ) );
} );
