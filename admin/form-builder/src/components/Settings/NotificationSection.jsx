import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../../store';
import SettingsField from './SettingsField';

/**
 * Notification settings section.
 *
 * Renders notification sub-sections (new_post, update_post) from settings_items data.
 * Each notification sub-section has toggle, to, subject, body fields.
 * Uses store notification CRUD for managing notification entries.
 *
 * The notification data structure from PHP:
 * notification_settings.section.new_post = { label, desc, fields: { new, new_to, new_subject, new_body } }
 * notification_settings.section.update_post = { label, desc }
 *
 * Notification values are stored in state.settings with keys like:
 * wpuf_settings[notification][new], wpuf_settings[notification][new_to], etc.
 * But the React store flattens these to individual keys in settings.
 */
export default function NotificationSection( { sectionData } ) {
    const settings = useSelect( ( select ) => select( STORE_NAME ).getSettings(), [] );
    const notifications = useSelect( ( select ) => select( STORE_NAME ).getNotifications(), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );

    const handleChange = useCallback( ( name, value ) => {
        updateFormSetting( name, value );
    }, [ updateFormSetting ] );

    if ( ! sectionData || ! sectionData.section ) {
        return null;
    }

    return (
        <div className="wpuf-settings-section wpuf-notification-section">
            { Object.entries( sectionData.section ).map( ( [ subKey, subSection ] ) => (
                <NotificationSubSection
                    key={ subKey }
                    subKey={ subKey }
                    subSection={ subSection }
                    settings={ settings }
                    onChange={ handleChange }
                />
            ) ) }
        </div>
    );
}

/**
 * A single notification sub-section (e.g. New Post Notification).
 */
function NotificationSubSection( { subKey, subSection, settings, onChange } ) {
    const [ isExpanded, setIsExpanded ] = useState( true );

    if ( ! subSection ) {
        return null;
    }

    const hasFields = subSection.fields && Object.keys( subSection.fields ).length > 0;

    return (
        <div className="wpuf-notification-subsection wpuf-mb-6 wpuf-border wpuf-border-gray-200 wpuf-rounded-lg">
            <button
                type="button"
                onClick={ () => setIsExpanded( ! isExpanded ) }
                className="wpuf-w-full wpuf-flex wpuf-items-center wpuf-justify-between wpuf-px-6 wpuf-py-4 wpuf-border-0 wpuf-bg-gray-50 wpuf-cursor-pointer wpuf-rounded-t-lg"
            >
                <div>
                    <h3 className="wpuf-text-base wpuf-font-semibold wpuf-text-gray-900 wpuf-m-0">
                        { subSection.label }
                    </h3>
                    { subSection.desc && (
                        <p className="wpuf-text-sm wpuf-text-gray-500 wpuf-mt-1 wpuf-mb-0">
                            { subSection.desc }
                        </p>
                    ) }
                </div>
                <svg
                    className={ `wpuf-w-5 wpuf-h-5 wpuf-text-gray-400 wpuf-transition-transform ${ isExpanded ? 'wpuf-rotate-180' : '' }` }
                    fill="none" viewBox="0 0 24 24" strokeWidth="2" stroke="currentColor"
                >
                    <path strokeLinecap="round" strokeLinejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                </svg>
            </button>

            { isExpanded && hasFields && (
                <div className="wpuf-space-y-5 wpuf-p-6">
                    { Object.entries( subSection.fields ).map( ( [ fieldName, fieldDef ] ) => {
                        // Use the field's `name` prop if available (e.g. wpuf_settings[notification][new])
                        // Fall back to fieldName as the key in settings
                        const settingKey = fieldDef.name || fieldName;

                        return (
                            <SettingsField
                                key={ fieldName }
                                field={ fieldDef }
                                name={ settingKey }
                                value={ settings[ settingKey ] !== undefined ? settings[ settingKey ] : ( fieldDef.value || '' ) }
                                onChange={ onChange }
                                settings={ settings }
                            />
                        );
                    } ) }
                </div>
            ) }

            { isExpanded && ! hasFields && (
                <div className="wpuf-p-6 wpuf-text-sm wpuf-text-gray-500">
                    { __( 'No notification fields configured for this section.', 'wp-user-frontend' ) }
                </div>
            ) }
        </div>
    );
}
