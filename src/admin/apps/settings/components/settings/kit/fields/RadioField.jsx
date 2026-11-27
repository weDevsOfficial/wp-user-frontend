import { Radio } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { selectOptions } from './SelectDropdown';

/**
 * Radio setting (legacy `radio` / `radio_inline`) on the shared Radio (4.6a).
 */
export default function RadioField( { field, name, value, onChange, inline = false } ) {
    return (
        <>
            <SettingLabel field={ field } />
            <Radio
                name={ name }
                options={ selectOptions( field.options ) }
                value={ value || field.default || '' }
                onChange={ ( next ) => onChange( name, next ) }
                inline={ inline }
                className={ inline ? 'gap-x-6' : 'gap-y-2' }
            />
        </>
    );
}
