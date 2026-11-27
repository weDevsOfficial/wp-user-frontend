/**
 * Textarea with the WPUF control look; onChange gets the text as typed.
 */
import { Textarea as PuiTextarea, cn } from '@wedevs/plugin-ui';

/**
 * @param {Object}   props
 * @param {string}   [props.value]    Value.
 * @param {Function} [props.onChange] ( value: string, event ) => void
 * @param {number}   [props.rows=4]   Rows.
 */
export default function Textarea( { value, onChange, rows = 4, className, ...props } ) {
    return (
        <PuiTextarea
            rows={ rows }
            value={ value ?? '' }
            onChange={ ( event ) => onChange?.( event.target.value, event ) }
            className={ cn( 'field-sizing-fixed min-h-0 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-400 bg-white border border-gray-300 rounded-md shadow-none focus-visible:border-primary focus-visible:ring-2 focus-visible:ring-primary/30 disabled:bg-gray-50 disabled:cursor-not-allowed', className ) }
            { ...props }
        />
    );
}
