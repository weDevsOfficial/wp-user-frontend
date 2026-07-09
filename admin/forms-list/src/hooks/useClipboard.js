/**
 * Custom hook for clipboard copy with visual feedback.
 *
 * @since WPUF_SINCE
 */
import { useState, useCallback, useRef } from '@wordpress/element';

/**
 * Provides clipboard copy functionality with a 2-second "copied" indicator.
 *
 * @return {Object} { copiedKey, copyToClipboard }
 */
const useClipboard = () => {
    const [ copiedKey, setCopiedKey ] = useState( null );
    const timerRef = useRef( null );

    const copyToClipboard = useCallback( ( text, key ) => {
        if ( timerRef.current ) {
            clearTimeout( timerRef.current );
        }

        return navigator.clipboard.writeText( text ).then( () => {
            setCopiedKey( key );
            timerRef.current = setTimeout( () => {
                setCopiedKey( null );
                timerRef.current = null;
            }, 2000 );
        } );
    }, [] );

    return { copiedKey, copyToClipboard };
};

export default useClipboard;
