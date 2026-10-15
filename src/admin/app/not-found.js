/**
 * The admin app's not-found card, in the kit EmptyState's look. An unknown
 * route loads no screen sheet (nor React), so the card is plain DOM and
 * brings its few rules itself, once.
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';

const CSS = `
.wpuf-app-404{max-width:560px;margin:48px auto;padding:48px 32px;background:#fff;border:1px solid #e5e7eb;border-radius:10px;box-shadow:0 1px 2px rgba(0,0,0,.05);text-align:center;color:#111827;font-family:"Plus Jakarta Sans",ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,sans-serif}
.wpuf-app-404__icon{display:flex;align-items:center;justify-content:center;width:56px;height:56px;margin:0 auto 20px;border-radius:9999px;background:#ecfdf5;color:#059669}
.wpuf-app-404 h2{margin:0 0 8px;font-size:18px;line-height:28px;font-weight:600;color:#111827}
.wpuf-app-404 p{margin:0 auto 24px;max-width:380px;font-size:14px;line-height:22px;color:#6b7280}
.wpuf-app-404__actions{display:flex;flex-wrap:wrap;justify-content:center;gap:12px}
.wpuf-app-404__actions a{display:inline-flex;align-items:center;height:36px;padding:0 16px;border:1px solid #d1d5db;border-radius:6px;background:#fff;color:#374151;font-size:14px;font-weight:500;text-decoration:none;box-shadow:none}
.wpuf-app-404__actions a:hover,.wpuf-app-404__actions a:focus{background:#f9fafb;color:#374151}
.wpuf-app-404__actions a.is-primary{border-color:transparent;background:#059669;color:#fff}
.wpuf-app-404__actions a.is-primary:hover,.wpuf-app-404__actions a.is-primary:focus{background:#10b981;color:#fff}`;

/**
 * Show the card in the app container.
 *
 * @param {HTMLElement} root App container (emptied by the caller).
 */
export default function renderNotFound( root ) {
    if ( ! document.getElementById( 'wpuf-app-404-css' ) ) {
        const style = document.createElement( 'style' );

        style.id = 'wpuf-app-404-css';
        style.textContent = CSS;
        document.head.append( style );
    }

    const box = document.createElement( 'div' );

    box.className = 'wpuf-app-404 wpuf-admin-app-not-found';
    box.setAttribute( 'role', 'status' );
    // lucide "compass" (the kit's icon set) as markup; the texts go in as text.
    box.innerHTML = '<div class="wpuf-app-404__icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="m16.24 7.76-1.804 5.411a2 2 0 0 1-1.265 1.265L7.76 16.24l1.804-5.411a2 2 0 0 1 1.265-1.265z"/></svg></div><h2></h2><p></p><div class="wpuf-app-404__actions"><a class="is-primary" href="#/post-forms"></a><a href="#" data-back=""></a></div>';
    box.querySelector( 'h2' ).textContent = __( 'Page not found', 'wp-user-frontend' );
    box.querySelector( 'p' ).textContent = __( 'This page does not exist. It may have moved, or the link is out of date.', 'wp-user-frontend' );
    box.querySelector( '.is-primary' ).textContent = __( 'Go to Post Forms', 'wp-user-frontend' );
    box.querySelector( '[data-back]' ).textContent = __( 'Go back', 'wp-user-frontend' );
    box.querySelector( '[data-back]' ).addEventListener( 'click', ( event ) => {
        event.preventDefault();
        window.history.back();
    } );

    root.append( box );
}
