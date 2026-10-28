import { NumberInput } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Number setting (develop type="number") on the shared NumberInput (4.4e);
 * the value stays the string as typed, as develop's input posted it.
 */
export default function NumberField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <NumberInput
                id={ name }
                className="w-full"
                value={ value ?? '' }
                onChange={ ( next ) => onChange( name, next ) }
                min={ field.min }
                max={ field.max }
                step={ field.step || 1 }
                allowNegative
            />
        </>
    );
}
