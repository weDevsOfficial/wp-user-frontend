import { NumberInput } from '@wpuf/components';
import { CONTROL_SIZE } from '../controlSize';
import SettingLabel, { LongHelp } from './SettingLabel';

/**
 * Number setting on the shared NumberInput (4.6a). The value stays the string
 * as typed (legacy shape); negatives are allowed unless the field sets a
 * minimum of 0 or more, as the legacy number input.
 */
export default function NumberField( { field, name, value, onChange } ) {
    const min = undefined === field.min || '' === field.min ? undefined : Number( field.min );

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <NumberInput
                id={ name }
                className={ CONTROL_SIZE }
                value={ value !== undefined && value !== null ? value : ( field.default || '' ) }
                onChange={ ( next ) => onChange( name, next ) }
                allowNegative={ ! ( min >= 0 ) }
                min={ field.min }
                max={ field.max }
                step={ field.step || 1 }
            />
            <LongHelp html={ field.long_help } />
        </>
    );
}
