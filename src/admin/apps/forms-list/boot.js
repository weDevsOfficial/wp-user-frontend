/**
 * The forms list's page globals, read in one place; live getters.
 *
 * @since WPUF_SINCE
 */

/** @return {Object} `wpuf_admin_script` (nonces, admin strings). */
export const adminScript = () => window.wpuf_admin_script || {};

/** @return {Object} `wpuf_form_templates` (the template picker's data). */
export const formTemplates = () => window.wpuf_form_templates || {};
