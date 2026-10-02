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
                    // Meta keys must be valid identifiers: lowercase, no spaces or
                    // special characters (mirrors the auto-generated key). Prevents
                    // invalid keys like "radio button" that break the field wrapper
                    // CSS class and the input id / label "for" association.
                    onChange={ ( e ) => onChange( e.target.value.replace( /\W/g, '_' ).toLowerCase() ) }
                    readOnly={ isReadOnly }
                    className={ builderClassNames( 'text' ) }
                />
            </div>
        </div>
    );
}
