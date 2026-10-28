/**
 * Number input (value stays the string as typed) and its range variant.
 * Negative numbers are blocked unless allowNegative (develop behaviour).
 */
import { Input, cn } from '@wedevs/plugin-ui';

import { CONTROL_CLASS } from './TextInput';
import { numberKeyAllowed } from './values';

/**
 * @param {Object}   props
 * @param {string}   [props.value]         Value (string as typed).
 * @param {Function} [props.onChange]      ( value: string, event ) => void
 * @param {boolean}  [props.allowNegative] Allow `-`.
 * @param {string}   [props.variant]       'number' (default) or 'range'.
 */
export default function NumberInput( { value, onChange, allowNegative = false, variant = 'number', className, onKeyDown, ...props } ) {
    const change = ( event ) => onChange?.( event.target.value, event );

    if ( 'range' === variant ) {
        return (
            <input
                data-wpuf-ui=""
                type="range"
                value={ value ?? '' }
                onChange={ change }
                className={ cn( 'w-full accent-primary cursor-pointer', className ) }
                { ...props }
            />
        );
    }

    return (
        <Input
            type="number"
            value={ value ?? '' }
            onChange={ change }
            onKeyDown={ ( event ) => {
                if ( ! numberKeyAllowed( event.key, allowNegative ) ) {
                    event.preventDefault();
                }
                onKeyDown?.( event );
            } }
            className={ cn( CONTROL_CLASS, className ) }
            { ...props }
        />
    );
}
