import { Toggle } from '@wpuf/components';
import SettingLabel from './SettingLabel';
import { builderData } from '../../../utils/boot';

/**
 * Fields that show a Pro badge icon next to their label when Pro is not active.
 * Matches Vue's $badge_fields in wpuf_render_settings_field().
 */
const BADGE_FIELDS = [ 'enable_multistep', 'notification_edit' ];

// Same "on" values as PHP wpuf_is_checkbox_or_toggle_on().
const isOn = ( value ) => true === value || [ 'on', 'yes', 'true', '1' ].includes( value );

/**
 * Toggle setting (develop type="toggle") on the shared Toggle (4.4e): label
 * and help on the left, the switch on the right, in develop's 2/5 row.
 * Develop's post form toggle posts a hidden 'off'; the registration form
 * toggle has none, so off leaves the key out (modules check isset).
 */
export default function ToggleField( { field, name, value, onChange } ) {
    const data = builderData();
    const offValue = data.form_type === 'wpuf_profile' ? undefined : 'off';
    const showProBadge = ! data.is_pro_active && BADGE_FIELDS.includes( name );

    return (
        <div className="flex items-center justify-between w-2/5">
            <SettingLabel field={ field } htmlFor={ name }>
                { showProBadge && <img className="ml-2" src={ ( data.asset_url || '' ) + '/images/pro-badge.svg' } alt="" /> }
            </SettingLabel>
            <Toggle
                id={ name }
                className="ml-2"
                value={ isOn( value ) ? 'on' : 'off' }
                checkedValue="on"
                uncheckedValue={ offValue }
                onChange={ ( next ) => onChange( name, next ) }
            />
        </div>
    );
}
