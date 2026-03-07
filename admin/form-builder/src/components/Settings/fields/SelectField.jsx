import { useCallback, useEffect, useRef } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from '../SettingsField';
import HelpTextIcon from './HelpTextIcon';

/**
 * Select dropdown field — matches Vue wpuf_render_settings_field() for type="select".
 * Initializes selectize to match Vue's styled dropdowns.
 */
export default function SelectField( { field, name, value, onChange } ) {
    const options = field.options || {};
    const selectRef = useRef( null );

    const handleChange = useCallback( ( e ) => {
        onChange( name, e.target.value );
    }, [ name, onChange ] );

    // Initialize selectize like Vue does:
    // $('.wpuf-settings-container select:not(.wpuf-no-selectize)').selectize()
    useEffect( () => {
        if ( typeof jQuery === 'undefined' || ! jQuery.fn.selectize || ! selectRef.current ) {
            return;
        }

        const $el = jQuery( selectRef.current );

        if ( $el.data( 'selectize' ) ) {
            return;
        }

        const selectize = $el.selectize( {
            onChange( val ) {
                onChange( name, val );
            },
        } );

        return () => {
            if ( selectize[ 0 ] && selectize[ 0 ].selectize ) {
                selectize[ 0 ].selectize.destroy();
            }
        };
    }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

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
            <select
                ref={ selectRef }
                id={ name }
                defaultValue={ value || field.default || '' }
                onChange={ handleChange }
                className={ SETTING_CLASS_NAMES.dropdown }
            >
                { Object.entries( options ).map( ( [ optValue, optLabel ] ) => (
                    <option key={ optValue } value={ optValue }>
                        { optLabel }
                    </option>
                ) ) }
            </select>
        </>
    );
}
