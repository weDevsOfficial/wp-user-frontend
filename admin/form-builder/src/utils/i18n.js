/**
 * i18n Strategy for the React Form Builder
 *
 * Approach:
 * - Use @wordpress/i18n functions: __(), _x(), _n(), sprintf()
 * - Text domain: 'wp-user-frontend'
 * - All user-facing strings are wrapped with __()
 * - PHP side: wp_set_script_translations('wpuf-form-builder-react', 'wp-user-frontend')
 *   is called in Admin_Form_Builder::admin_enqueue_scripts()
 * - Legacy i18n strings from wpuf_form_builder.i18n remain available via
 *   the store's i18n state for backwards compatibility with Pro extensions
 *
 * Usage in components:
 *   import { __ } from '@wordpress/i18n';
 *   <label>{ __( 'Field Label', 'wp-user-frontend' ) }</label>
 */

export { __, _x, _n, sprintf } from '@wordpress/i18n';
