import { Checkbox } from '@wpuf/components';
import { HelpTextIcon } from '@wpuf/components';

const isOn = ( value ) => true === value || 'yes' === value || 'on' === value;

// Unset option: the field default, as the legacy screen showed it (`std`).
const current = ( value, field ) => ( undefined !== value && null !== value ? value : field.default );

/**
 * Checkbox setting on the shared Checkbox (4.6a): box before the label,
 * stores 'on' / 'off' (legacy shape).
 */
export default function CheckboxField( { field, name, value, onChange } ) {
    return (
        <div className="flex items-center">
            <Checkbox
                id={ name }
                className="mr-2"
                value={ isOn( current( value, field ) ) ? 'on' : 'off' }
                checkedValue="on"
                uncheckedValue="off"
                onChange={ ( next ) => onChange( name, next ) }
            />
            { field.label && (
                <label htmlFor={ name } className="text-sm font-medium leading-5 text-gray-700 my-1.5">
                    { field.label }
                </label>
            ) }
            { field.help_text && <HelpTextIcon text={ field.help_text } /> }
        </div>
    );
}
