import { Textarea } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Textarea input for field settings (Vue field-textarea), on the shared
 * Textarea wrapper (4.4c).
 */
export default function TextareaInput( { optionField, value, onChange } ) {
    return (
        <div className="panel-field-opt panel-field-opt-textarea">
            <div className="flex">
                <label className="mb-2" htmlFor={ optionField.name }>
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                </label>
            </div>
            <Textarea
                id={ optionField.name }
                rows={ optionField.rows || 5 }
                value={ value ?? '' }
                onChange={ ( next ) => onChange( next ) }
                className="w-full"
            />
        </div>
    );
}
