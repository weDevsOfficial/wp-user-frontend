import SettingHelpText from './SettingHelpText';

/**
 * Textarea input for field settings.
 * Replaces Vue field-textarea component.
 */
export default function TextareaInput( { optionField, value, onChange, builderClassNames } ) {
    return (
        <div className="panel-field-opt panel-field-opt-textarea">
            <div className="wpuf-flex">
                <label className="wpuf-mb-2">
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>
            <textarea
                className={ builderClassNames( 'textareafield' ) }
                rows={ optionField.rows || 5 }
                value={ value || '' }
                onChange={ ( e ) => onChange( e.target.value ) }
            />
        </div>
    );
}
