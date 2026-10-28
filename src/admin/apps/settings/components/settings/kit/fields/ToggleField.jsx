import { Toggle } from '@wpuf/components';
import SettingLabel from './SettingLabel';

const isOn = ( value ) => true === value || 'yes' === value || 'on' === value;

// Unset option: the field default, as the legacy screen showed it (`std`).
const current = ( value, field ) => ( undefined !== value && null !== value ? value : field.default );

/**
 * Toggle setting on the shared Toggle (4.6a): label left, switch right in a
 * 2/5 row; stores 'on' / 'off'.
 */
export default function ToggleField( { field, name, value, onChange } ) {
    return (
        <div className="flex items-center justify-between w-2/5">
            <SettingLabel field={ field } htmlFor={ name } />
            <Toggle
                id={ name }
                className="ml-2"
                value={ isOn( current( value, field ) ) ? 'on' : 'off' }
                checkedValue="on"
                uncheckedValue="off"
                onChange={ ( next ) => onChange( name, next ) }
            />
        </div>
    );
}
