import { createRoot } from '@wordpress/element';
import { ScreenSlots, WpufProviders } from '@wpuf/components';
import deprecated from '@wordpress/deprecated';
import { addAction, doAction, removeAction } from '@wordpress/hooks';
import { BUILDER_SLOTS, settingsSlotName } from './slots';
import { dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { request, restPath } from '@wpuf/api';
import { STORE_NAME } from './store';
import { DEFAULT_STATE } from './store/reducer';
import { registerScreen } from '../../app/client';
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
import { hasRecaptchaApiKeys, hasTurnstileApiKeys } from './utils/globalHelpers';
import { fireRootInit } from './extensions/hooks';
import { registerFreeFieldPreviews } from './components/FieldPreview';
import { useFieldClasses, formatPrice } from './hooks/useFieldClasses';
import { isPhpTruthy, isRichEditor } from './utils/fieldUtils';
import HelpText from './components/FieldPreview/HelpText';
import SettingHelpText from './components/FieldSettings/inputs/SettingHelpText';
import LegacySlot from './common/LegacySlot';
import FormBuilder from './components/FormBuilder';
import { openHiddenTaxonomies } from './common/BuilderDialogs';
import { builderLoadError, builderSkeleton } from './skeleton';

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

    // A fresh builder: the admin app opens one builder after another without a
    // page load, so nothing of the previous form stays (selection, dirty flag).
    store.initializeState( { ...DEFAULT_STATE } );

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
// Preview rules shared with Pro's previews (rich editor as the frontend, PHP truthiness).
window.wpuf.isRichEditor = isRichEditor;
window.wpuf.isPhpTruthy = isPhpTruthy;
window.wpuf.formatPrice = formatPrice;
window.wpuf.HelpText = HelpText;
window.wpuf.SettingHelpText = SettingHelpText;
window.wpuf.LegacySlot = LegacySlot;
window.wpuf.builderSlots = { ...BUILDER_SLOTS, settings: settingsSlotName };

/**
 * Retired Vue globals (4.4g): window.wpuf_mixins stays defined and
 * wpuf_form_builder.event_hub forwards to wp.hooks actions
 * `wpuf.formBuilder.event.<name>`; both warn once (@wordpress/deprecated).
 */
function installRetiredGlobals( mixinsOverride ) {
    const builder = window.wpuf_form_builder || {};
    const eventAction = ( name ) => `wpuf.formBuilder.event.${ name }`;
    const warnHub = () => deprecated( 'wpuf_form_builder.event_hub', { alternative: 'wp.hooks actions wpuf.formBuilder.event.<name>', plugin: 'WP User Frontend' } );

    builder.event_hub = {
        $on( name, callback ) {
            warnHub();
            addAction( eventAction( name ), 'wpuf/event-hub', callback );
        },
        $emit( name, ...args ) {
            warnHub();
            doAction( eventAction( name ), ...args );
        },
        $off( name ) {
            warnHub();
            removeAction( eventAction( name ), 'wpuf/event-hub' );
        },
    };
    window.wpuf_form_builder = builder;

    let mixins = mixinsOverride || window.wpuf_mixins || {};

    try {
        Object.defineProperty( window, 'wpuf_mixins', {
            configurable: true,
            get() {
                deprecated( 'window.wpuf_mixins', { alternative: 'window.wpuf.registerFieldPreview / registerFieldSettingInput', plugin: 'WP User Frontend' } );
                return mixins;
            },
            // The admin app sets it again for the next builder.
            set( value ) {
                mixins = value || {};
            },
        } );
    } catch ( e ) {
        window.wpuf_mixins = mixins;
    }
}

/**
 * Start a builder in its mount element, from the data in the window globals.
 *
 * @param {HTMLElement} container The `#wpuf-form-builder-app` element.
 * @param {Object}      [mixins]  `wpuf_mixins` of this builder (admin app).
 *
 * @return {Object} React root.
 */
function startBuilder( container, mixins ) {
    installRetiredGlobals( mixins );
    initializeStore();
    registerFreeFieldPreviews();

    // Register built-in field validators
    registerFieldValidator( 'has_recaptcha_api_keys', hasRecaptchaApiKeys );
    registerFieldValidator( 'has_turnstile_api_keys', hasTurnstileApiKeys );

    // Once per opened builder: extensions register against this form's data.
    fireRootInit();

    // Show "Pro Fields Hidden" warning when form has custom taxonomy fields and Pro is not active
    const builderData = window.wpuf_form_builder || {};

    if ( builderData.has_hidden_taxonomies && ! builderData.is_pro_active ) {
        setTimeout( openHiddenTaxonomies, 500 );
    }

    const root = createRoot( container );
    // The shared layer (design.md D24): `host` keeps the builder's own markup
    // as it is; only the shared wrappers get the plugin-ui styles (D25).
    root.render(
        <WpufProviders host>
            { /* Slots + PluginArea scope `wpuf-form-builder` (4.4g, slots.js). */ }
            <ScreenSlots screen="form-builder">
                <FormBuilder />
            </ScreenSlots>
        </WpufProviders>
    );

    return root;
}

/**
 * Builder routes of the admin app, by form post type.
 */
const ROUTES = {
    wpuf_forms: { base: '/post-forms', page: 'wpuf-post-forms' },
    wpuf_profile: { base: '/registration-forms', page: 'wpuf-profile-forms' },
};

/**
 * The builder screen's form: `#wpuf-form-builder` with the mount element and
 * the hidden inputs the save reads (Admin_Form_Builder::form_attributes()).
 *
 * @param {Object} attributes `builder_form` of the builder boot.
 *
 * @return {Object} { form, mount }.
 */
function builderForm( attributes ) {
    const route = ROUTES[ attributes.post_type ] || ROUTES.wpuf_forms;
    const form = document.createElement( 'form' );
    const mount = document.createElement( 'div' );
    const hidden = ( name, value ) => {
        const input = document.createElement( 'input' );

        input.type = 'hidden';
        input.name = name;
        input.value = value;
        form.append( input );
    };

    form.id = 'wpuf-form-builder';
    form.className = `!wpuf-bg-white !wpuf-static !wpuf-w-[calc(100%+20px)] wpuf-ml-[-20px] !wpuf-p-0 wpuf-form-builder-${ attributes.form_type }`;
    form.method = 'post';
    form.setAttribute( 'action', '' );
    mount.id = 'wpuf-form-builder-app';
    form.append( mount );

    if ( attributes.form_settings_key ) {
        hidden( 'form_settings_key', attributes.form_settings_key );
    }

    hidden( 'wpuf_form_builder_nonce', attributes.nonce );
    hidden( '_wp_http_referer', `${ window.location.pathname }?page=${ route.page }&action=edit&id=${ attributes.form_id }` );
    hidden( 'wpuf_form_id', attributes.form_id );

    return { form, mount };
}

/**
 * A builder route of the admin app: a new form (created over REST, then its
 * edit route) or a form's builder (its data over REST, built as on the
 * builder screen).
 *
 * @param {HTMLElement} element Route element.
 * @param {Object}      context App context.
 *
 * @return {Function} Cleanup.
 */
function mountInApp( element, context ) {
    const type = context.route.formType || 'wpuf_forms';
    let root = null;
    let cancelled = false;

    // The builder's shape, shimmering, until the form arrives.
    element.append( builderSkeleton() );

    const open = async () => {
        let id = parseInt( context.params.id, 10 ) || 0;

        // A new form: created over REST, then this mount takes its edit route
        // (no remount, so one loading state).
        if ( ! id ) {
            const created = await request( restPath( 'wpuf/v1', '/admin/forms' ), { method: 'POST', data: { type } } );

            if ( cancelled ) {
                return;
            }

            id = parseInt( created.data.id, 10 );
            context.navigate( `${ ( ROUTES[ type ] || ROUTES.wpuf_forms ).base }/${ id }/edit`, { replace: true, adopt: true } );
        }

        const body = await request( restPath( 'wpuf/v1', `/admin/forms/${ id }/builder` ) );
        const data = body.data || {};
        const attributes = data.builder_form || {};

        if ( cancelled ) {
            return;
        }

        // A form of the other type: open it on its own route.
        if ( attributes.post_type && attributes.post_type !== type && ROUTES[ attributes.post_type ] ) {
            context.navigate( `${ ROUTES[ attributes.post_type ].base }/${ id }/edit`, { replace: true } );

            return;
        }

        window.wpuf_form_builder = data.wpuf_form_builder || {};
        window.wpuf_single_objects = data.wpuf_single_objects || [];

        const { form, mount } = builderForm( attributes );

        element.replaceChildren( form );
        root = startBuilder( mount, data.wpuf_mixins );
    };

    open().catch( ( error ) => {
        if ( ! cancelled ) {
            element.replaceChildren( builderLoadError( ( error && error.message ) || __( 'The form builder could not be loaded.', 'wp-user-frontend' ) ) );
        }
    } );

    return () => {
        cancelled = true;

        if ( root ) {
            root.unmount();
            dispatch( STORE_NAME ).initializeState( { ...DEFAULT_STATE } );
        }
    };
}

/**
 * Mount the builder: on its own page (the builder screen printed the form and
 * the data), or on a builder route of the admin app.
 */
registerScreen( 'form-builder', [ 'wpuf-form-builder-app' ], ( element, context ) => {
    if ( context ) {
        return mountInApp( element, context );
    }

    // After the bundles that depend on this one (Pro) have added their
    // rootInit listeners.
    let root = null;
    const start = () => {
        root = startBuilder( element );
    };

    if ( 'loading' === document.readyState ) {
        document.addEventListener( 'DOMContentLoaded', start );
    } else {
        start();
    }

    return () => root && root.unmount();
} );
