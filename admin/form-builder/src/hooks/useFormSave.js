import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../store';
import { fireBeforeSave, fireAfterSave } from '../extensions/hooks';
import { showToast } from '../common/Toast';

/**
 * Hook for handling form save via AJAX.
 *
 * Sends form_fields, notifications, and form_settings as JSON from the React store.
 * Also serializes remaining PHP form elements (nonce, post_id) via FormData.
 *
 * @return {Object} { isSaving, saveForm }
 */
export default function useFormSave() {
    const [ isSaving, setIsSaving ] = useState( false );

    const { formFields, notifications, settings } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            formFields: store.getFormFields(),
            notifications: store.getNotifications(),
            settings: store.getSettings(),
        };
    }, [] );

    const { markClean, setFormFields, setFormSettings, setCurrentPanel } = useDispatch( STORE_NAME );

    const saveForm = useCallback( () => {
        if ( isSaving ) {
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

        wp.ajax.send( 'wpuf_form_builder_save_form', {
            data: {
                form_data: formData,
                form_fields: JSON.stringify( formFields ),
                notifications: JSON.stringify( notifications ),
                settings: JSON.stringify( settings ),
            },

            success( response ) {
                if ( response.form_fields ) {
                    setFormFields( response.form_fields );
                }

                if ( response.form_settings ) {
                    setFormSettings( response.form_settings );
                }

                setIsSaving( false );
                setCurrentPanel( 'form-fields-v4-1' );

                setTimeout( () => {
                    markClean();
                }, 500 );

                showToast( __( 'Form data saved.', 'wp-user-frontend' ) );
                fireAfterSave();
            },

            error( response ) {
                setIsSaving( false );

                if ( response && typeof response === 'string' ) {
                    showToast( response, 'error' );
                } else {
                    showToast( __( 'Something went wrong saving the form.', 'wp-user-frontend' ), 'error' );
                }
            },
        } );
    }, [ isSaving, formFields, notifications, settings, markClean, setFormFields, setFormSettings, setCurrentPanel ] );

    return { isSaving, saveForm };
}
