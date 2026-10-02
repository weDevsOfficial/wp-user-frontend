import { useCallback } from '@wordpress/element';
import HelpTextIcon from './HelpTextIcon';

/**
 * Fields that show a Pro badge icon next to their label when Pro is not active.
 * Matches Vue's $badge_fields in wpuf_render_settings_field().
 */
const BADGE_FIELDS = [ 'enable_multistep', 'notification_edit' ];

/**
 * Toggle switch field — matches Vue wpuf_render_settings_field() for type="toggle".
 *
 * Vue structure: label + help_text on left, checkbox-based toggle on right,
 * all inside a flex row with justify-between and w-2/5.
 * Uses sr-only checkbox + peer classes for the toggle visual.
 */
export default function ToggleField( { field, name, value, onChange } ) {
    const isOn = value === 'yes' || value === true || value === 'on';
    const data = window.wpuf_form_builder || {};
    const isProActive = !! data.is_pro_active;
    const showProBadge = ! isProActive && BADGE_FIELDS.includes( name );
    const proBadgeUrl = ( data.asset_url || '' ) + '/images/pro-badge.svg';

    const handleToggle = useCallback( () => {
        onChange( name, isOn ? 'off' : 'on' );
    }, [ name, isOn, onChange ] );

    return (
        <div className="wpuf-flex wpuf-items-center wpuf-justify-between wpuf-w-2/5">
            <div className="wpuf-flex wpuf-items-center">
                { field.label && (
                    <label htmlFor={ name } className="wpuf-text-sm wpuf-text-gray-700 wpuf-my-2">
                        { field.label }
                    </label>
                ) }
                { field.help_text && <HelpTextIcon text={ field.help_text } /> }
                { showProBadge && (
                    <img className="wpuf-ml-2" src={ proBadgeUrl } alt="" />
                ) }
            </div>
            <label
                htmlFor={ name }
                className="wpuf-relative wpuf-inline-flex wpuf-items-center wpuf-cursor-pointer wpuf-ml-2"
            >
                <input
                    type="checkbox"
                    id={ name }
                    checked={ isOn }
                    onChange={ handleToggle }
                    className="wpuf-sr-only wpuf-peer"
                />
                <span className="wpuf-flex wpuf-items-center wpuf-w-10 wpuf-h-4 wpuf-bg-gray-300 wpuf-rounded-full wpuf-peer peer-checked:wpuf-bg-primary after:wpuf-w-6 after:wpuf-h-6 after:wpuf-bg-white after:wpuf-rounded-full after:wpuf-shadow-md after:wpuf-duration-300 peer-checked:after:wpuf-translate-x-4 after:wpuf-border after:wpuf-border-solid after:wpuf-border-gray-50" />
            </label>
        </div>
    );
}
