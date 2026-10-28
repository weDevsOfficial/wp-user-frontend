import { TextInput } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Text meta input for field settings (Vue field-text-meta), on the shared
 * TextInput wrapper (4.4c).
 *
 * Read-only only when the option declares it (`is_read_only`), as develop:
 * a saved field's meta key can be renamed (owner 2026-10-05).
 */
export default function TextMetaInput( { optionField, value, onChange } ) {
    const isReadOnly = !! optionField.is_read_only;

    return (
        <div className="panel-field-opt panel-field-opt-text panel-field-opt-text-meta">
            <div className="flex">
                <label
                    htmlFor={ optionField.name }
                    className="wpuf-option-field-title text-gray-700 font-medium"
                >
                    { optionField.title }
                </label>
                <SettingHelpText text={ optionField.help_text } />
            </div>
            <div className="mt-2">
                <TextInput
                    id={ optionField.name }
                    value={ value ?? '' }
                    // Meta keys must be valid identifiers: lowercase, no spaces or
                    // special characters (mirrors the auto-generated key). Prevents
                    // invalid keys like "radio button" that break the field wrapper
                    // CSS class and the input id / label "for" association.
                    onChange={ ( next ) => onChange( next.replace( /\W/g, '_' ).toLowerCase() ) }
                    readOnly={ isReadOnly }
                    className="w-full"
                />
            </div>
        </div>
    );
}
