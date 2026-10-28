/**
 * Custom hook for clipboard copy with visual feedback.
 *
 * @since WPUF_SINCE
 */
import { useState, useCallback, useRef } from '@wordpress/element';

/**
 * Copy text. The async Clipboard API exists only on secure pages (https or
 * localhost); elsewhere (a plain http admin) copy through a hidden textarea.
 *
 * @param {string} text Text to copy.
 *
 * @return {Promise} Resolves when copied.
 */
export const writeText = ( text ) => {
    if ( window.navigator.clipboard?.writeText ) {
        return window.navigator.clipboard.writeText( text );
    }

    return new Promise( ( resolve, reject ) => {
        const area = document.createElement( 'textarea' );
        area.value = text;
        area.setAttribute( 'readonly', '' );
        area.style.position = 'fixed';
        area.style.opacity = '0';
        document.body.appendChild( area );
        area.select();

        let copied = false;
        try {
            copied = document.execCommand( 'copy' );
        } catch ( e ) {
            copied = false;
        }

        document.body.removeChild( area );

        if ( copied ) {
            resolve();
        } else {
            reject( new Error( 'copy failed' ) );
        }
    } );
};

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

        return writeText( text ).then( () => {
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
