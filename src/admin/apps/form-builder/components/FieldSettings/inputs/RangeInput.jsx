import { NumberInput } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Range slider for field settings (e.g. column count; Vue field-range), on
 * the shared NumberInput range variant (4.4c). Bounds come from the field's
 * min_column / max_column.
 */
export default function RangeInput( { optionField, field, value, onChange } ) {
    const minColumn = field.min_column || 1;
    const maxColumn = field.max_column || 3;

    return (
        <div className="panel-field-opt panel-field-opt-text">
            <div className="flex">
                <label htmlFor={ `wpuf-${ optionField.name }-${ field.id }` }>
                    { optionField.title }
                    <SettingHelpText text={ optionField.help_text } />
                    { optionField.min_column }
                </label>
            </div>
            <NumberInput
                id={ `wpuf-${ optionField.name }-${ field.id }` }
                variant="range"
                value={ value || String( minColumn ) }
                onChange={ ( next ) => onChange( next ) }
                min={ minColumn }
                max={ maxColumn }
            />
        </div>
    );
}
