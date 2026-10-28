import { __ } from '@wordpress/i18n';
import { Select } from '@wpuf/components';
import SettingHelpText from './SettingHelpText';

/**
 * Select for field settings (Vue field-select's custom dropdown), on the
 * shared Select wrapper (4.4c). An empty value shows the option's default,
 * else develop's placeholder; the default is only shown, not stored: opening
 * the panel writes nothing (Q6).
 */
export default function SelectInput( { optionField, field, value, onChange } ) {
    const options = optionField.options || {};
    const shown = value && undefined !== options[ value ] ? value : ( ! value ? optionField.default : value );
    const id = `wpuf-${ optionField.name }-${ field ? field.id : 'field' }`;

    return (
        <div className="panel-field-opt panel-field-opt-select">
            <div className="flex">
                { optionField.title && (
                    <label className="mb-0!" htmlFor={ id }>
                        { optionField.title }
                        <SettingHelpText text={ optionField.help_text } />
                    </label>
                ) }
            </div>
            <div className="option-fields-section mt-1.5">
                <Select
                    id={ id }
                    options={ options }
                    value={ shown }
                    onChange={ ( next ) => onChange( next ) }
                    placeholder={ __( 'Select an option', 'wp-user-frontend' ) }
                />
            </div>
        </div>
    );
}
