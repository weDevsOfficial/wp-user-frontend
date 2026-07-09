import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../../../stores-react/settings/constants';
import SelectDropdown from './SelectDropdown';

/**
 * Profile Forms for User Roles — a role → profile-form mapping table. Replaces
 * the legacy `wpuf_settings_field_profile` callback (nested `wpuf_profile['roles']`
 * storage the flat React save can't write).
 *
 * Roles, available forms and the current map arrive in the `profile_role_forms`
 * side-channel (`extra`); edits are written back there and persisted server-side
 * via `wpuf_settings_saved` (Pro only).
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
        <div className="wpuf-mt-2">
            <p className="wpuf-mb-4 wpuf-text-sm wpuf-text-gray-500">
                { __( 'Select profile/registration forms for user roles. These forms populate extra edit-profile fields in the backend.', 'wp-user-frontend' ) }
            </p>

            { Object.entries( roles ).map( ( [ role, name ] ) => (
                <div key={ role } className="wpuf-mb-3 wpuf-flex wpuf-items-center wpuf-gap-4">
                    <span className="wpuf-w-44 wpuf-shrink-0 wpuf-text-sm wpuf-font-medium wpuf-text-gray-700">{ name }</span>
                    <div className="wpuf-min-w-0 wpuf-flex-1">
                        <SelectDropdown
                            field={ { options } }
                            name={ `role_${ role }` }
                            value={ map[ role ] ? String( map[ role ] ) : '' }
                            onChange={ ( n, val ) => setRole( role, val ) }
                        />
                    </div>
                </div>
            ) ) }

            { ! isPro && (
                <p className="wpuf-mt-3 wpuf-rounded-md wpuf-border wpuf-border-dashed wpuf-border-gray-300 wpuf-bg-gray-50 wpuf-px-3 wpuf-py-2 wpuf-text-xs wpuf-text-gray-500">
                    { __( 'Mapping forms to roles is a WP User Frontend Pro feature.', 'wp-user-frontend' ) }
                </p>
            ) }
        </div>
    );
}
