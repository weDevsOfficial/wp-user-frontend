/**
 * Entry point for the Forms List React application.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { doAction } from '@wordpress/hooks';
import FormsListApp from './FormsListApp';

const container = document.getElementById( 'wpuf-post-forms-list-table-view' )
    || document.getElementById( 'wpuf-profile-forms-list-table-view' );

if ( container ) {
    createRoot( container ).render( <FormsListApp /> );
}

doAction( 'wpuf.formsList.init' );
