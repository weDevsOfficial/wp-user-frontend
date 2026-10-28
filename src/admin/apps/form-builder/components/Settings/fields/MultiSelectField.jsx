import { MultiSelect } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Multi-select setting (develop type="multi-select", selectize) on the shared
 * MultiSelect (4.4e). Options in `always_selected` stay in the stored list.
 * A placeholder-only list ("No subscription packs available") shows as a
 * note: the shared MultiSelect handles it (develop let it be picked and stored).
 */
export default function MultiSelectField( { field, name, value, onChange } ) {
    const alwaysSelected = field.always_selected || [];
    const current = Array.isArray( value ) ? value : ( value ? [ value ] : [] );

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <MultiSelect
                id={ name }
                className="w-full"
                options={ field.options || {} }
                value={ current }
                onChange={ ( next ) => onChange( name, [ ...new Set( [ ...alwaysSelected, ...next ] ) ] ) }
            />
        </>
    );
}
