/**
 * Small hooks the field components share.
 *
 * @since WPUF_SINCE
 */
import { useEffect, useRef } from '@wordpress/element';

/**
 * Calls `onOutside` on a pointer down or a focus move outside `ref`, while `active`.
 *
 * @param {Object}   ref       Element ref.
 * @param {boolean}  active    Listen only while true (an open dropdown).
 * @param {Function} onOutside Handler.
 */
export function useOutside( ref, active, onOutside ) {
    const handler = useRef( onOutside );
    handler.current = onOutside;

    useEffect( () => {
        if ( ! active ) {
            return undefined;
        }

        const listener = ( event ) => {
            if ( ref.current && ! ref.current.contains( event.target ) ) {
                handler.current( event );
            }
        };

        document.addEventListener( 'mousedown', listener );
        document.addEventListener( 'focusin', listener );

        return () => {
            document.removeEventListener( 'mousedown', listener );
            document.removeEventListener( 'focusin', listener );
        };
    }, [ ref, active ] );
}

/** A stable id per component instance, for label / control pairs. */
let counter = 0;

export function useUid( prefix = 'wpuf' ) {
    const ref = useRef( null );

    if ( null === ref.current ) {
        counter += 1;
        ref.current = `${ prefix }-${ counter }`;
    }

    return ref.current;
}
