import SettingHelpText from './SettingHelpText';

/**
 * Range slider input for field settings (e.g. column count).
 * Replaces Vue field-range component.
 *
 * Reads min_column / max_column from the field to set range bounds.
 */
export default function RangeInput( { optionField, field, value, onChange } ) {
    const minColumn = field.min_column || 1;
    const maxColumn = field.max_column || 3;

    return (
        <div className="panel-field-opt panel-field-opt-text">
            <div className="wpuf-flex">
                <label>
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                    { optionField.min_column }
                </label>
            </div>
            <input
                type="range"
                value={ value || minColumn }
                onChange={ ( e ) => onChange( e.target.value ) }
                min={ minColumn }
                max={ maxColumn }
            />
        </div>
    );
}
