/**
 * The account app: mounts on the `[data-wpuf-mount="account"]` root the
 * `[wpuf_account]` shortcode rendered, with the profile, sections and
 * stats the server put in the boot script.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { mountAll } from '@wpuf/frontend-kit';
import App from './App';
import './account.css';

mountAll( 'account', ( root, boot ) => {
    createRoot( root ).render( <App boot={ boot } root={ root } /> );
} );
