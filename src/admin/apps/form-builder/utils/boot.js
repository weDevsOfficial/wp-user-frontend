/**
 * The form builder's page globals, read in one place. Live getters: the
 * admin app swaps `wpuf_form_builder` when it opens another form without a
 * page load (index.jsx), and Pro adds keys to it.
 *
 * @since WPUF_SINCE
 */

/** @return {Object} `wpuf_form_builder` (fields, settings, i18n, urls). */
export const builderData = () => window.wpuf_form_builder || {};

/** @return {Object} `wpuf_admin_script` (nonces, admin strings). */
export const adminScript = () => window.wpuf_admin_script || {};

/** @return {string} `wpuf_admin_url`, '' when missing. */
export const adminUrl = () => window.wpuf_admin_url || '';
