/**
 * Text input (text, url, email, password) with the WPUF control look:
 * 36px, 8px 12px, 14px, gray-300 border, 6px radius, primary focus ring.
 * onChange gets the string as typed.
 */
import { Input, cn } from '@wedevs/plugin-ui';

export const CONTROL_CLASS = 'h-9 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 bg-white border border-gray-300 rounded-md shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30 disabled:bg-gray-50 disabled:opacity-100 disabled:cursor-not-allowed';

/**
 * @param {Object}   props
 * @param {string}   [props.value]    Value.
 * @param {Function} [props.onChange] ( value: string, event ) => void
 * @param {string}   [props.type]     text|url|email|password|search|tel
 */
export default function TextInput( { value, onChange, type = 'text', className, ...props } ) {
    return (
        <Input
            type={ type }
            value={ value ?? '' }
            onChange={ ( event ) => onChange?.( event.target.value, event ) }
            className={ cn( CONTROL_CLASS, className ) }
            { ...props }
        />
    );
}
