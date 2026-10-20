import { useEffect, useCallback, useRef } from '@wordpress/element';
import { select, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dialogs } from '@wpuf/components';
import { STORE_NAME } from '../store';
import { addRouteGuard } from '../../../app/client';
import { waitForPendingClean } from '../common/saveState';

/**
 * Hook that warns users before leaving the page with unsaved changes. In the
 * admin app, leaving the builder route asks first too (no page unload there).
 */
export default function useDirtyState() {
    const isDirty = useSelect( ( select ) => {
        return select( STORE_NAME ).getIsDirty();
    }, [] );
    const dirtyRef = useRef( isDirty );

    dirtyRef.current = isDirty;

    const handleBeforeUnload = useCallback( ( e ) => {
        if ( isDirty ) {
            e.preventDefault();
            e.returnValue = __( 'You have unsaved changes. Are you sure you want to leave?', 'wp-user-frontend' );
        }
    }, [ isDirty ] );

    useEffect( () => {
        window.addEventListener( 'beforeunload', handleBeforeUnload );

        return () => {
            window.removeEventListener( 'beforeunload', handleBeforeUnload );
        };
    }, [ handleBeforeUnload ] );

    useEffect(
        () => addRouteGuard( () => {
            // Nothing unsaved: answer at once (the app leaves without waiting).
            if ( ! dirtyRef.current ) {
                return true;
            }

            return confirmLeave();
        } ),
        []
    );
}

/**
 * Ask before leaving with unsaved changes. Right after a save the form is
 * marked clean a moment later, so wait for that first.
 *
 * @return {Promise<boolean>} Leave.
 */
async function confirmLeave() {
    await waitForPendingClean();

    if ( ! select( STORE_NAME ).getIsDirty() ) {
        return true;
    }

    return dialogs.confirm( {
        tone: 'danger',
        icon: 'warning',
        title: __( 'Unsaved Changes', 'wp-user-frontend' ),
        message: __( 'You have unsaved changes. Are you sure you want to leave?', 'wp-user-frontend' ),
        confirmText: __( 'Discard Changes', 'wp-user-frontend' ),
        cancelText: __( 'Continue Editing', 'wp-user-frontend' ),
    } );
}
