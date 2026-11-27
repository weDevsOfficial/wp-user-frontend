/**
 * React hooks shared by the WPUF admin screens (window.wpuf.hooks).
 */
import { useEffect } from '@wordpress/element';

/**
 * The screen's boot payload (`window.wpufAdmin`, printed by Admin\BootPayload).
 *
 * @return {Object} Boot payload ({} when missing).
 */
export function useBoot() {
    return ( 'undefined' !== typeof window && window.wpufAdmin ) || {};
}

/**
 * Whether the current user may manage WPUF (`wpuf_admin_role()`), from the
 * boot payload. The server still checks every request.
 *
 * @return {boolean} Can manage.
 */
export function useCan() {
    return true === useBoot().canManage;
}

/**
 * Ask before leaving the page while there are unsaved changes.
 *
 * @param {boolean} dirty Whether there are unsaved changes.
 */
export function useUnsavedGuard( dirty ) {
    useEffect( () => {
        if ( ! dirty ) {
            return undefined;
        }

        const onBeforeUnload = ( event ) => {
            event.preventDefault();
            // Chrome needs returnValue set; the browser shows its own text.
            event.returnValue = '';
        };

        window.addEventListener( 'beforeunload', onBeforeUnload );

        return () => window.removeEventListener( 'beforeunload', onBeforeUnload );
    }, [ dirty ] );
}
