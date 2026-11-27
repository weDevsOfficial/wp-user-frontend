/**
 * Page-number windows of the two develop paginations (kept exactly).
 */

/**
 * Forms list: pages current-delta .. current+delta; none for a single page.
 *
 * @param {number} current Current page.
 * @param {number} total   Total pages.
 * @param {number} [delta] Pages each side (develop 2).
 *
 * @return {number[]} Pages.
 */
export function pagesAround( current, total, delta = 2 ) {
    if ( total <= 1 ) {
        return [];
    }

    const pages = [];

    for ( let page = Math.max( 1, current - delta ); page <= Math.min( total, current + delta ); page++ ) {
        pages.push( page );
    }

    return pages;
}

/**
 * Subscriptions: `max` buttons starting at 1 on the first page (or when all
 * fit), at total - max on the last page, else at current - 1.
 *
 * @param {number} current Current page.
 * @param {number} total   Total pages.
 * @param {number} [max]   Visible buttons (develop 3).
 *
 * @return {number[]} Pages.
 */
export function pagesFixed( current, total, max = 3 ) {
    let start = current - 1;

    if ( 1 === current || total <= max ) {
        start = 1;
    } else if ( current === total ) {
        start = total - max;
    }

    const pages = [];

    for ( let page = start; page <= Math.min( start + max - 1, total ); page++ ) {
        pages.push( page );
    }

    return pages;
}
