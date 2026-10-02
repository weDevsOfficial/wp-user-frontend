import { useCallback, useEffect, useRef } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from '../SettingsField';

/**
 * Date picker field — matches Vue wpuf_render_settings_field() for type="date".
 *
 * Initializes jQuery datetimepicker on mount, mirroring Vue's SettingsTab.init().
 */
export default function DateField( { field, name, value, onChange } ) {
    const inputRef = useRef( null );

    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    useEffect( () => {
        if ( inputRef.current && window.jQuery && jQuery.fn.datetimepicker ) {
            jQuery( inputRef.current ).datetimepicker( {
                onChangeDateTime( dp, $input ) {
                    onChange( name, $input.val() );
                },
            } );
        }

        return () => {
            if ( inputRef.current && window.jQuery && jQuery.fn.datetimepicker ) {
                jQuery( inputRef.current ).datetimepicker( 'destroy' );
            }
        };
    }, [ name, onChange ] );

    return (
        <>
            { field.label && (
                <div className="wpuf-flex wpuf-items-center">
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                </div>
            ) }
            <input
                ref={ inputRef }
                type="text"
                id={ name }
                value={ value || '' }
                onChange={ handleChange }
                className={ `datepicker ${ SETTING_CLASS_NAMES.text }` }
            />
        </>
    );
}
