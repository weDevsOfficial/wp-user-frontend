/**
 * The account app: mounts on the `[data-wpuf-mount="account"]` root the
 * `[wpuf_account]` shortcode rendered, with the profile, sections and
 * stats the server put in the boot script.
 *
 * @since WPUF_SINCE
 */
import { dispatch } from '@wordpress/data';
import { createRoot } from '@wordpress/element';
import { applyFilters } from '@wordpress/hooks';
import { mountAll } from '@wpuf/frontend-kit';
import App from './App';
import { store } from './store';
import './account.css';

mountAll( 'account', ( root, boot ) => {
    const data = applyFilters( 'wpuf.frontend.account.boot', boot );

    dispatch( store ).init( data );
    createRoot( root ).render( <App data={ data } root={ root } /> );
} );
