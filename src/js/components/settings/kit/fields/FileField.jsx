import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import HelpTextIcon from './HelpTextIcon';

/**
 * File / media field — a URL input plus a "Select" button that opens the
 * WordPress media library (wp.media, enqueued on the settings page). Stores the
 * chosen attachment URL, matching the legacy `type=file` settings field
 * (Invoice `set_logo`, Email `header_image`).
 */
export default function FileField( { field, name, value, onChange } ) {
    const current = value || field.default || '';

    const handleInput = useCallback( ( e ) => onChange( name, e.target.value ), [ name, onChange ] );

    const openLibrary = useCallback( () => {
        const media = window.wp && window.wp.media;
        if ( ! media ) {
            return;
        }
        const frame = media( {
            title: __( 'Select or Upload', 'wp-user-frontend' ),
            button: { text: __( 'Use this file', 'wp-user-frontend' ) },
            multiple: false,
        } );
        frame.on( 'select', () => {
            const attachment = frame.state().get( 'selection' ).first().toJSON();
            onChange( name, attachment.url );
        } );
        frame.open();
    }, [ name, onChange ] );

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-flex wpuf-items-center wpuf-gap-2">
                <input
                    type="text"
                    id={ name }
                    value={ current }
                    onChange={ handleInput }
                    className="wpuf-min-w-0 wpuf-flex-1 !wpuf-py-2.5 !wpuf-px-3.5 wpuf-text-gray-700 !wpuf-shadow-sm placeholder:wpuf-text-gray-400 wpuf-border !wpuf-border-gray-300 !wpuf-rounded-md"
                    placeholder={ field.placeholder || __( 'No file selected', 'wp-user-frontend' ) }
                />
                <button
                    type="button"
                    onClick={ openLibrary }
                    className="wpuf-shrink-0 wpuf-rounded-md wpuf-border !wpuf-border-gray-300 wpuf-bg-white wpuf-px-4 wpuf-py-2.5 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700 hover:wpuf-bg-gray-50"
                >
                    { __( 'Select', 'wp-user-frontend' ) }
                </button>
            </div>
            { current && /\.(png|jpe?g|gif|svg|webp)$/i.test( current ) && (
                <img src={ current } alt="" className="wpuf-mt-2 wpuf-max-h-16 wpuf-rounded wpuf-border wpuf-border-gray-200" />
            ) }
        </>
    );
}
