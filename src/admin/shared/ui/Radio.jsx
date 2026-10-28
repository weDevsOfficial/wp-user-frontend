/**
 * Radio group: options [{ value, label }] or { value: label }; onChange gets
 * the option value (string).
 */
import { RadioGroup, RadioGroupItem, cn } from '@wedevs/plugin-ui';

import { normalizeOptions, selectShownValue } from './values';

/**
 * @param {Object}       props
 * @param {*}            [props.value]       Stored value.
 * @param {Array|Object} props.options       Options.
 * @param {Function}     [props.onChange]    ( value: string ) => void
 * @param {string}       [props.name]        Group name (also the id prefix).
 * @param {boolean}      [props.inline]      Options in one row.
 */
export default function Radio( { value, options, onChange, name = 'wpuf-radio', inline = false, disabled, className, ...props } ) {
    const items = normalizeOptions( options );

    return (
        <RadioGroup
            name={ name }
            value={ selectShownValue( value, items ) }
            onValueChange={ ( next ) => onChange?.( next ) }
            disabled={ disabled }
            className={ cn( inline ? 'flex flex-wrap gap-4' : 'grid gap-2', className ) }
            { ...props }
        >
            { items.map( ( item ) => {
                const id = `${ name }-${ item.value }`;

                return (
                    <label key={ item.value } htmlFor={ id } className="inline-flex items-center gap-2 text-sm text-gray-700 cursor-pointer">
                        <RadioGroupItem id={ id } value={ item.value } data-value={ item.value } disabled={ item.disabled } className="cursor-pointer" />
                        <span>{ item.label }</span>
                    </label>
                );
            } ) }
        </RadioGroup>
    );
}
