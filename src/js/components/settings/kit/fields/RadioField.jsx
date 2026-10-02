import { useCallback } from '@wordpress/element';
import HelpTextIcon from './HelpTextIcon';

/**
 * Radio group field — covers legacy `radio` and `radio_inline` types.
 * Net-new for the settings screen (the Form Builder kit only ships pic-radio).
 */
export default function RadioField( { field, name, value, onChange, inline = false } ) {
    const options = field.options || {};
    const current = value || field.default || '';

    const handleChange = useCallback(
        ( optValue ) => onChange( name, optValue ),
        [ name, onChange ]
    );

    return (
        <>
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">{ field.label }</label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
            </div>
            <div className={ inline ? 'wpuf-flex wpuf-gap-6' : 'wpuf-space-y-2' }>
                { Object.entries( options ).map( ( [ optValue, optLabel ] ) => (
                    <label key={ optValue } className="wpuf-flex wpuf-items-center wpuf-gap-2 wpuf-cursor-pointer">
                        <input
                            type="radio"
                            name={ name }
                            value={ optValue }
                            checked={ current === optValue }
                            onChange={ () => handleChange( optValue ) }
                            className="!wpuf-mt-0 wpuf-h-4 wpuf-w-4 !wpuf-border-gray-300 checked:!wpuf-border-primary checked:!wpuf-bg-primary focus:!wpuf-ring-transparent"
                        />
                        <span className="wpuf-text-sm wpuf-text-gray-700">
                            { typeof optLabel === 'object' && optLabel !== null
                                ? ( optLabel.label || optLabel.name || optValue )
                                : optLabel }
                        </span>
                    </label>
                ) ) }
            </div>
        </>
    );
}
