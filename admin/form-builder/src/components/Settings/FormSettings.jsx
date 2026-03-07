import { useState, useMemo } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { STORE_NAME } from '../../store';
import SettingsNav from './SettingsNav';
import SettingsSection from './SettingsSection';
import ModulesEmptyState from './ModulesEmptyState';

/**
 * Main form settings component — mirrors post-form-settings.php Vue template.
 */
export default function FormSettings() {
    const data = window.wpuf_form_builder || {};
    const isProActive = useSelect( ( select ) => select( STORE_NAME ).getIsProActive(), [] );
    const settingsTitles = useMemo( () => {
        const titles = data.settings_titles || {};
        return applyFilters( 'wpuf.formBuilder.settingsTabs', titles );
    }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

    const settingsItems = useMemo( () => {
        const items = data.settings_items || {};
        return applyFilters( 'wpuf.formBuilder.settingsItems', items );
    }, [] ); // eslint-disable-line react-hooks/exhaustive-deps

    // Determine default active tab from first sub-item
    const defaultTab = useMemo( () => {
        const firstTop = Object.values( settingsTitles )[ 0 ];
        if ( firstTop && firstTop.sub_items ) {
            return Object.keys( firstTop.sub_items )[ 0 ] || '';
        }
        return '';
    }, [ settingsTitles ] );

    const [ activeTab, setActiveTab ] = useState( defaultTab );

    // Compute active settings title from settings_titles sub_items
    const activeSettingsTitle = useMemo( () => {
        for ( const topKey in settingsTitles ) {
            const subItems = settingsTitles[ topKey ].sub_items || {};
            if ( subItems[ activeTab ] ) {
                return subItems[ activeTab ].label || '';
            }
        }
        return '';
    }, [ settingsTitles, activeTab ] );

    // Find the section data for the active tab
    const activeSectionData = useMemo( () => {
        for ( const topKey in settingsItems ) {
            if ( settingsItems[ topKey ] && settingsItems[ topKey ][ activeTab ] ) {
                return settingsItems[ topKey ][ activeTab ];
            }
        }
        return null;
    }, [ settingsItems, activeTab ] );

    // Cancel URL
    const postFormsUrl = ( window.wpuf_form_builder?.post?.post_type === 'wpuf_profile' )
        ? ( window.wpuf_admin_url || '' ) + 'admin.php?page=wpuf-profile-forms'
        : ( window.wpuf_admin_url || '' ) + 'admin.php?page=wpuf-post-forms';

    return (
        <div className="wpuf-settings-container wpuf-border wpuf-border-gray-200 wpuf-rounded-lg wpuf-m-4 wpuf-flex wpuf-transition-transform wpuf-duration-200 wpuf-ease-in-out">
            <SettingsNav
                activeTab={ activeTab }
                onTabChange={ setActiveTab }
            />

            <div className="wpuf-w-3/4 wpuf-min-h-screen wpuf-p-8">
                { /* Active tab title */ }
                <div className="wpuf-pb-8">
                    <h2 className="wpuf-text-2xl wpuf-m-0 wpuf-leading-none">
                        { activeSettingsTitle }
                    </h2>
                </div>

                { /* Section body */ }
                <div className="wpuf-border-y wpuf-border-gray-200 wpuf-py-8">
                    <SettingsSection
                        sectionKey={ activeTab }
                        sectionData={ activeSectionData }
                    />

                    { ! activeSectionData && activeTab === 'modules' && (
                        <ModulesEmptyState isProActive={ isProActive } />
                    ) }

                    { ! activeSectionData && activeTab && activeTab !== 'modules' && (
                        <div className="wpuf-text-center wpuf-py-12 wpuf-text-gray-500">
                            <p>{ __( 'No settings available for this section.', 'wp-user-frontend' ) }</p>
                        </div>
                    ) }
                </div>

                { /* Cancel + Save buttons */ }
                <div className="wpuf-flex wpuf-space-x-4 wpuf-items-center wpuf-mt-8">
                    <a
                        href={ postFormsUrl }
                        className="wpuf-inline-flex wpuf-items-center wpuf-gap-x-2 wpuf-rounded-md wpuf-px-8 wpuf-py-3 wpuf-text-gray-700 hover:wpuf-text-gray-700 hover:wpuf-bg-gray-50 wpuf-ring-1 wpuf-ring-inset wpuf-ring-gray-300 hover:wpuf-cursor-pointer"
                    >
                        { __( 'Cancel', 'wp-user-frontend' ) }
                    </a>
                    <button
                        type="button"
                        onClick={ () => {
                            // Trigger save via the existing form builder save mechanism
                            const saveBtn = document.querySelector( '#wpuf-form-builder-save-form' );
                            if ( saveBtn ) {
                                saveBtn.click();
                            }
                        } }
                        className="wpuf-btn-primary wpuf-w-full"
                    >
                        { __( 'Save Form', 'wp-user-frontend' ) }
                    </button>
                </div>
            </div>
        </div>
    );
}
