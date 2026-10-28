import { Radio } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Radio group for field settings (Vue field-radio), on the shared Radio
 * wrapper (4.4c). The stored option value is kept as is; inline options sit
 * in one row like develop's.
 */
export default function RadioInput( { optionField, field, value, onChange } ) {
    return (
        <div className="panel-field-opt panel-field-opt-radio">
            <div className="flex">
                <label className="wpuf-option-field-title text-gray-700 font-medium">
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>
            <Radio
                name={ `wpuf-${ optionField.name }-${ field ? field.id : 'field' }` }
                options={ optionField.options || {} }
                value={ value }
                onChange={ ( next ) => onChange( next ) }
                inline={ !! optionField.inline }
                className={ optionField.inline ? 'gap-x-8' : 'gap-3' }
            />
        </div>
    );
}
