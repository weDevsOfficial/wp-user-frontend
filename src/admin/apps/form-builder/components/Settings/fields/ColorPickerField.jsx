import { ColorPicker } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Color setting (develop type="color-picker") on the shared ColorPicker
 * (4.4e): label left, picker right in develop's 2/5 row. Shows the resolved
 * value (stored, else the default); stores the picked hex.
 */
export default function ColorPickerField( { field, name, value, onChange } ) {
    return (
        <div className="flex items-center justify-between w-2/5">
            <SettingLabel field={ field } htmlFor={ name } />
            <ColorPicker
                id={ name }
                className="ml-2"
                value={ value || field.default || '' }
                onChange={ ( next ) => onChange( name, next ) }
            />
        </div>
    );
}
