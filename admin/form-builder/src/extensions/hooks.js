import { applyFilters, doAction } from '@wordpress/hooks';

// Filter hooks
export function filterPanelSections( sections ) {
    return applyFilters( 'wpuf.formBuilder.panelSections', sections );
}

export function filterFieldSettings( settings, fieldTemplate ) {
    return applyFilters( 'wpuf.formBuilder.fieldSettings', settings, fieldTemplate );
}

export function filterCanvasRender( classes, field ) {
    return applyFilters( 'wpuf.formBuilder.canvasRender', classes, field );
}

export function filterIntegrations( integrations ) {
    return applyFilters( 'wpuf.formBuilder.integrations', integrations );
}

// Settings filter hooks (Sections 10-12)
export function filterSettingsTabs( tabs ) {
    return applyFilters( 'wpuf.formBuilder.settingsTabs', tabs );
}

export function filterSettingsFields( fields, sectionKey ) {
    return applyFilters( 'wpuf.formBuilder.settingsFields', fields, sectionKey );
}

export function filterSettingsItems( items ) {
    return applyFilters( 'wpuf.formBuilder.settingsItems', items );
}

// Action hooks
export function fireRootInit() {
    doAction( 'wpuf.formBuilder.rootInit' );
}

export function fireBeforeSave( formData ) {
    doAction( 'wpuf.formBuilder.beforeSave', formData );
}

export function fireAfterSave( response ) {
    doAction( 'wpuf.formBuilder.afterSave', response );
}
