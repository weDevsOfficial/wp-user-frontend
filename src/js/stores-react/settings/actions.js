import { __ } from '@wordpress/i18n';
import { ACTION_TYPES } from './constants';
import { fetchSettings, saveSettings } from '../../api/settings';

export function setData( data ) {
    return { type: ACTION_TYPES.SET_DATA, data };
}

export function setIsLoading( isLoading ) {
    return { type: ACTION_TYPES.SET_IS_LOADING, isLoading };
}

export function setIsSaving( isSaving ) {
    return { type: ACTION_TYPES.SET_IS_SAVING, isSaving };
}

export function setIsDirty( isDirty ) {
    return { type: ACTION_TYPES.SET_IS_DIRTY, isDirty };
}

export function setActiveTab( activeTab ) {
    return { type: ACTION_TYPES.SET_ACTIVE_TAB, activeTab };
}

export function setSearch( search ) {
    return { type: ACTION_TYPES.SET_SEARCH, search };
}

export function setValues( values ) {
    return { type: ACTION_TYPES.SET_VALUES, values };
}

export function setError( error ) {
    return { type: ACTION_TYPES.SET_ERROR, error };
}

/**
 * Update a single field value within a section.
 */
export function setValue( sectionId, name, value ) {
    return { type: ACTION_TYPES.SET_VALUE, sectionId, name, value };
}

/**
 * Update a custom (own-option) value in the `extra` side-channel — used by
 * settings that don't live as a plain section field (tax rates, role-based
 * email templates). Persisted via the `wpuf_settings_saved` Pro hook.
 */
export function setExtraValue( key, value ) {
    return { type: ACTION_TYPES.SET_EXTRA_VALUE, key, value };
}

/**
 * Mark the current `extra` channel as the saved baseline (call after a
 * successful save so Discard reverts to the persisted state, not the pre-save
 * snapshot).
 */
export function setExtraSaved() {
    return { type: ACTION_TYPES.SET_EXTRA_SAVED };
}

/**
 * Revert unsaved edits back to the last saved snapshot.
 */
export function discard() {
    return { type: ACTION_TYPES.DISCARD };
}

/**
 * Load the full settings payload from the REST endpoint.
 */
export function loadSettings() {
    return async ( { dispatch } ) => {
        dispatch.setIsLoading( true );
        try {
            const response = await fetchSettings();
            if ( response && response.success ) {
                dispatch.setData( response.data );
            } else {
                dispatch.setError( __( 'Failed to load settings.', 'wp-user-frontend' ) );
            }
        } catch ( e ) {
            dispatch.setError(
                ( e && e.message ) || __( 'Failed to load settings.', 'wp-user-frontend' )
            );
        }
        dispatch.setIsLoading( false );
    };
}

/**
 * Persist all current values to the REST endpoint.
 */
export function save() {
    return async ( { dispatch, select } ) => {
        dispatch.setIsSaving( true );
        dispatch.setError( null );

        let succeeded = false;
        try {
            const values = select.getValues();
            const extra = select.getExtra();
            const response = await saveSettings( values, extra );
            if ( response && response.success ) {
                if ( response.data && response.data.values ) {
                    const merged = { ...values, ...response.data.values };
                    dispatch.setValues( merged );
                }
                // Keep the extra channel's saved baseline in sync so a later
                // Discard doesn't roll back persisted own-option settings.
                dispatch.setExtraSaved();
                dispatch.setIsDirty( false );
                succeeded = true;
            } else {
                dispatch.setError(
                    ( response && response.message ) || __( 'Failed to save settings.', 'wp-user-frontend' )
                );
            }
        } catch ( e ) {
            dispatch.setError( e.message || __( 'Failed to save settings.', 'wp-user-frontend' ) );
        }
        dispatch.setIsSaving( false );

        // Explicit success signal so the UI can confirm a save without inferring
        // it from isSaving/error transitions.
        return succeeded;
    };
}
