import { useState, useMemo } from '@wordpress/element';
import { useDispatch, useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { applyFilters } from '@wordpress/hooks';
import { Save } from 'lucide-react';
import { STORE_NAME } from '../../store';
import SettingsNav from './SettingsNav';
import SettingsSection from './SettingsSection';
import LegacySlot, { getLegacySlots } from '../../common/LegacySlot';
import ModulesEmptyState from './ModulesEmptyState';
import useFormSave from '../../hooks/useFormSave';
import { BuilderSlot, settingsSlotName } from '../../slots';
import { getRouteQuery, inApp, setRouteQuery } from '../../../../app/client';
import { Button, Notice } from '@wpuf/components';
import { adminUrl, builderData } from '../../utils/boot';

/**
 * Main form settings component — mirrors post-form-settings.php Vue template.
 */
export default function FormSettings() {
    const data = builderData();
    const isProActive = useSelect( ( select ) => select( STORE_NAME ).getIsProActive(), [] );
    const settings = useSelect( ( select ) => select( STORE_NAME ).getSettings(), [] );
    const { updateFormSetting } = useDispatch( STORE_NAME );
    const { isSaving, saveForm } = useFormSave();
    const isDirty = useSelect( ( select ) => select( STORE_NAME ).getIsDirty(), [] );
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

    // The open section lives in the URL (`?tab=settings&section=<key>`).
    const [ activeTab, setActiveTabState ] = useState( () => {
        const section = getRouteQuery().section;
        const known = section && Object.values( settingsTitles ).some( ( top ) => top && top.sub_items && top.sub_items[ section ] );

        return known ? section : defaultTab;
    } );
    const setActiveTab = ( section ) => {
        setActiveTabState( section );
        setRouteQuery( { tab: 'settings', section } );
    };

    // Find the section data for the active tab
    const activeSectionData = useMemo( () => {
        for ( const topKey in settingsItems ) {
            if ( settingsItems[ topKey ] && settingsItems[ topKey ][ activeTab ] ) {
                return settingsItems[ topKey ][ activeTab ];
            }
        }
        return null;
    }, [ settingsItems, activeTab ] );

    // Cancel URL (in the admin app: the list route, no page load)
    const isProfileForm = builderData().post?.post_type === 'wpuf_profile';
    const postFormsUrl = inApp()
        ? `#${ isProfileForm ? '/registration-forms' : '/post-forms' }`
        : adminUrl() + ( isProfileForm ? 'admin.php?page=wpuf-profile-forms' : 'admin.php?page=wpuf-post-forms' );

    return (
        <div className="wpuf-settings-container mx-auto mt-3 mb-6 box-border w-full max-w-[1400px] px-4">
            { /* Title row over both columns, actions on the right (FlyHR settings: a
               fixed page title; the section name is the card title). Capped at
               FlyHR's 1400px content width; the Form Editor keeps the full width. */ }
            <div className="mb-6 flex flex-wrap items-center justify-between gap-4">
                <h2 className="text-2xl! font-bold m-0 leading-8 text-gray-900">
                    { __( 'Form Settings', 'wp-user-frontend' ) }
                </h2>
                { /* Cancel + Save buttons */ }
                <div className="flex items-center gap-3">
                    <Button variant="secondary" size="lg" href={ postFormsUrl }>
                        { __( 'Cancel', 'wp-user-frontend' ) }
                    </Button>
                    <Button
                        size="lg"
                        onClick={ saveForm }
                        busy={ isSaving }
                        disabled={ ! isDirty }
                        title={ isDirty || isSaving ? undefined : __( 'No changes to save', 'wp-user-frontend' ) }
                    >
                        { ! isSaving && <Save size={ 16 } strokeWidth={ 2 } aria-hidden="true" /> }
                        { isSaving ? __( 'Saving…', 'wp-user-frontend' ) : __( 'Save Form', 'wp-user-frontend' ) }
                    </Button>
                </div>
            </div>

            <div className="flex items-start gap-6">
                <SettingsNav
                    activeTab={ activeTab }
                    onTabChange={ setActiveTab }
                />

                <div className="min-w-0 flex-1">

                    { getLegacySlots().unsupported && (
                        <Notice tone="warning" className="mb-6">
                            { __( 'An extension added builder settings that need scripts or Vue templates. They are shown as static fields here; their interactive parts do not run.', 'wp-user-frontend' ) }
                        </Notice>
                    ) }

                    { /* Section body */ }
                    <div>
                        <SettingsSection
                            sectionKey={ activeTab }
                            sectionData={ activeSectionData }
                        />

                        { /* Extensions' panel for this tab (4.4g). */ }
                        <BuilderSlot name={ settingsSlotName( activeTab ) } fillProps={ { tab: activeTab, settings, updateSetting: updateFormSetting } } />

                        { ! activeSectionData && activeTab === 'modules' && (
                            <ModulesEmptyState isProActive={ isProActive } />
                        ) }

                        { ! activeSectionData && activeTab && activeTab !== 'modules' && (
                            <div className="text-center py-12 text-gray-500">
                                <p>{ __( 'No settings available for this section.', 'wp-user-frontend' ) }</p>
                            </div>
                        ) }
                    </div>

                    { /* Settings other plugins printed on the settings tab hooks
                        (wpuf_form_builder_settings_tabs_{type}, wpuf_{post,profile}_form_tab). */ }
                    <LegacySlot id="tab-settings" html={ getLegacySlots().tabs.settings } className="mt-6" />
                    <LegacySlot id="tab-form-tab" html={ getLegacySlots().tabs.form_tab } className="mt-6" />
                </div>
            </div>
        </div>
    );
}
