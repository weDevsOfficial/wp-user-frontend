import SettingHelpText from './SettingHelpText';

/**
 * Text meta input for field settings (e.g. meta key name).
 * Replaces Vue field-text-meta component.
 *
 * Has is_read_only support for preventing meta key edits on existing fields.
 */
export default function TextMetaInput( { optionField, field, value, onChange, builderClassNames } ) {
    const isReadOnly = !! optionField.is_read_only || ! field.is_new;

    return (
        <div className="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
            <div className="wpuf-flex">
                <label
                    htmlFor={ optionField.name }
                    className="wpuf-option-field-title wpuf-font-sm wpuf-text-gray-700 wpuf-font-medium"
                >
                    { optionField.title }
                </label>
                <SettingHelpText text={ optionField.help_text } />
            </div>
            <div className="wpuf-mt-2">
                <input
                    id={ optionField.name }
                    type="text"
                    value={ value || '' }
                    onChange={ ( e ) => onChange( e.target.value ) }
                    readOnly={ isReadOnly }
                    className={ builderClassNames( 'text' ) }
                />
            </div>
        </div>
    );
}
