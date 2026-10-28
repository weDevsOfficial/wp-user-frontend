import { __ } from '@wordpress/i18n';
import { Select } from '@wpuf/components';
import { hasEmptyOption, orderedOptions } from './settingOptions';
import SettingLabel from './SettingLabel';

/**
 * Select setting (develop type="select", selectize) on the shared Select
 * (4.4e). Shows the resolved value; with nothing stored and no default it
 * shows "- Select -" and writes nothing until a pick (Q6: an untouched save
 * keeps the stored settings).
 */
export default function SelectField( { field, name, value, onChange } ) {
    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <Select
                id={ name }
                options={ orderedOptions( field.options ) }
                value={ '' === value && ! hasEmptyOption( field.options ) ? undefined : value }
                placeholder={ __( '- Select -', 'wp-user-frontend' ) }
                onChange={ ( next ) => onChange( name, next ) }
            />
        </>
    );
}
