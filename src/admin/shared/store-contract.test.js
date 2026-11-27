/**
 * The @wordpress/data stores expose their modules with `import * as actions`
 * / `selectors`, so an action or selector another thunk calls through
 * `dispatch( STORE ).name()` / `select( STORE ).name()` must stay exported
 * even when nothing imports it by name (a pruning pass once un-exported
 * `setListError` and the subscriptions list stopped loading).
 */
const { readFileSync, readdirSync, statSync } = require( 'node:fs' );
const { join, resolve } = require( 'node:path' );

const root = resolve( __dirname, '..', 'apps' );

function walk( dir, out = [] ) {
    for ( const name of readdirSync( dir ) ) {
        const file = join( dir, name );

        if ( statSync( file ).isDirectory() ) {
            if ( 'node_modules' !== name && '__mocks__' !== name ) {
                walk( file, out );
            }
        } else if ( /(^|\/)(actions|selectors)\.js$/.test( file ) ) {
            out.push( file );
        }
    }

    return out;
}

const storeFiles = walk( root );

test( 'store action and selector modules exist', () => {
    expect( storeFiles.length ).toBeGreaterThan( 5 );
} );

test.each( storeFiles )( '%s exports every name its own code calls on the store', ( file ) => {
    const source = readFileSync( file, 'utf8' );
    const defined = new Set( [ ...source.matchAll( /^(export\s+)?(?:const|function)\s+(\w+)/gm ) ].map( ( m ) => m[ 2 ] ) );
    const exported = new Set( [ ...source.matchAll( /^export\s+(?:const|function)\s+(\w+)/gm ) ].map( ( m ) => m[ 1 ] ) );
    // dispatch( STORE ).name(, dispatch.name(, select( STORE ).name(, registry.dispatch( X ).name(
    const called = new Set( [ ...source.matchAll( /(?:dispatch|select)(?:\([^)]*\))?\s*\.\s*(\w+)\s*\(/g ) ].map( ( m ) => m[ 1 ] ) );
    const missing = [ ...called ].filter( ( name ) => defined.has( name ) && ! exported.has( name ) );

    expect( missing ).toEqual( [] );
} );
