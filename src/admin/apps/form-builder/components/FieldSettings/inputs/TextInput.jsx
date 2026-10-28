import { TextInput as SharedTextInput, NumberInput } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Text / number input for field settings (Vue field-text), on the shared
 * wrappers (TextInput, NumberInput; 4.4c). The stored value shows as it is
 * (`?? ''`: a stored 0 stays 0); number options keep develop's free typing,
 * negative values included.
 */
export default function TextInput( { optionField, value, onChange } ) {
    const isNumber = optionField.variation === 'number' || optionField.type === 'number';
    const shared = {
        id: optionField.name,
        value: value ?? '',
        onChange: ( next ) => onChange( next ),
        disabled: !! optionField.disabled,
        readOnly: !! optionField.readonly,
        className: 'w-full',
    };

    return (
        <div className="panel-field-opt panel-field-opt-text">
            <div className="flex">
                <label
                    htmlFor={ optionField.name }
                    className="wpuf-option-field-title text-gray-700 font-medium"
                >
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>
            { isNumber ? <NumberInput { ...shared } allowNegative /> : <SharedTextInput { ...shared } /> }
        </div>
    );
}
