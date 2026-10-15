/**
 * The admin app's not-found card (a state card).
 *
 * @since WPUF_SINCE
 */
import { __ } from '@wordpress/i18n';
import renderStateCard from './state-card';

/**
 * Show the card in the app container.
 *
 * @param {HTMLElement} root App container (emptied by the caller).
 */
export default function renderNotFound( root ) {
    renderStateCard( root, {
        icon: 'compass',
        className: 'wpuf-admin-app-not-found',
        title: __( 'Page not found', 'wp-user-frontend' ),
        text: __( 'This page does not exist. It may have moved, or the link is out of date.', 'wp-user-frontend' ),
        actions: [
            { label: __( 'Go to Post Forms', 'wp-user-frontend' ), href: '#/post-forms', primary: true },
            { label: __( 'Go back', 'wp-user-frontend' ), back: true },
        ],
    } );
}
