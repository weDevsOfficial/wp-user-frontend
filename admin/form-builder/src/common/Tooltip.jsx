import { useState, useRef, useEffect } from '@wordpress/element';

/**
 * Tooltip component for help text.
 * Replaces the Vue help-text component and jQuery tooltip.
 *
 * @param {Object} props
 * @param {string} props.text    Tooltip text content
 * @param {string} props.placement 'top' | 'bottom' | 'left' | 'right'
 */
export default function Tooltip( { text, placement = 'top', children } ) {
    const [ visible, setVisible ] = useState( false );
    const ref = useRef( null );

    if ( ! text ) {
        return children || null;
    }

    return (
        <span
            ref={ ref }
            className="wpuf-tooltip-wrapper"
            style={ { position: 'relative', display: 'inline-block' } }
            onMouseEnter={ () => setVisible( true ) }
            onMouseLeave={ () => setVisible( false ) }
        >
            { children || (
                <span className="wpuf-tooltip-icon dashicons dashicons-editor-help" />
            ) }
            { visible && (
                <span
                    className={ `wpuf-tooltip wpuf-tooltip-${ placement }` }
                    role="tooltip"
                    dangerouslySetInnerHTML={ { __html: text } }
                />
            ) }
        </span>
    );
}
