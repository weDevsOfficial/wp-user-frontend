import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../../stores/settings/constants';
import SelectDropdown from './SelectDropdown';
import QuickLinks from './QuickLinks';

/**
 * Profile Forms for User Roles — a role → profile-form mapping table. Replaces
 * the legacy `wpuf_settings_field_profile` callback (nested `wpuf_profile['roles']`
 * storage the flat React save can't write).
 *
 * Roles, available forms and the current map arrive in the `profile_role_forms`
 * side-channel (`extra`); edits are written back there and persisted server-side
 * via `wpuf_settings_saved` (Pro only). Without Pro nothing would store a
 * pick, so the selects are locked, as develop's disabled `pro-preview` rows.
 */
const KEY = 'profile_role_forms';

export default function ProfileFormRoles() {
    const { data, isPro } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            data: store.getExtraValue( KEY ) || {},
            isPro: ( store.getCaps() || {} ).is_pro,
        };
    }, [] );

    const { setExtraValue } = useDispatch( STORE_NAME );

    const roles = data.roles || {};
    const forms = data.forms || [];
    const map = data.map || {};

    const options = { '': __( '— Select —', 'wp-user-frontend' ) };
    forms.forEach( ( f ) => {
        options[ String( f.id ) ] = f.title;
    } );

    const setRole = ( role, formId ) => setExtraValue( KEY, { ...data, map: { ...map, [ role ]: formId } } );

    return (
        <div className="mt-2">
            <p className="mb-4 text-sm text-gray-500">
                { __( 'Select profile/registration forms for user roles. These forms populate extra edit-profile fields in the backend.', 'wp-user-frontend' ) }
            </p>

            { Object.entries( roles ).map( ( [ role, name ] ) => (
                <div key={ role } className="mb-3 flex items-center gap-4">
                    <span className="w-44 shrink-0 text-sm font-medium text-gray-700">{ name }</span>
                    <div className="min-w-0 flex-1">
                        <SelectDropdown
                            field={ { options } }
                            name={ `role_${ role }` }
                            value={ map[ role ] ? String( map[ role ] ) : '' }
                            onChange={ ( n, val ) => setRole( role, val ) }
                            disabled={ ! isPro }
                        />
                        { isPro && <QuickLinks kind="profile_form" value={ map[ role ] ? String( map[ role ] ) : '' } addNew={ false } /> }
                    </div>
                </div>
            ) ) }

            <QuickLinks kind="profile_form" />

            { ! isPro && (
                <p className="mt-3 rounded-md border border-dashed border-gray-300 bg-gray-50 px-3 py-2 text-xs text-gray-500">
                    { __( 'Mapping forms to roles is a WP User Frontend Pro feature.', 'wp-user-frontend' ) }
                </p>
            ) }
        </div>
    );
}
