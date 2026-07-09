import { useCallback } from '@wordpress/element';
import HelpTextIcon from './HelpTextIcon';

/**
 * Checkbox-group field for legacy `wpuf_settings_multiselect` callback fields
 * (e.g. Show Admin Bar roles, exportable post types). Stores an ARRAY of the
 * checked option keys — identical shape to the legacy multi-select storage.
 */
export default function CheckboxGroupField( { field, name, value, onChange } ) {
    const options = field.options || {};
    const selected = Array.isArray( value )
        ? value
        : ( value ? Object.values( value ) : ( field.default || [] ) );

    const toggle = useCallback(
        ( key ) => {
            const set = new Set( selected );
            if ( set.has( key ) ) {
                set.delete( key );
            } else {
                set.add( key );
            }
            onChange( name, Array.from( set ) );
        },
        [ name, onChange, selected ]
    );

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ field.label }</label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className="wpuf-mt-1 wpuf-grid wpuf-grid-cols-2 wpuf-gap-x-6 wpuf-gap-y-2">
                { Object.entries( options ).map( ( [ key, optLabel ] ) => (
                    <label key={ key } className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-cursor-pointer">
                        <input
                            type="checkbox"
                            checked={ selected.includes( key ) }
                            onChange={ () => toggle( key ) }
                            className="wpuf-h-4 wpuf-w-4 wpuf-rounded !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary focus:!wpuf-ring-transparent"
                        />
                        <span className="wpuf-text-sm wpuf-text-gray-700">{ optLabel }</span>
                    </label>
                ) ) }
            </div>
        </>
    );
}
