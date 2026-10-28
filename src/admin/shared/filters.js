/**
 * Every `wp.hooks` name and SlotFill name the WPUF React admin fires, reads
 * or renders. These are public contracts: Pro and third-party code listen to
 * them, so a name never changes; new names are added here first.
 *
 * Runtime copy: `window.wpuf.filters` (admin-runtime entry).
 * Guard: shared/filters.test.js fails when source code uses a name that is
 * not listed here.
 */
import { applyFilters, doAction } from '@wordpress/hooks';

export const HOOKS = Object.freeze( {
    // Form builder (free fires, Pro listens).
    FORM_BUILDER_ROOT_INIT: 'wpuf.formBuilder.rootInit',
    FORM_BUILDER_BEFORE_SAVE: 'wpuf.formBuilder.beforeSave',
    FORM_BUILDER_AFTER_SAVE: 'wpuf.formBuilder.afterSave',
    FORM_BUILDER_PANEL_SECTIONS: 'wpuf.formBuilder.panelSections',
    FORM_BUILDER_FIELD_SETTINGS: 'wpuf.formBuilder.fieldSettings',
    FORM_BUILDER_FIELD_DEPENDENCIES: 'wpuf.formBuilder.fieldDependencies',
    FORM_BUILDER_FIELD_CSS_CLASSES: 'wpuf.formBuilder.fieldCssClasses',
    FORM_BUILDER_CANVAS_RENDER: 'wpuf.formBuilder.canvasRender',
    FORM_BUILDER_INTEGRATIONS: 'wpuf.formBuilder.integrations',
    FORM_BUILDER_SETTINGS_TABS: 'wpuf.formBuilder.settingsTabs',
    FORM_BUILDER_SETTINGS_FIELDS: 'wpuf.formBuilder.settingsFields',
    FORM_BUILDER_SETTINGS_ITEMS: 'wpuf.formBuilder.settingsItems',
    FORM_BUILDER_OPTION_DATA_BULK_ADD: 'wpuf.formBuilder.optionDataBulkAdd',
    FORM_BUILDER_INTEGRATION_CONDITIONS: 'wpuf.formBuilder.integrationConditions',
    // Settings screen: component per field type, `wpuf.settings.field.<type>`.
    SETTINGS_FIELD: 'wpuf.settings.field',

    // Forms list.
    FORMS_LIST_INIT: 'wpuf.formsList.init',
    FORMS_LIST_TABLE_COLUMNS: 'wpuf.formsList.tableColumns',
    FORMS_LIST_PAGE_TITLE: 'wpuf.formsList.pageTitle',
    FORMS_LIST_GET_SHORTCODE: 'wpuf.formsList.getShortcode',
    FORMS_LIST_SHORTCODE_RENDER: 'wpuf.formsList.shortcodeRender',
    FORMS_LIST_AI_FORM_BUILDER_AVAILABLE: 'wpuf.formsList.aiFormBuilderAvailable',

    // Admin app shell: after a route mounted ({ route, params, query }).
    ADMIN_ROUTE_CHANGED: 'wpuf.admin.routeChanged',

    // AI form builder.
    AI_FORM_BUILDER_INIT: 'wpuf.aiFormBuilder.init',
    AI_FORM_BUILDER_CREATE_FORM_DATA: 'wpuf.aiFormBuilder.createFormData',

    // Subscriptions.
    SUBSCRIPTION_INIT: 'wpuf.subscription.init',
    SUBSCRIPTION_ITEMS_LOADED: 'wpuf.subscription.itemsLoaded',
    SUBSCRIPTION_ITEM_SAVED: 'wpuf.subscription.itemSaved',
    SUBSCRIPTION_FORM_MOUNTED: 'wpuf.subscription.formMounted',
    SUBSCRIPTION_FORM_UNMOUNTED: 'wpuf.subscription.formUnmounted',
    SUBSCRIPTION_BEFORE_SAVE: 'wpuf.subscription.beforeSave',
    SUBSCRIPTION_AFTER_SAVE: 'wpuf.subscription.afterSave',
    SUBSCRIPTION_VALIDATE_FIELDS: 'wpuf.subscription.validateFields',
    SUBSCRIPTION_ITEM_BEFORE_SAVE: 'wpuf.subscription.itemBeforeSave',
    SUBSCRIPTION_BOX_MENU_ITEMS: 'wpuf.subscription.boxMenuItems',
    SUBSCRIPTION_BLANK_ITEM: 'wpuf.subscription.blankItem',
    SUBSCRIPTION_FIELD_COMPONENT: 'wpuf.subscription.fieldComponent',
} );

export const SLOTS = Object.freeze( {
    // Form builder (4.4g, apps/form-builder/slots.js); settings panels use
    // `wpuf-form-builder-settings-<tab>`.
    FORM_BUILDER_FIELD_OPTIONS_AFTER: 'wpuf-form-builder-field-options-after',
    FORM_BUILDER_OPTION_DATA_ACTIONS: 'wpuf-form-builder-option-data-actions',
    FORM_BUILDER_OPTION_DATA_AFTER: 'wpuf-form-builder-option-data-after',
    FORM_BUILDER_CANVAS_SUBMIT_AREA: 'wpuf-form-builder-canvas-submit-area',
    FORM_BUILDER_CANVAS_BOTTOM: 'wpuf-form-builder-canvas-bottom',
    SUBSCRIPTION_FORM_FOOTER: 'WpufSubscriptionFormFooter',
    SUBSCRIPTION_FORM_SIDEBAR: 'WpufSubscriptionFormSidebar',
    SUBSCRIPTION_TAB_CONTENT: 'WpufSubscriptionTabContent',
    SUBSCRIPTION_AFTER_SUBSECTION: 'WpufSubscriptionAfterSubsection',
    SUBSCRIPTION_LIST_ACTIONS: 'WpufSubscriptionListActions',
    SUBSCRIPTION_BOX_FOOTER: 'WpufSubscriptionBoxFooter',
} );

export const HOOK_NAMES = Object.freeze( Object.values( HOOKS ) );

/**
 * Warn in development when a name is not part of the contract.
 *
 * @param {string} name Hook name.
 */
function checkName( name ) {
    if ( 'production' !== process.env.NODE_ENV && ! HOOK_NAMES.includes( name ) ) {
        // eslint-disable-next-line no-console
        console.warn( `[wpuf] "${ name }" is not listed in shared/filters.js HOOKS.` );
    }
}

/**
 * `applyFilters` for a listed hook name.
 *
 * @param {string} name  One of HOOKS.
 * @param {*}      value Value to filter.
 * @param {...*}   args  Extra arguments.
 *
 * @return {*} Filtered value.
 */
export function applyWpufFilters( name, value, ...args ) {
    checkName( name );

    return applyFilters( name, value, ...args );
}

/**
 * `doAction` for a listed hook name.
 *
 * @param {string} name One of HOOKS.
 * @param {...*}   args Arguments.
 */
export function doWpufAction( name, ...args ) {
    checkName( name );
    doAction( name, ...args );
}
