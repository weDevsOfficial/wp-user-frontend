import { Checkbox } from '@wpuf/components';
import { HelpTextIcon } from '@wpuf/components';

const isOn = ( value ) => true === value || [ 'on', 'yes', 'true', '1' ].includes( value );

/**
 * Checkbox setting (develop type="checkbox") on the shared Checkbox (4.4e):
 * box before the label. Develop's settings checkbox has no hidden input, so
 * unchecked leaves the key out.
 */
export default function CheckboxField( { field, name, value, onChange } ) {
    return (
        <div className="flex items-center">
            <Checkbox
                id={ name }
                value={ isOn( value ) ? 'on' : '' }
                checkedValue="on"
                uncheckedValue={ undefined }
                onChange={ ( next ) => onChange( name, next ) }
                className="mr-2"
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
