import { useCallback } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Button, TextInput } from '@wpuf/components';
import { CONTROL_SIZE } from '../controlSize';
import SettingLabel from './SettingLabel';

/**
 * File / media setting: a URL input plus a "Select" button that opens the
 * WordPress media library (wp.media, enqueued on the settings page). Stores
 * the chosen attachment URL like the legacy `type=file` field (Invoice
 * `set_logo`, Email `header_image`). On the shared wrappers since 4.6a.
 */
export default function FileField( { field, name, value, onChange } ) {
    const current = value || field.default || '';

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
            onChange( name, frame.state().get( 'selection' ).first().toJSON().url );
        } );
        frame.open();
    }, [ name, onChange ] );

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <div className="flex items-center gap-2">
                <TextInput
                    id={ name }
                    className={ `${ CONTROL_SIZE } min-w-0 flex-1` }
                    value={ current }
                    onChange={ ( next ) => onChange( name, next ) }
                    placeholder={ field.placeholder || __( 'No file selected', 'wp-user-frontend' ) }
                />
                <Button variant="secondary" className="shrink-0 h-[40px] font-medium" onClick={ openLibrary }>
                    { __( 'Select', 'wp-user-frontend' ) }
                </Button>
            </div>
            { current && /\.(png|jpe?g|gif|svg|webp)$/i.test( current ) && (
                <img src={ current } alt="" className="mt-2 max-h-16 rounded-sm border border-gray-200" />
            ) }
        </>
    );
}
