import { useState, useCallback } from '@wordpress/element';
import { select as selectStore, useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../store';
import { stripSessionIds } from '../store/reducer';
import { fireBeforeSave, fireAfterSave } from '../extensions/hooks';
import { showToast } from '../common/Toast';
import { openSaveValidationError } from '../common/BuilderDialogs';
import { getLegacySettingsPayload } from '../common/LegacySlot';
import { request, restPath } from '@wpuf/api';
import { setPendingClean } from '../common/saveState';
import { withShownFirstOptions } from '../components/Settings/shownFirstOptions';
import { isOn, validatePaymentSettings, validateRequiredFields } from '../utils/saveValidation';
import { invalidRequiredSettings, invalidSettingsMessage } from '../utils/requiredSettings';
import { isFieldShown } from '../components/Settings/useFieldDependencies';

/**
 * Hook for handling form save over REST (`wpuf/v1/admin/forms/{id}`).
 *
 * Sends form_fields, notifications, and form_settings as JSON from the React store.
 * Also serializes remaining PHP form elements (nonce, post_id) via FormData
 * (Builder\FormSave hands them to the `wpuf_form_builder_save_form` listeners).
 *
 * @return {Object} { isSaving, saveForm }
 */
export default function useFormSave() {
    const [ isSaving, setIsSaving ] = useState( false );

    const { formFields, notifications, settings, formType, isDirty } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            formFields: store.getFormFields(),
            notifications: store.getNotifications(),
            settings: store.getSettings(),
            formType: store.getFormType(),
            isDirty: store.getIsDirty(),
        };
    }, [] );

    const { markClean, setFormFields, setFormSettings, setCurrentPanel } = useDispatch( STORE_NAME );

    const saveForm = useCallback( () => {
        if ( isSaving ) {
            return;
        }

        // Validate required fields exist
        const fieldsError = validateRequiredFields( formFields, formType );

        if ( fieldsError ) {
            openSaveValidationError( fieldsError );
            return;
        }

        // Selects shown with develop's first option are stored (and validated) with
        // it on an edited save; an untouched save changes nothing stored (G3).
        const saveSettings = isDirty ? withShownFirstOptions( settings, ( window.wpuf_form_builder || {} ).settings_items ) : settings;

        // Required settings left empty (button texts, redirect page / URL / message,
        // registration e-mails): say which and where instead of storing them empty.
        const invalidSettings = invalidRequiredSettings( {
            settings: saveSettings,
            formType,
            items: ( window.wpuf_form_builder || {} ).settings_items || {},
            isVisible: ( key ) => isFieldShown( key, saveSettings ),
        } );

        if ( invalidSettings.length ) {
            openSaveValidationError( invalidSettingsMessage( invalidSettings ) );
            return;
        }

        // Client-side payment validation
        const paymentError = validatePaymentSettings( saveSettings );

        if ( paymentError ) {
            openSaveValidationError( paymentError );
            return;
        }

        fireBeforeSave();
        setIsSaving( true );

        const formElement = document.getElementById( 'wpuf-form-builder' );

        if ( ! formElement ) {
            setIsSaving( false );
            return;
        }

        // Serialize remaining PHP form elements (nonce, post_id, etc.)
        const formData = new URLSearchParams( new FormData( formElement ) ).toString();

        // Settings other plugins printed on the builder hooks (legacy slots).
        const legacy = getLegacySettingsPayload();

        const formId = parseInt( new FormData( formElement ).get( 'wpuf_form_id' ), 10 ) || 0;

        // What this save sends: edits made while it runs (the canvas and the
        // settings stay editable) must not be replaced by the response or
        // marked clean.
        const titleOf = () => ( formElement.querySelector( '[name="post_title"]' ) || {} ).value;
        const sent = { formFields, settings, notifications, title: titleOf() };
        const unchanged = () => {
            const store = selectStore( STORE_NAME );

            return store.getFormFields() === sent.formFields
                && store.getSettings() === sent.settings
                && store.getNotifications() === sent.notifications
                && titleOf() === sent.title;
        };

        request( restPath( 'wpuf/v1', `/admin/forms/${ formId }` ), {
            method: 'POST',
            data: {
                form_data: formData,
                form_fields: JSON.stringify( stripSessionIds( formFields ) ),
                notifications: JSON.stringify( notifications ),
                settings: JSON.stringify( saveSettings ),
                touched: isDirty ? '1' : '',
                legacy_settings: legacy.data,
                legacy_settings_keys: JSON.stringify( legacy.keys ),
            },
        } )
            .then( ( body ) => {
                const response = body && body.data ? body.data : {};
                const keep = unchanged();

                if ( keep && response.form_fields ) {
                    setFormFields( response.form_fields );
                }

                if ( keep && response.form_settings ) {
                    setFormSettings( response.form_settings );
                }

                if ( keep ) {
                    // The saved copies above are the new baseline: compare
                    // later edits with them, or the form never turns clean.
                    const store = selectStore( STORE_NAME );

                    sent.formFields = store.getFormFields();
                    sent.settings = store.getSettings();
                }

                setIsSaving( false );
                setCurrentPanel( 'form-fields-v4-1' );

                setPendingClean( new Promise( ( resolve ) => {
                    setTimeout( () => {
                        if ( keep && unchanged() ) {
                            markClean();
                        }
                        resolve();
                    }, 500 );
                } ) );

                showToast( __( 'Saved form data', 'wp-user-frontend' ) );
                fireAfterSave();
            } )
            .catch( ( error ) => {
                setIsSaving( false );

                if ( error && 'string' === typeof error.message && error.status ) {
                    showToast( error.message, 'error' );
                } else {
                    showToast( __( 'Something went wrong saving the form.', 'wp-user-frontend' ), 'error' );
                }
            } );
    }, [ isSaving, formFields, notifications, settings, isDirty, markClean, setFormFields, setFormSettings, setCurrentPanel ] );

    return { isSaving, saveForm };
}
