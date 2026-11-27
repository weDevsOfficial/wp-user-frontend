/**
 * Bridge from the subscriptions notice store (quick edit, preferences) to
 * the shared toasts: each notice is shown with `notify` (plugin-ui's default
 * Toaster in WpufProviders) and then removed from the store.
 */
import { useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { notify } from '@wpuf/components';

export default function Notices() {
    const notices = useSelect( ( select ) => select( 'wpuf/subscriptions-notice' ).getNotices(), [] );
    const { removeNotice } = useDispatch( 'wpuf/subscriptions-notice' );

    useEffect( () => {
        if ( ! notices || ! notices.length ) {
            return;
        }

        notices.forEach( ( notice ) => notify( notice.message, notice.type ) );

        // Last index first, so the earlier indexes stay valid.
        for ( let index = notices.length - 1; index >= 0; index-- ) {
            removeNotice( index );
        }
    }, [ notices, removeNotice ] );

    return null;
}
