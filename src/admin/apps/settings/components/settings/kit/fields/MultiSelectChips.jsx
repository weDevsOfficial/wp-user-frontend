import { __ } from '@wordpress/i18n';
import { MultiSelect } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { selectOptions } from './SelectDropdown';

/**
 * Multiple select setting (legacy multicheck, `wpuf_settings_multiselect`,
 * role lists) on the shared MultiSelect (4.6a).
 *
 * Emits a list of the selected option keys; the server stores it in the legacy
 * shape (a list for `wpuf_settings_multiselect`, `{ key: key }` for
 * multicheck). Multicheck stores `''` once everything is unchecked; only a
 * value that was never saved shows the default.
 */
export default function MultiSelectChips( { field, name, value, onChange } ) {
    let selected = [];
    if ( Array.isArray( value ) ) {
        selected = value;
    } else if ( value && 'object' === typeof value ) {
        selected = Object.values( value );
    } else if ( undefined === value ) {
        selected = field.default || [];
    }

    return (
        <>
            <SettingLabel field={ field } />
            <div className="mt-1">
                <MultiSelect
                    className="w-full min-h-9 text-sm"
                    options={ selectOptions( field.options ) }
                    value={ selected.map( String ) }
                    placeholder={ field.placeholder || __( 'Select…', 'wp-user-frontend' ) }
                    onChange={ ( next ) => onChange( name, next ) }
                />
            </div>
        </>
    );
}
