import { useCallback, useEffect, useRef } from '@wordpress/element';
import { SETTING_CLASS_NAMES } from '../SettingsField';
import HelpTextIcon from './HelpTextIcon';

/**
 * Multi-select field — matches Vue wpuf_render_settings_field() for type="multi-select".
 * Initializes selectize when jQuery is available.
 */
export default function MultiSelectField( { field, name, value, onChange } ) {
    const options = field.options || {};
    const selectRef = useRef( null );
    const alwaysSelected = field.always_selected || [];

    const currentValue = Array.isArray( value ) ? value : ( value ? [ value ] : [] );

    const handleChange = useCallback( ( e ) => {
        const selected = Array.from( e.target.selectedOptions, ( opt ) => opt.value );
        const merged = [ ...new Set( [ ...alwaysSelected, ...selected ] ) ];
        onChange( name, merged );
    }, [ name, onChange, alwaysSelected ] );

    // Initialize selectize if available
    useEffect( () => {
        if ( typeof jQuery === 'undefined' || ! jQuery.fn.selectize || ! selectRef.current ) {
            return;
        }

        const $el = jQuery( selectRef.current );

        if ( $el.data( 'selectize' ) ) {
            return;
        }

        const selectize = $el.selectize( {
            plugins: [ 'remove_button' ],
            onChange( selectedValues ) {
                const merged = [ ...new Set( [ ...alwaysSelected, ...( selectedValues || [] ) ] ) ];
                onChange( name, merged );
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
                multiple
                value={ currentValue }
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
