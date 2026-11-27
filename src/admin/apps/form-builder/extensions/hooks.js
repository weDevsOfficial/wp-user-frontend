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

// Settings filter hooks (Sections 10-12)
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
