import { pagesAround, pagesFixed } from './pages';

// The develop algorithms, copied as the oracle.
function devForms( current, total ) {
    if ( total <= 1 ) return [];
    const r = [];
    for ( let i = Math.max( 1, current - 2 ); i <= Math.min( total, current + 2 ); i++ ) r.push( i );
    return r;
}
function devSubs( current, total, max = 3 ) {
    let start;
    if ( current === 1 || total <= max ) start = 1;
    else if ( current === total ) start = total - max;
    else start = current - 1;
    const r = [];
    for ( let i = start; i <= Math.min( start + max - 1, total ); i++ ) r.push( i );
    return r;
}

test( 'page windows equal develop for every page of 1..12 pages', () => {
    for ( let total = 0; total <= 12; total++ ) {
        for ( let current = 1; current <= Math.max( 1, total ); current++ ) {
            expect( pagesAround( current, total ) ).toEqual( devForms( current, total ) );
            expect( pagesFixed( current, total ) ).toEqual( devSubs( current, total ) );
        }
    }
} );
