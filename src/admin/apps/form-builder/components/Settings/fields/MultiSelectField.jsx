import { MultiSelect } from '@wpuf/components';
import SettingLabel from './SettingLabel';

/**
 * Option keys a setting sends as an "empty list" message, not as a choice
 * (Pro View Control's `no_packs` when no subscription pack exists).
 */
const PLACEHOLDER_KEYS = [ 'no_packs' ];

/**
 * Multi-select setting (develop type="multi-select", selectize) on the shared
 * MultiSelect (4.4e). Options in `always_selected` stay in the stored list.
 * A list made only of a placeholder option shows its text as a note: it is
 * not something to tick (develop let it be picked and stored).
 */
export default function MultiSelectField( { field, name, value, onChange } ) {
    const alwaysSelected = field.always_selected || [];
    const current = Array.isArray( value ) ? value : ( value ? [ value ] : [] );
    const options = field.options || {};
    const keys = Object.keys( options );

    if ( keys.length && keys.every( ( key ) => PLACEHOLDER_KEYS.includes( key ) ) ) {
        return (
            <>
                <SettingLabel field={ field } htmlFor={ name } />
                <div id={ name } className="flex h-9 w-full items-center rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 text-sm text-gray-500">
                    { options[ keys[ 0 ] ] }
                </div>
            </>
        );
    }

    return (
        <>
            <SettingLabel field={ field } htmlFor={ name } />
            <MultiSelect
                id={ name }
                className="w-full"
                options={ options }
                value={ current }
                onChange={ ( next ) => onChange( name, [ ...new Set( [ ...alwaysSelected, ...next ] ) ] ) }
            />
        </>
    );
}
