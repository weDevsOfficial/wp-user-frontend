import { useEffect, useCallback } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../store';

/**
 * Hook that warns users before leaving the page with unsaved changes.
 */
export default function useDirtyState() {
    const isDirty = useSelect( ( select ) => {
        return select( STORE_NAME ).getIsDirty();
    }, [] );

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
}
