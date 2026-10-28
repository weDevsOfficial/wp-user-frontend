/**
 * Entry point for the Forms List React application.
 *
 * @since WPUF_SINCE
 */
import { createRoot } from '@wordpress/element';
import { doAction } from '@wordpress/hooks';
import FormsListApp from './FormsListApp';
import { registerScreen } from '../../app/client';

// On its own page it mounts into the list container; in the admin app the
// shell mounts it on the post or registration list route (task 5d), after
// writing that list's window globals.
registerScreen( 'forms-list', [ 'wpuf-post-forms-list-table-view', 'wpuf-profile-forms-list-table-view' ], ( container, context ) => {
    const root = createRoot( container );
    const route = context && context.route ? context.route.id : '';

    // A post form's Submissions page shares the list's boot, sheet and shell.
    const submissionsOf = 'post-form-submissions' === route ? parseInt( context.params.id, 10 ) || 0 : 0;

    root.render( <FormsListApp submissionsOf={ submissionsOf } /> );

    return () => root.unmount();
} );

doAction( 'wpuf.formsList.init' );
