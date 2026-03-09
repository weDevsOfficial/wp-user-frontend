import SettingHelpText from './SettingHelpText';

/**
 * Text/number input for field settings.
 * Replaces Vue field-text component.
 */
export default function TextInput( { optionField, field, value, onChange, builderClassNames } ) {
    const inputType = optionField.variation === 'number' || optionField.type === 'number' ? 'number' : 'text';

    return (
        <div className="panel-field-opt panel-field-opt-text">
            <div className="wpuf-flex">
                <label
                    htmlFor={ optionField.name }
                    className="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium"
                >
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>
            <input
                id={ optionField.name }
                type={ inputType }
                value={ value || '' }
                onChange={ ( e ) => onChange( e.target.value ) }
                disabled={ !! optionField.disabled }
                readOnly={ !! optionField.readonly }
                className={ builderClassNames( 'text' ) }
            />
        </div>
    );
}
