/**
 * The dashboard app: mounts on every `[data-wpuf-mount="dashboard"]` root
 * the `[wpuf_dashboard]` shortcode rendered (Frontend_Dashboard::mount_dashboard()),
 * with the first page and the signed attributes from the boot script.
 *
 * @since WPUF_SINCE
 */
import { dispatch } from '@wordpress/data';
import { createRoot } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { mountAll } from '@wpuf/frontend-kit';
import Dashboard from './Dashboard';
import { store } from './store';
import './dashboard.css';

mountAll( 'dashboard', ( root, boot ) => {
    const data = applyFilters( 'wpuf.frontend.dashboard.boot', boot );
    const mountKey = root.id;
    const signed = { atts: data.atts, sig: data.sig };

    dispatch( store ).init( mountKey, data.initial, data.deleted ? __( 'Post Deleted', 'wp-user-frontend' ) : '' );

    if ( data.error ) {
        dispatch( store ).setError( mountKey, data.error );
    }

    createRoot( root ).render( <Dashboard mountKey={ mountKey } signed={ signed } /> );
} );
