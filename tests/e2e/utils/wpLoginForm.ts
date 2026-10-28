import { type Page } from '@playwright/test';

/**
 * Log in through wp-login.php and wait until the browser has left it.
 *
 * wp-login.php empties `#user_pass` about 200ms after the page loads (its
 * focus script). Under load a fill could land before that timer and be wiped,
 * so the browser's required check stopped the submit and the test waited out
 * its timeout. The password is filled once the timer has run and checked
 * right before the click.
 */
export async function wpLoginForm( page: Page, baseUrl: string, login: string, password: string ) {
    await page.goto( `${ baseUrl }/wp-login.php`, { waitUntil: 'load' } );
    await page.waitForTimeout( 500 );

    const pass = page.locator( '#user_pass' );
    await page.locator( '#user_login' ).fill( login );
    await pass.fill( password );

    if ( ( await pass.inputValue() ) !== password ) {
        await pass.fill( password );
    }

    await Promise.all( [
        page.waitForURL( ( url ) => ! url.pathname.endsWith( '/wp-login.php' ), { timeout: 30000 } ),
        page.locator( '#wp-submit' ).click(),
    ] );
    await page.waitForLoadState( 'load' );
}
