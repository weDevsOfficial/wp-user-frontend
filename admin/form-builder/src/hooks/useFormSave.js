import { useState, useCallback } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { STORE_NAME } from '../store';
import { fireBeforeSave, fireAfterSave } from '../extensions/hooks';
import { showToast } from '../common/Toast';
import { showAlert } from '../common/SwalModal';

/**
 * Check if a toggle/checkbox value is considered "on".
 */
function isOn( val ) {
    return val === 'on' || val === 'yes' || val === true || val === '1';
}

/**
 * Check if a field template exists in the form fields list,
 * including inside column_field and repeat_field inner_fields.
 *
 * @param {Array}  formFields List of form field objects.
 * @param {Array}  templates  Template names to look for.
 * @return {boolean}
 */
function hasFieldTemplate( formFields, templates ) {
    for ( const field of formFields ) {
        if ( ! field || ! field.template ) {
            continue;
        }

        if ( templates.includes( field.template ) ) {
            return true;
        }

        // Check inside column/repeat fields
        if ( /^(column|repeat)_field$/.test( field.template ) && field.inner_fields ) {
            for ( const colKey of Object.keys( field.inner_fields ) ) {
                const innerFields = field.inner_fields[ colKey ];

                if ( Array.isArray( innerFields ) ) {
                    for ( const inner of innerFields ) {
                        if ( inner && inner.template && templates.includes( inner.template ) ) {
                            return true;
                        }
                    }
                }
            }
        }
    }

    return false;
}

/**
 * Validate that required fields exist in the form.
 *
 * Post forms must have post_title, post_content, or post_excerpt.
 * Profile forms must have user_email.
 *
 * @param {Array}  formFields Form fields array.
 * @param {string} formType   Form type ('wpuf_forms' or 'wpuf_profile').
 * @return {string|null} Error message or null if valid.
 */
function validateRequiredFields( formFields, formType ) {
    if ( formType === 'wpuf_forms' ) {
        if ( ! hasFieldTemplate( formFields, [ 'post_title', 'post_content', 'post_excerpt' ] ) ) {
            return __( 'Form must contain at least a Post Title, Post Content, or Post Excerpt field.', 'wp-user-frontend' );
        }
    }

    if ( formType === 'wpuf_profile' ) {
        if ( ! hasFieldTemplate( formFields, [ 'user_email' ] ) ) {
            return __( 'Form must contain a User Email field.', 'wp-user-frontend' );
        }
    }

    return null;
}

/**
 * Validate payment settings before save.
 *
 * @param {Object} settings Form settings.
 * @return {string|null} Error message or null if valid.
 */
function validatePaymentSettings( settings ) {
    if ( ! isOn( settings.payment_options ) ) {
        return null;
    }

    const paymentOption = settings.choose_payment_option;

    if ( paymentOption === 'force_pack_purchase' && isOn( settings.fallback_ppp_enable ) ) {
        const cost = parseFloat( settings.fallback_ppp_cost );

        if ( ! cost || cost <= 0 ) {
            return __( 'Cost for each additional post after pack limit is reached is required when Pay-per-post billing when limit exceeds is enabled.', 'wp-user-frontend' );
        }
    }

    if ( paymentOption === 'enable_pay_per_post' ) {
        const cost = parseFloat( settings.pay_per_post_cost );

        if ( ! cost || cost <= 0 ) {
            return __( 'Charge for each post is required when Pay as you post is selected.', 'wp-user-frontend' );
        }
    }

    return null;
}

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

    const { formFields, notifications, settings, formType } = useSelect( ( select ) => {
        const store = select( STORE_NAME );
        return {
            formFields: store.getFormFields(),
            notifications: store.getNotifications(),
            settings: store.getSettings(),
            formType: store.getFormType(),
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
            showAlert( { title: __( 'Form Validation Error!', 'wp-user-frontend' ), message: fieldsError } );
            return;
        }

        // Client-side payment validation
        const paymentError = validatePaymentSettings( settings );

        if ( paymentError ) {
            showAlert( { title: __( 'Form Validation Error!', 'wp-user-frontend' ), message: paymentError } );
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
