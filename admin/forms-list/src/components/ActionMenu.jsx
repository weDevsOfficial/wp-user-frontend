/**
 * ActionMenu component — three-dot dropdown menu for per-row actions.
 *
 * @since WPUF_SINCE
 */
import { useState, useRef, useEffect } from '@wordpress/element';

const ActionMenu = ( { items, onAction } ) => {
    const [ isOpen, setIsOpen ] = useState( false );
    const menuRef = useRef( null );

    useEffect( () => {
        const handleClickOutside = ( event ) => {
            if ( menuRef.current && ! menuRef.current.contains( event.target ) ) {
                setIsOpen( false );
            }
        };

        if ( isOpen ) {
            document.addEventListener( 'mousedown', handleClickOutside );
        }

        return () => {
            document.removeEventListener( 'mousedown', handleClickOutside );
        };
    }, [ isOpen ] );

    return (
        <div ref={ menuRef } className="wpuf-relative wpuf-inline-block wpuf-text-left">
            <div>
                <button
                    type="button"
                    onClick={ () => setIsOpen( ! isOpen ) }
                    className="wpuf-inline-flex wpuf-w-full wpuf-justify-center wpuf-rounded-md wpuf-px-2 wpuf-py-2 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50 focus:wpuf-outline-none focus-visible:wpuf-ring-2 focus-visible:wpuf-ring-white focus-visible:wpuf-ring-opacity-75"
                >
                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" className="wpuf-h-5 wpuf-w-5 wpuf-text-gray-400 hover:wpuf-text-gray-600">
                        <path d="M5 12H5.01M12 12H12.01M19 12H19.01M6 12C6 12.5523 5.55228 13 5 13C4.44772 13 4 12.5523 4 12C4 11.4477 4.44772 11 5 11C5.55228 11 6 11.4477 6 12ZM13 12C13 12.5523 12.5523 13 12 13C11.4477 13 11 12.5523 11 12C11 11.4477 11.4477 11 12 11C12.5523 11 13 11.4477 13 12ZM20 12C20 12.5523 19.5523 13 19 13C18.4477 13 18 12.5523 18 12C18 11.4477 18.4477 11 19 11C19.5523 11 20 11.4477 20 12Z" stroke="currentColor" strokeWidth="1.5" strokeLinecap="round" strokeLinejoin="round" />
                    </svg>
                </button>
            </div>

            { isOpen && (
                <div className="wpuf-absolute wpuf-right-0 wpuf-mt-2 wpuf-w-40 wpuf-origin-top-right wpuf-divide-y wpuf-divide-gray-100 wpuf-rounded-md wpuf-bg-white wpuf-shadow-lg wpuf-ring-1 wpuf-ring-black wpuf-ring-opacity-5 focus:wpuf-outline-none wpuf-z-10">
                    <div className="wpuf-px-1 wpuf-py-1">
                        { items.map( ( item ) => (
                            <button
                                key={ item.action }
                                onClick={ () => {
                                    onAction( item.action );
                                    setIsOpen( false );
                                } }
                                className={
                                    'wpuf-group wpuf-flex wpuf-w-full wpuf-items-center wpuf-rounded-md wpuf-px-2 wpuf-py-2 wpuf-text-sm wpuf-bg-transparent wpuf-border-0 wpuf-cursor-pointer ' +
                                    ( item.className || '!wpuf-text-gray-900' ) + ' ' +
                                    ( item.hoverClassName || 'hover:!wpuf-bg-primary hover:!wpuf-text-white' )
                                }
                            >
                                { item.label }
                            </button>
                        ) ) }
                    </div>
                </div>
            ) }
        </div>
    );
};

export default ActionMenu;
