import { createRoot } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { STORE_NAME } from './store';
import {
    registerFieldPreview,
    registerFieldSettingInput,
    registerFieldValidator,
    getFieldPreview,
    getFieldSettingInput,
    getFieldValidators,
    getAllFieldPreviews,
    getAllFieldSettingInputs,
} from './extensions/registry';
import { __ } from '@wordpress/i18n';
import { hasRecaptchaApiKeys, hasTurnstileApiKeys } from './utils/globalHelpers';
import { fireRootInit } from './extensions/hooks';
import { registerFreeFieldPreviews } from './components/FieldPreview';
import { useFieldClasses, formatPrice } from './hooks/useFieldClasses';
import HelpText from './components/FieldPreview/HelpText';
import FormBuilder from './components/FormBuilder';

/**
 * Initialize the store from PHP-localized data.
 */
function initializeStore() {
    const data = window.wpuf_form_builder || {};

    const initialState = {
        post: data.post || {},
        formFields: data.form_fields || [],
        panelSections: ( data.panel_sections || [] ).map( ( section ) => ( {
            ...section,
            show: section.show !== undefined ? section.show : true,
        } ) ),
        fieldSettings: data.field_settings || {},
        notifications: data.notifications || [],
        settings: data.form_settings || {},
        integrations: data.integrations || {},
        isProActive: !! data.is_pro_active,
        formType: data.form_type || data.post?.post_type || '',
        i18n: data.i18n || {},
    };

    const store = dispatch( STORE_NAME );

    // Set all state at once, then override with individual setters that
    // have specific reducer logic (e.g. panelSections adds `show` flag).
    store.initializeState( initialState );
    store.setFormFields( initialState.formFields );
    store.setPanelSections( initialState.panelSections );
    store.setFormSettings( initialState.settings );

    // Populate taxonomy section fields based on current post type
    const wpPostTypes = data.wp_post_types || {};
    const currentPostType = ( data.form_settings || {} ).post_type || 'post';
    const taxonomies = wpPostTypes[ currentPostType ];

    if ( taxonomies ) {
        store.setPanelSectionFields( 'taxonomies', Object.keys( taxonomies ) );
    }

    // Update taxonomy section when post type dropdown changes
    const postTypeDropdown = document.querySelector( 'select[name="wpuf_settings[post_type]"]' );

    if ( postTypeDropdown ) {
        postTypeDropdown.addEventListener( 'change', ( e ) => {
            const newTaxonomies = wpPostTypes[ e.target.value ];
            store.setPanelSectionFields( 'taxonomies', newTaxonomies ? Object.keys( newTaxonomies ) : [] );
        } );
    }

    return initialState;
}

/**
 * Expose the global wpuf API for Pro extensions.
 */
window.wpuf = window.wpuf || {};
window.wpuf.registerFieldPreview = registerFieldPreview;
window.wpuf.registerFieldSettingInput = registerFieldSettingInput;
window.wpuf.getFieldPreview = getFieldPreview;
window.wpuf.getFieldSettingInput = getFieldSettingInput;
window.wpuf.getAllFieldPreviews = getAllFieldPreviews;
window.wpuf.getAllFieldSettingInputs = getAllFieldSettingInputs;
window.wpuf.registerFieldValidator = registerFieldValidator;
window.wpuf.getFieldValidators = getFieldValidators;
window.wpuf.storeName = STORE_NAME;
window.wpuf.useFieldClasses = useFieldClasses;
window.wpuf.formatPrice = formatPrice;
window.wpuf.HelpText = HelpText;

/**
 * Mount the React app.
 */
document.addEventListener( 'DOMContentLoaded', () => {
    const container = document.getElementById( 'wpuf-form-builder-app' );

    if ( ! container ) {
        return;
    }

    initializeStore();
    registerFreeFieldPreviews();

    // Register built-in field validators
    registerFieldValidator( 'has_recaptcha_api_keys', hasRecaptchaApiKeys );
    registerFieldValidator( 'has_turnstile_api_keys', hasTurnstileApiKeys );

    fireRootInit();

    // Show "Pro Fields Hidden" warning when form has custom taxonomy fields and Pro is not active
    const builderData = window.wpuf_form_builder || {};

    if ( builderData.has_hidden_taxonomies && ! builderData.is_pro_active && typeof window.Swal !== 'undefined' ) {
        setTimeout( () => {
            const assetUrl = builderData.asset_url || '';

            window.Swal.fire( {
                title: '',
                html: '<div class="wpuf-pro-modal-content">' +
                    '<div class="wpuf-pro-modal-left">' +
                    '<div class="wpuf-pro-modal-icon">' +
                    '<img src="' + assetUrl + '/images/free-circle.svg" alt="' + __( 'Pro upgrade notification icon', 'wp-user-frontend' ) + '">' +
                    '</div>' +
                    '<h2 class="wpuf-pro-modal-title">' + __( 'Pro Fields Hidden', 'wp-user-frontend' ) + '</h2>' +
                    '<p class="wpuf-pro-modal-text">' + __( 'This form includes custom taxonomy fields from third-party plugins. These are Pro-only and are hidden in both the builder and frontend until WPUF Pro is activated.', 'wp-user-frontend' ) + '</p>' +
                    '</div>' +
                    '<div class="wpuf-pro-modal-right">' +
                    '<img src="' + assetUrl + '/images/event-pro-field.jpeg" alt="' + __( 'Event Pro Field preview', 'wp-user-frontend' ) + '">' +
                    '</div>' +
                    '</div>',
                icon: false,
                showCancelButton: false,
                showConfirmButton: true,
                confirmButtonColor: '#059669',
                confirmButtonText: __( 'Okay', 'wp-user-frontend' ),
                customClass: {
                    popup: 'wpuf-pro-taxonomy-warning',
                    confirmButton: 'wpuf-btn-primary',
                    icon: 'wpuf-warning-icon',
                },
                width: '1038px',
                padding: '36px',
            } );
        }, 500 );
    }

    const root = createRoot( container );
    root.render( <FormBuilder /> );
} );
